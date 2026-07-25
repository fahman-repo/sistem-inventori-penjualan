@extends('adminlte::page')

@section('admin_title', 'Daftar Pajak')

@section('content_header', 'Daftar Pajak')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            <div class="card">
                <div class="card-header">
                    <a href="{{ route('taxes.create') }}" class="btn btn-primary">
                        <i class="fa fa-plus"></i> Tambah Pajak
                    </a>
                </div>
                <div class="card-body">
                    @if($taxes->count() > 0)
                    <table id="taxes-table" class="table table-bordered table-hover dataTable" style="width:100%">
                        <thead class="thead-light">
                            <tr>
                                <th>#</th>
                                <th>Nama</th>
                                <th>Tarif</th>
                                <th>Status</th>
                                <th>Tanggal Dibuat</th>
                                <th class="text-center">Tindakan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($taxes as $tax)
                            <tr>
                                <td>{{ $loop->iteration + ($taxes->currentPage() - 1) * $taxes->perPage() }}</td>
                                <td>{{ $tax->name }}</td>
                                <td><span class="badge badge-info">{{ number_format($tax->rate, 2) }}%</span></td>
                                <td>
                                    @if($tax->is_active)
                                        <span class="badge badge-success">Aktif</span>
                                    @else
                                        <span class="badge badge-secondary">Nonaktif</span>
                                    @endif
                                </td>
                                <td>{{ $tax->created_at->format('d/m/Y H:i') }}</td>
                                <td class="text-center">
                                    <a href="{{ route('taxes.edit', $tax->id) }}" class="btn btn-sm btn-warning">
                                        <i class="fa fa-edit"></i> Edit
                                    </a>
                                    <form action="{{ route('taxes.destroy', $tax->id) }}" method="POST" class="d-inline"
                                          onsubmit="return confirm('Yakin ingin menghapus pajak ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-danger">
                                            <i class="fa fa-trash"></i> Hapus
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @else
                    <p class="text-muted">Tidak ada pajak yang ditemukan.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script>
    $(document).ready(function() {
        $('#taxes-table').DataTable();
    });
</script>
@endsection
