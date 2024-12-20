<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pesanan;
use App\Models\Pembayaran;

class PembayaranController extends Controller
{
    public function formPembayaran($kodePesanan)
    {
        $pesanan = Pesanan::where('kode_pesanan', $kodePesanan)->first();

        if (!$pesanan) {
            return redirect()->back()->with('error', 'Pesanan tidak ditemukan.');
        }

        return view('form_pembayaran', compact('pesanan'));
    }

    public function prosesPembayaran(Request $request)
    {
        $request->validate([
            'kode_pesanan' => 'required|exists:pesanan,kode_pesanan',
            'jumlah' => 'required|numeric|min:0',
            'total_harga' => 'required|numeric|min:0',
            'metode' => 'required|in:cash,debit,qr',
        ]);

    // Generate kode_pembayaran dengan format PEM-tanggalwaktu
    $kodePembayaran = 'PEM-' . now()->format('YmdHis');

    $kembalian = $request->jumlah - $request->total_harga;

    $pembayaran = new Pembayaran();
    $pembayaran->kode_pembayaran = $kodePembayaran;
    $pembayaran->kode_pesanan = $request->kode_pesanan;
    $pembayaran->jumlah = $request->jumlah;
    $pembayaran->kembalian = $kembalian > 0 ? $kembalian : 0;
    $pembayaran->metode = $request->metode;

    // Handle pembayaran non-cash (debit & QR)
    if ($request->metode === 'debit') {
        $pembayaran->card_num = $request->card_num;
        $pembayaran->exp_date = $request->exp_date;
        $pembayaran->zjp_code = $request->zjp_code;
        $pembayaran->pin = $request->pin;
        $pembayaran->authorized_debit = $request->authorized_debit ?? 0;
    } elseif ($request->metode === 'qr') {
        $pembayaran->qr_code = $request->qr_code;
        $pembayaran->authorized_qr = $request->authorized_qr ?? 0;
    }

    $pembayaran->save();

    // Tandai pesanan sebagai telah dibayar
    $pesanan = Pesanan::where('kode_pesanan', $request->kode_pesanan)->first();
    $pesanan->is_paid = true;
    $pesanan->save();

    return redirect()->route('list-pesanan')->with('success', 'Pembayaran berhasil diproses dengan Kode Pembayaran: ' . $kodePembayaran);
}


public function listPembayaran()
{
    $pembayaran = Pembayaran::all();

    return view('list-pembayaran', compact('pembayaran'));
}

}
