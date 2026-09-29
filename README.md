<div align="center">

# 🛍️ Belanjaja — Modern Fullstack Marketplace Platform

[![Laravel](https://img.shields.io/badge/Laravel-12.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
[![Vue.js](https://img.shields.io/badge/Vue.js-3.x-4FC08D?style=for-the-badge&logo=vuedotjs&logoColor=white)](https://vuejs.org)
[![Inertia.js](https://img.shields.io/badge/Inertia.js-Modern_SPA-9553E9?style=for-the-badge&logo=inertia&logoColor=white)](https://inertiajs.com)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS-3.x-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white)](https://tailwindcss.com)
[![PHP](https://img.shields.io/badge/PHP-8.3-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://php.net)
[![CI/CD Pipeline](https://github.com/dxvnee/belanjaja-laravel/actions/workflows/ci.yml/badge.svg)](https://github.com/dxvnee/belanjaja-laravel/actions/workflows/ci.yml)
[![Tests](https://img.shields.io/badge/Tests-80_Passed-success?style=for-the-badge&logo=checkmarx&logoColor=white)](#-automated-testing)
[![License](https://img.shields.io/badge/License-MIT-blue?style=for-the-badge)](LICENSE)

<p align="center">
  Aplikasi marketplace modern *end-to-end* yang dibangun dengan arsitektur <b>Laravel 12</b> dan <b>Vue 3 Inertia.js</b>. Menghubungkan pembeli dan penjual secara dinamis dengan algoritma rekomendasi feeds berbasis preferensi, kalkulasi ongkos kirim otomatis berdasarkan jarak geografis kota, integrasi gateway pembayaran <b>Midtrans Snap</b>, dan sistem pencetakan faktur invoice PDF.
</p>

</div>

---

## 🌟 Fitur Unggulan (Core Highlights)

### 1. 🎯 Algoritma Rekomendasi & Feeds Dinamis
- **Smart Scoring Weights**: Menghitung bobot relevansi produk secara dinamis berdasarkan kategori favorit pengguna, ulasan rating bintang $\ge 4.0$, ketersediaan stok, dan kedekatan lokasi pembeli dengan penjual (*Near You*).
- **Tab Feeds Fleksibel**: Pilihan mode tampilan *Rekomendasi*, *Populer*, *Di Dekat Anda*, dan *Semua Produk*.
- **Session Seed Shuffling**: Setiap refresh menghasilkan rekomendasi produk baru yang variatif tanpa mengorbankan relevansi produk unggulan.

### 2. 🚚 Engine Ongkos Kirim Berdasarkan Jarak ([`ShippingService`](app/Services/ShippingService.php))
- **Pemetaan Zona Geografis Otomatis**: Menghitung jarak antara kota toko penjual (`product.location`) dan alamat penerima (`address.city`):
  - **Dalam Kota (`local`)**: Kota yang sama atau bertetangga.
  - **Antar Kota (`regional`)**: Klaster pulau/regional yang sama (misal sesama Jawa atau sesama Bali/Nusa Tenggara).
  - **Lintas Wilayah / Antar Pulau (`inter_region`)**: Antar pulau berbeda di Indonesia.
- **3 Kategori Layanan Pengiriman**: *Hemat (Ekonomis)*, *Reguler (Standar)*, dan *Kilat (Express)* dengan tarif dinamis dan estimasi waktu sampai (ETD).

### 3. 🏷️ Multi-Kategori Produk (Hingga 3 Kategori)
- Satu produk dapat memiliki hingga **3 kategori relevan** sekaligus melalui relasi pivot Many-to-Many (`category_product`).
- Kategori utama tetap tersinkronisasi otomatis untuk kompatibilitas penuh.
- Filter pencarian dan instant preview mendukung pencarian lintas multi-kategori.

### 4. ⚡ Instant Search Autocomplete Preview
- Pencarian instan langsung menampilkan *live preview dropdown* 4–5 produk teratas lengkap dengan gambar thumbnail, harga, status ketersediaan stok, dan tag kategori.
- Mendukung filter real-time berdasarkan kategori terpilih.

### 5. 📊 Dashboard Penjual & Fulfillment Pesanan
- **Metrik Analitik Penjualan**: Ringkasan performa real-time mencakup *Total Pendapatan Bersih*, *Pesanan Menunggu Pengiriman*, *Total Pesanan*, dan *Katalog Produk Aktif vs Habis*.
- **Alur Pemenuhan Pesanan (Fulfillment)**: Modal input nomor resi pengiriman real-time oleh penjual saat status pesanan `paid` (terbayar).
- **Manajemen Katalog**: Filter ketersediaan stok (*Semua, Tersedia, Habis*) dan pencarian produk toko secara lokal.

### 6. 💳 Transaksi, Midtrans Webhook & Concurrency Safety
- Mendukung alur **Keranjang Belanja** maupun **Beli Langsung (Buy Now)**.
- **Pessimistic Locking (`lockForUpdate()`) & `DB::transaction()`**: Menjamin ketersediaan stok produk secara atomik saat checkout bersamaan, mencegah masalah *overselling* atau *race conditions*.
- **Midtrans Webhook & Signature Verification ([`MidtransWebhookService`](app/Services/MidtransWebhookService.php))**: Verifikasi keamanan signature SHA-512 dengan `hash_equals()` untuk proteksi *timing attacks*, penanganan notifikasi (*settlement, capture, cancel, expire*), mekanisme idempoten, dan pemulihan stok otomatis (*atomic stock restoration*).
- Integrasi Midtrans Snap Popup Payment dengan kalkulasi biaya ongkos kirim otomatis masuk ke rincian tagihan.

### 7. 🧾 Cetak Faktur PDF Invoice Resmi
- Dibuat menggunakan template Blade khusus dengan layout profesional, barcode nomor transaksi, rincian biaya kurir, dan tabel barang.
- Mendukung **Stream Preview di Browser** maupun **Unduh PDF Langsung**.

### 8. ⭐ Sistem Ulasan & Reputasi Toko
- Verifikasi pembelian: Hanya pembeli yang telah mengonfirmasi barang diterima (`completed`) yang dapat memberikan rating bintang (1–5) dan ulasan.
- Halaman profil publik penjual ([`/seller/{id}`](app/Http/Controllers/SellerController.php)) lengkap dengan metrik ulasan toko.

### 9. 🔄 CI/CD Automation (GitHub Actions)
- Otomatisasi pengujian dan kompilasi aset pada setiap `push` dan `pull_request` ke cabang `main`.
- Menjalankan pipeline komprehensif: setup PHP 8.3, dependency caching, `npm run build`, dan eksekusi test PHPUnit secara headless.

### 10. 🌙 Modern Design & Dark Mode Support
- Dibangun dengan **Tailwind CSS** mendukung perpindahan instan antara *Light Mode* dan *Dark Mode*.
- Komponen interaktif: Glassmorphism modal dialog, Skeleton loaders, micro-animations, dan tata letak responsif untuk perangkat mobile maupun desktop.

---

## 🛠️ Tech Stack & Arsitektur

| Layer | Teknologi |
| :--- | :--- |
| **Backend Framework** | [Laravel 12](https://laravel.com) |
| **Language Runtime** | [PHP 8.3+](https://www.php.net) |
| **Frontend Framework** | [Vue 3](https://vuejs.org) (Composition API, `<script setup>`) |
| **SPA Bridge** | [Inertia.js v2](https://inertiajs.com) |
| **Styling & Icons** | [Tailwind CSS 3](https://tailwindcss.com), [@heroicons/vue](https://github.com/tailwindlabs/heroicons) |
| **Authentication** | [Laravel Jetstream](https://jetstream.laravel.com) (Sanctum, 2FA, Profile Management) |
| **Database** | PostgreSQL / SQLite |
| **Payment Gateway** | [Midtrans Snap API](https://midtrans.com) & Webhook Handler |
| **PDF Generation** | [Barryvdh Laravel DomPDF](https://github.com/barryvdh/laravel-dompdf) |
| **CI / CD** | GitHub Actions Pipeline (`.github/workflows/ci.yml`) |
| **Testing Suite** | PHPUnit (80 Automated Test Cases, 455 Assertions) |
| **Build Tool** | [Vite 7](https://vitejs.dev) |

---

## 🚀 Panduan Instalasi & Menjalankan

### Prasyarat:
- PHP $\ge$ 8.2 (disarankan PHP 8.3)
- Composer
- Node.js $\ge$ 18 & NPM
- Database (PostgreSQL atau SQLite)

### Langkah-langkah:

1. **Clone Repositori**:
   ```bash
   git clone https://github.com/username/belanjaja.git
   cd belanjaja
   ```

2. **Pasang Dependensi Backend & Frontend**:
   ```bash
   composer install
   npm install
   ```

3. **Konfigurasi Environment**:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
   *Sesuaikan pengaturan koneksi database dan kredensial Midtrans di file `.env`.*

4. **Migrasi Database & Seeding Data**:
   ```bash
   php artisan migrate --seed
   ```

5. **Buat Symlink Storage (untuk upload foto produk)**:
   ```bash
   php artisan storage:link
   ```

6. **Kompilasi Aset Frontend**:
   ```bash
   npm run build
   # atau untuk mode pengembangan:
   # npm run dev
   ```

7. **Jalankan Server Lokal**:
   ```bash
   php artisan serve
   ```
   Buka peramban di `http://localhost:8000`.

---

## 🧪 Automated Testing

Aplikasi ini dilengkapi pengujian otomatis menyeluruh untuk memastikan seluruh alur bisnis berjalan stabil:

```bash
php artisan test
```

### Hasil Eksekusi Uji:
```text
PASS  Tests\Unit\ExampleTest
PASS  Tests\Feature\AddressTest
PASS  Tests\Feature\CheckoutTest
PASS  Tests\Feature\DashboardTest
PASS  Tests\Feature\JualProductTest
PASS  Tests\Feature\MidtransWebhookTest
PASS  Tests\Feature\NewEcommerceFeaturesTest
PASS  Tests\Feature\OrderFulfillmentTest
PASS  Tests\Feature\OrderInvoiceTest
PASS  Tests\Feature\ProductDetailTest
PASS  Tests\Feature\ReviewTest
PASS  Tests\Feature\SellerProfileTest
...

Tests:    80 passed (455 assertions)
Duration: 1.81s
```

---

## 📁 Struktur Komponen Utama

```bash
belanjaja/
├── .github/
│   └── workflows/
│       └── ci.yml                      # GitHub Actions CI/CD Pipeline
├── app/
│   ├── Http/Controllers/
│   │   ├── AdminController.php         # Dashboard & Metrik Penjual
│   │   ├── CheckoutController.php      # Checkout (Pessimistic Lock & DB::transaction)
│   │   ├── DashboardController.php     # Algoritma Feeds & Pencarian
│   │   ├── JualController.php          # Pasang Iklan Multi-Kategori
│   │   ├── OrderController.php         # Transaksi, Midtrans & Invoice PDF
│   │   └── ProductController.php       # Detail & Manajemen Produk
│   ├── Models/
│   │   ├── Category.php                # Relasi Many-to-Many Produk
│   │   ├── Order.php                   # Order & Shipping Service
│   │   ├── Product.php                 # Relasi Kategori, Rating, Ulasan
│   │   └── User.php                    # Multi-Role Pembeli & Penjual
│   └── Services/
│       ├── MidtransWebhookService.php  # Signature Verification & Idempotency
│       └── ShippingService.php         # Engine Jarak Geografis & Tarif
├── resources/
│   ├── js/
│   │   ├── Components/
│   │   │   ├── CategoryFilter.vue      # Filter Chip Kategori
│   │   │   ├── ProductCard.vue         # Kartu Produk Pembeli
│   │   │   ├── SellerProductCard.vue   # Kartu Produk Penjual
│   │   │   ├── StatCard.vue            # Metrik Statistik Dashboard
│   │   │   ├── SearchAutocomplete.vue  # Pencarian Instan Dropdown
│   │   │   └── StatusSpan.vue          # Badge Status Pembayaran & Kurir
│   │   └── Pages/
│   │       ├── Admin.vue               # Halaman Dashboard Penjual
│   │       ├── Checkout.vue            # Halaman Checkout & Opsi Ongkir
│   │       ├── Dashboard.vue           # Beranda & Rekomendasi Produk
│   │       └── Search.vue              # Halaman Hasil Pencarian
│   └── views/
│       └── invoices/order.blade.php    # Template Cetak PDF Faktur
└── tests/Feature/
    ├── CheckoutTest.php                # Uji Checkout, Ongkir & Concurrency Rollback
    ├── DashboardTest.php               # Uji Feeds & Algoritma Scoring
    ├── MidtransWebhookTest.php         # Uji Signature, Idempotency & Stock Restoration
    ├── NewEcommerceFeaturesTest.php    # Uji Multi-Kategori & Shipping
    ├── OrderFulfillmentTest.php        # Uji Resi & Status Pemenuhan
    └── OrderInvoiceTest.php            # Uji Validasi & Unduh PDF
```
