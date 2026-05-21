@php
    $voucherUrls = array_values(array_filter((array) ($voucherUrls ?? [])));
    $voucherUrl = $voucherUrl ?? '';
    if ($voucherUrl !== '' && $voucherUrls === []) {
        $voucherUrls = [$voucherUrl];
    }
    $primaryVoucherUrl = $voucherUrls[0] ?? $voucherUrl;
    $redirectUrl = $redirectUrl ?? url('/');
    $heading = $heading ?? 'Saved';
    $message = $message ?? 'Opening voucher in a new tab...';
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $heading }}</title>
    <style>
        body { font-family: sans-serif; background: #f5f7fa; color: #1f2d3d; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; }
        .card { background: #fff; padding: 28px 36px; border-radius: 10px; box-shadow: 0 10px 30px rgba(0,0,0,.08); text-align: center; max-width: 460px; }
        h2 { margin: 0 0 8px; }
        a { color: #0a96cc; text-decoration: none; }
        a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="card">
        <h2>{{ $heading }}</h2>
        <p>{{ $message }}</p>
        <p style="font-size: 13px; color: #54667a;">
            If the voucher didn't open,
            <a href="{{ $primaryVoucherUrl }}" target="_blank" rel="noopener">click here</a>.
            Otherwise you'll be redirected shortly.
        </p>
    </div>
    <script>
        (function () {
            try {
                var urls = @json($voucherUrls);
                if (!urls.length) {
                    window.location.href = @json($redirectUrl);
                    return;
                }

                var firstWindow = window.open(urls[0], '_blank');
                for (var i = 1; i < urls.length; i++) {
                    window.open(urls[i], '_blank');
                }

                if (!firstWindow) {
                    window.location.href = @json($primaryVoucherUrl);
                    return;
                }
            } catch (e) { /* popup blocked - fall through */ }
            setTimeout(function () {
                window.location.href = @json($redirectUrl);
            }, 400);
        })();
    </script>
</body>
</html>
