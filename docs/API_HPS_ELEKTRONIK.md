# API HPS Elektronik - Price Checking

## Overview

API endpoint untuk mengecek harga dan grade barang elektronik berdasarkan parameter yang diberikan.

## Endpoint

```
POST /api/v1/hps-elektronik/check-price
```

## Request

### Headers
```
Content-Type: application/json
Accept: application/json
x-token: <YOUR_STATIC_TOKEN>
```

### Body Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| jenis_barang | string | Yes | Jenis barang (contoh: "handphone", "laptop", "tablet") |
| merek | string | Yes | Merek barang (contoh: "samsung", "apple", "xiaomi") |
| nama_barang | string | Yes | Nama/tipe barang (contoh: "galaxy s23", "iphone 14", "redmi note 12") |
| kelengkapan | string | Yes | Kelengkapan barang (contoh: "fullset like new", "fullset", "unit only") |

### Kelengkapan Mapping

Sistem akan melakukan mapping otomatis dari input kelengkapan:

| Input Kelengkapan | Kondisi | Grade |
|-------------------|---------|-------|
| fullset like new, full set like new, fs like new, fsln | fullset like new | A |
| fullset, full set, fs, lengkap | fullset | B |
| unit only, unit saja, uo, unit aja | unit only | C |
| like new, ln, seperti baru | like new | A |
| second, bekas, used | second | B |
| grade a, gradea | - | A |
| grade b, gradeb | - | B |
| grade c, gradec | - | C |

## Example Request

```bash
curl -X POST https://your-domain.com/api/v1/hps-elektronik/check-price \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "x-token: <YOUR_STATIC_TOKEN>" \
  -d '{
    "jenis_barang": "handphone",
    "merek": "samsung",
    "nama_barang": "galaxy s23",
    "kelengkapan": "fullset like new"
  }'
```

## Response

### Success Response (200 OK)

```json
{
  "success": true,
  "message": "Data harga berhasil ditemukan",
  "data": {
    "request": {
      "jenis_barang": "handphone",
      "merek": "samsung",
      "nama_barang": "galaxy s23",
      "kelengkapan": "fullset like new"
    },
    "results": [
      {
        "id": 123,
        "jenis_barang": "Handphone",
        "merek": "Samsung",
        "barang": "Galaxy S23 8/256GB",
        "tahun": 2023,
        "grade": "A",
        "kondisi": "Fullset Like New",
        "harga": 7500000,
        "harga_formatted": "Rp 7.5jt",
        "match_score": 100
      },
      {
        "id": 124,
        "jenis_barang": "Handphone",
        "merek": "Samsung",
        "barang": "Galaxy S23 8/128GB",
        "tahun": 2023,
        "grade": "A",
        "kondisi": "Fullset Like New",
        "harga": 6500000,
        "harga_formatted": "Rp 6.5jt",
        "match_score": 90
      }
    ],
    "best_match": {
      "id": 123,
      "jenis_barang": "Handphone",
      "merek": "Samsung",
      "barang": "Galaxy S23 8/256GB",
      "tahun": 2023,
      "grade": "A",
      "kondisi": "Fullset Like New",
      "harga": 7500000,
      "harga_formatted": "Rp 7.5jt",
      "match_score": 100
    },
    "price_range": {
      "min": 6500000,
      "max": 7500000,
      "min_formatted": "Rp 6.5jt",
      "max_formatted": "Rp 7.5jt"
    }
  }
}
```

### Error Response (404 Not Found)

```json
{
  "success": false,
  "message": "Tidak ditemukan data yang sesuai",
  "data": []
}
```

### Validation Error Response (422)

```json
{
  "success": false,
  "message": "The given data was invalid.",
  "errors": {
    "jenis_barang": ["The jenis barang field is required."],
    "merek": ["The merek field is required."]
  }
}
```

## Match Score Algorithm

Sistem menggunakan algoritma scoring untuk menentukan hasil yang paling relevan:

- **Exact match jenis_barang**: +30 points
- **Partial match jenis_barang**: +20 points
- **Exact match merek**: +30 points  
- **Partial match merek**: +20 points
- **Match nama_barang**: +20 points
- **Exact match kondisi**: +20 points
- **Match grade**: +10 points

Total maksimal score: 100 points

## Usage Examples

### Example 1: Handphone Search

```json
{
  "jenis_barang": "handphone",
  "merek": "apple",
  "nama_barang": "iphone 14 pro",
  "kelengkapan": "fullset"
}
```

### Example 2: Laptop Search

```json
{
  "jenis_barang": "laptop",
  "merek": "asus",
  "nama_barang": "vivobook 14",
  "kelengkapan": "unit only"
}
```

### Example 3: Using Grade

```json
{
  "jenis_barang": "tablet",
  "merek": "ipad",
  "nama_barang": "ipad pro 11",
  "kelengkapan": "grade a"
}
```

## Notes

1. Pencarian tidak case-sensitive (tidak membedakan huruf besar/kecil)
2. Sistem akan mencari exact match terlebih dahulu, kemudian partial match
3. Hasil diurutkan berdasarkan match score tertinggi
4. Hanya menampilkan barang dengan status `active = true`
5. Endpoint ini memerlukan static token via header `x-token`
6. Set token di `.env`:
```
API_STATIC_TOKEN=your-token-here
```
