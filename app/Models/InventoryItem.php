<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'campus_id',
        'item_code',
        'category',
        'item_name',
        'brand',
        'model_no',
        'serial_no',
        'quantity',
        'unit',
        'minimum_stock',
        'condition_status',
        'room_location',
        'purchase_date',
        'remarks',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'purchase_date' => 'date',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function categories(): array
    {
        return [
            'computer_systems_accessories' => 'Computer Systems & Accessories',
            'furniture' => 'Furniture',
            'stationery' => 'Stationery',
            'cleaning_supplies' => 'Clean-up Items',
            'ac_devices' => 'AC Devices',
            'networking_devices' => 'Networking Devices',
            'electrical_items' => 'Electrical Items',
            'other' => 'Others',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function categoryLabels(): array
    {
        return self::categories() + [
            'ac_networking_devices' => 'AC & Networking Devices',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function units(): array
    {
        return [
            'pcs' => 'Pieces',
            'set' => 'Set',
            'box' => 'Box',
            'pack' => 'Pack',
            'ream' => 'Ream',
            'bottle' => 'Bottle',
            'meter' => 'Meter',
            'unit' => 'Unit',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function conditionStatuses(): array
    {
        return [
            'good' => 'Good',
            'needs_repair' => 'Needs Repair',
            'damaged' => 'Damaged',
            'disposed' => 'Disposed',
        ];
    }

    public function campus(): BelongsTo
    {
        return $this->belongsTo(Campus::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function categoryLabel(): string
    {
        return self::categoryLabels()[$this->category] ?? ucfirst(str_replace('_', ' ', $this->category));
    }
}
