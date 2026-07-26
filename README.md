<div align="center">
    <h1>♨️ Guciravel</h1>
    <p><strong>The Visual N+1 Query Healer for Laravel</strong></p>
    <p><i>Mendeteksi, Melacak, dan Menyembuhkan Penyakit Performa Laravel secara Visual.</i></p>
</div>

---

## 📖 Apa itu Guciravel?
**Guciravel** adalah pustaka pendeteksi *N+1 Query* khusus untuk lingkungan *Development*. Terinspirasi dari Pemandian Air Panas Guci di Tegal yang terkenal menyembuhkan penyakit, pustaka ini menyembuhkan "penyakit kronis" *N+1 Query* yang sering membunuh performa aplikasi Laravel Anda.

Berbeda dengan fitur bawaan Laravel (`preventLazyLoading`) yang hanya melempar halaman *Error* menakutkan, Guciravel bekerja seperti **Intelijen Rahasia**. Ia membiarkan aplikasi Anda berjalan normal, namun jika mendeteksi adanya *N+1 Query*, ia akan menyuntikkan **Layar Peringatan (Alert Panel)** yang sangat informatif di pojok peramban Anda.

## 🛡️ Enterprise-Grade Safety
Guciravel dirancang oleh *Principal Engineer* dengan mempertimbangkan **Production Safety** secara mutlak:
- **Zero Overhead di Production:** Pustaka ini **Mati Total** jika `APP_DEBUG=false` atau jika Anda berada di `production`.
- **Bebas Kebocoran Memori (RAM Safe):** Memiliki pembatas memori bawaan (Maksimal 1000 *query* terdeteksi) sehingga tidak akan menghancurkan RAM Anda saat digunakan di dalam proses *Long-Running* seperti *Queue Worker* (`php artisan queue:work`).
- **CLI Protected:** Otomatis menonaktifkan dirinya saat dijalankan dari Terminal/Console. Ia hanya aktif pada *Web Request* (HTML).

---

## 📦 Instalasi

Karena ini adalah alat bantu *Development*, **sangat disarankan** untuk menginstalnya hanya sebagai dependensi *dev* (`--dev`).

```bash
composer require ginganomercy/guciravel --dev
```

*Paket ini menggunakan fitur Auto-Discovery Laravel. Anda tidak perlu mendaftarkan Service Provider secara manual.*

---

## 🚀 Cara Penggunaan

Anda **tidak perlu melakukan konfigurasi apa pun (Zero Config)**. 
Cukup jalankan aplikasi Laravel Anda secara lokal seperti biasa.

### Skenario Deteksi:
1. Anda membuat relasi Eloquent (Misal: `User` has many `Posts`).
2. Anda melakukan iterasi tanpa *Eager Loading* di dalam file Blade Anda:
   ```blade
   @foreach(App\Models\User::all() as $user)
       <!-- INI ADALAH N+1 TRIGGER! -->
       Jumlah Postingan: {{ $user->posts->count() }} 
   @endforeach
   ```
3. Saat Anda memuat halaman tersebut di *browser*, **Panel Guciravel** akan muncul secara otomatis di pojok kanan bawah layar.

### Apa yang Akan Ditampilkan Guciravel?
Panel Guciravel akan memberikan hasil diagnosis forensik:
1. 🧮 **Jumlah Duplikasi:** Berapa kali *query* yang sama dieksekusi.
2. 💾 **Raw SQL:** Kode SQL mentah yang memicu masalah.
3. 📍 **Lokasi File:** **(SANGAT PENTING!)** Guciravel akan melacak tumpukan eksekusi dan memberi tahu Anda nama file spesifik beserta nomor barisnya (contoh: `resources/views/users.blade.php (Line: 12)`).
4. 💊 **Saran Obat:** Rekomendasi perbaikan menggunakan metode `with()`.

---

## ⚠️ Rekomendasi & Praktik Terbaik

### 1. Kapan Harus Menggunakan `with()`?
Guciravel akan menyarankan penggunaan `with()`. Ini berarti Anda harus mengubah pemanggilan Eloquent Anda dari:
```php
// ❌ Salah (Akan memicu N+1)
$users = User::all();
```
Menjadi:
```php
// ✅ Benar (Eager Loading)
$users = User::with('posts')->get();
```

### 2. Apakah ini menggantikan `preventLazyLoading()`?
Tidak. Keduanya bisa bekerja berdampingan. Namun, jika Anda menyalakan `Model::preventLazyLoading(! app()->isProduction());` di `AppServiceProvider`, aplikasi Anda akan langsung "Mati" (melempar *Exception*) sebelum Guciravel sempat menggambar UI cantiknya. 

Jika Anda lebih menyukai pendekatan visual dan edukatif Guciravel (terutama jika Anda bekerja dengan tim *developer* Junior yang butuh panduan lokasi kode spesifik), Anda tidak perlu menyalakan `preventLazyLoading()`.

### 3. Kompatibilitas dengan SPA/API
Guciravel dirancang **hanya** untuk menginjeksi halaman HTML murni.
- Jika Anda menembak API (mendapatkan respons JSON), Guciravel akan secara diam-diam mematikan dirinya agar tidak merusak format JSON Anda.
- Jika Anda menggunakan pustaka **TurboBlade**, Guciravel sepenuhnya kompatibel dan layarnya akan tetap bertahan selama navigasi.

---

## 📜 Lisensi
Pustaka ini bersifat sumber terbuka (Open-Sourced) di bawah Lisensi [MIT](https://opensource.org/licenses/MIT).