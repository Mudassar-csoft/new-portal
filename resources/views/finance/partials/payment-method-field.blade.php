<div class="form-group col-lg-3 col-md-6">
    <label class="form-label required">Payment Method</label>
    <select name="payment_method" class="form-control" required>
        <option value="cash" @selected(old('payment_method') === 'cash')>Cash</option>
        <option value="bank" @selected(old('payment_method') === 'bank')>Bank</option>
        <option value="cheque" @selected(old('payment_method') === 'cheque')>Cheque</option>
    </select>
</div>
