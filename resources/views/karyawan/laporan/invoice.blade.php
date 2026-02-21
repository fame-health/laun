@extends('layouts.backend')
@section('title','Karyawan - Invoice Customer')
@section('header','Invoice Customer')
@section('content')
<div class="col-md-12">
    <div class="card card-body printableArea">
        <h3><b>INVOICE</b> <span class="pull-right">{{$data->invoice}}</span></h3>
        <hr>
        <div class="row">
            <div class="col-md-12">
                <div class="pull-left">
                    <address>
                        <h3> &nbsp;<b class="text-danger">{{$data->user->nama_cabang}}</b></h3>
                        <p class="text-muted m-l-5"> Diterima Oleh <span style="margin-left:20px"> </span>: {{$data->user->name}}
                            <br/> Alamat <span style="margin-left:70px"> </span>: {{$data->user->alamat_cabang}},
                            <br/> No. Telp <span style="margin-left:65px"> </span>: {{$data->user->no_telp}}
                        </p>
                    </address>
                </div>
                <div class="pull-right text-right">
                    <address>
                        <h3>Detail Order Customer :</h3>
                        <p class="text-muted m-l-30">
                            {{$data->customers->nama}}
                            <br/> {{$data->customers->alamat}}
                            <br/> {{$data->customers->no_telp}}
                        </p>
                        <p class="m-t-30"><b>Tanggal Masuk :</b> <i class="fa fa-calendar"></i> {{ \Carbon\Carbon::parse($data->tgl_transaksi)->format('d-m-Y') }}</p>
                        <p><b>Estimasi Selesai :</b> <i class="fa fa-calendar"></i>
                            {{ \Carbon\Carbon::parse($data->tgl_transaksi)->addDays($data->hari)->format('d-m-Y') }}
                        </p>
                        <p><b>Tanggal Diambil :</b> <i class="fa fa-calendar"></i>
                            @if ($data->tgl_ambil)
                                {{ \Carbon\Carbon::parse($data->tgl_ambil)->format('d-m-Y') }}
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </p>
                    </address>
                </div>
            </div>
            <div class="col-md-12">
                <div class="table-responsive m-t-20" style="clear: both;">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th class="text-center">#</th>
                                <th>Jenis Pakaian</th>
                                <th class="text-right">Berat</th>
                                <th class="text-right">Harga</th>
                                <th class="text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $subTotal = 0;
                                $discountAmount = 0;
                                $totalAfterDisc = 0;
                            @endphp
                            @foreach ($invoice as $item)
                                @php
                                    $subTotal = $item->kg * $item->harga;
                                    $discountAmount = ($subTotal * $item->disc) / 100;
                                    $totalAfterDisc = $subTotal - $discountAmount;
                                @endphp
                                <tr>
                                    <td class="text-center">{{ $loop->iteration }}</td>
                                    <td>{{ $item->price->jenis }}</td>
                                    <td class="text-right">{{ $item->kg }} Kg</td>
                                    <td class="text-right">{{ Rupiah::getRupiah($item->harga) }} /Kg</td>
                                    <td class="text-right">{{ Rupiah::getRupiah($subTotal) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="4" class="text-right">Sub Total :</th>
                                <th class="text-right">{{ Rupiah::getRupiah($subTotal) }}</th>
                            </tr>
                            <tr>
                                <th colspan="4" class="text-right">Diskon ({{ $item->disc }}%) :</th>
                                <th class="text-right">- {{ Rupiah::getRupiah($discountAmount) }}</th>
                            </tr>
                            <tr>
                                <th colspan="4" class="text-right">Total Bayar :</th>
                                <th class="text-right"><h4><b>{{ Rupiah::getRupiah($item->harga_akhir) }}</b></h4></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            <div class="col-md-12">
                <div class="row">
                    <div class="col-md-6">
                        <div class="pull-left m-t-10">
                            <h6 style="font-weight:bold">Metode Pembayaran :</h6>
                            <ol style="padding-left: 20px;">
                                @foreach ($bank as $banks)
                                    <li style="color: #666; margin-bottom: 5px;">
                                        <strong>{{ $banks->nama_bank }}</strong> <br>
                                        {{ $banks->no_rekening }} a/n {{ $banks->nama_pemilik }}
                                    </li>
                                @endforeach
                            </ol>

                            @if($data->status_payment == 'Pending')
                                <div class="alert alert-warning mt-3" style="max-width: 300px;">
                                    <i class="fa fa-clock-o"></i> Menunggu Pembayaran
                                </div>
                            @else
                                <div class="alert alert-success mt-3" style="max-width: 300px;">
                                    <i class="fa fa-check-circle"></i> Sudah Dibayar
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="pull-right m-t-10 text-right">
                            <table class="table table-clear" style="width: 100%;">
                                <tbody>
                                    <tr>
                                        <td class="text-right"><strong>Status Laundry :</strong></td>
                                        <td class="text-right">
                                            @if ($data->status_order == 'Process')
                                                <span class="badge badge-info">Diproses</span>
                                            @elseif($data->status_order == 'Done')
                                                <span class="badge badge-success">Selesai</span>
                                            @elseif($data->status_order == 'Delivery')
                                                <span class="badge badge-primary">Diambil</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-right"><strong>Status Pembayaran :</strong></td>
                                        <td class="text-right">
                                            @if ($data->status_payment == 'Success')
                                                <span class="badge badge-success">Lunas</span>
                                            @else
                                                <span class="badge badge-warning">Pending</span>
                                            @endif
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="clearfix"></div>
                <hr>

                <div class="text-right">
                    <a href="{{ url('pelayanan') }}" class="btn btn-outline btn-danger" style="color:white; margin-right: 5px;">
                        <i class="fa fa-arrow-left"></i> Kembali
                    </a>
                    <a href="{{ url('cetak-invoice/'.$data->id. '/print') }}" target="_blank" class="btn btn-success">
                        <i class="fa fa-print"></i> Print
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
    .badge {
        padding: 5px 10px;
        font-size: 12px;
        border-radius: 4px;
    }
    .badge-info {
        background-color: #17a2b8;
        color: white;
    }
    .badge-success {
        background-color: #28a745;
        color: white;
    }
    .badge-primary {
        background-color: #007bff;
        color: white;
    }
    .badge-warning {
        background-color: #ffc107;
        color: black;
    }
    .table-clear td {
        border: none !important;
        padding: 5px !important;
    }
    .alert {
        padding: 10px;
        border-radius: 4px;
        margin-top: 10px;
    }
    .alert-warning {
        background-color: #fff3cd;
        border: 1px solid #ffeeba;
        color: #856404;
    }
    .alert-success {
        background-color: #d4edda;
        border: 1px solid #c3e6cb;
        color: #155724;
    }
    ol {
        list-style-type: decimal;
        margin-top: 5px;
    }
    ol li {
        color: #666 !important;
        line-height: 1.6;
    }
</style>
@endsection
