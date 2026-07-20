<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Collection;

class FinanceReportExport implements FromCollection, WithHeadings, WithTitle, WithStyles, ShouldAutoSize
{
    public function __construct(
        private string $reportType,
        private array  $data,
    ) {}

    public function collection(): Collection
    {
        return match ($this->reportType) {
            'fee_collection' => $this->feeCollectionRows(),
            default          => collect(),
        };
    }

    public function headings(): array
    {
        return match ($this->reportType) {
            'fee_collection' => [
                'Receipt #', 'Date', 'Student', 'Invoice #',
                'Amount Paid', 'Method', 'Reference', 'Received By',
            ],
            default => [],
        };
    }

    public function title(): string
    {
        return match ($this->reportType) {
            'fee_collection' => 'Fee Collection',
            default          => 'Report',
        };
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    // ── Private row builders ──────────────────────────────────────────────────
    private function feeCollectionRows(): Collection
    {
        return collect($this->data['payments'] ?? [])->map(fn ($p) => [
            $p->receipt_number,
            optional($p->payment_date)->format('Y-m-d'),
            optional($p->invoice?->student)->full_name ?? '—',
            $p->invoice?->invoice_number ?? '—',
            number_format($p->amount_paid, 2),
            ucfirst(str_replace('_', ' ', $p->payment_method ?? '')),
            $p->transaction_reference ?? '',
            optional($p->receivedBy)->full_name ?? '—',
        ]);
    }
}
