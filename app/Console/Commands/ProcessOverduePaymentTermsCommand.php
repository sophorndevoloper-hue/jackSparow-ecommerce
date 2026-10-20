<?php

namespace App\Console\Commands;

use App\Models\Order;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('orders:process-overdue-terms')]
#[Description('Scan B2B orders operating under payment terms that have passed their due date and apply late penalty fees.')]
class ProcessOverduePaymentTermsCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $overdueOrders = Order::with('paymentTerm')
            ->whereNotNull('payment_term_id')
            ->whereIn('payment_status', ['pending', 'partially_paid'])
            ->whereDate('payment_due_date', '<', now()->toDateString())
            ->whereNull('late_fee_applied_at')
            ->get();

        $count = 0;
        foreach ($overdueOrders as $order) {
            $term = $order->paymentTerm;
            if (! $term || (float) $term->late_fee_percentage <= 0) {
                continue;
            }

            $lateFee = round((float) $order->total_amount * ((float) $term->late_fee_percentage / 100), 2);

            $order->update([
                'late_fee_amount' => $lateFee,
                'late_fee_applied_at' => now(),
                'admin_notes' => trim($order->admin_notes." [Late fee of \${$lateFee} ({$term->late_fee_percentage}%) applied on ".now()->toFormattedDateString().']'),
            ]);

            $this->info("Applied \${$lateFee} late fee to overdue order #{$order->order_number}.");
            $count++;
        }

        $this->info("Processed {$count} overdue order(s) with late penalty fees.");

        return Command::SUCCESS;
    }
}
