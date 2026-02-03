<?php

namespace App\Observers;

use App\Models\EventPayment;
use App\Services\EventChecklistService;
use App\Services\Finance\InstallmentPaymentMatcher;

class EventPaymentObserver
{
    public function created(EventPayment $payment): void
    {
        if ($payment->event) {
            app(EventChecklistService::class)->updateAuto($payment->event);
        }

        if ($payment->contract) {
            app(InstallmentPaymentMatcher::class)->syncFromPayments($payment->contract);
        }
    }

    public function updated(EventPayment $payment): void
    {
        if ($payment->event) {
            app(EventChecklistService::class)->updateAuto($payment->event);
        }

        if ($payment->contract) {
            app(InstallmentPaymentMatcher::class)->syncFromPayments($payment->contract);
        }
    }

    public function deleted(EventPayment $payment): void
    {
        if ($payment->contract) {
            app(InstallmentPaymentMatcher::class)->syncFromPayments($payment->contract);
        }
    }
}
