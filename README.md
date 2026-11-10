# 🍽️ NutriMate – Personal Food Recommendation System

NutriMate merupakan aplikasi berbasis web yang dikembangkan untuk memberikan rekomendasi makanan secara personal berdasarkan kebutuhan energi, kandungan makronutrien, dan preferensi makanan pengguna.

Aplikasi ini menerapkan metode **Hybrid Recommender System** dengan menggabungkan pendekatan **Health-Based Filtering** dan **Content-Based Filtering** untuk menghasilkan rekomendasi makanan yang lebih sesuai dengan kondisi dan preferensi pengguna.

## 📌 Fitur Utama

- **Manajemen Profil Pengguna** – Mengelola data usia, jenis kelamin, berat badan, tinggi badan, dan aktivitas fisik.
- **Perhitungan Kebutuhan Energi** – Menghitung BMI, BMR, TDEE, serta target kebutuhan kalori harian.
- **Preferensi Makanan** – Memilih 7 makanan favorit sebagai dasar personalisasi rekomendasi.
- **Rekomendasi Makanan Personal** – Menghasilkan rekomendasi berdasarkan kebutuhan energi, makronutrien, dan karakteristik makanan.
- **Informasi Nilai Gizi** – Menampilkan informasi kalori, protein, lemak, dan karbohidrat pada makanan.
- **Riwayat Rekomendasi** – Menyimpan dan menampilkan riwayat rekomendasi makanan pengguna.

## ⚙️ Metode Rekomendasi

NutriMate menggunakan pendekatan **Hybrid Recommender System** dengan arsitektur Sequential (Cascade), melalui dua tahapan:

### 1. Health-Based Filtering (HBF)

Tahap pertama melakukan penyaringan makanan berdasarkan:
- Waktu makan (sarapan, makan siang, makan malam, dan camilan).
- Kesesuaian kebutuhan kalori dengan toleransi sebesar ±20% dari target kalori per waktu makan.
- Kesesuaian kandungan makronutrien, meliputi protein, lemak, dan karbohidrat.

### 2. Content-Based Filtering (CBF)

Tahap kedua menghitung tingkat kemiripan makanan berdasarkan karakteristik makanan yang disukai pengguna menggunakan metode **Cosine Similarity**.

Karakteristik makanan yang digunakan meliputi:
- Cara pengolahan.
- Cita rasa.
- Gaya masakan.
- Bahan utama.
- Karakteristik tambahan makanan.

### 3. Hybrid Score

Hasil rekomendasi diperoleh dengan menggabungkan nilai Content-Based Filtering dan Macro Score menggunakan persamaan:

**Hybrid Score = (0.5 × Similarity) + (0.5 × Macro Score)**

Makanan kemudian diurutkan berdasarkan nilai Hybrid Score tertinggi dan ditampilkan dalam bentuk Top-5 rekomendasi.

## 🛠️ Teknologi yang Digunakan

| Teknologi | Keterangan |
|---|---|
| Laravel 12.66 | Framework pengembangan aplikasi |
| PHP 8.2 | Bahasa pemrograman backend |
| MySQL | Database management system |
| HTML5 | Struktur halaman web |
| CSS3 | Styling antarmuka |
| JavaScript | Interaksi halaman web |
| Bootstrap | Framework antarmuka pengguna |
| XAMPP 3.3 | Local development environment |
| Git & GitHub | Version control dan repository |

## 📂 Struktur Project

```text
nutrimate-app/
├── app/
│   ├── Http/
│   │   └── Controllers/
│   └── Models/
├── database/
│   ├── migrations/
│   └── seeders/
├── public/
│   ├── css/
│   ├── img/
│   └── js/
├── resources/
│   └── views/
│       ├── layouts/
│       └── ...
├── routes/
│   └── web.php
├── storage/
├── tests/
├── .env.example
├── artisan
├── composer.json
└── README.md
```

## 🚀 Instalasi dan Menjalankan Project

Ikuti langkah-langkah berikut untuk menjalankan NutriMate secara lokal.

### 1. Clone Repository

```bash
git clone https://github.com/USERNAME/nutrimate-app.git
```

### 2. Masuk ke Direktori Project

```bash
cd nutrimate-app
```

### 3. Install Dependencies

```bash
composer install
```

### 4. Konfigurasi Environment

Salin file `.env.example` menjadi `.env`.

```bash
copy .env.example .env
```

Sesuaikan konfigurasi database pada file `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nutrimate
DB_USERNAME=root
DB_PASSWORD=
```

### 5. Generate Application Key

```bash
php artisan key:generate
```

### 6. Jalankan Migrasi Database

```bash
php artisan migrate
```

Jika tersedia database seeder, jalankan:

```bash
php artisan db:seed
```

### 7. Jalankan Aplikasi

```bash
php artisan serve
```

Buka browser dan akses:

```text
http://127.0.0.1:8000
```

## 🎯 Tujuan Pengembangan

NutriMate dikembangkan sebagai implementasi Hybrid Recommender System untuk membantu pengguna memperoleh rekomendasi makanan yang mempertimbangkan kebutuhan energi dan preferensi makanan secara personal.

## 👩‍💻 Pengembang

**Yesinka Vlorena Mutiasari**

Project ini dikembangkan sebagai bagian dari penyelesaian Tugas Akhir.

---

© 2026 NutriMate. All Rights Reserved.
