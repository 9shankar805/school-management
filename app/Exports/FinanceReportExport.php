<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class FinanceReportExport implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize, WithStyles
{
    public function __construct(
        protected $records,
        protected string $type
    ) {}

    public function collection(): Collection
    {
        return match ($this->type) {
            'fee_collection' => collect($this->records)->map(fn($p) => [
                $p->invoice->student->first_name . ' ' . $p->invoice->student->last_name,
                $p->invoice->title ?? '—',
                '$' . number_format($p->amount, 2),
                \Carbon\Carbon::parse($p->payment_date)->format('d M Y'),
                ucfirst($p->payment_method ?? 'cash'),
            ]),

            'expenses' => collect($this->records)->map(fn($e) => [
                $e->expense_date->format('d M Y'),
                $e->title,
                $e->category,
                '$' . number_format($e->amount, 2),
                ucfirst($e->payment_method),
                $e->reference ?? '—',
                $e->recordedBy->first_name ?? '—',
            ]),

            'income' => collect($this->records)->map(fn($r) => [
                $r->income_date->format('d M Y'),
                $r->title,
                $r->category,
                '$' . number_format($r->amount, 2),
                ucfirst($r->payment_method),
                $r->reference ?? '—',
            ]),

            'outstanding' => collect($this->records)->map(fn($inv) => [
                $inv->student->first_name . ' ' . $inv->student->last_name,
                $inv->title,
                '$' . number_format($inv->amount, 2),
                $inv->due_date ? \Carbon\Carbon::parse($inv->due_date)->format('d M Y') : '—',
                ucfirst($inv->status),
            ]),

            'ledger' => collect([
                ['Category', 'Amount'],
                ['Fee Collections', '$' . number_format($this->records['fees'] ?? 0, 2)],
                ['Other Income',    '$' . number_format($this->records['income'] ?? 0, 2)],
                ['Expenses',        '-$' . number_format($this->records['expenses'] ?? 0, 2)],
                ['Net Balance',     '$' . number_format(($this->records['fees'] ?? 0) + ($this->records['income'] ?? 0) - ($this->records['expenses'] ?? 0), 2)],
            ]),

            default => collect(),
        };
    }

    public function headings(): array
    {
        return match ($this->type) {
            'fee_collection' => ['Student', 'Invoice', 'Amount Paid', 'Payment Date', 'Method'],
            'expenses'       => ['Date', 'Title', 'Category', 'Amount', 'Method', 'Reference', 'Recorded By'],
            'income'         => ['Date', 'Title', 'Category', 'Amount', 'Method', 'Reference'],
            'outstanding'    => ['Student', 'Invoice', 'Amount', 'Due Date', 'Status'],
            'ledger'         => [],
            default          => [],
        };
    }

    public function title(): string
    {
        return ucwords(str_replace('_', ' ', $this->type));
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font'      => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill'      => ['fillType' => 'solid', 'startColor' => ['argb' => 'FF1a3c6e']],
                'alignment' => ['horizontal' => 'center'],
            ],
        ];
    }
}
