<?php

namespace App\Exports;

use App\Models\CustomerDebt;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CustomerDebtExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function collection()
    {
        $query = CustomerDebt::with(['customer', 'sale']);

        if ($this->request->filled('status')) {
            $query->where('status', $this->request->input('status'));
        }

        if ($this->request->filled('customer_id')) {
            $query->where('customer_id', $this->request->input('customer_id'));
        }

        return $query->latest()->get();
    }

    public function map($debt): array
    {
        $statusLabel = match ($debt->status) {
            'unpaid'  => 'Belum Dibayar',
            'partial' => 'Sebagian',
            'paid'    => 'Lunas',
            default   => ucfirst($debt->status),
        };

        return [
            $debt->customer->name ?? '-',
            $debt->sale->invoice_number ?? '-',
            $debt->total_amount,
            $debt->paid_amount,
            $debt->remaining_amount,
            $debt->due_date ? $debt->due_date->format('d/m/Y') : '-',
            $statusLabel,
        ];
    }

    public function headings(): array
    {
        return [
            'Pelanggan',
            'Invoice Penjualan',
            'Total Piutang',
            'Sudah Dibayar',
            'Sisa Piutang',
            'Jatuh Tempo',
            'Status',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    public function title(): string
    {
        return 'Daftar Piutang Pelanggan';
    }
}
