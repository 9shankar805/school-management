<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithMapping;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;

class IemisStudentExport implements
    FromCollection,
    WithHeadings,
    WithTitle,
    WithStyles,
    ShouldAutoSize,
    WithMapping
{
    public function __construct(
        private Collection $rows,
        private mixed      $session,
    ) {}

    public function collection(): Collection
    {
        return $this->rows->values();
    }

    public function map($row): array
    {
        static $sn = 0;
        $sn++;
        $row['SN'] = $sn;
        return array_values($row);
    }

    public function headings(): array
    {
        if ($this->rows->isEmpty()) {
            return ['No Data'];
        }
        return array_keys($this->rows->first());
    }

    public function title(): string
    {
        return 'IEMIS Students ' . ($this->session?->session_name ?? now()->year);
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            // Header row — dark blue background, white bold text
            1 => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => [
                    'fillType'   => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF1E3A8A'],
                ],
            ],
        ];
    }
}
