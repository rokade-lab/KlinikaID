# KlinikaID — lab peserta

Simulasi layanan klinik: pasien, dokter, jadwal, kunjungan, dokumen, profil, dan penugasan.

Repositori ini adalah titik awal latihan secure coding. Fork repositori, jalankan secara lokal, temukan masalah, lalu ajukan perbaikan beserta pengujian. Aplikasi sengaja memuat kelemahan untuk latihan; gunakan data dummy dan jalankan hanya di komputer lokal. Port Docker dibatasi ke loopback.

## Menjalankan lokal

Prasyarat: Docker Engine atau Docker Desktop dengan Compose v2, dan Git. Build pertama membutuhkan internet untuk mengunduh image dan dependency.

```sh
git clone <URL-fork-Anda>
cd <folder-repositori>
docker compose up -d
```

Perintah pertama membangun image, membuat database, menjalankan migrasi, dan mengisi data dummy. Tunggu sampai `docker compose ps` menampilkan service `proxy` dan `app` dalam status healthy, lalu buka **https://localhost:19443**. Akun dan password dummy tercantum di [docs/AKUN-LAB.md](docs/AKUN-LAB.md).

Sertifikat HTTPS lokal bersifat self-signed dan dibuat otomatis di `data/tls/`. Untuk CLI gunakan `curl --cacert data/tls/server.pem https://localhost:19443/healthz`. Pada browser, percayai sertifikat ini hanya untuk lab lokal.

Jika port sudah digunakan, salin `.env.example` menjadi `.env`, ubah nilainya, lalu jalankan kembali `docker compose up -d`.

## Data lokal

Semua data runtime disimpan di folder `data/` pada working directory: database PostgreSQL (`data/postgres`), dokumen (`data/storage`), log aplikasi (`data/logs`), dan sertifikat TLS (`data/tls`). Folder ini diabaikan Git dan tidak boleh di-commit.

## Perintah harian

```sh
docker compose ps
docker compose logs -f app proxy
docker compose down
docker compose up -d --build   # bangun ulang setelah mengubah source
```

`down` mempertahankan data di `data/`. Untuk mengulang dari data dummy awal:

```sh
docker compose down
rm -rf data
docker compose up -d
```

Pada Linux, file di `data/` dapat dimiliki root. Jika `rm -rf data` ditolak, jalankan `docker run --rm -v "$PWD/data:/d" alpine sh -c 'rm -rf /d/*'` lalu hapus folder kosongnya.

Dockerfile tersedia di `docker/` dan runtime didefinisikan di `compose.yaml`. Service `init` menjalankan migrasi dan seed sekali setiap `up`; bila data sudah ada, seed dilewati.

## Pengerjaan dan pengumpulan

1. Baca [panduan peserta](docs/PANDUAN-PESERTA.md) dan jalankan alur aplikasi normal.
2. Buat branch `fix/<topik>` pada fork Anda.
3. Catat masalah dan bukti awal dengan data dummy.
4. Perbaiki source atau konfigurasi, tambahkan pengujian yang membuktikan perbaikan, lalu uji ulang alur sah.
5. Buka pull request di fork Anda sesuai instruksi kelas; isi [template laporan](docs/LAPORAN.md).

Sertakan langkah reproduksi, dampak, alasan perubahan, cara menguji, dan risiko tersisa. Jangan sertakan data sensitif atau isi folder `data/`.
