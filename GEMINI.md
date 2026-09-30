# Aturan Pengembangan & Git Workflow

## Aturan Wajib (Mandatory Rule)
Setiap kali selesai melakukan modifikasi, penambahan, penghapusan, atau perbaikan pada script/kode program (PHP, Blade, JS, CSS, migrasi, konfigurasi, dokumen rancangan, dll.) dan pengujian/verifikasi selesai dilakukan:

1. Periksa berkas yang berubah menggunakan `git status`.
2. Tambahkan berkas yang telah dimodifikasi menggunakan:
   ```bash
   git add .
   ```
   *(Pastikan berkas sensitif seperti `.env` dan `database/*.sqlite*` tetap diabaikan oleh `.gitignore`)*.
3. Buat commit dengan pesan yang deskriptif dan jelas mengenai perubahan yang dilakukan:
   ```bash
   git commit -m "<tipe>(<cakupan>): <penjelasan perubahan>"
   ```
4. Kirimkan (*push*) langsung commit ke remote Git repository:
   ```bash
   git push origin main
   ```
5. Sertakan informasi commit dan push tersebut dalam respon kepada pengguna.
