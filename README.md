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
| | (Semua) | Lihat Artikel | `API Artikel` |
| | (Semua) | Lihat Pemetaan | `API Pemetaan` |
| | (Semua) | Kelola Produk Hukum | `API Produk Hukum` |
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

### 3. Bagaimana Cara Menampilkan Peta GeoJSON?

Endpoint `GET /api/pemetaan/{id}` (yang menggunakan `PemetaanResource`) akan memberi Anda *metadata* peta. Untuk menampilkan peta di Leaflet/Mapbox, Anda harus menggunakan endpoint khusus:

**`GET /api/pemetaan/{id}/geojson`**

Endpoint ini secara cerdas akan:
* Jika `tipe` = `manual`, ia akan mengembalikan data JSON mentah dari kolom `koordinat`.
* Jika `tipe` = `upload`, ia akan membaca file (`.geojson`) dari *storage* dan menampilkannya.

Endpoint ini mengembalikan *raw* GeoJSON (`Content-Type: application/geo+json`), bukan JSON API standar, sehingga siap dibaca oleh *library* peta.

### 4. Bagaimana Upload Artikel dengan Banyak Gambar?

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

... (Bagian ini bisa diisi dengan sisa file `README.md` default Laravel) ...

## 🤝 Berkontribusi

Terima kasih telah mempertimbangkan untuk berkontribusi pada kerangka kerja Laravel! Panduan kontribusi dapat ditemukan di [dokumentasi Laravel](https://laravel.com/docs/contributions).

## 💼 Kode Etik

Untuk memastikan komunitas Laravel ramah bagi semua, harap tinjau dan patuhi [Kode Etik](https://laravel.com/docs/contributions#code-of-conduct).

## 🛡️ Kerentanan Keamanan

Jika Anda menemukan kerentanan keamanan dalam Laravel, silakan kirim email ke Yasdil Lasoma melalui [yasdilofficial@gmail.com](mailto:yasdilofficial@gmail.com). Semua kerentanan keamanan akan segera ditangani.

## 📄 Lisensi

Kerangka kerja Laravel adalah perangkat lunak sumber terbuka yang dilisensikan di bawah [lisensi MIT](https://opensource.org/licenses/MIT).