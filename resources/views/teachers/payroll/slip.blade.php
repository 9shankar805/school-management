<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Salary Slip - {{ $payroll->teacher?->full_name }} ({{ $payroll->month_name }} {{ $payroll->year }})</title>
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family: DejaVu Sans, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; font-size: 9.5pt; color: #1e293b; background: #fff; line-height: 1.45; }
.page { width: 210mm; margin: 0 auto; padding: 14mm 16mm; }
.header { border-bottom: 2.5pt solid #4338ca; padding-bottom: 5mm; margin-bottom: 5mm; display: table; width: 100%; }
.header-left { display: table-cell; vertical-align: top; width: 60%; }
.header-right { display: table-cell; vertical-align: top; width: 40%; text-align: right; }
.school-name { font-size: 15pt; font-weight: bold; color: #312e81; text-transform: uppercase; letter-spacing: 0.5pt; }
.school-sub { font-size: 8.5pt; color: #64748b; margin-top: 1mm; }
.slip-title { font-size: 12pt; font-weight: bold; color: #1e1b4b; text-transform: uppercase; letter-spacing: 1.5pt; }
.slip-period { font-size: 9.5pt; font-weight: bold; color: #4338ca; margin-top: 1.5mm; }

.status-badge { display: inline-block; padding: 2pt 8pt; border-radius: 9999pt; font-size: 7.5pt; font-weight: bold; text-transform: uppercase; margin-top: 1.5mm; }
.status-paid { background: #dcfce7; color: #166534; border: 0.5pt solid #86efac; }
.status-draft { background: #fef3c7; color: #92400e; border: 0.5pt solid #fcd34d; }

.employee-card { background: #f8fafc; border: 0.75pt solid #e2e8f0; border-radius: 6pt; padding: 4mm 5mm; margin-bottom: 5mm; }
.emp-grid { display: table; width: 100%; }
.emp-row { display: table-row; }
.emp-col { display: table-cell; padding: 2mm 3mm; vertical-align: top; width: 25%; }
.emp-label { font-size: 7.5pt; color: #64748b; text-transform: uppercase; letter-spacing: 0.3pt; }
.emp-value { font-size: 9pt; font-weight: 700; color: #0f172a; margin-top: 1mm; }

.attendance-card { display: table; width: 100%; margin-bottom: 5mm; border-collapse: separate; border-spacing: 2mm 0; }
.att-cell { display: table-cell; width: 25%; background: #f1f5f9; border: 0.5pt solid #cbd5e1; border-radius: 4pt; padding: 2.5mm; text-align: center; }
.att-val { font-size: 12pt; font-weight: bold; }
.att-lbl { font-size: 7.5pt; color: #475569; text-transform: uppercase; margin-top: 1mm; }

.table-wrap { width: 100%; margin-bottom: 5mm; border: 0.75pt solid #e2e8f0; border-radius: 6pt; overflow: hidden; }
.calc-table { width: 100%; border-collapse: collapse; }
.calc-table th { background: #e0e7ff; color: #3730a3; padding: 3mm 4mm; font-size: 8.5pt; font-weight: 700; text-transform: uppercase; text-align: left; border-bottom: 0.75pt solid #c7d2fe; width: 50%; }
.calc-table td { padding: 2.5mm 4mm; border-bottom: 0.5pt solid #f1f5f9; font-size: 8.5pt; vertical-align: middle; }
.calc-table tr:nth-child(even) td { background: #fafafa; }
.item-row { display: table; width: 100%; }
.item-label { display: table-cell; text-align: left; }
.item-val { display: table-cell; text-align: right; font-weight: 600; color: #0f172a; }

.subtotal-row td { background: #f8fafc !important; font-weight: 700; color: #0f172a; border-top: 1pt solid #cbd5e1; padding: 3mm 4mm; }

.net-banner { background: #312e81; color: #ffffff; border-radius: 6pt; padding: 4mm 6mm; margin-bottom: 5mm; display: table; width: 100%; }
.net-left { display: table-cell; vertical-align: middle; width: 60%; }
.net-right { display: table-cell; vertical-align: middle; text-align: right; width: 40%; }
.net-title { font-size: 8.5pt; text-transform: uppercase; letter-spacing: 1pt; color: #c7d2fe; }
.net-sub { font-size: 7.5pt; color: #e0e7ff; margin-top: 1mm; }
.net-amount { font-size: 16pt; font-weight: 800; color: #ffffff; letter-spacing: 0.5pt; }

.notes-box { background: #fffbeb; border: 0.75pt solid #fef3c7; border-radius: 4pt; padding: 3mm 4mm; font-size: 8pt; color: #92400e; margin-bottom: 5mm; }

.footer { border-top: 0.75pt solid #cbd5e1; padding-top: 6mm; margin-top: 6mm; display: table; width: 100%; font-size: 8pt; color: #64748b; }
.footer-left { display: table-cell; vertical-align: bottom; width: 40%; }
.footer-center { display: table-cell; vertical-align: bottom; width: 30%; text-align: center; }
.footer-right { display: table-cell; vertical-align: bottom; width: 30%; text-align: center; }
.sig-line { border-top: 0.75pt solid #475569; width: 42mm; margin: 0 auto 2mm auto; }
</style>
</head>
<body>
<div class="page">

    {{-- Header --}}
    <div class="header">
        <div class="header-left">
            <div class="school-name">{{ config('app.name', 'School Management') }}</div>
            <div class="school-sub">Faculty Payroll & Compensation Center · Monthly Payslip</div>
        </div>
        <div class="header-right">
            <div class="slip-title">Salary Slip</div>
            <div class="slip-period">{{ $payroll->nepali_month_name }} ({{ $payroll->month_name }}) {{ $payroll->year }}</div>
            <div>
                <span class="status-badge {{ $payroll->status === 'paid' ? 'status-paid' : 'status-draft' }}">
                    {{ $payroll->status === 'paid' ? 'DISBURSED / PAID' : ($payroll->is_approved ? 'APPROVED' : 'PENDING DRAFT') }}
                </span>
            </div>
        </div>
    </div>

    {{-- Employee Information --}}
    <div class="employee-card">
        <div class="emp-grid">
            <div class="emp-row">
                <div class="emp-col">
                    <div class="emp-label">Faculty Name</div>
                    <div class="emp-value">{{ $payroll->teacher?->full_name ?? 'N/A' }}</div>
                </div>
                <div class="emp-col">
                    <div class="emp-label">Employee ID</div>
                    <div class="emp-value">EMP-{{ $payroll->teacher_id }}</div>
                </div>
                <div class="emp-col">
                    <div class="emp-label">Department</div>
                    <div class="emp-value">{{ $payroll->teacher?->departments?->pluck('name')->join(', ') ?: 'Teaching Staff' }}</div>
                </div>
                <div class="emp-col">
                    <div class="emp-label">Pay Period</div>
                    <div class="emp-value">{{ $payroll->month_name }} {{ $payroll->year }}</div>
                </div>
            </div>
            <div class="emp-row">
                <div class="emp-col">
                    <div class="emp-label">Bank Name</div>
                    <div class="emp-value">{{ $payroll->teacher?->bank_name ?: 'N/A' }}</div>
                </div>
                <div class="emp-col">
                    <div class="emp-label">Bank Account No.</div>
                    <div class="emp-value">{{ $payroll->teacher?->bank_account_no ?: 'Direct Cash / Cheque' }}</div>
                </div>
                <div class="emp-col">
                    <div class="emp-label">Payment Mode</div>
                    <div class="emp-value">{{ ucwords(str_replace('_', ' ', $payroll->payment_method ?? 'Bank Transfer')) }}</div>
                </div>
                <div class="emp-col">
                    <div class="emp-label">Disbursement Date</div>
                    <div class="emp-value">{{ $payroll->payment_date ? $payroll->payment_date->format('d M, Y') : 'Pending' }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Attendance Summary --}}
    <div class="attendance-card">
        <div class="att-cell">
            <div class="att-val" style="color:#0f172a;">{{ $payroll->working_days }}</div>
            <div class="att-lbl">Working Days</div>
        </div>
        <div class="att-cell">
            <div class="att-val" style="color:#166534;">{{ $payroll->present_days }}</div>
            <div class="att-lbl">Present Days</div>
        </div>
        <div class="att-cell">
            <div class="att-val" style="color:#991b1b;">{{ $payroll->absent_days }}</div>
            <div class="att-lbl">Absent Days</div>
        </div>
        <div class="att-cell">
            <div class="att-val" style="color:#4338ca;">{{ $payroll->leave_days }}</div>
            <div class="att-lbl">Approved Leaves</div>
        </div>
    </div>

    {{-- Earnings & Deductions Table --}}
    <div class="table-wrap">
        <table class="calc-table">
            <thead>
                <tr>
                    <th>Earnings & Additions (NPR)</th>
                    <th>Statutory & Other Deductions (NPR)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <div class="item-row">
                            <span class="item-label">Basic Salary</span>
                            <span class="item-val">Rs. {{ number_format($payroll->basic_salary, 2) }}</span>
                        </div>
                    </td>
                    <td>
                        <div class="item-row">
                            <span class="item-label">TDS / Income Tax</span>
                            <span class="item-val" style="color:#b91c1c;">Rs. {{ number_format($payroll->tax_deduction ?: $payroll->tds_payable, 2) }}</span>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="item-row">
                            <span class="item-label">Allowances</span>
                            <span class="item-val">Rs. {{ number_format($payroll->allowances, 2) }}</span>
                        </div>
                    </td>
                    <td>
                        <div class="item-row">
                            <span class="item-label">Social Security Fund (SSF)</span>
                            <span class="item-val" style="color:#b91c1c;">Rs. {{ number_format($payroll->ssf_deduction, 2) }}</span>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="item-row">
                            <span class="item-label">Overtime Pay</span>
                            <span class="item-val">Rs. {{ number_format($payroll->overtime, 2) }}</span>
                        </div>
                    </td>
                    <td>
                        <div class="item-row">
                            <span class="item-label">Citizen Investment Trust (CIT)</span>
                            <span class="item-val" style="color:#b91c1c;">Rs. {{ number_format($payroll->cit_deduction, 2) }}</span>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="item-row">
                            <span class="item-label">Bonus / Incentives</span>
                            <span class="item-val">Rs. {{ number_format($payroll->bonus, 2) }}</span>
                        </div>
                    </td>
                    <td>
                        <div class="item-row">
                            <span class="item-label">Late Attendance Fine</span>
                            <span class="item-val" style="color:#b91c1c;">Rs. {{ number_format($payroll->late_fine, 2) }}</span>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="item-row">
                            <span class="item-label">Special Adjustments</span>
                            <span class="item-val">Rs. 0.00</span>
                        </div>
                    </td>
                    <td>
                        <div class="item-row">
                            <span class="item-label">Loan / Other Deductions</span>
                            <span class="item-val" style="color:#b91c1c;">Rs. {{ number_format($payroll->other_deductions + $payroll->loan_deduction + $payroll->advance_deduction, 2) }}</span>
                        </div>
                    </td>
                </tr>
                <tr class="subtotal-row">
                    <td>
                        <div class="item-row">
                            <span class="item-label">Total Gross Earnings</span>
                            <span class="item-val" style="font-size:10pt; color:#312e81;">Rs. {{ number_format($payroll->gross_salary, 2) }}</span>
                        </div>
                    </td>
                    <td>
                        <div class="item-row">
                            <span class="item-label">Total Deductions</span>
                            <span class="item-val" style="font-size:10pt; color:#b91c1c;">- Rs. {{ number_format($payroll->total_deductions, 2) }}</span>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    {{-- Net Payable Banner --}}
    <div class="net-banner">
        <div class="net-left">
            <div class="net-title">NET PAYABLE AMOUNT</div>
            <div class="net-sub">Total Disbursed Salary after All Deductions and Taxes</div>
        </div>
        <div class="net-right">
            <div class="net-amount">NPR {{ number_format($payroll->net_salary, 2) }}</div>
        </div>
    </div>

    @if($payroll->notes)
    <div class="notes-box">
        <strong>Remarks / Notes:</strong> {{ $payroll->notes }}
    </div>
    @endif

    {{-- Signatures & Verification --}}
    <div class="footer">
        <div class="footer-left">
            <div>Generated On: {{ now()->format('d M, Y H:i') }}</div>
            <div>Computer Generated Salary Advice</div>
        </div>
        <div class="footer-center">
            <div class="sig-line"></div>
            <div>{{ $payroll->teacher?->full_name }}</div>
            <div style="font-size:7pt; color:#94a3b8;">Faculty Member</div>
        </div>
        <div class="footer-right">
            <div class="sig-line"></div>
            <div>{{ $payroll->approver?->full_name ?? ($payroll->processor?->full_name ?? 'Finance Officer') }}</div>
            <div style="font-size:7pt; color:#94a3b8;">Authorized Finance / HR Signatory</div>
        </div>
    </div>

</div>
</body>
</html>
