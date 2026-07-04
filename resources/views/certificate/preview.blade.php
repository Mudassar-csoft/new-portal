@php
    $certificateBackground = file_exists(public_path('img/clogo.jpg'))
        ? asset('img/clogo.jpg')
        : asset('theme/img/logo-career.webp');

    $certificateLogo = file_exists(public_path('img/clogo.png'))
        ? asset('img/clogo.png')
        : asset('theme/img/logo-career.webp');
@endphp

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificate Preview</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@500;700&family=Montserrat:wght@400;600;700;800&display=swap" rel="stylesheet">
  <style>
:root{
    --primary:#0b75b7;
    --text:#222;
    --muted:#666;
    --border:#333;
}

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
}

html,
body{
    width:100%;
    height:100%;
    background:#edf2f7;
    font-family:'Montserrat',sans-serif;
    color:var(--text);
}

@page{
    size:30cm 21cm;
    margin:0;
}

@media print{

    body{
        background:#fff;
        -webkit-print-color-adjust:exact;
        print-color-adjust:exact;
    }

    .preview-toolbar{
        display:none;
    }

    .certificate-page-wrap{
        padding:0;
    }

    .certificate-page{
        margin:0;
        box-shadow:none;
    }

}

.preview-toolbar{
    display:flex;
    justify-content:flex-end;
    gap:12px;
    padding:18px 25px;
    background:#edf2f7;
}

.preview-toolbar a,
.preview-toolbar button{

    padding:10px 20px;
    border-radius:30px;
    border:none;
    cursor:pointer;
    text-decoration:none;
    font-weight:600;
}

.preview-toolbar a{
    background:#fff;
    color:#222;
    border:1px solid #ddd;
}

.preview-toolbar button{
    background:#0b75b7;
    color:#fff;
}

.certificate-page-wrap{
    display:flex;
    justify-content:center;
    padding:20px;
}

.certificate-page{

    width:30cm;
    height:21cm;
    background:#fff;
    position:relative;
    overflow:hidden;

    border:2px solid #222;

    box-shadow:0 10px 25px rgba(0,0,0,.15);

}

.certificate-page::before{

    content:"";
    position:absolute;
    top:12px;
    left:12px;
    right:12px;
    bottom:12px;

    border:1px solid #888;

    pointer-events:none;

}

.certificate-watermark{

    position:absolute;
    inset:0;

    background:url('{{ $certificateBackground }}') center center no-repeat;
    background-size:45%;

    opacity:.06;

}

.top-banner{

    position:absolute;
    top:0;
    right:0;
    width:260px;
    z-index:2;

}

.top-banner img{

    width:100%;
    display:block;

}

.certificate-inner{

    position:relative;
    z-index:5;

    height:100%;

    display:flex;
    flex-direction:column;
    align-items:center;

    text-align:center;

    padding:40px 60px;

}

.logo-area{

    margin-top:20px;

}

.brand-mark{

    width:100px;

}

.certificate-title{

    margin-top:20px;

    font-family:'Cinzel',serif;
    font-size:46px;
    font-weight:700;

}

.presented-text{

    margin-top:25px;

    font-size:18px;

    color:var(--muted);

}

.student-name{

    margin-top:12px;

    padding-bottom:8px;

    min-width:420px;

    border-bottom:2px solid #222;

    font-size:30px;
    font-weight:700;
    text-transform:uppercase;

}

.completion-line{

    margin-top:25px;

    font-size:18px;

    color:var(--muted);

}

.program-title{

    margin-top:12px;

    padding-bottom:8px;

    min-width:500px;

    border-bottom:2px solid #222;

    font-size:26px;
    font-weight:700;

}

.certificate-date-line{

    margin-top:25px;

    font-size:18px;

}

.partner-logos{

    margin-top:auto;
    margin-bottom:25px;

}

.partner-logos img{

    width:80%;

}

.certificate-footer{

    width:100%;

    display:flex;
    justify-content:space-between;
    align-items:flex-end;

}

.verification-block{

    text-align:left;
    font-size:13px;
    line-height:1.7;

}

.signature-block{

    text-align:center;

}

.signature{

    width:130px;
    margin-bottom:5px;

}

.founder-name{

    font-weight:700;
    font-size:15px;

}

.founder-title{

    font-size:13px;

}
</style>
</head>
<body>

    <div class="preview-toolbar">
        <a href="{{ route('certificate.index', ['scope' => 'printing']) }}">Back</a>
        <button type="button" onclick="window.print()">Print</button>
    </div>

    <div class="certificate-page-wrap">

        <div class="certificate-page">

            <!-- Top Right Banner -->
            <div class="top-banner">
                <img src="{{ asset('img/header.png') }}" alt="Header">
            </div>

            <!-- Background Watermark -->
            <div class="certificate-watermark"></div>

            <div class="certificate-inner">

                <!-- Logo -->
                <div class="logo-area">
                    <img src="{{ $certificateLogo }}" class="brand-mark" alt="Career Institute">
                </div>

                <!-- Certificate Title -->
                <h1 class="certificate-title">
                    Certificate of Achievement
                </h1>

                <!-- Presented To -->
                <div class="presented-text">
                    Presented to:
                </div>

                <div class="student-name">
                    {{ strtoupper($studentName) }}
                </div>

                <!-- Completion Text -->
                <div class="completion-line">
                    for successfully completing the training course
                </div>

                <!-- Course Name -->
                <div class="program-title">
                    {{ $programTitle }}
                </div>

                <!-- Date -->
                <div class="certificate-date-line">
                    {{ $dateLine }}
                </div>

                <!-- Partner Logos -->
                <div class="partner-logos">
                    <img src="{{ asset('img/partners.png') }}" alt="Partners">
                </div>

                <!-- Footer -->
                <div class="certificate-footer">

                    <div class="verification-block">

                        <strong>
                            For Verification Please Visit:
                        </strong>

                        <div>
                            www.career.edu.pk
                        </div>

                        <div>
                            Verification ID :
                            {{ $admission->roll_number ?: $admission->registration_number ?: 'N/A' }}
                        </div>

                        @if($admission->campus?->code)
                            <div>
                                Campus :
                                {{ $admission->campus->code }}
                            </div>
                        @endif

                    </div>

                    <div class="signature-block">

                        <img src="{{ asset('img/signature.png') }}" class="signature" alt="Signature">

                        <div class="founder-name">
                            Muhammad Adeel Javaid
                        </div>

                        <div class="founder-title">
                            Founder And Chairman
                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

</body>
</html>
