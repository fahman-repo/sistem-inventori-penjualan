@extends('adminlte::page')

@section('admin_title', 'Daftar Supplier')

@section('content_header', 'Daftar Supplier')

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
                    <a href="{{ route('suppliers.create') }}" class="btn btn-primary">
                        <i class="fa fa-plus"></i> Tambah Supplier
                    </a>
                </div>
                <div class="card-body">
                    @if($suppliers->count() > 0)
                    <table id="suppliers-table" class="table table-bordered table-hover dataTable" style="width:100%">
                        <thead class="thead-light">
                            <tr>
                                <th>#</th>
                                <th>Nama</th>
                                <th>Telepon</th>
                                <th>Email</th>
                                <th>Jumlah Pembelian</th>
                                <th>Tanggal Dibuat</th>
                                <th class="text-center">Tindakan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($suppliers as $supplier)
                            <tr>
                                <td>{{ $loop->iteration + ($suppliers->currentPage() - 1) * $suppliers->perPage() }}</td>
                                <td>{{ $supplier->name }}</td>
                                <td>{{ $supplier->phone ?? '-' }}</td>
                                <td>{{ $supplier->email ?? '-' }}</td>
                                <td>{{ $supplier->purchases_count }}</td>
                                <td>{{ $supplier->created_at->format('d/m/Y H:i') }}</td>
                                <td class="text-center">
                                    <a href="{{ route('suppliers.show', $supplier->id) }}" class="btn btn-sm btn-info">
                                        <i class="fa fa-eye"></i> Detail
                                    </a>
                                    <a href="{{ route('suppliers.edit', $supplier->id) }}" class="btn btn-sm btn-warning">
                                        <i class="fa fa-edit"></i> Edit
                                    </a>
                                    <form action="{{ route('suppliers.destroy', $supplier->id) }}" method="POST" class="d-inline"
                                          onsubmit="return confirm('Yakin ingin menghapus supplier ini?')">
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
                    <p class="text-muted">Tidak ada supplier yang ditemukan.</p>
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
        $('#suppliers-table').DataTable();
    });
</script>
@endsection