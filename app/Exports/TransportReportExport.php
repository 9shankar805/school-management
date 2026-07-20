<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TransportReportExport implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize, WithStyles
{
    public function __construct(
        protected Collection $records,
        protected string $reportType
    ) {}

    public function collection(): Collection
    {
        return match ($this->reportType) {
            'fleet' => $this->records->map(fn($v) => [
                $v->name, $v->registration_number, ucfirst($v->type),
                $v->make, $v->model, $v->year, $v->capacity,
                ucfirst($v->fuel_type), ucfirst($v->status),
                $v->driver?->name ?? '—',
                $v->insurance_expiry?->format('d M Y') ?? '—',
                $v->fitness_expiry?->format('d M Y') ?? '—',
            ]),

            'drivers' => $this->records->map(fn($d) => [
                $d->name, $d->employee_id ?? '—', $d->phone ?? '—', $d->email ?? '—',
                $d->license_number, $d->license_type ?? '—',
                $d->license_expiry?->format('d M Y') ?? '—',
                $d->currentVehicle?->name ?? '—',
                ucfirst($d->status),
            ]),

            'routes' => $this->records->map(fn($r) => [
                $r->name, $r->code ?? '—',
                $r->vehicle?->name ?? '—', $r->driver?->name ?? '—',
                $r->morning_departure ?? '—', $r->afternoon_departure ?? '—',
                $r->distance_km ?? '—',
                '$' . number_format($r->monthly_fee, 2),
                $r->active_students_count,
                ucfirst($r->status),
            ]),

            'students' => $this->records->map(fn($s) => [
                $s->student->first_name . ' ' . $s->student->last_name,
                $s->student->email,
                $s->route->name,
                $s->stop?->name ?? '—',
                ucfirst($s->direction),
                '$' . number_format($s->monthly_fee, 2),
                ucfirst($s->status),
                $s->start_date->format('d M Y'),
                $s->end_date?->format('d M Y') ?? 'Ongoing',
            ]),

            'fuel' => $this->records->map(fn($f) => [
                $f->vehicle->name, $f->vehicle->registration_number,
                $f->date->format('d M Y'),
                $f->litres, '$' . number_format($f->cost_per_litre, 2),
                '$' . number_format($f->total_cost, 2),
                $f->odometer_reading ?? '—',
                $f->fuel_station ?? '—',
            ]),

            'maintenance' => $this->records->map(fn($m) => [
                $m->vehicle->name, $m->vehicle->registration_number,
                $m->type_label, $m->title,
                $m->service_date->format('d M Y'),
                $m->next_service_date?->format('d M Y') ?? '—',
                '$' . number_format($m->cost, 2),
                $m->service_provider ?? '—',
                ucfirst($m->status),
            ]),

            default => collect(),
        };
    }

    public function headings(): array
    {
        return match ($this->reportType) {
            'fleet'       => ['Name', 'Reg. No.', 'Type', 'Make', 'Model', 'Year', 'Capacity', 'Fuel', 'Status', 'Driver', 'Insurance Expiry', 'Fitness Expiry'],
            'drivers'     => ['Name', 'Employee ID', 'Phone', 'Email', 'License No.', 'License Type', 'License Expiry', 'Assigned Vehicle', 'Status'],
            'routes'      => ['Route Name', 'Code', 'Vehicle', 'Driver', 'Morning Dep.', 'Afternoon Dep.', 'Distance (km)', 'Monthly Fee', 'Active Students', 'Status'],
            'students'    => ['Student Name', 'Email', 'Route', 'Stop', 'Direction', 'Monthly Fee', 'Status', 'Start Date', 'End Date'],
            'fuel'        => ['Vehicle', 'Reg. No.', 'Date', 'Litres', 'Cost/Litre', 'Total Cost', 'Odometer', 'Station'],
            'maintenance' => ['Vehicle', 'Reg. No.', 'Type', 'Title', 'Service Date', 'Next Service', 'Cost', 'Provider', 'Status'],
            default       => [],
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
