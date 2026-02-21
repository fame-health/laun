<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\{
    transaksi,
    customer,
    LaundrySetting,
    User,
    harga,
    DataBank
};
use App\Http\Requests\HargaRequest;
use DB;
use Auth;
use Session;
use Carbon\Carbon;

class FinanceController extends Controller
{
    // =========================
    // DASHBOARD FINANCE
    // =========================
    public function index()
    {
        $chartMonthSalary = DB::table('transaksis')
            ->select('bulan', DB::raw('SUM(harga_akhir) AS jml'))
            ->whereYear('created_at', date('Y'))
            ->groupBy('bulan')
            ->get();

        $chartMonth = '';
        for ($i = 1; $i <= 12; $i++) {
            $found = false;
            foreach ($chartMonthSalary as $row) {
                if ((int)$row->bulan === $i) {
                    $chartMonth .= $row->jml . ',';
                    $found = true;
                }
            }
            if (!$found) {
                $chartMonth .= '0,';
            }
        }

        $incomeAll   = transaksi::where('status_payment', 'Success')->sum('harga_akhir');
        $incomeY     = transaksi::where('status_payment', 'Success')->where('tahun', date('Y'))->sum('harga_akhir');
        $incomeM     = transaksi::where('status_payment', 'Success')->where('tahun', date('Y'))->where('bulan', date('n'))->sum('harga_akhir');
        $incomeYOld  = transaksi::where('status_payment', 'Success')->where('tahun', date('Y') - 1)->sum('harga_akhir');
        $incomeD     = transaksi::where('status_payment', 'Success')->whereDate('created_at', Carbon::today())->sum('harga_akhir');
        $incomeDOld  = transaksi::where('status_payment', 'Success')->whereDate('created_at', Carbon::yesterday())->sum('harga_akhir');

        $kgDay   = transaksi::whereDate('created_at', Carbon::today())->sum('kg');
        $kgMonth = transaksi::whereMonth('created_at', date('m'))->whereYear('created_at', date('Y'))->sum('kg');
        $kgYear  = transaksi::whereYear('created_at', date('Y'))->sum('kg');

        $getCabang = User::whereHas('transaksi', function ($q) {
            $q->whereYear('created_at', date('Y'))
              ->whereMonth('created_at', date('m'));
        })->get();

        $target = LaundrySetting::first();

        return view('modul_admin.finance.index', compact(
            'chartMonth',
            'incomeY',
            'incomeM',
            'incomeYOld',
            'incomeD',
            'incomeDOld',
            'incomeAll',
            'getCabang',
            'kgDay',
            'kgMonth',
            'kgYear',
            'target'
        ));
    }

    // =========================
    // DATA HARGA
    // =========================
    public function dataharga()
    {
        $harga     = harga::with('harga_user')->orderBy('id', 'DESC')->get();
        $karyawan  = User::where('auth', 'Karyawan')->first();
        $getcabang = User::where('auth', 'Karyawan')->where('status', 'Active')->get();
        $getBank   = DataBank::where('user_id', Auth::id())->count();

        return view('modul_admin.laundri.harga', compact(
            'harga',
            'karyawan',
            'getcabang',
            'getBank'
        ));
    }

    // =========================
    // SIMPAN HARGA
    // =========================
    public function hargastore(HargaRequest $request)
    {
        $hargaBersih = preg_replace('/[^0-9]/', '', $request->harga);

        harga::create([
            'user_id' => $request->user_id,
            'jenis'   => $request->jenis,
            'kg'      => 1000,
            'harga'   => $hargaBersih,
            'hari'    => $request->hari,
            'status'  => 1,
        ]);

        Session::flash('success', 'Tambah Data Harga Berhasil');
        return redirect('data-harga');
    }

    // =========================
    // EDIT HARGA (AJAX)
    // =========================
    public function hargaedit(Request $request)
    {
        $hargaBersih = preg_replace('/[^0-9]/', '', $request->harga);

        $editharga = harga::findOrFail($request->id_harga);
        $editharga->update([
            'jenis'  => $request->jenis,
            'kg'     => $request->kg,
            'harga'  => $hargaBersih,
            'hari'   => $request->hari,
            'status' => $request->status,
        ]);

        Session::flash('success', 'Edit Data Harga Berhasil');
        return response()->json($editharga);
    }
}
