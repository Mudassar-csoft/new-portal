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
            box-sizing: border-box;
            margin: 0;
            padding: 0;
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
            z-index: 5;
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

        .certificate-stack {
            padding: 12px 20px 32px;
        }

        .certificate-sheet {
            width: 280mm;
            height: 209mm;
            margin: 0 auto;
            position: relative;
            overflow: hidden;
            background: url('{{ $certificateBackground }}') center center / cover no-repeat;
            box-shadow: 0 20px 40px rgba(15, 23, 42, 0.18);
        }

        .certificate-sheet + .certificate-sheet {
            margin-top: 18px;
        }

        .certificate-frame {
            position: relative;
            width: 100%;
            height: 100%;
            padding: 36mm 18mm 18mm;
        }

        .certificate-title {
            display: flex;
            justify-content: center;
            text-align: center;
            font-family: 'Cloister Black', 'Old English Text MT', 'UnifrakturCook', serif;
            font-size: 60px;
            line-height: 1;
            letter-spacing: 2px;
            font-weight: 400;
        }

        .certificate-pre {
            display: flex;
            justify-content: center;
            margin-top: 0.5in;
            font-size: 16px;
            font-weight: 600;
        }

        .certificate-name {
            display: flex;
            justify-content: center;
            margin-top: 0.2in;
            text-align: center;
        }

        .certificate-name span {
            display: inline-block;
            padding: 0 8px 8px;
            border-bottom: 2px solid #000;
            font-size: 25px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: -1px;
        }

        .certificate-line {
            display: flex;
            justify-content: center;
            margin-top: 0.2in;
            text-align: center;
            font-size: 16px;
            font-weight: 400;
        }

        .certificate-course {
            display: flex;
            justify-content: center;
            margin-top: 0.3in;
            text-align: center;
        }

        .certificate-course span {
            display: inline-block;
            padding: 0 8px 8px;
            border-bottom: 2px solid #000;
            font-size: 25px;
            font-weight: 800;
            letter-spacing: -1px;
            max-width: 76%;
        }

        .certificate-date {
            display: flex;
            justify-content: center;
            margin-top: 0.18in;
            text-align: center;
            font-size: 16px;
            font-weight: 400;
        }

        .certificate-footer {
            position: absolute;
            left: 20px;
            right: 50px;
            bottom: 24px;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            gap: 30px;
            font-size: 12px;
        }

        .certificate-footer-left {
            line-height: 1.45;
        }

        .certificate-footer-right {
            min-width: 235px;
            padding-top: 4px;
            border-top: 2px solid #000;
            text-align: center;
            line-height: 1.45;
        }

        .certificate-footer-right strong,
        .certificate-footer-left strong {
            font-weight: 800;
        }

        @media print {
            @page {
                size: 280mm 209mm;
                margin: 0;
            }

            body {
                background: #fff;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .preview-toolbar {
                display: none;
            }

            .certificate-stack {
                padding: 0;
            }

            .certificate-sheet {
                margin: 0;
                box-shadow: none;
                page-break-after: always;
                break-after: page;
            }

            .certificate-sheet:last-child {
                page-break-after: auto;
                break-after: auto;
            }
        }

        @media (max-width: 1200px) {
            .certificate-stack {
                overflow-x: auto;
                padding-left: 12px;
                padding-right: 12px;
            }

            .certificate-sheet {
                transform-origin: top center;
            }
        }
    </style>
</head>
<body>
    <div class="preview-toolbar">
        <a href="{{ $backUrl ?? route('certificate.index', ['scope' => 'printing']) }}">Back</a>
        <button type="button" onclick="window.print()">Print</button>
    </div>

    <div class="certificate-stack">
        @foreach(($previewItems ?? collect()) as $preview)
            @php
                $admission = $preview['admission'];
                $studentName = $preview['studentName'];
                $programTitle = $preview['programTitle'];
                $dateLine = $preview['dateLine'];
            @endphp
            <section class="certificate-sheet">
                <div class="certificate-frame">
                    <div class="certificate-title">Certificate of Achievement</div>

                    <div class="certificate-pre">
                        <strong>Presented to:</strong>
                    </div>

                    <div class="certificate-name">
                        <span>{{ $studentName }}</span>
                    </div>

                    <div class="certificate-line">
                        <span>for successfully completing the training course</span>
                    </div>

                    <div class="certificate-course">
                        <span>{{ $programTitle }}</span>
                    </div>

                    <div class="certificate-date">
                        <span>{{ $dateLine }}</span>
                    </div>

                    <div class="certificate-footer">
                        <div class="certificate-footer-left">
                            <div><strong>For Verification, please visit: www.career.edu.pk</strong></div>
                            <div>Verification ID: {{ $admission->roll_number ?: $admission->registration_number ?: 'N/A' }}</div>
                        </div>

                        <div class="certificate-footer-right">
                            <div><strong>Muhammad Adeel Javaid</strong></div>
                            <div>Founder and Chairman</div>
                        </div>
                    </div>
                </div>
            </section>
        @endforeach
    </div>
</body>
</html>
