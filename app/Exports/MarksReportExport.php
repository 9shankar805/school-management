<?php

namespace App\Exports;

use App\Models\Course;
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

    /**
     * Ordered list of course names used for the header columns.
     * Computed from the UNION of all students' courses, deduplicated by NAME
     * (not id) because each semester has its own course row with a different
     * id for the same subject. This prevents duplicate columns.
     */
    private array $headerCourseNames = [];

    /** $results is the Collection->toArray() from ResultRepository::getClassResults() */
    public function __construct(array $results, SchoolClass $class, Semester $semester)
    {
        $this->results  = $results;
        $this->class    = $class;
        $this->semester = $semester;

        // Build the canonical course column order from the COURSES TABLE (the
        // configured subject list), NOT from students' results. This ensures
        // subjects with zero marks entered still appear as columns with blank
        // cells. Deduplicated by course_name (each semester has its own row).
        // Also includes any subjects found in student results as a safety net.
        $dbNames = Course::where('class_id', $class->id)
            ->distinct()
            ->pluck('course_name')
            ->toArray();

        $resultNames = collect($results)
            ->flatMap(function ($r) {
                return collect($r['courses'] ?? [])->map(function ($c) {
                    $course = $c['course'] ?? null;
                    if (is_array($course)) return $course['course_name'] ?? null;
                    return $course?->course_name ?? null;
                });
            })
            ->filter()
            ->unique()
            ->toArray();

        $this->headerCourseNames = collect(array_merge($dbNames, $resultNames))
            ->filter()
            ->unique()
            ->sort()
            ->values()
            ->toArray();
    }

    public function title(): string
    {
        return 'Results ' . $this->class->class_name . ' ' . $this->semester->semester_name;
    }

    public function headings(): array
    {
        // Dynamic course columns — derived from the deduplicated course name list
        // so each subject appears exactly once regardless of how many semester
        // course rows exist for it.
        $courseCols = [];
        foreach ($this->headerCourseNames as $name) {
            $courseCols[] = $name . ' (FM)';
            $courseCols[] = $name . ' (Grade)';
        }

        return array_merge(
            ['Rank', 'Student Name'],
            $courseCols,
            ['Total GPA', 'Total Marks', 'Passed', 'Failed', 'Result']
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

            // Index this student's courses by course_name so we can align with
            // the header (deduplicated by name). Missing courses get blank cells
            // so the summary columns never drift left.
            $studentCourses = collect($row['courses'] ?? [])->keyBy(function ($c) {
                $course = $c['course'] ?? null;
                if (is_array($course)) return $course['course_name'] ?? '';
                return $course?->course_name ?? '';
            });

            foreach ($this->headerCourseNames as $name) {
                $c = $studentCourses->get($name);
                $line[] = $c ? $c['final_marks'] : '';
                $line[] = $c ? $c['grade'] : '';
            }

            $line[] = $row['gpa'];
            $line[] = round($row['totalMarks'], 1);
            $line[] = $row['passed'];
            $line[] = $row['failed'];
            $line[] = ($row['resultStatus'] ?? '') === 'PASS' ? 'PASS' : '';

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
