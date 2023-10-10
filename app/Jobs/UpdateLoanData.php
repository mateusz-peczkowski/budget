<?php

namespace App\Jobs;

use App\Models\Loan;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UpdateLoanData implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $loan;

    /**
     * Create a new job instance.
     */
    public function __construct(Loan $loan)
    {
        $this->loan = $loan;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if ($this->loan === 'archive')
            return;

        if ($this->loan->status !== 'late') {
            $this->loan->status = 'current';

            if ($this->loan->payments()->where('status', '!=', 'paid')->count() === 0)
                $this->loan->status = 'paid';
        }

        if ($this->loan) {
            $data = $this->calculatePaymentsData($this->loan);
            $this->loan->last_payment = $data['last_payment'];
            $this->loan->overall_value = $data['overall_value'];
            $this->loan->paid_value = $data['paid_value'];
            $this->loan->remaining_value = $data['remaining_value'];
            $this->loan->paid_percent = $data['paid_percent'];
            $this->loan->next_payment_value = $data['next_payment_value'];
            $this->loan->remaining_payments_count = $data['remaining_payments_count'];
            $this->loan->remaining_payments_years = $data['remaining_payments_years'];
            $this->loan->date_ending = $data['date_ending'];
        }

        if ($this->loan->isDirty())
            $this->loan->saveQuietly();
    }

    private function calculatePaymentsData(Loan $loan)
    {
        $payments = $loan->payments()->get();

        if (!$payments || ($payments && !$payments->count()))
            return [
                'last_payment'             => NULL,
                'overall_value'            => NULL,
                'paid_value'               => NULL,
                'remaining_value'          => NULL,
                'paid_percent'             => NULL,
                'next_payment_value'       => NULL,
                'remaining_payments_count' => NULL,
                'remaining_payments_years' => NULL,
                'date_ending'              => NULL,
            ];

        $paidPayments = $loan->payments()->where('status', 'paid')->orderBy('pay_date', 'DESC')->get();
        $nextPayment = $loan->payments()->where('status', 'pending')->orderBy('date', 'asc')->first();

        $additionalValue = $loan->additional_value ? floatval($loan->additional_value) : 0;

        $overallValue = ($payments ? floatval($payments->sum('value')) : 0) + $additionalValue;
        $paidValue = ($paidPayments ? floatval($paidPayments->sum('value')) : 0) + $additionalValue;
        $nextPaymentValue = $nextPayment ? floatval($nextPayment->value) : 0;

        $remainingPaymentsCount = count($payments) - count($paidPayments);
        $remainingPaymentsYears = ceil($remainingPaymentsCount / 12);

        $lastPayment = $paidPayments && $paidPayments[0] ? $paidPayments[0]->pay_date : NULL;
        $dateEnding = NULL;

        if ($lastPayment)
            $dateEnding = $loan->payments()->orderBy('date', 'desc')->first()->date;

        return [
            'last_payment'             => $lastPayment,
            'overall_value'            => $overallValue,
            'paid_value'               => $paidValue,
            'remaining_value'          => $overallValue - $paidValue,
            'paid_percent'             => $overallValue ? round(($paidValue / $overallValue) * 100, 2) : 0,
            'next_payment_value'       => $nextPaymentValue,
            'remaining_payments_count' => $remainingPaymentsCount,
            'remaining_payments_years' => $remainingPaymentsYears,
            'date_ending'              => $dateEnding,
        ];
    }
}
