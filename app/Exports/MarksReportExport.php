<?php

namespace App\Exports;

use App\Models\SchoolClass;
use App\Models\Semester;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class MarksReportExport implements FromArray, WithHeadings, WithTitle, WithColumnWidths, WithStyles
{
    private array  $results;
    private SchoolClass $class;
    private Semester    $semester;

    /** $results is the Collection->toArray() from ResultRepository::getClassResults() */
    public function __construct(array $results, SchoolClass $class, Semester $semester)
    {
        $this->results  = $results;
        $this->class    = $class;
        $this->semester = $semester;
    }

    public function title(): string
    {
        return 'Results ' . $this->class->class_name . ' ' . $this->semester->semester_name;
    }

    public function headings(): array
    {
        // Dynamic course columns — derive from first student's course list
        $courseCols = [];
        if (! empty($this->results)) {
            foreach ($this->results[0]['courses'] as $c) {
                $name = $c['course']?->course_name ?? 'Course';
                $courseCols[] = $name . ' (FM)';
                $courseCols[] = $name . ' (Grade)';
            }
        }

        return array_merge(
            ['Rank', 'Student Name'],
            $courseCols,
            ['GPA', 'Total Marks', 'Passed', 'Failed', 'Result']
        );
    }

    public function array(): array
    {
        $rows = [];

        foreach ($this->results as $row) {
            $student = $row['student'];
            $line    = [
                $row['rank'],
                $student?->full_name ?? '—',
            ];

            foreach ($row['courses'] as $c) {
                $line[] = $c['final_marks'];
                $line[] = $c['grade'];
            }

            $line[] = $row['gpa'];
            $line[] = round($row['totalMarks'], 1);
            $line[] = $row['passed'];
            $line[] = $row['failed'];
            $line[] = $row['failed'] === 0 ? 'PASS' : 'FAIL';

            $rows[] = $line;
        }

        return $rows;
    }

    public function columnWidths(): array
    {
        // Fixed columns — dynamic course columns get default width
        return ['A' => 6, 'B' => 28];
    }

    public function styles(Worksheet $sheet): array
    {
        // Insert 3 title rows above the heading row
        $sheet->insertNewRowBefore(1, 3);

        $lastCol = $sheet->getHighestColumn();

        $sheet->mergeCells("A1:{$lastCol}1");
        $sheet->setCellValue('A1', config('app.name', 'School') . ' — Exam Result Sheet');

        $sheet->mergeCells("A2:{$lastCol}2");
        $sheet->setCellValue('A2',
            'Class: ' . $this->class->class_name . '   |   Semester: ' . $this->semester->semester_name .
            '   |   Generated: ' . now()->format('d M Y')
        );

        $sheet->mergeCells("A3:{$lastCol}3");
        // blank separator row

        // Title styles
        $sheet->getStyle('A1')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 14],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $sheet->getStyle('A2')->applyFromArray([
            'font'      => ['size' => 11],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        // Header row (now row 4)
        $sheet->getStyle("A4:{$lastCol}4")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4F46E5'],
            ],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        // Conditional colour: PASS = green, FAIL = red in last column
        $highestRow = $sheet->getHighestRow();
        for ($row = 5; $row <= $highestRow; $row++) {
            $val = $sheet->getCell("{$lastCol}{$row}")->getValue();
            $color = $val === 'PASS' ? 'd1fae5' : 'fee2e2';
            $sheet->getStyle("{$lastCol}{$row}")->applyFromArray([
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $color]],
            ]);
        }

        return [];
    }
}
