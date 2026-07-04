<div class="form-group col-lg-3 col-md-6">
    <label class="form-label required">Payment Date</label>
    <input type="date" name="payment_date" class="form-control" value="{{ old('payment_date', now()->toDateString()) }}" required>
</div>
<div class="form-group col-lg-3 col-md-6">
    <label class="form-label required">Paid Amount</label>
    <input type="number" step="0.01" min="1" name="paid_amount" class="form-control" value="{{ old('paid_amount') }}" required>
</div>
