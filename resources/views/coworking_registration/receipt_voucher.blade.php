@php
    $registration = $receipt->coworkingRegistration;
    $campus = $receipt->campus ?? $registration?->campus;
    $receiptTitle = match ($receipt->receipt_type) {
        'security_fee' => 'Security Fee Slip',
        'security_refund' => 'Security Refund Receipt',
        default => 'Coworking Charges Receipt',
    };
    $paidDate = optional($receipt->paid_at ?? $registration?->registration_date)->format('d-m-Y');
    $receiptTypeLabel = match ($receipt->receipt_type) {
        'security_fee' => 'Security Fee',
        'security_refund' => 'Security Refund',
        default => 'Coworking Charges',
    };
@endphp<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Security Fee Voucher</title>

<style>
    *{
        margin:0;
        padding:0;
        box-sizing:border-box;
    }

    body{
        background:#f2f2f2;
        padding:20px;
    }

    .voucher{
        width:1000px;
        margin:20px auto;
        background:#fff;
        border:2px solid #000;
        padding:18px;
    }

    .copy-title{
        text-align:center;
        font-size:22px;
        margin-bottom:10px;
    }

    .top-section{
        display:flex;
        justify-content:space-between;
        align-items:flex-start;
        margin-bottom:15px;
    }

    .logo-area{
        display:flex;
        gap:15px;
        align-items:flex-start;
    }

    .logo{
        width:90px;
        height:90px;
        background:#0c6b7a;
        color:#fff;
        display:flex;
        align-items:center;
        justify-content:center;
        border-radius:6px;
        font-size:22px;
        font-weight:bold;
    }

    .institute-text{
        line-height:1.5;
        font-size:18px;
        font-weight:bold;
        color:#333;
    }

    .voucher-heading{
        text-align:left;
    }

    .voucher-heading h1{
        font-size:42px;
        font-weight:500;
        margin-bottom:8px;
    }

    .voucher-heading p{
        font-size:14px;
        margin:3px 0;
    }

    table{
        width:100%;
        border-collapse:collapse;
        margin-bottom:15px;
    }

    table th,
    table td{
        border:1px solid #000;
        padding:10px;
        text-align:left;
        font-size:18px;
    }

    table th{
        font-weight:bold;
    }

    .amount-table{
        width:50%;
    }

    .amount-words{
        border:1px solid #000;
        padding:10px;
        font-size:18px;
        margin-top:10px;
    }

    .note-section{
        display:flex;
        justify-content:space-between;
        margin-top:20px;
        font-size:13px;
        color:#777;
    }

    .footer{
        margin-top:60px;
        font-size:15px;
        line-height:1.6;
    }
    img.brand-logo {
        width: 320px;
    height: 80px;
    }
    @media print{
        body{
            background:#fff;
        }

        .voucher{
            box-shadow:none;
            page-break-after:always;
        }
    }
</style>
</head>

<body>

<!-- MEMBER COPY -->
<div class="voucher">

    <div class="copy-title">Member Copy</div>

    <div class="top-section">

        <div class="logo-area">

            <img src="{{ asset('theme/img/career-updated-logo.png') }}" alt="Career Institute Logo" class="brand-logo">    
            
        </div>

        <div class="voucher-heading">
            <h3>Payment VOUCHER</h3>

            <p><strong>Receipt No:</strong> CIFSD02-SEC-0526-00001</p>
            <p><strong>Date:</strong> 22-05-2026</p>
            <p><strong>Registration No:</strong> CIFSD02-CWS-0526-00001</p>
            <p><strong>Campus:</strong> CIFSD02 - fsd Campus</p>
        </div>

    </div>

    <table>
        <tr>
            <th>Member Name</th>
            <th>Receipt Type</th>
            <th>Timing</th>
            <th>Date of Joining</th>
        </tr>

        <tr>
            <td>web</td>
            <td>Security Fee</td>
            <td>09:00AM-09:00AM</td>
            <td>22-05-2026</td>
        </tr>
    </table>

    <table class="amount-table">

        <tr>
            <td>Registration No</td>
            <td>CIFSD02-CWS-0526-00001</td>
        </tr>

        <tr>
            <td>Remarks</td>
            <td>Security fee collected at the time of coworking registration.</td>
        </tr>

        <tr>
            <td>Total Paid</td>
            <td>Rs. 20,000</td>
        </tr>

    </table>

    <div class="amount-words">
        Amount in Words: Twenty Thousand Only.
    </div>

    <div class="note-section">
        <div>
            This receipt can be produce when demanded <br>
            Fee once paid is not Refundable.
        </div>

        <div>
            For Career Institute - fsd Campus
        </div>
    </div>

    <div class="footer">
        Career Institute, P-165 B, 262 Millat Rd, Millat Chowk,
        Gulistan Colony, Faisalabad, Punjab, Pakistan - 38000 <br>

        Ph:0314-4444010 / 0341-4444010 <br>

        www.career.edu.pk
    </div>

</div>


<!-- CAMPUS COPY -->
<div class="voucher">

    <div class="copy-title">Campus Copy</div>

    <div class="top-section">

        <div class="logo-area">

            
            <img src="{{ asset('theme/img/career-updated-logo.png') }}" alt="Career Institute Logo" class="brand-logo">    
           

           
        </div>

        <div class="voucher-heading">
            <h3>Payment VOUCHER</h3>

            <p><strong>Receipt No:</strong> CIFSD02-SEC-0526-00001</p>
            <p><strong>Date:</strong> 22-05-2026</p>
            <p><strong>Registration No:</strong> CIFSD02-CWS-0526-00001</p>
            <p><strong>Campus:</strong> CIFSD02 - fsd Campus</p>
        </div>

    </div>

    <table>
        <tr>
            <th>Member Name</th>
            <th>Receipt Type</th>
            <th>Timing</th>
            <th>Date of Joining</th>
        </tr>

        <tr>
            <td>web</td>
            <td>Security Fee</td>
            <td>09:00AM-09:00AM</td>
            <td>22-05-2026</td>
        </tr>
    </table>

    <table class="amount-table">

        <tr>
            <td>Registration No</td>
            <td>CIFSD02-CWS-0526-00001</td>
        </tr>

        <tr>
            <td>Remarks</td>
            <td>Security fee collected at the time of coworking registration.</td>
        </tr>

        <tr>
            <td>Total Paid</td>
            <td>Rs. 20,000</td>
        </tr>

    </table>

    <div class="amount-words">
        Amount in Words: Twenty Thousand Only.
    </div>

    <div class="note-section">
        <div>
            This receipt can be produce when demanded <br>
            Fee once paid is not Refundable.
        </div>

        <div>
            For Career Institute - fsd Campus
        </div>
    </div>

    <div class="footer">
        Career Institute, P-165 B, 262 Millat Rd, Millat Chowk,
        Gulistan Colony, Faisalabad, Punjab, Pakistan - 38000 <br>

        Ph:0314-4444010 / 0341-4444010 <br>

        www.career.edu.pk
    </div>

</div>

</body>
</html>