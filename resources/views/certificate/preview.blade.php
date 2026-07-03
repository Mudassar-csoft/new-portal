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
        :root {
            --ink: #1d1a17;
            --muted: #4f4a43;
            --accent: #0c7eb8;
            --paper: #fbf7ec;
            --border: rgba(81, 63, 35, 0.36);
        }

        * {
            box-sizing: border-box;
        }

        html, body {
            margin: 0;
            padding: 0;
            min-height: 100%;
            background: #edf2f7;
            color: var(--ink);
        }

        body {
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

        .certificate-page-wrap {
            padding: 10px 24px 32px;
        }

        .certificate-page {
            width: 297mm;
            min-height: 210mm;
            margin: 0 auto;
            position: relative;
            overflow: hidden;
            background:
                radial-gradient(circle at top, rgba(255, 255, 255, 0.88), rgba(251, 247, 236, 0.98) 58%),
                linear-gradient(135deg, rgba(12, 126, 184, 0.08), rgba(177, 133, 44, 0.08));
            border: 14px solid rgba(145, 111, 39, 0.18);
            box-shadow: 0 28px 60px rgba(15, 23, 42, 0.14);
        }

        .certificate-page::before {
            content: '';
            position: absolute;
            inset: 22px;
            border: 2px solid var(--border);
            pointer-events: none;
        }

        .certificate-page::after {
            content: '';
            position: absolute;
            inset: 0;
            background:
                url('{{ $certificateBackground }}') center center / cover no-repeat;
            opacity: 0.18;
            pointer-events: none;
        }

        .certificate-inner {
            position: relative;
            z-index: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-start;
            min-height: 210mm;
            padding: 24mm 22mm 18mm;
            text-align: center;
        }

        .brand-mark {
            width: 96px;
            height: auto;
            margin-bottom: 16px;
        }

        .certificate-kicker {
            font-size: 13px;
            font-weight: 700;
            letter-spacing: 0.34em;
            text-transform: uppercase;
            color: #876a2c;
            margin: 0 0 16px;
        }

        .certificate-title {
            margin: 0;
            font-family: 'Cinzel', serif;
            font-size: 56px;
            font-weight: 700;
            line-height: 1.05;
            letter-spacing: 0.04em;
        }

        .certificate-subtitle {
            margin: 24px 0 0;
            font-size: 18px;
            color: var(--muted);
        }

        .student-name {
            margin: 20px 0 0;
            padding: 0 18px 10px;
            font-size: 31px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            border-bottom: 2px solid rgba(29, 26, 23, 0.75);
        }

        .completion-line {
            margin: 20px 0 0;
            font-size: 18px;
            color: var(--muted);
        }

        .program-title {
            margin: 26px 0 0;
            padding: 0 18px 10px;
            font-size: 28px;
            font-weight: 700;
            border-bottom: 2px solid rgba(29, 26, 23, 0.75);
        }

        .certificate-date-line {
            margin: 22px 0 0;
            font-size: 18px;
            color: var(--ink);
        }

        .certificate-meta {
            width: 100%;
            margin-top: auto;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            gap: 24px;
            padding-top: 34px;
        }

        .verification-block {
            text-align: left;
            font-size: 13px;
            line-height: 1.7;
        }

        .verification-block strong {
            display: block;
            margin-bottom: 4px;
        }

        .signature-block {
            min-width: 250px;
            padding-top: 12px;
            border-top: 2px solid rgba(29, 26, 23, 0.82);
            text-align: center;
            font-size: 13px;
            line-height: 1.6;
        }

        .signature-block strong {
            display: block;
            font-size: 15px;
        }

        @media print {
            @page {
                size: A4 landscape;
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

            .certificate-page-wrap {
                padding: 0;
            }

            .certificate-page {
                width: 297mm;
                min-height: 210mm;
                box-shadow: none;
                border-width: 12px;
                margin: 0;
            }
        }

        @media (max-width: 1100px) {
            .certificate-page-wrap {
                padding: 10px 12px 24px;
                overflow-x: auto;
            }

            .certificate-page {
                transform-origin: top center;
            }
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
            <div class="certificate-inner">
                <img src="{{ $certificateLogo }}" alt="Career Institute" class="brand-mark">
                <p class="certificate-kicker">Career Institute</p>
                <h1 class="certificate-title">Certificate of Achievement</h1>

                <p class="certificate-subtitle"><strong>Presented to:</strong></p>
                <div class="student-name">{{ $studentName }}</div>

                <p class="completion-line">for successfully completing the training course</p>
                <div class="program-title">{{ $programTitle }}</div>

                <p class="certificate-date-line">{{ $dateLine }}</p>

                <div class="certificate-meta">
                    <div class="verification-block">
                        <strong>For Verification, please visit: www.career.edu.pk</strong>
                        <div>Verification ID: {{ $admission->roll_number ?: $admission->registration_number ?: 'N/A' }}</div>
                        @if($admission->campus?->code)
                            <div>Campus: {{ $admission->campus->code }}</div>
                        @endif
                    </div>

                    <div class="signature-block">
                        <strong>Muhammad Adeel Javaid</strong>
                        <div>Founder and Chairman</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
