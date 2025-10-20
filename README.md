# HPS Dashboard (Slim Scope)

Project Laravel yang disederhanakan untuk kebutuhan HPS (Harga Perkiraan Sementara) dengan fokus pada modul:

- User Management (`users`)
- HPS Emas (`hps_emas`)
- HPS Elektronik (`hps_elektronik`)
- FAQ Chatbot QnA (`faq_chatbot_qna`)

Serta tabel pendukung Laravel: `jobs`, `job_batches`, `failed_jobs`, `sessions`, `password_reset_tokens`, dan `migrations`.

Autentikasi API publik menggunakan static token melalui header `x-token`.

---

## Daftar Isi

- Ringkasan Arsitektur & Modul
- Instalasi & Menjalankan Aplikasi
- Database & Migrations yang Dipertahankan
- Seeding Data (Minimal)
- Autentikasi API (Static Token x-token)
- API HPS Elektronik: Check Price (Grade & Harga)
- Postman Collection & Environment
- Struktur Direktori Penting
- Perintah Umum (Artisan)

---

## Ringkasan Arsitektur & Modul

Modul yang aktif dalam scope saat ini:

- User Management (CRUD)
- HPS Emas (import, listing, export)
- HPS Elektronik (import, listing, export, dan API check-price)
- FAQ Chatbot QnA (import, CRUD)

Middleware utama:

- `ValidateStaticToken` (membaca header `x-token`)
- `auth.sanctum` (untuk user API bawaan Laravel)

---

## Instalasi & Menjalankan Aplikasi

1) Persiapan

```bash
composer install
cp .env.example .env
php artisan key:generate
```

2) Konfigurasi Database (.env)

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=hps_dashboard
DB_USERNAME=postgres
DB_PASSWORD=your_password

# Static token untuk API publik
API_STATIC_TOKEN=your-static-token
```

3) Migrasi (fresh) + seeding minimal

```bash
php artisan migrate:fresh --seed
```

4) Jalankan server dev

```bash
php artisan serve
# akses: http://127.0.0.1:8000
```

---

## Database & Migrations yang Dipertahankan

Direktori: `database/migrations`

Tabel yang dipertahankan untuk scope saat ini:

- `users`, `password_reset_tokens`, `sessions`
- `jobs`, `job_batches`, `failed_jobs`
- `hps_emas`
- `hps_elektronik`
- `faq_chatbot_qna`
- `migrations`

Catatan: File migrations lain yang tidak relevan telah dihapus.

---

## Seeding Data (Minimal)

Seeder aktif (lihat `database/seeders/DatabaseSeeder.php`):

- `UserSeeder`
- `HpsEmasSeeder`
- `HpsElektronikSeeder`
- `FaqChatbotQnaSeeder`

Menjalankan seeder:

```bash
php artisan db:seed
```

---

## Autentikasi API (Static Token x-token)

- Middleware: `App\Http\Middleware\ValidateStaticToken`
- Header yang wajib dikirim: `x-token: <API_STATIC_TOKEN>`
- Konfigurasi token di `.env`: `API_STATIC_TOKEN=...`

Contoh cURL:

```bash
curl -X POST http://127.0.0.1:8000/api/v1/hps-elektronik/check-price \
  -H "Content-Type: application/json" \
  -H "x-token: $API_STATIC_TOKEN" \
  -d '{
    "jenis_barang": "handphone",
    "merek": "samsung",
    "nama_barang": "galaxy s23",
    "kelengkapan": "fullset like new"
  }'
```

---

## API HPS Elektronik: Check Price (Grade & Harga)

- Endpoint: `POST /api/v1/hps-elektronik/check-price`
- Deskripsi: Mengembalikan grade dan harga berdasarkan `jenis_barang`, `merek`, `nama_barang`, dan `kelengkapan`.
- Proteksi: Static token `x-token`.

Request headers:

```
Content-Type: application/json
Accept: application/json
x-token: <API_STATIC_TOKEN>
```

Body (JSON):

```json
{
  "jenis_barang": "handphone",
  "merek": "samsung",
  "nama_barang": "galaxy s23",
  "kelengkapan": "fullset like new"
}
```

Response (200 OK - contoh ringkas):

```json
{
  "success": true,
  "message": "Data harga berhasil ditemukan",
  "data": {
    "request": { ... },
    "results": [
      {
        "id": 22722,
        "jenis_barang": "HANDPHONE",
        "merek": "SAMSUNG",
        "barang": "SAMSUNG GALAXY S23 8/128 GB",
        "tahun": 2023,
        "grade": "A",
        "kondisi": "FULLSET LIKE NEW",
        "harga": "5780000.00",
        "harga_formatted": "Rp 5.8jt",
        "match_score": 110
      }
    ],
    "best_match": { ... },
    "price_range": null
  },
  "meta": {
    "timestamp": "...",
    "version": "1.0",
    "request_id": "..."
  }
}
```

Logika Pencarian (ringkas):

- Normalisasi input (lowercase/trim)
- Filter aktif (`active = true`) pada `hps_elektronik`
- Pencocokan bertahap: exact kondisi/grade → fallback partial
- Perankingan hasil menggunakan skor kesesuaian (jenis, merek, barang, kondisi/grade)

---

## Postman Collection & Environment

Direktori: `postman/`

- Collection: `HPS_Dashboard.postman_collection.json`
- Environment:
  - `HPS_Dashboard_Local.postman_environment.json` (baseUrl: `http://127.0.0.1:8000`)
  - `HPS_Dashboard_Staging.postman_environment.json` (baseUrl: ganti sesuai staging)

Variabel environment yang dipakai:

- `baseUrl`
- `x-token`

Langkah pakai:

1) Import collection & environment ke Postman
2) Pilih environment (Local/Staging)
3) Set `x-token` sesuai `.env` atau token staging
4) Jalankan request "HPS Elektronik → Check Price"

---

## Struktur Direktori Penting

```
app/
  Http/
    Controllers/
      Api/V1/HpsElektronikController.php
    Middleware/ValidateStaticToken.php
  Models/
    HpsElektronik.php
    HpsEmas.php
    FaqChatbotQna.php
    User.php

database/
  migrations/   # hanya migrasi relevan per scope
  seeders/
    DatabaseSeeder.php

routes/
  api.php       # memuat api_v1.php
  api_v1.php    # endpoint v1 (users, hps-elektronik check-price)
  web.php

postman/
  HPS_Dashboard.postman_collection.json
  HPS_Dashboard_Local.postman_environment.json
  HPS_Dashboard_Staging.postman_environment.json
```

---

## Perintah Umum (Artisan)

```bash
# Jalankan server dev
php artisan serve

# Daftar routes
php artisan route:list

# Migrasi
php artisan migrate
php artisan migrate:fresh --seed

# Cache & konfigurasi
php artisan cache:clear
php artisan config:clear
php artisan view:clear

# Pantau log
tail -f storage/logs/laravel.log
```

---

## Catatan

- Format angka ID disesuaikan untuk jutaan: contoh `6_500_000 → 6.5jt` (lihat `App\Helpers\NumberHelper`).
- Endpoint publik memerlukan header `x-token` yang valid.
- Modul non-esensial (di luar scope ini) telah dihapus untuk menjaga kesederhanaan.


