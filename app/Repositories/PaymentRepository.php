<?php

namespace App\Repositories;

use App\Models\Invoice;
use App\Models\Payment;
use App\Interfaces\PaymentInterface;

class PaymentRepository implements PaymentInterface {
    public function getAllInvoices() {
        return Invoice::with('student', 'payments')->get();
    }

    public function getInvoiceById($id) {
        return Invoice::with('student', 'payments')->findOrFail($id);
    }

    public function createInvoice($request) {
        return Invoice::create($request->all());
    }

    public function updateInvoice($request, $id) {
        $invoice = Invoice::findOrFail($id);
        $invoice->update($request->all());
        
        $this->updateInvoiceStatus($invoice);
        
        return $invoice;
    }

    public function deleteInvoice($id) {
        $invoice = Invoice::findOrFail($id);
        $invoice->delete();
    }

    public function createPayment($request, $invoice_id) {
        $invoice = Invoice::findOrFail($invoice_id);
        
        $payment = Payment::create([
            'invoice_id' => $invoice_id,
            'amount_paid' => $request->amount_paid,
            'payment_date' => $request->payment_date,
            'payment_method' => $request->payment_method,
        ]);

        $this->updateInvoiceStatus($invoice);
        
        return $payment;
    }
    
    private function updateInvoiceStatus(Invoice $invoice) {
        $totalPaid = $invoice->payments()->sum('amount_paid');
        
        if ($totalPaid >= $invoice->amount) {
            $invoice->update(['status' => 'paid']);
        } elseif ($totalPaid > 0) {
            $invoice->update(['status' => 'partial']);
        } else {
            $invoice->update(['status' => 'unpaid']);
        }
    }
}
