<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pesanan;
use App\Models\Menu;

class PesananController extends Controller
{
    // Menampilkan menu dan keranjang
    public function index()
    {
        $menus = \DB::table('menus')->get(); // Mengambil semua menu
        $keranjang = session()->get('keranjang', []); // Mengambil keranjang dari sesi
        return view('index', compact('menus', 'keranjang'));
    }

    // Menambahkan menu ke keranjang
    public function tambahKeKeranjang(Request $request)
    {
        $user = session('user');

        if (!in_array($user->role, ['waiters', 'kasir'])) {
            abort(403, 'Unauthorized action.');
        }

        $kodeMenu = $request->input('kode_menu');
        $menu = \DB::table('menus')->where('kode_menu', $kodeMenu)->first();

        if (!$menu) {
            return redirect()->back()->with('error', 'Menu tidak ditemukan.');
        }

        $keranjang = session()->get('keranjang', []);
        $keranjang[$kodeMenu] = $keranjang[$kodeMenu] ?? ['menu' => $menu, 'jumlah' => 0];
        $keranjang[$kodeMenu]['jumlah']++;

        session()->put('keranjang', $keranjang);

        return redirect()->back()
            ->with('success', 'Menu berhasil ditambahkan ke keranjang.')
            ->with('keranjang_debug', session()->get('keranjang'));
    }

    // Melihat keranjang
    public function lihatKeranjang()
    {
        $keranjang = session()->get('keranjang', []);
        $totalHarga = collect($keranjang)->reduce(function ($carry, $item) {
            return $carry + $item['menu']->harga * $item['jumlah'];
        }, 0);

        return view('pesanan', compact('keranjang', 'totalHarga'));
    }

    // Memperbarui keranjang
    public function updateKeranjang(Request $request)
    {
        $menuId = $request->input('menu_id');
        $jumlah = $request->input('jumlah');
        $keranjang = session()->get('keranjang', []);

        if (isset($keranjang[$menuId])) {
            if ($jumlah > 0) {
                $keranjang[$menuId]['jumlah'] = $jumlah;
            } else {
                unset($keranjang[$menuId]);
            }
            session()->put('keranjang', $keranjang);
        }

        return redirect()->route('pesanan.keranjang')->with('success', 'Keranjang berhasil diperbarui.');
    }

    // Menyimpan pesanan
    public function simpanPesanan(Request $request)
    {
        $request->validate([
            'nama_pelanggan' => 'required',
            'bangku' => 'nullable|required_without:is_bawa_pulang',
            'is_bawa_pulang' => 'nullable|boolean|required_without:bangku',
            'catatan_tambahan' => 'nullable',
        ]);

        $keranjang = session()->get('keranjang', []);
        $detailPesanan = [];

        foreach ($keranjang as $kodeMenu => $item) {
            $detailPesanan[] = [
                'kode_menu' => $item['menu']->kode_menu,
                'deskripsi' => $item['menu']->deskripsi,
                'harga' => $item['menu']->harga,
                'jumlah' => $item['jumlah'],
            ];
        }

        $totalHarga = collect($detailPesanan)->reduce(function ($carry, $item) {
            return $carry + ($item['harga'] * $item['jumlah']);
        }, 0);

        Pesanan::create([
            'kode_pesanan' => 'PES-' . time(),
            'nama_pelanggan' => $request->input('nama_pelanggan'),
            'bangku' => $request->input('bangku'),
            'is_bawa_pulang' => $request->input('is_bawa_pulang') ? 1 : 0,
            'catatan_tambahan' => $request->input('catatan_tambahan'),
            'detail_pesanan' => json_encode($detailPesanan),
            'total_harga' => $totalHarga,
            'status' => 'Dalam Antrian',
        ]);

        session()->forget('keranjang');

        return redirect()->route('pesanan.list')->with('success', 'Pesanan berhasil disimpan.');
    }

    // Menampilkan daftar pesanan
    public function listPesanan()
    {
        $pesanan = Pesanan::all();
        return view('list_pesanan', compact('pesanan'));
    }

    // Memperbarui status pesanan
    public function updateStatus(Request $request, $kodePesanan)
    {
        $pesanan = Pesanan::where('kode_pesanan', $kodePesanan)->firstOrFail();
        $pesanan->status = $request->status;
        $pesanan->save();

        return back()->with('success', 'Status pesanan berhasil diperbarui!');
    }


    // Menampilkan detail pesanan berdasarkan kode_pesanan
    public function show($kode_pesanan)
{
    $pesanan = Pesanan::where('kode_pesanan', $kode_pesanan)->first();

    // Jika pesanan tidak ditemukan, tampilkan halaman 404
    if (!$pesanan) {
        abort(404);
    }

    // Hanya kirimkan ke view jika is_paid bernilai 0
    if ($pesanan->is_paid === 0) {
        return view('pesanan.show', compact('pesanan'));
    }

    // Redirect atau tampilkan pesan jika is_paid adalah 1
    return redirect()->route('pesanan.list-pesanan')->with('info', 'Pesanan ini sudah dibayar.');
}

}
