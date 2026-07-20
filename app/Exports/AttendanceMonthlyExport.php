<?php

namespace App\Exports;

use App\Models\SchoolClass;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AttendanceMonthlyExport implements
    FromArray,
    WithHeadings,
    WithTitle,
    WithColumnWidths,
    WithStyles
{
    public function __construct(
        private readonly array      $rows,
        private readonly SchoolClass $class,
        private readonly int         $month,
        private readonly int         $year,
    ) {}

    public function title(): string
    {
        return 'Attendance ' . Carbon::createFromDate($this->year, $this->month, 1)->format('M Y');
    }

    public function headings(): array
    {
        return [
            '#',
            'Student Name',
            'Total Days',
            'Present',
            'Absent',
            'Late',
            'Attendance %',
            'Status',
        ];
    }

    public function array(): array
    {
        $data = [];
        foreach ($this->rows as $i => $row) {
            $student    = $row['student'];
            $percentage = $row['percentage'];

            $data[] = [
                $i + 1,
                $student ? $student->full_name : 'Unknown',
                $row['total'],
                $row['present'],
                $row['absent'],
                $row['late'],
                $percentage . '%',
                $percentage >= 75 ? 'OK' : 'LOW',
            ];
        }

        return $data;
    }

    public function columnWidths(): array
    {
        return [
            'A' => 5,
            'B' => 30,
            'C' => 12,
            'D' => 10,
            'E' => 10,
            'F' => 10,
            'G' => 15,
            'H' => 10,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        // Header row style
        $sheet->getStyle('A1:H1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4F46E5'],
            ],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        // Add school name + month title above headers
        $sheet->insertNewRowBefore(1, 2);
        $sheet->mergeCells('A1:H1');
        $sheet->setCellValue('A1', config('app.name', 'School') . ' — Monthly Attendance Report');
        $sheet->mergeCells('A2:H2');
        $sheet->setCellValue(
            'A2',
            'Class: ' . $this->class->class_name . ' | ' .
            Carbon::createFromDate($this->year, $this->month, 1)->format('F Y')
        );
        $sheet->getStyle('A1:H2')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 13],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        return [];
    }
}
