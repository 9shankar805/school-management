<?php

namespace App\Interfaces;

interface PaymentInterface {
    public function getAllInvoices();
    public function getInvoiceById($id);
    public function createInvoice($request);
    public function updateInvoice($request, $id);
    public function deleteInvoice($id);
    public function createPayment($request, $invoice_id);
}
