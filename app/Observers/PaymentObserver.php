<?php

namespace App\Observers;

use App\Models\ActualPayment;
use App\Models\PaymentHistory;

class PaymentObserver
{
    public function created(ActualPayment $payment)
    {
        PaymentHistory::logAction(
            $payment->contract_id,
            'created',
            'actual_payments',
            $payment->id,
            null,
            $payment->toArray(),
            'Yangi to\'lov qo\'shildi'
        );
    }

    public function updated(ActualPayment $payment)
    {
        PaymentHistory::logAction(
            $payment->contract_id,
            'updated',
            'actual_payments',
            $payment->id,
            $payment->getOriginal(),
            $payment->getChanges(),
            'To\'lov ma\'lumotlari yangilandi'
        );
    }

    public function deleted(ActualPayment $payment)
    {
        PaymentHistory::logAction(
            $payment->contract_id,
            'deleted',
            'actual_payments',
            $payment->id,
            $payment->toArray(),
            null,
            'To\'lov o\'chirildi'
        );
    }
}

