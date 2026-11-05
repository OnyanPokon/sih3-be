# Laravel API dengan FrankenPHP PHP 8.2.35

Konfigurasi utama berada di folder ini. Project Compose `container-backend-sih3`,
image `backend-sih3:php8.2.35`, port host default 8000. Konfigurasi Nginx lama
hanya disimpan sebagai referensi; jangan jalankan bersamaan pada port/data yang sama.

## Setup pertama

Dari root proyek:

```bash
cp .env.example .env # hanya jika belum ada
cd docker
cp .env.example .env # hanya jika belum ada
./build.sh --pull=false
docker compose config --quiet
docker compose up -d --force-recreate
docker compose ps
curl -fsS http://localhost:8000/up
```

Atur koneksi database, mail, SSO, queue dan secret di `.env` root sebelum start.
Database host dapat menggunakan `host.docker.internal`; `127.0.0.1` di container
menunjuk container sendiri. Database eksternal tidak disediakan stack ini.
Migration dijalankan sesuai prosedur aplikasi: `docker compose exec app php artisan migrate --force`.
Build membuat `.env` yang belum ada dan menghasilkan APP_KEY kosong tanpa menampilkan key.
Jangan mengganti APP_KEY aplikasi yang sudah berisi data terenkripsi.
Compose membaca `.env` root melalui `env_file` dan memasangnya read-only pada `/app/.env`.
File tersebut tidak masuk image. `docker/.env` hanya menyimpan nama, port, limit dan UID/GID.

Route aktif adalah API/JSON; view welcome scaffold yang memiliki `@vite` tidak dipakai.
Tidak perlu manifest atau build frontend. Image tidak memasang Node/npm/node_modules.
Jika route kelak memakai view Vite, sediakan seluruh artifact dari pipeline terpisah
beserta validator manifest sebelum membangun Docker.

## Persistensi dan permission

`../volume/data:/app/storage` menyimpan upload disk public/private, log, session dan cache.
`public/storage` menunjuk `/app/storage/app/public`. Tidak ditemukan upload langsung ke
public sehingga tidak ada mount upload tambahan. Source aplikasi immutable di image.
Storage host lama tidak otomatis dipindahkan; salin data yang diperlukan ke volume/data
saat stack dihentikan, sebelum startup pertama. Jangan menimpa data volume yang sudah ada.

`build.sh` otomatis menjalankan `prepare-storage.sh` setelah image berhasil dibangun.
Helper membaca UID/GID user `app` pada image, memperbaiki hanya file/direktori storage,
tidak mengikuti symlink dan tidak menghapus data. Folder kosong disiapkan entrypoint;
tidak ada aset upload bawaan yang perlu di-seed.
UID/GID default 1000; build pertama menyesuaikan user host atau SUDO_UID/SUDO_GID nonzero.
Runtime root ditolak. Perbaikan permission tanpa rebuild:

```bash
./prepare-storage.sh
docker compose exec app sh -c 'whoami; id; ls -ld /app /app/storage'
docker compose exec app sh -c 'f=$(mktemp /app/storage/.probe.XXXXXX); rm "$f"'
```

NFS root-squash, SELinux, user namespace atau filesystem read-only mungkin perlu
pengaturan host jika helper gagal. Build berhenti bila pemeriksaan tulis gagal.
Cache bootstrap dibersihkan sebelum Artisan pertama agar cache provider dev tidak
terbawa ke production. Override APP_*_CACHE ditolak; gunakan path standar.
`optimize:clear` dapat memerlukan backend cache/database yang dikonfigurasi tersedia.

## Operasi harian

```bash
cd docker
docker compose logs --tail 100 -f app
docker compose stop
docker compose start
docker compose down
```

`stop`/`start` mempertahankan container dan data. `down` menghapus container/network;
bind mount volume/data tetap ada. Hindari `down --volumes` jika data perlu dipertahankan.
Worker/scheduler belum diaktifkan pada konfigurasi awal dan tidak ditambahkan otomatis.

Setelah perubahan source, Dockerfile, PHP/extensions, entrypoint atau php.ini:

```bash
./build.sh --pull=false
docker compose up -d --force-recreate
docker compose exec app php -v
```

Perubahan UID/GID juga memerlukan build dan helper ulang. Perubahan resource limit,
port SERVER_PORT atau Caddyfile bind mount cukup recreate; periksa port host agar tidak bentrok.
Caddy menolak URL berisi `.php` dan URL upload dengan ekstensi script berbahaya.
Aturan ini tidak membaca filename multipart; validasi upload tetap tanggung jawab Laravel.
Health `/up` hanya memeriksa boot HTTP, bukan seluruh integrasi aplikasi.

## Validasi lokal

Image dan service telah diuji dengan PHP 8.2.35, user `app` UID/GID 1000,
HTTP `/up` dan `/` berhasil 200. Container tanpa mount juga berhasil boot HTTP.
Pemeriksaan image memastikan `.env`, Node, npm, node_modules, public/hot, tests,
metadata Git dan source frontend tidak masuk runtime. Pemeriksaan platform Composer lulus.
Fixture packages/services/config lama dibersihkan entrypoint sebelum Artisan.
Fixture permission mencakup folder root-owned, folder kosong, pemanggilan berulang,
checksum data lama dan penolakan target symlink. URL PHP/script yang diuji menghasilkan 403.

Lockfile ZipStream disesuaikan dari 3.2.0 (PHP >=8.3) ke 3.1.2 untuk PHP 8.2;
uji pembuatan dan pembacaan ZIP berhasil. Composer platform PHP dikunci 8.2.35.
Pengujian lokal memakai `.env` baru dengan CACHE_STORE=file dan SESSION_DRIVER=file.
Database, queue, mail, SSO dan endpoint bisnis yang bergantung integrasi eksternal
belum diuji; hasil healthcheck tidak membuktikan koneksi tersebut.
