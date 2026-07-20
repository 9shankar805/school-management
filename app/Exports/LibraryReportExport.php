<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class LibraryReportExport implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize, WithStyles
{
    public function __construct(
        protected Collection $records,
        protected string $reportType
    ) {}

    public function collection(): Collection
    {
        return match ($this->reportType) {
            'catalog' => $this->records->map(fn($b) => [
                $b->title,
                $b->author ?? '—',
                $b->category?->name ?? '—',
                $b->isbn ?? '—',
                $b->barcode ?? '—',
                $b->publisher ?? '—',
                $b->edition ?? '—',
                $b->publication_year ?? '—',
                $b->language,
                $b->qty,
                $b->available_qty,
                $b->price ? '$' . number_format($b->price, 2) : '—',
                $b->shelf_location ?? '—',
            ]),

            'issued', 'overdue' => $this->records->map(fn($i) => [
                $i->book->title,
                $i->member->user->first_name . ' ' . $i->member->user->last_name,
                $i->member->card_number,
                $i->issue_date->format('d M Y'),
                $i->due_date->format('d M Y'),
                $i->return_date?->format('d M Y') ?? '—',
                $i->overdue_days > 0 ? $i->overdue_days . ' days' : '—',
                $i->fine_amount > 0 ? '$' . number_format($i->fine_amount, 2) : '—',
                ucfirst($i->fine_status),
                ucfirst($i->status),
            ]),

            'fines' => $this->records->map(fn($i) => [
                $i->book->title,
                $i->member->user->first_name . ' ' . $i->member->user->last_name,
                $i->member->card_number,
                $i->due_date->format('d M Y'),
                $i->return_date?->format('d M Y') ?? '—',
                $i->overdue_days,
                '$' . number_format($i->fine_amount, 2),
                ucfirst($i->fine_status),
            ]),

            'members' => $this->records->map(fn($m) => [
                $m->user->first_name . ' ' . $m->user->last_name,
                $m->user->email,
                $m->card_number,
                ucfirst($m->member_type),
                ucfirst($m->status),
                $m->max_books,
                $m->loan_days,
                $m->active_issues_count ?? 0,
                $m->outstanding_fine > 0 ? '$' . number_format($m->outstanding_fine, 2) : '—',
                $m->membership_start->format('d M Y'),
                $m->membership_end?->format('d M Y') ?? 'Indefinite',
            ]),

            default => collect(),
        };
    }

    public function headings(): array
    {
        return match ($this->reportType) {
            'catalog'           => ['Title', 'Author', 'Category', 'ISBN', 'Barcode', 'Publisher', 'Edition', 'Year', 'Language', 'Total Qty', 'Available', 'Price', 'Shelf'],
            'issued', 'overdue' => ['Book Title', 'Member Name', 'Card No.', 'Issue Date', 'Due Date', 'Return Date', 'Overdue Days', 'Fine Amount', 'Fine Status', 'Status'],
            'fines'             => ['Book Title', 'Member Name', 'Card No.', 'Due Date', 'Return Date', 'Overdue Days', 'Fine Amount', 'Fine Status'],
            'members'           => ['Name', 'Email', 'Card No.', 'Type', 'Status', 'Max Books', 'Loan Days', 'Active Loans', 'Outstanding Fine', 'Joined', 'Expires'],
            default             => [],
        };
    }

    public function title(): string
    {
        return ucfirst($this->reportType) . ' Report';
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
