@php
    $certificateBackground = file_exists(public_path('img/clogo.jpg'))
        ? asset('img/clogo.jpg')
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
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@600;800&family=UnifrakturCook:wght@700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html, body {
            width: 100%;
            min-height: 100%;
        }

        body {
            background: #edf2f7;
            color: #000;
            font-family: 'Montserrat', sans-serif;
        }

        .preview-toolbar {
            position: sticky;
            top: 0;
            z-index: 10;
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            padding: 18px 24px;
            background: rgba(237, 242, 247, 0.96);
            backdrop-filter: blur(8px);
        }

        .preview-toolbar button,
        .preview-toolbar a {
            border: 0;
            border-radius: 999px;
            padding: 10px 18px;
            font: inherit;
            font-weight: 700;
            cursor: pointer;
            text-decoration: none;
        }

        .preview-toolbar button {
            background: #1596df;
            color: #fff;
        }

        .preview-toolbar a {
            background: #fff;
            color: #24415d;
            border: 1px solid #d5dee8;
        }

        .page-stack {
            padding: 12px 20px 32px;
        }

        .page {
            width: 297mm;
            height: 210mm;
            margin: 0 auto;
            overflow: hidden;
            background: url('{{ $certificateBackground }}') left top / 100% 100% no-repeat;
            box-shadow: 0 20px 40px rgba(15, 23, 42, 0.18);
        }

        .page + .page {
            margin-top: 18px;
        }

        .header {
            height: 1.5in;
            width: 100%;
        }

        .c_title {
            width: 100%;
            display: flex;
            justify-content: center;
            margin-top: 7mm;
            margin-left: 5px;
            font-family: 'Cloister Black', 'Old English Text MT', 'UnifrakturCook', serif;
            font-size: 60px;
            font-weight: 400;
            letter-spacing: 2px;
            line-height: 1;
        }

        .c_title h1 {
            font-family: 'Cloister Black', 'Old English Text MT', 'UnifrakturCook', serif;
            font-size: 60px;
            font-weight: 400;
            margin-right: 40px !important;
            margin-left: 5px;
            line-height: 1;
        }

        .pre {
            display: flex;
            justify-content: center;
            margin-right: 40px !important;
            margin-left: 5px;
            font-family: 'Montserrat', sans-serif;
            transform: translateY(-4mm);
        }

        .pre p {
            margin-top: 0.5in;
            font-size: 16px;
        }

        .s_name {
            display: flex;
            justify-content: center;
            margin-right: 40px !important;
            margin-left: 5px;
            font-family: 'Montserrat', sans-serif;
            font-weight: 800;
            transform: translateY(-4mm);
        }

        .s_name h1 {
            margin-top: 0.2in;
            padding: 0 8px 8px;
            border-bottom: 2px solid #000;
            font-size: 25px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: -1px;
            line-height: 1.15;
            text-align: center;
        }

        .t_line {
            display: flex;
            justify-content: center;
            margin-right: 40px !important;
            margin-left: 5px;
            font-family: sans-serif;
            transform: translateY(-4mm);
        }

        .t_line p {
            margin-top: 0.2in;
            font-size: 16px;
            font-weight: 400;
            text-align: center;
        }

        .c_name {
            display: flex;
            justify-content: center;
            margin-left: 5px;
            font-family: 'Montserrat', sans-serif;
            font-weight: 800;
            transform: translateY(-4mm);
        }

        .c_name h1 {
            margin-top: 0.3in;
            margin-right: 40px !important;
            margin-left: 5px;
            padding: 0 8px 8px;
            border-bottom: 2px solid #000;
            font-size: 25px;
            font-weight: 800;
            letter-spacing: -1px;
            line-height: 1.15;
            text-align: center;
            max-width: 70%;
        }

        .footer {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            margin-top: 2in;
            margin-left: 20px;
            margin-right: 50px;
            font-family: sans-serif;
            font-size: 12px;
            gap: 28px;
        }

        .left {
            line-height: 1.4;
        }

        .right {
            min-width: 250px;
            padding-top: 4px;
            border-top: 2px solid #000;
            line-height: 1.4;
            text-align: center;
        }

        @media print {
            @page {
                size: A4 landscape;
                margin: 0;
            }

            html, body {
                width: 297mm !important;
                height: 210mm !important;
                overflow: hidden !important;
            }

            body {
                background: #fff;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .preview-toolbar {
                display: none;
            }

            .page-stack {
                padding: 0;
                margin: 0;
                width: 100%;
                min-width: 0;
                max-width: none;
            }

            .page {
                width: 297mm;
                min-width: 297mm;
                max-width: 297mm;
                height: 210mm;
                min-height: 210mm;
                max-height: 210mm;
                margin: 0 auto;
                box-shadow: none;
                page-break-inside: avoid;
                break-inside: avoid;
            }

            .page:not(:last-child) {
                page-break-after: always;
                break-after: page;
            }
        }

        @media (max-width: 1200px) {
            .page-stack {
                overflow-x: auto;
                padding-left: 12px;
                padding-right: 12px;
            }
        }
    </style>
</head>
<body>
    <div class="preview-toolbar">
        <a href="{{ $backUrl ?? route('certificate.index', ['scope' => 'printing']) }}">Back</a>
        <button type="button" onclick="window.print()">Print</button>
    </div>

    <div class="page-stack">
        @foreach(($previewItems ?? collect()) as $preview)
            @php
                $admission = $preview['admission'];
                $studentName = $preview['studentName'];
                $programTitle = $preview['programTitle'];
                $dateLine = $preview['dateLine'];
            @endphp
            <section class="page">
                <div class="header"></div>

                <div class="c_title">
                    <h1>Certificate of Achievement</h1>
                </div>

                <div class="pre">
                    <p><strong>Presented to:</strong></p>
                </div>

                <div class="s_name">
                    <h1>{{ $studentName }}</h1>
                </div>

                <div class="t_line">
                    <p>for successfully completing the training course</p>
                </div>

                <div class="c_name">
                    <h1>{{ $programTitle }}</h1>
                </div>

                <div class="t_line">
                    <p>{{ $dateLine }}</p>
                </div>

                <div class="footer">
                    <div class="left">
                        <p><strong>For Verification, please visit: www.career.edu.pk</strong></p>
                        <p>Verification ID: {{ $admission->roll_number ?: $admission->registration_number ?: 'N/A' }}</p>
                    </div>
                    <div class="right">
                        <p><strong>Muhammad Adeel Javaid</strong></p>
                        <p>Founder and Chairman</p>
                    </div>
                </div>
            </section>
        @endforeach
    </div>
</body>
</html>
