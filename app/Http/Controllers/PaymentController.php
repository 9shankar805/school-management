<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Interfaces\PaymentInterface;
use App\Interfaces\UserInterface;

class PaymentController extends Controller
{
    protected $paymentRepository;
    protected $userRepository;

    public function __construct(PaymentInterface $paymentRepository, UserInterface $userRepository) {
        $this->paymentRepository = $paymentRepository;
        $this->userRepository = $userRepository;
    }

    public function index() {
        $invoices = $this->paymentRepository->getAllInvoices();
        return view('payments.index', compact('invoices'));
    }

    public function create() {
        // Just fetch some random active session to show students, or all students
        // In a real app we would pass session correctly, but for this demo:
        $students = \App\Models\User::where('role', 'student')->get();
        return view('payments.create', compact('students'));
    }

    public function store(Request $request) {
        $request->validate([
            'student_id' => 'required|exists:users,id',
            'title' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'due_date' => 'nullable|date',
        ]);

        $this->paymentRepository->createInvoice($request);
        return redirect()->route('payments.index')->with('status', 'Invoice created successfully.');
    }

    public function pay($id) {
        $invoice = $this->paymentRepository->getInvoiceById($id);
        return view('payments.pay', compact('invoice'));
    }

    public function processPayment(Request $request, $id) {
        $request->validate([
            'amount_paid' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
        ]);

        $this->paymentRepository->createPayment($request, $id);
        return redirect()->route('payments.index')->with('status', 'Payment recorded successfully.');
    }
}
