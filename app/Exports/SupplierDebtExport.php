<?php

namespace App\Exports;

use App\Models\SupplierDebt;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SupplierDebtExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $query = SupplierDebt::with(['supplier', 'purchase']);

        // Filter by status
        if ($this->request->filled('status')) {
            $query->where('status', $this->request->input('status'));
        }

        // Filter by supplier
        if ($this->request->filled('supplier_id')) {
            $query->where('supplier_id', $this->request->input('supplier_id'));
        }

        return $query->latest()->get();
    }

    /**
     * @param SupplierDebt $debt
     * @return array
     */
    public function map($debt): array
    {
        $statusLabel = match ($debt->status) {
            'unpaid'  => 'Belum Dibayar',
            'partial' => 'Sebagian',
            'paid'    => 'Lunas',
            default   => ucfirst($debt->status),
        };

        return [
            $debt->supplier->name ?? '-',
            $debt->purchase->invoice_number ?? '-',
            $debt->total_amount,
            $debt->paid_amount,
            $debt->remaining_amount,
            $debt->due_date ? $debt->due_date->format('d/m/Y') : '-',
            $statusLabel,
        ];
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'Supplier',
            'Invoice Pembelian',
            'Total Utang',
            'Sudah Dibayar',
            'Sisa Utang',
            'Jatuh Tempo',
            'Status',
        ];
    }

    /**
     * @param Worksheet $sheet
     */
    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as bold (header)
            1 => ['font' => ['bold' => true]],
        ];
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return 'Daftar Utang Supplier';
    }
}