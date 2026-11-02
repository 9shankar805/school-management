<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class MarksTemplateExport implements FromArray, WithHeadings, WithTitle, WithColumnWidths, WithStyles, WithEvents
{
    /** @var array  Each item: ['student_id', 'roll_no', 'name'] */
    private array $students;

    /**
     * Subjects array — for all-subjects mode:
     *   [['course_id' => 5, 'name' => 'Nepali', 'fm' => 100, 'pm' => 40], ...]
     * For single-subject mode, one entry with exam_id instead of course_id.
     */
    private array $subjects;

    private bool  $isAllSubjectsMode;
    private string $sheetTitle;
    private string $className;
    private string $sectionName;
    private string $semesterName;

    public function __construct(
        array  $students,
        array  $subjects,
        bool   $isAllSubjectsMode,
        string $className,
        string $sectionName,
        string $semesterName
    ) {
        $this->students          = $students;
        $this->subjects          = $subjects;
        $this->isAllSubjectsMode = $isAllSubjectsMode;
        $this->className         = $className;
        $this->sectionName       = $sectionName;
        $this->semesterName      = $semesterName;
        $this->sheetTitle        = 'Marks Ledger';
    }

    public function title(): string
    {
        return $this->sheetTitle;
    }

    public function headings(): array
    {
        // Fixed columns
        $heads = ['student_id', 'roll_no', 'student_name'];

        // One column per subject/exam — label shows name + FM in brackets
        foreach ($this->subjects as $sub) {
            $heads[] = $sub['name'] . ' (FM:' . $sub['fm'] . ')';
        }

        return $heads;
    }

    public function array(): array
    {
        $rows = [];
        foreach ($this->students as $stu) {
            $row = [
                $stu['student_id'],
                $stu['roll_no'],
                $stu['name'],
            ];
            // Leave mark cells blank for teacher input
            foreach ($this->subjects as $sub) {
                $row[] = '';
            }
            $rows[] = $row;
        }
        return $rows;
    }

    public function columnWidths(): array
    {
        $widths = [
            'A' => 12,  // student_id
            'B' => 8,   // roll_no
            'C' => 28,  // student_name
        ];

        // Dynamic subject columns: D onward
        $colIndex = 4; // D = 4
        foreach ($this->subjects as $sub) {
            $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex);
            $widths[$col] = 18;
            $colIndex++;
        }

        return $widths;
    }

    public function styles(Worksheet $sheet): array
    {
        return [];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet    = $event->sheet->getDelegate();
                $lastCol  = $sheet->getHighestColumn();
                $lastRow  = $sheet->getHighestRow();

                // ── Insert 3 header/info rows above data ──────────────────
                $sheet->insertNewRowBefore(1, 3);

                // Row 1 — Title
                $sheet->mergeCells("A1:{$lastCol}1");
                $sheet->setCellValue('A1', 'MARKS ENTRY LEDGER — ' . strtoupper($this->className) . ' | ' . $this->sectionName . ' | ' . $this->semesterName);
                $sheet->getStyle('A1')->applyFromArray([
                    'font'      => ['bold' => true, 'size' => 13, 'color' => ['rgb' => 'FFFFFF']],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4F46E5']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);
                $sheet->getRowDimension(1)->setRowHeight(28);

                // Row 2 — Instruction
                $sheet->mergeCells("A2:{$lastCol}2");
                $sheet->setCellValue('A2', '⚠ DO NOT modify student_id, roll_no or student_name columns. Enter marks only in the coloured subject columns. Leave blank for absent.');
                $sheet->getStyle('A2')->applyFromArray([
                    'font'      => ['italic' => true, 'size' => 9, 'color' => ['rgb' => '92400E']],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FEF3C7']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);
                $sheet->getRowDimension(2)->setRowHeight(18);

                // Row 3 — blank separator
                $sheet->getRowDimension(3)->setRowHeight(6);

                // Row 4 — Column headings (shifted down by 3)
                $headingRow = 4;
                $sheet->getStyle("A{$headingRow}:{$lastCol}{$headingRow}")->applyFromArray([
                    'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E1B4B']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                    'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '6366F1']]],
                ]);
                $sheet->getRowDimension($headingRow)->setRowHeight(22);

                // student_id and roll_no columns — light gray, protected feel
                $dataStart  = $headingRow + 1;
                $dataEnd    = $sheet->getHighestRow();
                $sheet->getStyle("A{$dataStart}:A{$dataEnd}")->applyFromArray([
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F1F5F9']],
                    'font'      => ['color' => ['rgb' => '94A3B8'], 'size' => 9],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);
                $sheet->getStyle("B{$dataStart}:B{$dataEnd}")->applyFromArray([
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F1F5F9']],
                    'font'      => ['bold' => true, 'color' => ['rgb' => '4F46E5'], 'size' => 10],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);
                // Student name column
                $sheet->getStyle("C{$dataStart}:C{$dataEnd}")->applyFromArray([
                    'font'      => ['bold' => true, 'size' => 10],
                ]);

                // Subject/marks columns — light indigo tint, editable feel
                $subjectStartCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(4);
                $sheet->getStyle("{$subjectStartCol}{$headingRow}:{$lastCol}{$headingRow}")->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '6366F1']],
                ]);
                $sheet->getStyle("{$subjectStartCol}{$dataStart}:{$lastCol}{$dataEnd}")->applyFromArray([
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'EEF2FF']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                    'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'C7D2FE']]],
                ]);

                // Alternating row shading for readability
                for ($r = $dataStart; $r <= $dataEnd; $r++) {
                    if (($r - $dataStart) % 2 === 1) {
                        $sheet->getStyle("A{$r}:C{$r}")->applyFromArray([
                            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F8FAFC']],
                        ]);
                    }
                }

                // Freeze panes: freeze Roll + Name columns so subject cols scroll freely
                $sheet->freezePane("D{$dataStart}");

                // Auto-filter on heading row
                $sheet->setAutoFilter("A{$headingRow}:{$lastCol}{$headingRow}");
            },
        ];
    }
}
