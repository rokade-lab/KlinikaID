# Akun lokal KlinikaID

Akun berikut dibuat otomatis saat `docker compose up -d` pertama. Password bersifat dummy dan hanya untuk lab lokal.

| Username | Peran | Password |
|---|---|---|
| pasien_a | Pasien | `PasienA-Lab-2026!` |
| pasien_b | Pasien | `PasienB-Lab-2026!` |
| dokter_a | Dokter | `DokterA-Lab-2026!` |
| dokter_b | Dokter | `DokterB-Lab-2026!` |
| frontdesk | Frontdesk | `Frontdesk-Lab-2026!` |
| admin | Admin | `Admin-Lab-2026!` |

Password diambil dari `docker/lab-secrets/lab_accounts.json` saat seed pertama. Mengubah file itu setelah data terbentuk tidak mengubah password; ulangi dari data awal dengan menghapus folder `data/`.
