<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'contract_id',
        'action',
        'table_name',
        'record_id',
        'old_values',
        'new_values',
        'description',
        'user_id',
        'created_at'
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'created_at' => 'datetime'
    ];

    // ✅ Laravel will manage timestamps (created_at, updated_at)
    public $timestamps = true;

    // 🧩 Relationships
    public function contract()
    {
        return $this->belongsTo(Contract::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // ✅ Fixed static logger
    public static function logAction($contractId, $action, $tableName, $recordId, $oldValues = null, $newValues = null, $description = null)
    {
        try {
            $history = self::create([
                'contract_id' => $contractId,
                'action' => $action,
                'table_name' => $tableName,
                'record_id' => $recordId,
                'old_values' => $oldValues,
                'new_values' => $newValues,
                'description' => $description,
                'user_id' => auth()->id(), // null if not authenticated
                'created_at' => now()
            ]);

            \Log::info('✅ PaymentHistory logged', ['id' => $history->id]);

            return $history;
        } catch (\Exception $e) {
            \Log::error('❌ PaymentHistory logAction failed', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }

    public function getFormattedDescriptionAttribute()
    {
        if ($this->description) {
            return $this->description;
        }

        $actionText = [
            'created' => 'yaratildi',
            'updated' => 'yangilandi',
            'deleted' => 'o\'chirildi'
        ];

        $tableText = [
            'contracts' => 'Shartnoma',
            'payment_schedules' => 'To\'lov jadvali',
            'actual_payments' => 'Haqiqiy to\'lov'
        ];

        return ($tableText[$this->table_name] ?? $this->table_name) . ' ' . ($actionText[$this->action] ?? $this->action);
    }

    public function getChangesSummaryAttribute()
    {
        if (!$this->new_values || !$this->old_values) {
            return null;
        }

        $changes = [];
        foreach ($this->new_values as $field => $newValue) {
            $oldValue = $this->old_values[$field] ?? null;
            if ($oldValue !== $newValue) {
                $changes[] = [
                    'field' => $field,
                    'old' => $oldValue,
                    'new' => $newValue
                ];
            }
        }

        return $changes;
    }
}
