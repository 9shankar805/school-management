<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class InvoiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $student = \App\Models\User::where('role', 'student')->first();
        if ($student) {
            $invoice = \App\Models\Invoice::create([
                'student_id' => $student->id,
                'title' => 'Fall Semester Tuition',
                'amount' => 1500.00,
                'status' => 'partial',
                'due_date' => now()->addDays(30),
            ]);

            \App\Models\Payment::create([
                'invoice_id' => $invoice->id,
                'amount_paid' => 500.00,
                'payment_date' => now(),
                'payment_method' => 'Credit Card',
            ]);
        }
    }
}
