<?php

namespace App\Http\Controllers\Karyawan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\{transaksi, DataBank};
use Illuminate\Support\Facades\Auth;
use PDF;

class InvoiceController extends Controller
{
    // ===============================
    // TAMPIL INVOICE (HTML VIEW)
    // ===============================
    public function invoicekar(Request $request)
    {
        $invoice = transaksi::with('price')
            ->where('user_id', Auth::id())
            ->where('id', $request->id)
            ->get();

        $data = transaksi::with(['customers', 'user'])
            ->where('user_id', Auth::id())
            ->where('id', $request->id)
            ->firstOrFail();

        $bank = DataBank::get();

        return view('karyawan.laporan.invoice', compact('invoice', 'data', 'bank'));
    }


    // ===============================
    // CETAK INVOICE THERMAL 80MM
    // ===============================
    public function cetakinvoice(Request $request)
    {
        $invoice = transaksi::with('price')
            ->where('user_id', Auth::id())
            ->where('id', $request->id)
            ->get();

        $data = transaksi::with(['customers', 'user'])
            ->where('user_id', Auth::id())
            ->where('id', $request->id)
            ->firstOrFail();

        $bank = DataBank::get();

        /*
         |-------------------------------------------
         | Ukuran Thermal 80mm
         | 80mm = 226.77 point
         | Height dibuat panjang supaya tidak kepotong
         |-------------------------------------------
        */
        $customPaper = [0, 0, 226.77, 400];

        $pdf = PDF::loadView(
                    'karyawan.laporan.cetak',
                    compact('invoice', 'data', 'bank')
                )
                ->setPaper($customPaper);

        return $pdf->stream('invoice-thermal.pdf');
    }
}
