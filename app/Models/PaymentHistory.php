<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentHistory extends Model
{
    protected $fillable = [
        'contract_id',
        'action',
        'table_name',
        'record_id',
        'old_values',
        'new_values',
        'description',
        'formatted_description',
        'user_id',
        'amendment_id'

    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array'
    ];

    /**
     * Relationship with contract
     */
    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }

    /**
     * Relationship with user who performed the action
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Log an action to payment history
     */
    public static function logAction(
        int $contractId,
        string $action,
        string $tableName,
        int $recordId,
        array $oldValues = null,
        array $newValues = null,
        string $description = null
    ): void {
        try {
            $userId = auth()->id();

            // Generate formatted description if not provided
            if (!$description) {
                $description = self::generateDescription($action, $tableName, $oldValues, $newValues);
            }

            self::create([
                'contract_id' => $contractId,
                'action' => $action,
                'table_name' => $tableName,
                'record_id' => $recordId,
                'old_values' => $oldValues,
                'new_values' => $newValues,
                'description' => $description,
                'formatted_description' => self::generateFormattedDescription($action, $tableName, $oldValues, $newValues),
                'user_id' => $userId
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to log payment history', [
                'contract_id' => $contractId,
                'action' => $action,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Generate description for the action
     */
    private static function generateDescription(string $action, string $tableName, ?array $oldValues, ?array $newValues): string
    {
        $actionText = match($action) {
            'created' => 'yaratildi',
            'updated' => 'yangilandi',
            'deleted' => 'o\'chirildi',
            default => $action
        };

        $tableText = match($tableName) {
            'contracts' => 'Shartnoma',
            'payment_schedules' => 'To\'lov jadvali',
            'actual_payments' => 'Haqiqiy to\'lov',
            'contract_amendments' => 'Qo\'shimcha kelishuv',
            default => $tableName
        };

        return "{$tableText} {$actionText}";
    }

    /**
     * Generate formatted description with details
     */
    private static function generateFormattedDescription(string $action, string $tableName, ?array $oldValues, ?array $newValues): string
    {
        $baseDescription = self::generateDescription($action, $tableName, $oldValues, $newValues);

        if ($tableName === 'actual_payments' && $action === 'created' && $newValues) {
            $amount = $newValues['amount'] ?? 0;
            $paymentDate = $newValues['payment_date'] ?? '';
            $quarter = $newValues['quarter'] ?? '';
            $year = $newValues['year'] ?? '';

            return $baseDescription . ": " . number_format($amount, 0, '.', ' ') . " so'm ({$quarter}-chorak {$year})";
        }

        if ($tableName === 'payment_schedules' && $action === 'created' && $newValues) {
            $amount = $newValues['quarter_amount'] ?? 0;
            $quarter = $newValues['quarter'] ?? '';
            $year = $newValues['year'] ?? '';

            return $baseDescription . ": " . number_format($amount, 0, '.', ' ') . " so'm ({$quarter}-chorak {$year})";
        }

        if ($tableName === 'contract_amendments' && $action === 'created' && $newValues) {
            $oldAmount = $newValues['old_amount'] ?? 0;
            $newAmount = $newValues['new_amount'] ?? 0;
            $difference = $newAmount - $oldAmount;
            $sign = $difference >= 0 ? '+' : '';

            return $baseDescription . ": " . number_format($oldAmount, 0, '.', ' ') . " → " .
                   number_format($newAmount, 0, '.', ' ') . " ({$sign}" . number_format($difference, 0, '.', ' ') . ")";
        }

        return $baseDescription;
    }

    /**
     * Get action icon for UI
     */
    public function getActionIconAttribute(): string
    {
        return match($this->action) {
            'created' => 'plus-circle',
            'updated' => 'edit-2',
            'deleted' => 'trash-2',
            default => 'activity'
        };
    }

    /**
     * Get action color for UI
     */
    public function getActionColorAttribute(): string
    {
        return match($this->action) {
            'created' => 'green',
            'updated' => 'blue',
            'deleted' => 'red',
            default => 'gray'
        };
    }

    /**
     * Get changes summary
     */
    public function getChangesSummaryAttribute(): array
    {
        if (!$this->old_values || !$this->new_values) {
            return [];
        }

        $changes = [];
        foreach ($this->new_values as $field => $newValue) {
            $oldValue = $this->old_values[$field] ?? null;
            if ($oldValue !== $newValue) {
                $changes[$field] = [
                    'old' => $oldValue,
                    'new' => $newValue,
                    'field_label' => $this->getFieldLabel($field)
                ];
            }
        }

        return $changes;
    }

    /**
     * Get field label for display
     */
    private function getFieldLabel(string $field): string
    {
        return match($field) {
            'amount' => 'Summa',
            'payment_date' => 'To\'lov sanasi',
            'quarter_amount' => 'Chorak summasi',
            'total_amount' => 'Jami summa',
            'contract_number' => 'Shartnoma raqami',
            'year' => 'Yil',
            'quarter' => 'Chorak',
            'reason' => 'Sabab',
            'old_amount' => 'Eski summa',
            'new_amount' => 'Yangi summa',
            default => ucfirst(str_replace('_', ' ', $field))
        };
    }

    /**
     * Scope for recent history
     */
    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Scope for specific action
     */
    public function scopeAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Scope for specific table
     */
    public function scopeTable($query, string $tableName)
    {
        return $query->where('table_name', $tableName);
    }

    /**
     * Get formatted date for display
     */
    public function getFormattedDateAttribute(): string
    {
        return $this->created_at->format('d.m.Y H:i');
    }

    /**
     * Get human readable time
     */
    public function getHumanTimeAttribute(): string
    {
        return $this->created_at->diffForHumans();
    }
}
