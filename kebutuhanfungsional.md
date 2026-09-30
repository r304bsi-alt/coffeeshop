# Dokumen Kebutuhan Fungsional - Aplikasi Coffee Shop

## 1. Deskripsi Umum
Aplikasi Coffee Shop ini dirancang untuk memfasilitasi berbagai proses bisnis dalam sebuah kedai kopi, mulai dari pemesanan oleh pelanggan, manajemen menu, pengelolaan inventaris gudang, proses pengadaan barang, hingga pelaporan untuk pemilik. Aplikasi ini melibatkan beberapa aktor (role) dengan hak akses dan fitur yang berbeda-beda.

## 2. Aktor / Pengguna Sistem
1. **Owner** (Pemilik)
2. **Bagian Gudang**
3. **Bagian Pengadaan**
4. **Bagian Kasir** (Toko)
5. **Customer** (Pelanggan)
6. **Bagian Dapur** (Kitchen)

## 3. Kebutuhan Fungsional Berdasarkan Aktor

### 3.1. Owner (Pemilik)
- **Manajemen Pengguna (User Management):** 
  - Membuat akun pengguna baru.
  - Mengedit data pengguna.
  - Menghapus pengguna.
  - Melakukan banned/unbanned terhadap akun pengguna.
- **Laporan (Reporting):**
  - Melihat laporan penjualan.
  - Melihat laporan stok barang.
  - Melihat laporan pengadaan barang.
  - Melihat laporan laba rugi.
- **Persetujuan (Approval):**
  - Menyetujui (Approve) atau menolak permintaan pengadaan barang baru yang diajukan oleh Bagian Pengadaan.

### 3.2. Bagian Gudang
- **Penerimaan Barang (Inbound):**
  - Memproses penerimaan stok barang masuk dari supplier.
  - Mencocokkan barang masuk dari supplier dengan daftar order pengadaan yang telah disetujui.
- **Pengeluaran Barang (Outbound):**
  - Membuat daftar stok barang yang keluar dari gudang untuk dikirim/diterima ke bagian toko.
  - Sistem mencatat tanggal barang dikeluarkan dan tanggal barang diterima.
  - Sistem mencatat *user* yang mengeluarkan barang (dari sisi gudang) dan *user* yang menerima barang (dari sisi toko/kasir).

### 3.3. Bagian Pengadaan
- **Pengecekan Stok:**
  - Mengecek ketersediaan stok barang/bahan baku.
- **Permintaan Pengadaan (Purchase Order):**
  - Membuat permintaan pengadaan barang baru.
  - Mengajukan permintaan pengadaan kepada Owner untuk disetujui.
- **Eksekusi Pengadaan:**
  - Mencetak dokumen pesanan pengadaan atau mengirimkan pesanan via email ke supplier (hanya dapat dilakukan setelah disetujui Owner).

### 3.4. Bagian Kasir (Toko)
- **Penerimaan Stok Toko:**
  - Menerima stok barang yang dikirim dari gudang.
  - Saat barang dikonfirmasi diterima, sistem otomatis menambah stok item barang yang ada di toko.
- **Manajemen Menu & Stok:**
  - Menambah menu baru.
  - Mengunggah (Upload) gambar menu.
  - Menentukan status ketersediaan menu (Tersedia/Tidak Tersedia).
  - Mengelola *Master Data* komposisi resep/bahan baku (*Bill of Materials*) untuk setiap menu secara mendetail.
  - Pengurangan stok bahan baku akan dilakukan secara otomatis dan presisi (dalam satuan gram, mililiter, atau lainnya) terhadap inventaris toko untuk setiap pesanan yang telah berstatus lunas/valid.
- **Pemesanan Manual & Point of Sale (POS):**
  - Membuat order pesanan langsung dari *customer* (misal: *take away*, *dine in*, atau pemesanan langsung di meja/kasir).
  - Pada metode pembayaran Tunai (Cash), kasir menginput nominal uang yang dibayarkan oleh *customer*.
  - Dilengkapi tombol cepat "Uang Pas" dan pecahan nominal uang (20rb, 50rb, 100rb, +10rb, +50rb).
  - Sistem secara otomatis menghitung dan menampilkan uang kembalian secara *real-time*.
  - Terdapat validasi peringatan jika uang yang dibayarkan masih kurang dari total tagihan.
- **Proses Pembayaran Tunai (Cash Scan):**
  - Memindai (*scan*) QR Code Bayar yang ditunjukkan oleh *customer*.
  - Menampilkan detail pemesanan *customer* setelah QR Code di-*scan*.
  - Menginput uang yang diterima serta menghitung uang kembalian.
  - Menyelesaikan proses pembayaran tunai.
- **Notifikasi Pesanan Selesai:**
  - Mendapatkan notifikasi dari Bagian Dapur jika pesanan *customer* telah selesai dibuat.
  - Notifikasi yang diterima berisi informasi pesanan, nomor order, dan nomor meja untuk memanggil *customer*.

### 3.5. Customer (Pelanggan)
- **Autentikasi:**
  - Login menggunakan **Google Sign-In**.
- **Pemesanan Menu:**
  - Memindai (*scan*) QR Code yang ada di meja (*Table QR*) untuk memulai pemesanan.
  - Menjelajahi dan memilih menu berdasarkan kategori yang disediakan.
- **Pembayaran (Payment):**
  - **Metode Non-Tunai:** Melakukan pembayaran online yang terhubung dengan payment gateway **Midtrans** menggunakan metode **QRIS**.
  - **Metode Tunai (Cash):** Memilih metode pembayaran *Cash*. Sistem akan memunculkan QR Code Bayar yang dapat ditunjukkan kepada Kasir untuk dipindai dan diproses pembayarannya secara langsung.

### 3.6. Bagian Dapur (Kitchen)
- **Notifikasi Pesanan Masuk:**
  - Mendapatkan notifikasi orderan masuk secara otomatis setelah pembayaran dianggap lunas (berlaku untuk pembayaran via Kasir maupun notifikasi sukses dari Midtrans).
- **Manajemen Antrean Pesanan:**
  - Menampilkan daftar orderan yang masuk dan diurutkan berdasarkan waktu pesanan masuk paling awal (First In First Out / FIFO).
- **Penyelesaian Pesanan:**
  - Terdapat tombol "Selesai" yang dapat diklik oleh Bagian Dapur jika pesanan telah selesai disiapkan.
  - Saat tombol "Selesai" diklik, sistem otomatis mengirimkan notifikasi ke Bagian Kasir (berisi detail pesanan, nomor order, dan nomor meja) agar Kasir dapat memanggil *customer*.

## 4. Spesifikasi Teknis / Teknologi yang Digunakan
- **Bahasa Pemrograman & Framework:** PHP dengan **Laravel Framework**.
- **Database:**
  - *Development:* **SQLite** (digunakan sementara selama tahap pengembangan).
  - *Production:* **MySQL**.
- **Struktur & Skema Database:** Menggunakan **Laravel Migrations** untuk membuat dan mengelola versi skema tabel *database*.
- **Otorisasi & Manajemen Akses:** Menggunakan **Laravel Middleware** untuk mengatur *role* dan batasan akses tiap pengguna.
- **Validasi Data:** Menggunakan **Laravel Form Request** guna memastikan data yang di-*input* tervalidasi dengan aman dan rapi sebelum diproses.
- **Notifikasi *Real-Time*:** Menggunakan **JavaScript WebSocket** (bisa dipadukan dengan Laravel Reverb/Pusher) untuk *push notification* ke Bagian Dapur (pesanan baru) dan Kasir (pesanan selesai) tanpa perlu *refresh* halaman.
