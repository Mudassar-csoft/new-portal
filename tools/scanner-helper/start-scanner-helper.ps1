param(
    [int]$Port = 18777,
    [string]$BindHost = '127.0.0.1'
)

Set-StrictMode -Version Latest
$ErrorActionPreference = 'Stop'

$helperScript = Join-Path $PSScriptRoot 'scanner-helper.ps1'
$logPath = Join-Path $PSScriptRoot 'scanner-helper.log'

$existing = Get-CimInstance Win32_Process |
    Where-Object {
        $_.Name -match '^powershell' -and
        $_.CommandLine -and
        $_.CommandLine.Contains($helperScript)
    } |
    Select-Object -First 1

if ($existing) {
    Write-Output ('Scanner helper is already running. PID: {0}' -f $existing.ProcessId)
    exit 0
}

$arguments = @(
    '-NoProfile',
    '-ExecutionPolicy', 'Bypass',
        '-File', ('"{0}"' -f $helperScript),
        '-Port', $Port,
        '-BindHost', ('"{0}"' -f $BindHost),
        '-LogPath', ('"{0}"' -f $logPath)
) -join ' '

Start-Process -FilePath 'powershell.exe' -ArgumentList $arguments -WindowStyle Hidden

Start-Sleep -Seconds 2

try {
    $health = Invoke-WebRequest -Uri ('http://{0}:{1}/health' -f $BindHost, $Port) -UseBasicParsing -TimeoutSec 5
    Write-Output ('Scanner helper started successfully on http://{0}:{1}.' -f $BindHost, $Port)
    Write-Output $health.Content
} catch {
    Write-Output 'Scanner helper process was started, but health check did not respond yet.'
    Write-Output ('Check log: {0}' -f $logPath)
}
