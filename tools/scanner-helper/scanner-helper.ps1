param(
    [int]$Port = 18777,
    [string]$BindHost = '127.0.0.1',
    [string]$LogPath = ''
)

Set-StrictMode -Version Latest
$ErrorActionPreference = 'Stop'

if ([string]::IsNullOrWhiteSpace($LogPath)) {
    $LogPath = Join-Path $PSScriptRoot 'scanner-helper.log'
}

$prefix = 'http://{0}:{1}/' -f $BindHost, $Port
$jpegFormatId = '{B96B3CAF-0728-11D3-9D7B-0000F81EF32E}'

function Write-HelperLog {
    param([string]$Message)

    $timestamp = Get-Date -Format 'yyyy-MM-dd HH:mm:ss'
    Add-Content -LiteralPath $LogPath -Value ('[{0}] {1}' -f $timestamp, $Message)
}

function Add-CorsHeaders {
    param($Response)

    $Response.Headers['Access-Control-Allow-Origin'] = '*'
    $Response.Headers['Access-Control-Allow-Methods'] = 'GET, POST, OPTIONS'
    $Response.Headers['Access-Control-Allow-Headers'] = 'Content-Type'
    $Response.Headers['Access-Control-Expose-Headers'] = 'X-Scan-File-Name, X-Scan-Device'
}

function Send-JsonResponse {
    param(
        $Response,
        [int]$StatusCode,
        $Payload
    )

    $json = $Payload | ConvertTo-Json -Depth 8 -Compress
    $bytes = [System.Text.Encoding]::UTF8.GetBytes($json)

    Add-CorsHeaders -Response $Response
    $Response.StatusCode = $StatusCode
    $Response.ContentType = 'application/json; charset=utf-8'
    $Response.ContentLength64 = $bytes.Length
    $Response.OutputStream.Write($bytes, 0, $bytes.Length)
    $Response.OutputStream.Close()
}

function Send-BinaryResponse {
    param(
        $Response,
        [byte[]]$Bytes,
        [string]$ContentType,
        [string]$FileName,
        [string]$DeviceName
    )

    Add-CorsHeaders -Response $Response
    $Response.StatusCode = 200
    $Response.ContentType = $ContentType
    $Response.Headers['X-Scan-File-Name'] = $FileName
    $Response.Headers['X-Scan-Device'] = $DeviceName
    $Response.ContentLength64 = $Bytes.Length
    $Response.OutputStream.Write($Bytes, 0, $Bytes.Length)
    $Response.OutputStream.Close()
}

function Get-ScannerDevices {
    $manager = New-Object -ComObject WIA.DeviceManager
    $devices = @()

    foreach ($deviceInfo in @($manager.DeviceInfos)) {
        try {
            $name = [string]$deviceInfo.Properties.Item('Name').Value
        } catch {
            $name = [string]$deviceInfo.DeviceID
        }

        if ([int]$deviceInfo.Type -eq 1) {
            $devices += [PSCustomObject]@{
                name = $name
                deviceId = [string]$deviceInfo.DeviceID
                type = [int]$deviceInfo.Type
            }
        }
    }

    return $devices
}

function Resolve-ScannerDeviceInfo {
    param([string]$RequestedDeviceId)

    $manager = New-Object -ComObject WIA.DeviceManager
    $scannerInfos = @($manager.DeviceInfos) | Where-Object { [int]$_.Type -eq 1 }

    if ($RequestedDeviceId) {
        $matched = $scannerInfos | Where-Object { [string]$_.DeviceID -eq $RequestedDeviceId } | Select-Object -First 1
        if ($matched) {
            return $matched
        }
    }

    return $scannerInfos | Select-Object -First 1
}

function Invoke-WiaScan {
    param([string]$RequestedDeviceId)

    $deviceInfo = Resolve-ScannerDeviceInfo -RequestedDeviceId $RequestedDeviceId
    if (-not $deviceInfo) {
        throw 'No WIA scanner device was found on this Windows machine.'
    }

    $deviceName = try {
        [string]$deviceInfo.Properties.Item('Name').Value
    } catch {
        [string]$deviceInfo.DeviceID
    }

    $device = $deviceInfo.Connect()
    if (-not $device) {
        throw ('Unable to connect to scanner: {0}' -f $deviceName)
    }

    if ($device.Items.Count -lt 1) {
        throw ('Scanner does not expose a scan item: {0}' -f $deviceName)
    }

    $item = $device.Items.Item(1)
    if (-not $item) {
        throw ('Scanner item is not available: {0}' -f $deviceName)
    }

    $tempPath = Join-Path ([System.IO.Path]::GetTempPath()) ('crm-scan-' + [Guid]::NewGuid().ToString('N') + '.jpg')

    try {
        $imageFile = $item.Transfer($jpegFormatId)
        $imageFile.SaveFile($tempPath)

        return [PSCustomObject]@{
            deviceName = $deviceName
            fileName = 'scan-' + (Get-Date -Format 'yyyyMMdd-HHmmss') + '.jpg'
            contentType = 'image/jpeg'
            bytes = [System.IO.File]::ReadAllBytes($tempPath)
        }
    } finally {
        if (Test-Path -LiteralPath $tempPath) {
            Remove-Item -LiteralPath $tempPath -Force -ErrorAction SilentlyContinue
        }
    }
}

function Read-RequestPayload {
    param($Request)

    if (-not $Request.HasEntityBody) {
        return @{}
    }

    $reader = [System.IO.StreamReader]::new($Request.InputStream, $Request.ContentEncoding)
    try {
        $body = $reader.ReadToEnd()
    } finally {
        $reader.Dispose()
    }

    if ([string]::IsNullOrWhiteSpace($body)) {
        return @{}
    }

    try {
        return $body | ConvertFrom-Json -AsHashtable
    } catch {
        return @{}
    }
}

$listener = [System.Net.HttpListener]::new()
$listener.Prefixes.Add($prefix)

try {
    $listener.Start()
} catch {
    Write-HelperLog ('Failed to start listener on {0}: {1}' -f $prefix, $_.Exception.Message)
    throw
}

Write-HelperLog ('Scanner helper started at {0}' -f $prefix)

try {
    while ($listener.IsListening) {
        try {
            $context = $listener.GetContext()
        } catch {
            if ($listener.IsListening) {
                Write-HelperLog ('Listener error: {0}' -f $_.Exception.Message)
            }
            break
        }

        $request = $context.Request
        $response = $context.Response
        $path = $request.Url.AbsolutePath.TrimEnd('/')

        try {
            if ($request.HttpMethod -eq 'OPTIONS') {
                Add-CorsHeaders -Response $response
                $response.StatusCode = 204
                $response.OutputStream.Close()
                continue
            }

            switch ("$($request.HttpMethod) $path") {
                'GET /health' {
                    $devices = @(Get-ScannerDevices)
                    Send-JsonResponse -Response $response -StatusCode 200 -Payload @{
                        ok = $true
                        host = $BindHost
                        port = $Port
                        devices = $devices
                    }
                    continue
                }
                'POST /scan' {
                    $payload = Read-RequestPayload -Request $request
                    $requestedDeviceId = ''
                    if ($payload.ContainsKey('deviceId') -and $null -ne $payload['deviceId']) {
                        $requestedDeviceId = [string]$payload['deviceId']
                    }
                    $scanResult = Invoke-WiaScan -RequestedDeviceId $requestedDeviceId
                    Write-HelperLog ('Scan completed via device: {0}' -f $scanResult.deviceName)
                    Send-BinaryResponse -Response $response -Bytes $scanResult.bytes -ContentType $scanResult.contentType -FileName $scanResult.fileName -DeviceName $scanResult.deviceName
                    continue
                }
                default {
                    Send-JsonResponse -Response $response -StatusCode 404 -Payload @{
                        ok = $false
                        message = 'Route not found.'
                    }
                    continue
                }
            }
        } catch {
            $message = $_.Exception.Message
            Write-HelperLog ('Request failed for {0} {1}: {2}' -f $request.HttpMethod, $request.Url.AbsolutePath, $message)
            Send-JsonResponse -Response $response -StatusCode 500 -Payload @{
                ok = $false
                message = $message
            }
        }
    }
} finally {
    if ($listener.IsListening) {
        $listener.Stop()
    }

    $listener.Close()
    Write-HelperLog 'Scanner helper stopped.'
}
