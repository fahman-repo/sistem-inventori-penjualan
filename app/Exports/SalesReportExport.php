<?php

namespace App\Exports;

use App\Models\Sale;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SalesReportExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
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
        $query = Sale::with('user');

        // Filter by date range
        if ($this->request->filled('date_from')) {
            $query->where('sale_date', '>=', $this->request->input('date_from'));
        }

        if ($this->request->filled('date_to')) {
            $query->where('sale_date', '<=', $this->request->input('date_to'));
        }

        // Filter by kasir (user)
        if ($this->request->filled('user_id')) {
            $query->where('user_id', $this->request->input('user_id'));
        }

        // Non-admin only sees their own sales
        if (auth()->user()->role !== 'admin') {
            $query->where('user_id', auth()->id());
        }

        return $query->latest()->get();
    }

    /**
     * @param Sale $sale
     * @return array
     */
    public function map($sale): array
    {
        return [
            $sale->sale_date->format('d/m/Y'),
            $sale->invoice_number,
            $sale->user->name,
            $sale->total,
        ];
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'Tanggal',
            'Invoice',
            'Kasir',
            'Total',
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
        return 'Laporan Penjualan';
    }
}