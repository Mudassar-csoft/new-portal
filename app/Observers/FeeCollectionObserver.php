<?php

namespace App\Observers;

use App\Models\FeeCollection;
use App\Models\FeeCollectionLog;

class FeeCollectionObserver
{
    public function created(FeeCollection $feeCollection): void
    {
        $this->log($feeCollection, 'created', [], $feeCollection->getAttributes());
    }

    public function updated(FeeCollection $feeCollection): void
    {
        $changes = $feeCollection->getChanges();
        unset($changes['updated_at']);

        if (empty($changes)) {
            return;
        }

        $original = array_intersect_key($feeCollection->getOriginal(), $changes);

        $this->log($feeCollection, 'updated', $original, $changes);
    }

    public function deleted(FeeCollection $feeCollection): void
    {
        $this->log($feeCollection, 'deleted', $feeCollection->getOriginal(), []);
    }

    private function log(FeeCollection $feeCollection, string $action, array $oldValues, array $newValues): void
    {
        FeeCollectionLog::create([
            'fee_collection_id' => $feeCollection->id,
            'action' => $action,
            'old_values' => $oldValues ?: null,
            'new_values' => $newValues ?: null,
            'user_id' => auth()->id(),
        ]);
    }
}
