# 🍽️ NutriMate – Personal Food Recommendation System

NutriMate merupakan aplikasi berbasis web yang dikembangkan untuk memberikan rekomendasi makanan secara personal berdasarkan kebutuhan energi, kandungan makronutrien, dan preferensi makanan pengguna.

Aplikasi ini menerapkan metode **Hybrid Recommender System** dengan menggabungkan pendekatan **Health-Based Filtering** dan **Content-Based Filtering** untuk menghasilkan rekomendasi makanan yang lebih sesuai dengan kondisi dan preferensi pengguna.

## 📌 Fitur Utama

- **Manajemen Profil Pengguna** – Mengelola data usia, jenis kelamin, berat badan, tinggi badan, dan aktivitas fisik.
- **Perhitungan Kebutuhan Energi** – Menghitung BMI, BMR, TDEE, serta target kebutuhan kalori harian dan target kalori pada setiap waktu makan.
- **Preferensi Makanan** – Memilih 7 makanan favorit sebagai dasar personalisasi rekomendasi.
- **Rekomendasi Makanan Personal** – Menghasilkan 5 rekomendasi teratas (Top-5) pada waktu makan yang dipilih berdasarkan kebutuhan energi, kesesuaian makronutrien, dan karakteristik makanan yang disukai pengguna.
- **Informasi Nilai Gizi** – Menampilkan informasi kalori, protein, lemak, dan karbohidrat pada makanan.
- **Riwayat Rekomendasi** – Menyimpan dan menampilkan riwayat rekomendasi makanan pengguna.

## ⚙️ Metode Rekomendasi

NutriMate menggunakan pendekatan **Hybrid Recommender System** yang menggabungkan **Health-Based Filtering (HBF)** dan **Content-Based Filtering (CBF)**. Prosesnya dilakukan melalui penyaringan bertahap, kemudian dilanjutkan dengan penggabungan dua skor secara berbobot (*weighted*) untuk menentukan peringkat makanan.

Alur proses rekomendasi pada setiap waktu makan:

1. Sistem mengambil target kalori pada waktu makan yang dipilih (sarapan, makan siang, makan malam, atau camilan).
2. **Health-Based Filtering** menyaring makanan berdasarkan waktu makan dan rentang kalori.
3. Pada makanan yang lolos penyaringan, sistem menghitung **Similarity** (Content-Based Filtering) dan **Macro Score**.
4. Kedua nilai digabungkan menjadi **Hybrid Score**, lalu makanan diurutkan dan 5 teratas ditampilkan.

### 1. Health-Based Filtering (HBF)

Health-Based Filtering menyaring makanan agar sesuai dengan kebutuhan energi pengguna, berdasarkan dua kriteria:

- **Waktu makan** – hanya makanan yang sesuai dengan waktu makan yang dipilih (sarapan, makan siang, makan malam, atau camilan).
- **Kalori** – hanya makanan dengan kalori dalam rentang ±20% dari target kalori pada waktu makan tersebut.

Target kalori per waktu makan dihitung dari TDEE dengan proporsi sarapan 16,7%, makan siang 30,5%, makan malam 33,8%, dan camilan 19,0%.

### 2. Content-Based Filtering (CBF)

Content-Based Filtering menghitung tingkat kemiripan makanan dengan selera pengguna menggunakan metode **Cosine Similarity**. Profil preferensi pengguna dibentuk dari 7 makanan favorit, yaitu proporsi kemunculan setiap karakteristik makanan, kemudian dibandingkan dengan karakteristik setiap makanan yang lolos penyaringan.

Karakteristik makanan yang digunakan meliputi:

- Cara pengolahan.
- Cita rasa.
- Gaya masakan.
- Bahan utama.
- Karakteristik tambahan makanan.

### 3. Macro Score

Macro Score menilai kesesuaian kandungan karbohidrat, protein, dan lemak pada makanan terhadap target makronutrien pengguna pada waktu makan yang dipilih.

- Target makronutrien dibentuk dari rentang AMDR terhadap kebutuhan kalori harian: karbohidrat 45–65%, protein 10–35%, dan lemak 20–35%, kemudian dibagi sesuai proporsi energi waktu makan.
- Makanan yang kandungan makronutriennya berada dalam rentang target memperoleh skor 1. Di luar rentang, skor berkurang sesuai besarnya selisih.
- Macro Score merupakan rata-rata skor karbohidrat, protein, dan lemak, dengan nilai antara 0 sampai 1.

### 4. Hybrid Score

Nilai rekomendasi akhir diperoleh dengan menggabungkan nilai kemiripan (Similarity) dari Content-Based Filtering dan nilai kesesuaian makronutrien (Macro Score) menggunakan persamaan:

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
git clone https://github.com/yesinkavlorena/nutrimate-app.git
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
