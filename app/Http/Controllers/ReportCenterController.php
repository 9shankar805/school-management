<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportCenterController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:admin|principal');
    }

    public function index()
    {
        return view('reports.index');
    }

    public function builder()
    {
        return view('reports.builder');
    }

    public function schedule()
    {
        return view('reports.schedule');
    }

    // ── Column definitions per data source ────────────────────────────────
    private function sourceConfig(): array
    {
        return [
            'students' => [
                'label'   => 'Students Directory',
                'columns' => [
                    'id'             => ['label' => 'ID',              'select' => 'u.id'],
                    'first_name'     => ['label' => 'First Name',      'select' => 'u.first_name'],
                    'last_name'      => ['label' => 'Last Name',       'select' => 'u.last_name'],
                    'gender'         => ['label' => 'Gender',          'select' => 'u.gender'],
                    'class'          => ['label' => 'Grade / Class',   'select' => 'sc.class_name'],
                    'section'        => ['label' => 'Section',         'select' => 'se.section_name'],
                    'admission_no'   => ['label' => 'Admission No.',   'select' => 'u.admission_no'],
                    'enrollment_date'=> ['label' => 'Enrollment Date', 'select' => 'u.admission_date'],
                    'roll_no'        => ['label' => 'Roll No.',        'select' => 'u.roll_no'],
                    'email'          => ['label' => 'Email',           'select' => 'u.email'],
                    'phone'          => ['label' => 'Phone',           'select' => 'u.phone'],
                    'father_name'    => ['label' => "Father's Name",   'select' => 'spi.father_name'],
                    'mother_name'    => ['label' => "Mother's Name",   'select' => 'spi.mother_name'],
                ],
                'query' => function (array $cols, ?string $dateFrom, ?string $dateTo) {
                    $selectParts = [];
                    foreach ($cols as $key => $def) {
                        $selectParts[] = DB::raw($def['select'] . ' AS `' . $key . '`');
                    }
                    // Use a subquery to get the latest promotion per student
                    // avoiding GROUP BY issues with MySQL strict mode
                    $q = DB::table('users AS u')
                        ->select($selectParts)
                        ->where('u.role', 'student')
                        ->leftJoinSub(
                            DB::table('promotions')->select('student_id', 'class_id', 'section_id')
                                ->whereIn('id', function ($sub) {
                                    $sub->selectRaw('MAX(id)')->from('promotions')->groupBy('student_id');
                                }),
                            'p', 'p.student_id', '=', 'u.id'
                        )
                        ->leftJoin('school_classes AS sc',        'sc.id',         '=', 'p.class_id')
                        ->leftJoin('sections AS se',              'se.id',         '=', 'p.section_id')
                        ->leftJoinSub(
                            DB::table('student_parent_infos')->select('student_id', 'father_name', 'mother_name')
                                ->whereIn('id', function ($sub) {
                                    $sub->selectRaw('MAX(id)')->from('student_parent_infos')->groupBy('student_id');
                                }),
                            'spi', 'spi.student_id', '=', 'u.id'
                        )
                        ->whereNull('u.deleted_at');
                    if ($dateFrom) $q->whereDate('u.admission_date', '>=', $dateFrom);
                    if ($dateTo)   $q->whereDate('u.admission_date', '<=', $dateTo);
                    return $q;
                },
            ],

            'staff' => [
                'label'   => 'Staff Directory',
                'columns' => [
                    'id'        => ['label' => 'ID',        'select' => 'u.id'],
                    'name'      => ['label' => 'Full Name', 'select' => "CONCAT(COALESCE(u.first_name,''),' ',COALESCE(u.last_name,''))"],
                    'email'     => ['label' => 'Email',     'select' => 'u.email'],
                    'role'      => ['label' => 'Role',      'select' => 'u.role'],
                    'phone'     => ['label' => 'Phone',     'select' => 'u.phone'],
                    'join_date' => ['label' => 'Join Date', 'select' => 'u.created_at'],
                ],
                'query' => function (array $cols, ?string $dateFrom, ?string $dateTo) {
                    $selectParts = [];
                    foreach ($cols as $key => $def) {
                        $selectParts[] = DB::raw($def['select'] . ' AS `' . $key . '`');
                    }
                    $q = DB::table('users AS u')
                        ->select($selectParts)
                        ->whereNotIn('u.role', ['student', 'parent', 'super-admin'])
                        ->whereNull('u.deleted_at');
                    if ($dateFrom) $q->whereDate('u.created_at', '>=', $dateFrom);
                    if ($dateTo)   $q->whereDate('u.created_at', '<=', $dateTo);
                    return $q;
                },
            ],

            'invoices' => [
                'label'   => 'Invoices / Finance',
                'columns' => [
                    'invoice_number'=> ['label' => 'Invoice No.',   'select' => 'i.invoice_number'],
                    'student_name'  => ['label' => 'Student Name',  'select' => "CONCAT(COALESCE(u.first_name,''),' ',COALESCE(u.last_name,''))"],
                    'title'         => ['label' => 'Title',         'select' => 'i.title'],
                    'amount'        => ['label' => 'Amount',        'select' => 'i.amount'],
                    'total_amount'  => ['label' => 'Total',         'select' => 'i.total_amount'],
                    'status'        => ['label' => 'Status',        'select' => 'i.status'],
                    'due_date'      => ['label' => 'Due Date',      'select' => 'i.due_date'],
                    'billing_month' => ['label' => 'Month',         'select' => 'i.billing_month'],
                    'billing_year'  => ['label' => 'Year',          'select' => 'i.billing_year'],
                ],
                'query' => function (array $cols, ?string $dateFrom, ?string $dateTo) {
                    $selectParts = [];
                    foreach ($cols as $key => $def) {
                        $selectParts[] = DB::raw($def['select'] . ' AS `' . $key . '`');
                    }
                    $q = DB::table('invoices AS i')
                        ->select($selectParts)
                        ->leftJoin('users AS u', 'u.id', '=', 'i.student_id')
                        ->whereNull('i.deleted_at');
                    if ($dateFrom) $q->whereDate('i.due_date', '>=', $dateFrom);
                    if ($dateTo)   $q->whereDate('i.due_date', '<=', $dateTo);
                    return $q;
                },
            ],
        ];
    }

    // ── Resolve rows from request ──────────────────────────────────────────
    private function resolveRows(Request $request, int $limit = 0): array
    {
        $sources   = $this->sourceConfig();
        $sourceKey = $request->input('source', 'students');

        if (!isset($sources[$sourceKey])) {
            return ['columns' => [], 'rows' => [], 'label' => 'Unknown'];
        }

        $sourceDef  = $sources[$sourceKey];
        $allCols    = $sourceDef['columns'];
        $requested  = $request->input('columns', array_keys($allCols));
        // Only keep valid keys
        $selectedKeys = array_filter((array) $requested, fn($k) => isset($allCols[$k]));
        if (empty($selectedKeys)) $selectedKeys = array_keys($allCols);

        $selectedCols = array_intersect_key($allCols, array_flip($selectedKeys));
        $dateFrom     = $request->input('date_from') ?: null;
        $dateTo       = $request->input('date_to')   ?: null;

        try {
            $query = ($sourceDef['query'])($selectedCols, $dateFrom, $dateTo);
            if ($limit > 0) $query->limit($limit);
            $rows = $query->get()->map(fn($r) => (array) $r)->toArray();
        } catch (\Throwable $e) {
            // Return empty gracefully if table doesn't exist yet
            $rows = [];
        }

        return [
            'label'   => $sourceDef['label'],
            'columns' => $selectedCols,
            'rows'    => $rows,
        ];
    }

    // ── AJAX: live preview (max 20 rows) ──────────────────────────────────
    public function builderPreview(Request $request)
    {
        $data = $this->resolveRows($request, 20);
        return response()->json([
            'columns' => array_map(fn($c) => $c['label'], $data['columns']),
            'keys'    => array_keys($data['columns']),
            'rows'    => $data['rows'],
            'total'   => count($data['rows']),
        ]);
    }

    // ── Export: PDF or Excel ───────────────────────────────────────────────
    public function builderExport(Request $request)
    {
        $format = $request->input('format', 'excel'); // 'pdf' | 'excel'
        $data   = $this->resolveRows($request);       // no limit for export
        $title  = $data['label'] . ' Report';
        $cols   = $data['columns'];
        $rows   = $data['rows'];

        if ($format === 'pdf') {
            // Cap PDF at 500 rows to avoid memory exhaustion with large datasets
            if (count($rows) > 500) {
                $rows = array_slice($rows, 0, 500);
                $title .= ' (first 500 rows)';
            }
            ini_set('memory_limit', '256M');
            $pdf = Pdf::loadView('reports.export-pdf', compact('title', 'cols', 'rows'))
                ->setPaper('a4', 'landscape')
                ->setOptions([
                    'dpi'             => 120,
                    'defaultFont'     => 'DejaVu Sans',
                    'isHtml5ParserEnabled' => true,
                    'margin_top'    => 10,
                    'margin_bottom' => 10,
                    'margin_left'   => 10,
                    'margin_right'  => 10,
                ]);
            $filename = \Illuminate\Support\Str::slug($title) . '-' . now()->format('Ymd') . '.pdf';
            return $pdf->download($filename);
        }

        // Excel / CSV via simple CSV stream (no extra package needed)
        $filename = \Illuminate\Support\Str::slug($title) . '-' . now()->format('Ymd') . '.csv';
        $headers  = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        $callback = function () use ($cols, $rows) {
            $handle = fopen('php://output', 'w');
            // UTF-8 BOM so Excel opens it correctly
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));
            // Header row
            fputcsv($handle, array_map(fn($c) => $c['label'], $cols));
            // Data rows
            foreach ($rows as $row) {
                $line = [];
                foreach (array_keys($cols) as $key) {
                    $line[] = $row[$key] ?? '';
                }
                fputcsv($handle, $line);
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}
