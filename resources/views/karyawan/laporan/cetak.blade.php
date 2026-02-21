<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
@page {
    margin: 4mm;
}

body {
    font-family: monospace;
    font-size: 12px;
    margin: 0;
    color: #000;
}

.center {
    text-align: center;
}

.right {
    text-align: right;
}

.bold {
    font-weight: bold;
}

.line {
    border-top: 1px dashed #000;
    margin: 8px 0;
}

table {
    width: 100%;
    border-collapse: collapse;
}

td {
    padding: 4px 0;
    vertical-align: top;
}

.total-label {
    font-size: 14px;
    font-weight: bold;
}

.total-value {
    font-size: 16px;
    font-weight: bold;
}
</style>
</head>
<body>

<!-- HEADER TOKO -->
<div class="center">
    <div style="font-size:18px; font-weight:bold;">
        {{ strtoupper($data->user->nama_cabang) }}
    </div>
    <div style="font-size:12px;">
        {{ $data->user->alamat_cabang }} <br>
        Telp: {{ $data->user->no_telp }}
    </div>
</div>

<div class="line"></div>

<!-- INFO TRANSAKSI -->
<table>
<tr>
    <td>Invoice</td>
    <td class="right bold">{{ $data->invoice }}</td>
</tr>
<tr>
    <td>Tanggal</td>
    <td class="right">
        {{ \Carbon\Carbon::parse($data->tgl_transaksi)->format('d-m-Y') }}
    </td>
</tr>
<tr>
    <td>Kasir</td>
    <td class="right bold">{{ $data->user->name }}</td>
</tr>
</table>

<div class="line"></div>

<!-- CUSTOMER -->
<div>
    <div class="bold" style="font-size:14px;">
        {{ $data->customers->nama }}
    </div>
    Telp: {{ $data->customers->no_telp }}
</div>

<div class="line"></div>

<!-- DETAIL ITEM -->
@php
    $subTotal = 0;
@endphp

@foreach ($invoice as $item)
@php
    $sub = $item->kg * $item->harga;
    $subTotal += $sub;
@endphp

<div class="bold" style="margin-top:4px;">
    {{ $item->price->jenis }}
</div>

<table>
<tr>
    <td>
        {{ number_format($item->kg,1) }} Kg x {{ number_format($item->harga) }}
    </td>
    <td class="right bold">
        {{ number_format($sub) }}
    </td>
</tr>
</table>

@endforeach

<div class="line"></div>

<!-- TOTAL -->
<table>
<tr>
    <td>Subtotal</td>
    <td class="right bold">{{ number_format($subTotal) }}</td>
</tr>

@php
    $disc = $invoice->first()->disc ?? 0;
    $discountAmount = ($subTotal * $disc) / 100;
@endphp

<tr>
    <td>Diskon ({{ $disc }}%)</td>
    <td class="right bold">- {{ number_format($discountAmount) }}</td>
</tr>

<tr>
    <td class="total-label">TOTAL</td>
    <td class="right total-value">
        {{ number_format($subTotal - $discountAmount) }}
    </td>
</tr>
</table>

<div class="line"></div>

<!-- ESTIMASI -->
<div class="center" style="font-size:13px; font-weight:bold;">
    Estimasi Selesai <br>
    {{ \Carbon\Carbon::parse($data->tgl_transaksi)->addDays($data->hari)->format('d-m-Y') }}
</div>

<div class="line"></div>

<!-- FOOTER -->
<div class="center" style="font-size:11px;">
    Terima kasih telah menggunakan jasa kami <br>
    Laundry Anda Kami Jaga
</div>

</body>
</html>
