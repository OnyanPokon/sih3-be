<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework. You can also check out [Laravel Learn](https://laravel.com/learn), where you will be guided through building a modern Laravel application.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

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
