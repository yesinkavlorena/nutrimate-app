<p align="center">
  <img src="public/img/nutrimate.png" width="180" alt="NutriMate Logo">
</p>

<h1 align="center">NutriMate</h1>

<p align="center">
  <strong>Personal Food Recommendation System</strong>
</p>

<p align="center">
  Aplikasi berbasis web untuk memberikan rekomendasi makanan personal berdasarkan kebutuhan energi dan preferensi pengguna.
</p>

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-PHP-red" alt="Laravel">
  <img src="https://img.shields.io/badge/MySQL-Database-blue" alt="MySQL">
  <img src="https://img.shields.io/badge/Hybrid-Recommender%20System-green" alt="Hybrid Recommender System">
  <img src="https://img.shields.io/badge/Status-In%20Development-orange" alt="Status">
</p>

---


## 🍽️ About NutriMate

**NutriMate** adalah aplikasi berbasis web yang dirancang untuk membantu pengguna mendapatkan rekomendasi makanan berdasarkan kondisi dan preferensi pribadi.

Sistem tidak hanya mempertimbangkan makanan yang disukai pengguna, tetapi juga memperhatikan kebutuhan energi berdasarkan data profil pengguna.

Konsep utama NutriMate adalah:

> **Personalized Food Recommendation**

Dengan menggabungkan dua pendekatan utama:

* **Health-Based Filtering**
* **Content-Based Filtering**

sistem menghasilkan rekomendasi makanan yang lebih sesuai dengan kebutuhan dan preferensi pengguna.

---

## 🎯 Why I Created NutriMate

Dalam kehidupan sehari-hari, seseorang dapat mengalami kesulitan dalam menentukan makanan yang sesuai dengan kebutuhan energi sekaligus sesuai dengan selera.

Rekomendasi makanan yang hanya berdasarkan preferensi belum tentu sesuai dengan kebutuhan energi pengguna. Sebaliknya, makanan yang sesuai dengan kebutuhan energi belum tentu disukai oleh pengguna.

Karena itu, NutriMate dikembangkan dengan menggabungkan kedua aspek tersebut ke dalam satu sistem rekomendasi.

---

## 🧠 How NutriMate Works

NutriMate menggunakan pendekatan **Hybrid Recommender System** dengan model **sequential/cascade**.

### Health-Based Filtering

Tahap pertama menggunakan informasi pengguna untuk menentukan kebutuhan energi.

Data yang digunakan meliputi:

* Usia
* Jenis kelamin
* Berat badan
* Tinggi badan
* Aktivitas fisik

Data tersebut digunakan untuk menghitung:

```text
BMI → BMR → TDEE → Target Energi
```

Target energi kemudian digunakan untuk melakukan penyaringan makanan berdasarkan **waktu makan** yang dipilih pengguna.

### Content-Based Filtering

Setelah kandidat makanan diperoleh, sistem menggunakan **7 data preferensi makanan** dari pengguna.

Karakteristik makanan kemudian direpresentasikan dalam bentuk vektor dan dibandingkan menggunakan **Cosine Similarity**.

Makanan dengan tingkat kemiripan tertinggi akan mendapatkan peringkat lebih tinggi dalam hasil rekomendasi.

---

## 🔄 Recommendation Flow

```text
                    USER
                      │
                      ▼
              ┌───────────────┐
              │  User Profile │
              └───────┬───────┘
                      │
                      ▼
              ┌───────────────┐
              │ BMI / BMR /   │
              │     TDEE      │
              └───────┬───────┘
                      │
                      ▼
              ┌───────────────┐
              │ Energy Target │
              └───────┬───────┘
                      │
                      ▼
              ┌───────────────┐
              │ 7 Preferences │
              └───────┬───────┘
                      │
                      ▼
        ┌───────────────────────────┐
        │   Health-Based Filtering  │
        └─────────────┬─────────────┘
                      │
                      ▼
        ┌───────────────────────────┐
        │  Content-Based Filtering  │
        └─────────────┬─────────────┘
                      │
                      ▼
              ┌───────────────┐
              │    Cosine     │
```
