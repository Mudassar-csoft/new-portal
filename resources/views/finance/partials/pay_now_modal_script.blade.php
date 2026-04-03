<script>
    (function ($) {
        const receiptPreviewByMethod = @json($receiptPreviewByMethod ?? []);

        function methodCode(method) {
            const codes = {
                cash: 'CSH',
                bank: 'BNK',
                cheque: 'CHQ',
                online_transfer: 'OTR',
                easypaisa: 'EZY',
                jazzcash: 'JZZ'
            };

            return codes[method] || 'GEN';
        }

        function monthYearStamp() {
            const now = new Date();
            const month = String(now.getMonth() + 1).padStart(2, '0');
            const year = String(now.getFullYear()).slice(-2);

            return month + year;
        }

        function updateFinancePayModal($modal) {
            const method = $modal.find('.js-payment-method').val();
            const needsBank = method === 'bank' || method === 'cheque';
            const needsCheque = method === 'cheque';
            const needsRef = ['bank', 'online_transfer', 'easypaisa', 'jazzcash'].includes(method);
            const preview = receiptPreviewByMethod[method] || ('INV-' + methodCode(method) + '-' + monthYearStamp() + '-0000001');

            $modal.find('.js-bank-name-wrap').toggle(needsBank);
            $modal.find('.js-cheque-no-wrap').toggle(needsCheque);
            $modal.find('.js-payment-ref-wrap').toggle(needsRef);
            $modal.find('.js-receipt-preview').val(preview);
        }

        $(document).on('change', '.finance-pay-modal .js-payment-method', function () {
            updateFinancePayModal($(this).closest('.finance-pay-modal'));
        });

        $(document).on('shown.bs.modal', '.finance-pay-modal', function () {
            updateFinancePayModal($(this));
        });

        $('.finance-pay-modal').each(function () {
            updateFinancePayModal($(this));
        });
    })(jQuery);
</script>
