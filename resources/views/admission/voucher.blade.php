@php
	$adm = $admission;
	$campusCode = $adm->campus->code ?? 'N/A';
	$date = optional($adm->admission_date)->format('d-m-Y') ?? now()->format('d-m-Y');
	$total = number_format($adm->discounted_fee ?? $adm->fee_package ?? 0, 0);
	$studentName = $adm->student_name ?? 'Student';
@endphp

<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
	<title>Admission Voucher - {{ $adm->receipt_number }}</title>
	<style>
		body { font-family: Arial, sans-serif; margin: 0; padding: 0; background: #f5f5f5; }
		.wrapper { max-width: 900px; margin: 12px auto; background: #fff; border: 1px solid #ddd; padding: 16px; }
		.copy { border-bottom: 1px dashed #ccc; padding-bottom: 18px; margin-bottom: 18px; }
		.header { display: flex; justify-content: space-between; align-items: center; }
		.title { font-size: 14px; font-weight: 700; }
		.table { width: 100%; border-collapse: collapse; margin-top: 10px; }
		.table th, .table td { border: 1px solid #999; padding: 6px 8px; font-size: 13px; }
		.meta { text-align: right; font-size: 13px; line-height: 1.4; }
		.label { font-weight: 700; }
		.totals td { font-weight: 700; }
		.notes { font-size: 12px; color: #555; margin-top: 8px; }
	</style>
</head>
<body>
	<div class="wrapper">
		@foreach (['Student Copy', 'Campus Copy'] as $copy)
			<div class="copy">
				<div class="header">
					<div class="title">{{ $copy }}</div>
					<div class="meta">
						<div class="label">ADMISSION FEE VOUCHER</div>
						<div>Receipt No: {{ $adm->receipt_number }}</div>
						<div>Date: {{ $date }}</div>
						<div>Registration No: {{ $adm->registration_number ?? 'N/A' }}</div>
						<div>Campus Code: {{ $campusCode }}</div>
					</div>
				</div>

				<div style="margin: 12px 0 8px;">
					<div class="label">Student Name</div>
					<div style="border: 1px solid #999; padding: 6px 8px; font-size: 13px;">
						{{ $studentName }}
					</div>
				</div>

				<table class="table">
					<tr>
						<td width="60%">Admission Fee</td>
						<td width="40%">Rs. {{ number_format($adm->fee_package ?? 0, 0) }}</td>
					</tr>
					<tr>
						<td>Discount</td>
						<td>Rs. {{ number_format($adm->discount_amount ?? 0, 0) }}</td>
					</tr>
					<tr class="totals">
						<td>Total Payable</td>
						<td>Rs. {{ $total }}</td>
					</tr>
				</table>

				@php
					$amount = $adm->discounted_fee ?? $adm->fee_package ?? 0;
					$amountWords = number_format($amount, 0);
				@endphp
				<div class="notes">
					Amount in Words: <span class="label">{{ $amountWords }} Only.</span><br>
					Fee once paid is not refundable.
				</div>
			</div>
		@endforeach
	</div>
</body>
</html>
