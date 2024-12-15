<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1 class="mb-4">Pembayaran</h1>
        <form action="{{ route('pembayaran.proses') }}" method="POST">
            @csrf
            <!-- Menampilkan Harga Pesanan -->
            <div class="mb-3">
                <label for="total_harga" class="form-label">Total Harga</label>
                <input type="text" id="total_harga" class="form-control" value="Rp{{ number_format($pesanan->total_harga, 0, ',', '.') }}" disabled>
                <input type="hidden" name="total_harga" value="{{ $pesanan->total_harga }}">
            </div>

            <!-- Input Jumlah Uang -->
            <div class="mb-3">
                <label for="jumlah" class="form-label">Jumlah Pembayaran</label>
                <input type="number" name="jumlah" id="jumlah" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="kembalian" class="form-label">Kembalian</label>
                <input type="text" id="kembalian" class="form-control" value="Rp0" readonly>
            </div>            

            <!-- Pilihan Metode Pembayaran -->
            <div class="mb-3">
                <label for="metode" class="form-label">Metode Pembayaran</label>
                <select name="metode" id="metode" class="form-select" required>
                    <option value="cash">Cash</option>
                    <option value="debit">Debit</option>
                    <option value="qr">QR</option>
                </select>
            </div>

            <!-- Detail Debit -->
            <div id="debit-details" style="display: none;">
                <h5>Detail Debit</h5>
                <div class="mb-3">
                    <label for="card_num" class="form-label">Card Number</label>
                    <input type="number" name="card_num" id="card_num" class="form-control" placeholder="Masukkan nomor kartu">
                </div>
                <div class="mb-3">
                    <label for="exp_date" class="form-label">Expiration Date</label>
                    <input type="date" name="exp_date" id="exp_date" class="form-control">
                </div>
                <div class="mb-3">
                    <label for="zjp_code" class="form-label">ZJP Code</label>
                    <input type="number" name="zjp_code" id="zjp_code" class="form-control" placeholder="Masukkan ZJP Code">
                </div>
                <div class="mb-3">
                    <label for="pin" class="form-label">PIN</label>
                    <input type="password" name="pin" id="pin" class="form-control" placeholder="Masukkan PIN">
                </div>
                <div class="mb-3">
                    <label for="authorized_debit" class="form-label">Authorized</label>
                    <select name="authorized_debit" id="authorized_debit" class="form-select">
                        <option value="1">Yes</option>
                        <option value="0">No</option>
                    </select>
                </div>
            </div>

            <!-- Detail QR -->
            <div id="qr-details" style="display: none;">
                <h5>Detail QR</h5>
                <div class="mb-3">
                    <label for="qr_image" class="form-label">Scan QR Code</label>
                    <img src="{{ asset('images/Screenshot_1.png') }}" alt="QR Code" class="img-fluid">
                </div>
                <div class="mb-3">
                    <label for="authorized_qr" class="form-label">Authorized</label>
                    <select name="authorized_qr" id="authorized_qr" class="form-select">
                        <option value="1">Yes</option>
                        <option value="0">No</option>
                    </select>
                </div>
            </div>

            <!-- Tombol Submit -->
            <button type="submit" class="btn btn-primary">Proses Pembayaran</button>
        </form>
    </div>

    <!-- Script untuk Menyesuaikan Form Berdasarkan Metode Pembayaran -->
    <script>
        document.getElementById('metode').addEventListener('change', function () {
            const metode = this.value;
            document.getElementById('debit-details').style.display = metode === 'debit' ? 'block' : 'none';
            document.getElementById('qr-details').style.display = metode === 'qr' ? 'block' : 'none';
        });
    </script>
    <script>
        // Script to handle kembalian calculation
        document.getElementById('jumlah').addEventListener('input', function () {
            const totalHarga = parseInt(document.getElementById('total_harga').value.replace(/[^\d]/g, ''), 10); // Convert displayed price to number
            const jumlahPembayaran = parseInt(this.value, 10) || 0; // Handle empty or non-numeric input
    
            const kembalian = jumlahPembayaran - totalHarga;
            const kembalianDisplay = kembalian > 0 ? `Rp${kembalian.toLocaleString('id-ID')}` : 'Rp0';
    
            document.getElementById('kembalian').value = kembalianDisplay;
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>