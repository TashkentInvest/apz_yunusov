<?php
// PaymentHistory Model
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
        'user_id'
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'created_at' => 'datetime'
    ];

    public $timestamps = false;

    protected $dates = ['created_at'];

    // Relationships
    public function contract()
    {
        return $this->belongsTo(Contract::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Static method to log history
    public static function logAction($contractId, $action, $tableName, $recordId, $oldValues = null, $newValues = null, $description = null)
    {
        return self::create([
            'contract_id' => $contractId,
            'action' => $action,
            'table_name' => $tableName,
            'record_id' => $recordId,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'description' => $description,
            'user_id' => auth()->id(),
            'created_at' => now()
        ]);
    }

    // Get formatted description
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

    // Get changes summary
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
