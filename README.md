<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

---

## 🌐 Tentang API Proyek Ini

Ini adalah dokumentasi resmi untuk **API Backend SIH3**. API ini berfungsi sebagai *headless backend* untuk mengelola seluruh data yang berkaitan dengan pemetaan, artikel, produk hukum, manajemen instansi (Unor/OPD), dan personil.

API ini dibangun menggunakan Laravel dan dirancang untuk melayani berbagai *client*, termasuk aplikasi web admin dan *landing page* publik.

---

## 🚀 Panduan Instalasi

Untuk menjalankan proyek ini secara lokal, ikuti langkah-langkah berikut:

1.  **Clone repositori:**
    ```bash
    git clone https://gitlab.gorontaloprov.go.id/yasdilofficial/backend-sih3.git
    cd backend-sih3
    ```

2.  **Install dependensi Composer:**
    ```bash
    composer install
    ```

3.  **Salin file environment:**
    ```bash
    cp .env.example .env
    ```

4.  **Generate key aplikasi:**
    ```bash
    php artisan key:generate
    ```

5.  **Konfigurasi `.env`:**
    Sesuaikan koneksi database (`DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`) dan URL aplikasi (`APP_URL`) di file `.env` Anda.

6.  **Jalankan Migrasi & Seeder:**
    Perintah ini akan membuat struktur tabel dan mengisi data awal (jika ada).
    ```bash
    php artisan migrate --seed
    ```

7.  **Buat Storage Link:**
    Ini sangat **penting** agar file yang di-upload (seperti GeoJSON dan thumbnail) dapat diakses.
    ```bash
    php artisan storage:link
    ```

8.  **Jalankan server lokal:**
    ```bash
    php artisan serve
    ```
    Aplikasi Anda sekarang berjalan di `http://127.0.0.1:8000`.

---

## 🏛️ Arsitektur Sistem

Untuk menjaga kebersihan kode dan kemudahan pemeliharaan, proyek ini mengadopsi arsitektur **Controller-Service** yang diperluas.

* **Request** (`App\Http\Requests`)
    * **Tugas:** Melakukan validasi semua data yang masuk (input) dari *client*.
    * **Contoh:** `StorePemetaanRequest.php` memvalidasi bahwa `tipe` adalah 'manual' atau 'upload' dan file yang diunggah memiliki ekstensi yang benar (`.zip` atau `.geojson`).

* **Controller** (`App\Http\Controllers`)
    * **Tugas:** Hanya bertanggung jawab untuk mengoordinasi alur *request* dan *response* HTTP. *Controller* dibuat "tipis" (*thin controller*).
    * **Alur:** Menerima `Request`, memanggil *method* di `Service`, dan mengembalikan `Resource` atau `Response`.

* **Service** (`App\Services`)
    * **Tugas:** Menangani semua logika bisnis (business logic) yang kompleks. Ini adalah "otak" dari aplikasi.
    * **Contoh:** `PemetaanService.php` berisi logika untuk memproses file `.zip` SHP, mengonversinya, dan menyimpannya ke *storage*.

* **Resource** (`App\HttpHttp\Resources`)
    * **Tugas:** Mentransformasi data dari *model* Eloquent menjadi format JSON standar yang akan dikirim ke *client*. Ini memastikan format *output* API Anda selalu konsisten.
    * **Contoh:** `PemetaanResource.php` memformat data `Pemetaan` dan menyertakan URL lengkap ke file GeoJSON jika `tipe`='upload'.

* **Traits** (`App\Http\Traits`)
    * **Tugas:** Menyediakan kumpulan *method* yang dapat digunakan kembali (*reusable*) di berbagai kelas.
    * **Contoh:** `UploadFileMap.php` berisi logika untuk meng-handle *upload* file (SHP, GeoJSON) dan menghapus file, sehingga `PemetaanController` dan `PemetaanService` tetap bersih.

---

## 🔐 Role & Hak Akses API

Sistem ini memiliki tiga level hak akses utama yang menentukan API mana yang dapat mereka konsumsi.

| Role | Instansi | Tugas | API yang Digunakan |
| :--- | :--- | :--- | :--- |
| **Superadmin** | Bapppeda | Kelola Pengguna | `API Personil` |
| | PUPR | Kelola OPD | `API Unor` |
| |  | Lihat Artikel | `API Artikel` |
| |  | Lihat Pemetaan | `API Pemetaan` |
| | | Kelola Produk Hukum | `API Produk Hukum` |
| **Admin** | ESDM | Kelola Artikel | `API Artikel by Unor` |
| | Balai Sungai | Kelola Pemetaan | `API Pemetaan by Unor` |
| | Badan Meteorologi.. | Perbarui Profil | `API Personil Update Profil` |
| | DLHK | Perbarui OPD | `API Unor Update Profil` |
| **User (Non Login)** | Masyarakat | Lihat Artikel | `API Artikel Landing` |
| | Masyarakat | Lihat Peta | `API Pemetaan Landing` |

---

## 💡 Panduan Penggunaan & FAQ

### 1. Bagaimana Cara Autentikasi?

API ini menggunakan **Bearer Token** untuk autentikasi.

1.  Dapatkan token Anda dengan melakukan `POST` ke endpoint `api/auth/login` dengan *body* email dan password.
2.  Anda akan menerima token JWT.
3.  Sertakan token ini pada *header* `Authorization` untuk setiap *request* yang membutuhkan autentikasi.

`Header: Authorization: Bearer <TOKEN_ANDA>`

### 2. Bagaimana Alur Pembuatan User Baru?

Alur pembuatan user (Personil) di sistem ini unik dan tidak bisa dilakukan secara langsung. User harus terikat pada **OPD (Unor)**.

**Alur Wajib:**
1.  **Buat OPD (Unor) Dahulu:** Superadmin harus mendaftarkan OPD/Instansi baru terlebih dahulu melalui `POST /api/unor`.
2.  **Buat Personil (User) di Dalam OPD:** Setelah OPD ada, Superadmin dapat menambahkan Personil (yang juga bertindak sebagai User) ke dalam OPD tersebut melalui `POST /api/personil`. *Body request* ini **wajib** menyertakan `unor_id` dari OPD yang dibuat di langkah 1.
3.  **Password Default:** Password untuk user (personil) yang baru dibuat secara otomatis diatur sebagai **NIP (Nomor Induk Pegawai)** dari personil tersebut. User kemudian dapat login menggunakan email dan NIP-nya.

### 3. Bagaimana Alur Kelola Pemetaan (Upload vs Manual)?

Saat membuat atau mengedit data pemetaan, ada dua `tipe` yang bisa dipilih:

* **Tipe `upload`:**
    * **Input:** `tipe: "upload"` dan `file: [FILE_UPLOAD]`.
    * **Aturan:** File yang di-upload **harus** berupa `.zip` (berisi komponen .shp, .shx, .dbf) atau file `.geojson` mentah.
    * **Proses:** API akan otomatis mengonversi .zip atau memvalidasi .geojson, lalu menyimpannya ke *storage*. Kolom `koordinat` di database akan dikosongkan (`NULL`).

* **Tipe `manual`:**
    * **Input:** `tipe: "manual"` dan `koordinat: [JSON_DATA]`.
    * **Alur Frontend:** Frontend harus menampilkan peta (misal: Leaflet) dengan fitur *drawing tools* (Point, Polygon, Polyline).
    * **Proses:** Hasil *drawing* dari pengguna (dalam format GeoJSON Feature atau Geometry) dikirim sebagai *string* JSON atau *array* ke *field* `koordinat`. API akan menyimpan data JSON ini langsung di kolom `koordinat`. Kolom `file` akan dikosongkan (`NULL`).

### 4. Bagaimana Cara Menampilkan Peta (Perbandingan `upload` vs `manual`)?

Endpoint `GET /api/pemetaan/{id}` (yang menggunakan `PemetaanResource`) akan memberi Anda *metadata* peta (nama, deskripsi, dll).

Untuk menampilkan data peta secara visual di **Landing Page** (Leaflet/Mapbox), Anda **harus** menggunakan endpoint khusus:

**`GET /api/pemetaan/{id}/geojson`**

Endpoint ini dirancang cerdas untuk menangani kedua `tipe` pemetaan secara otomatis. Frontend **tidak perlu** melakukan `if/else`.

Berikut adalah perbandingan alur datanya:

| Tipe Peta | Sumber Data (Database) | Proses di Backend (oleh `geojson` endpoint) | Aksi di Frontend (SAMA UNTUK KEDUANYA) |
| :--- | :--- | :--- | :--- |
| **`upload`** | Kolom `file` berisi path (mis: `pemetaan/map-123.geojson`) | 1. Mengambil path file dari kolom `file`. <br> 2. Membaca file dari `storage`. <br> 3. Mengembalikan isi file (`Content-Type: application/geo+json`). | 1. Panggil `GET /api/pemetaan/{id}/geojson`. <br> 2. `L.geoJSON(data).addTo(map);` |
| **`manual`** | Kolom `koordinat` berisi data JSON (mis: `{"type": "Point", ...}`) | 1. Mengambil data JSON dari kolom `koordinat`. <br> 2. Mengembalikan data JSON tersebut (`Content-Type: application/json`). | 1. Panggil `GET /api/pemetaan/{id}/geojson`. <br> 2. `L.geoJSON(data).addTo(map);` |

**Kesimpulan:** Frontend (Landing Page) **tidak perlu** bingung. Cukup panggil `GET /api/pemetaan/{id}/geojson` dan tampilkan hasilnya. Backend sudah menangani semua kompleksitas.


### 5. Bagaimana Upload Artikel dengan Banyak Gambar?

Saat membuat/mengedit artikel, Anda dapat mengunggah banyak gambar *thumbnail*.

* **Create:** (`POST /api/artikel`)
    Gunakan `form-data` dan kirim file gambar dalam format *array* (misal: `thumbnails[0]`, `thumbnails[1]`, ...).
* **Update:** (`PUT /api/artikel/{id}`)
    * Untuk API ini hanya dapat mengelola pembaruan pada tabel artikel
    * Jika terjadi perubahan thumbnail pada artikel, maka akan mengonsumsi API dari thumbnail artikel :
    * Untuk dapat memperbarui **thumbnail-artikel**, melalui (`PUT /api/thumbnail-artikel/{id}`)
    * Untuk dapat menghapus **thumbnail-artikel**, melalui (`DELETE /api/thumbnail-artikel/{id}`)
---

## 📜 Tentang Laravel (Default)

Laravel adalah kerangka kerja aplikasi web dengan sintaks yang ekspresif dan elegan. Kami percaya pengembangan harus menjadi pengalaman yang menyenangkan dan kreatif agar benar-benar memuaskan.

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## 🤝 Berkontribusi

Terima kasih telah mempertimbangkan untuk berkontribusi pada kerangka kerja Laravel! Panduan kontribusi dapat ditemukan di [dokumentasi Laravel](https://laravel.com/docs/contributions).

## 💼 Kode Etik

Untuk memastikan komunitas Laravel ramah bagi semua, harap tinjau dan patuhi [Kode Etik](https://laravel.com/docs/contributions#code-of-conduct).

## 🛡️ Kerentanan Keamanan

Jika Anda menemukan kerentanan keamanan dalam Laravel, silakan kirim email ke Yasdil Lasoma melalui [yasdilofficial@gmail.com](mailto:yasdilofficial@gmail.com). Semua kerentanan keamanan akan segera ditangani.

## 📄 Lisensi

Kerangka kerja Laravel adalah perangkat lunak sumber terbuka yang dilisensikan di bawah [lisensi MIT](https://opensource.org/licenses/MIT).