@extends('adminlte::page')

@section('admin_title', 'Detail Pajak')

@section('content_header', 'Detail Pajak')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <table class="table table-bordered">
                        <tbody>
                            <tr>
                                <th>Nama</th>
                                <td>{{ $tax->name }}</td>
                            </tr>
                            <tr>
                                <th>Tarif</th>
                                <td>{{ number_format($tax->rate, 2) }}%</td>
                            </tr>
                            <tr>
                                <th>Status</th>
                                <td>
                                    @if($tax->is_active)
                                        <span class="badge badge-success">Aktif</span>
                                    @else
                                        <span class="badge badge-secondary">Nonaktif</span>
                                    @endif
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <a href="{{ route('taxes.index') }}" class="btn btn-secondary">
                        <i class="fa fa-arrow-left"></i> Kembali
                    </a>
                    <a href="{{ route('taxes.edit', $tax->id) }}" class="btn btn-warning">
                        <i class="fa fa-edit"></i> Edit
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
