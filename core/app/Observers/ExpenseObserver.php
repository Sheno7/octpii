<?php

namespace App\Observers;

use App\Enums\Status;
use App\Enums\TransactionType;
use App\Models\Expense;

class ExpenseObserver {
    /**
     * Handle the Expense "created" event.
     */
    public function created(Expense $expense): void {
        $transaction = $expense->transaction()->create([
            'type' => TransactionType::OUT,
            'amount' => $expense->amount,
            'date' => $expense->date,
            'status' => Status::PAYMENTCOMPLETED,
            'payment_method_id' => $expense->payment_method_id,
            'notes' => $expense->notes,
        ]);

        // Update the transaction_id on the expense model
        $expense->transaction_id = $transaction->id;
        $expense->save();
    }

    /**
     * Handle the Expense "updated" event.
     */
    public function updated(Expense $expense): void {
        if ($expense->transaction) {
            $expense->transaction->update([
                'amount' => $expense->amount,
                'date' => $expense->date,
                'payment_method_id' => $expense->payment_method_id,
                'notes' => $expense->notes,
            ]);
        }
    }

    /**
     * Handle the Expense "deleted" event.
     */
    public function deleted(Expense $expense): void {
        $expense->transaction()->forceDelete();
    }

    /**
     * Handle the Expense "restored" event.
     */
    public function restored(Expense $expense): void {
        //
    }

    /**
     * Handle the Expense "force deleted" event.
     */
    public function forceDeleted(Expense $expense): void {
        //
    }
}
