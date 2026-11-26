# Panduan peserta KlinikaID

Jalankan lab dengan `docker compose up -d` dari root repositori, buka https://localhost:19443, dan masuk memakai akun di [AKUN-LAB.md](AKUN-LAB.md). Log dapat dilihat dengan `docker compose logs -f app proxy`. Untuk kembali ke data dummy awal, jalankan `docker compose down`, hapus folder `data/`, lalu `docker compose up -d`.

Jelajahi aplikasi dengan setiap akun yang tersedia. Pahami peran pasien, dokter, frontdesk, dan admin dari perilaku aplikasi serta source, lalu tentukan sendiri hasil yang seharusnya untuk setiap fitur.

Mulai dengan memetakan fitur, identitas/peran, objek data, dan hasil yang diharapkan. Cari masalah melalui pengamatan runtime dan pembacaan source, lalu pisahkan bukti yang telah teruji dari dugaan.

Setiap perbaikan harus memiliki reproduksi sebelum perubahan, alasan teknis, dan verifikasi sesudah perubahan. Jangan hanya menyembunyikan tombol atau menonaktifkan seluruh fitur. Pertahankan fungsi bisnis dan uji penolakan tanpa merusak data.

Gunakan instance lokal milik sendiri. Simpan bukti yang sudah diredaksi dan laporkan melalui pull request sesuai arahan kelas.
