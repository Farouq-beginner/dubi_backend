# Notifikasi Kuis dan Redirect ke Quiz Detail

Ketika seorang siswa menyelesaikan sebuah kuis, sistem backend akan membuat notifikasi dengan data tambahan (extra fields) yang memungkinkan frontend untuk mengarahkan pengguna ke halaman kuis yang sudah dikerjakan.

## Struktur Notifikasi Kuis Selesai

Ketika kuis berhasil diselesaikan, notifikasi dibuat dengan struktur berikut:

```json
{
  "id": 1,
  "user_id": 5,
  "title": "Kuis Selesai",
  "body": "Selamat! Anda telah menyelesaikan kuis 'Kuis Matematika' dengan nilai 85.0.",
  "type": "success",
  "extra": {
    "quiz_id": 3,
    "attempt_id": 12,
    "score": 85.0
  },
  "is_read": 0,
  "created_at": "2025-12-10T10:30:00.000000Z"
}
```

## Field Penting di `extra`

| Field | Tipe | Deskripsi |
|-------|------|-----------|
| `quiz_id` | integer | ID kuis yang sudah diselesaikan |
| `attempt_id` | integer | ID percobaan kuis (untuk referensi atau detail score) |
| `score` | float | Nilai/skor yang diperoleh (0-100) |

## Implementasi di Frontend (Flutter)

Ketika user mengklik notifikasi jenis "Kuis Selesai" (atau tipe lain yang memiliki `extra.quiz_id`), navigasi ke halaman detail kuis:

### Contoh Handling

```dart
void handleNotificationTap(Notification notification) {
  if (notification.type == 'success' && notification.extra != null) {
    final quizId = notification.extra['quiz_id'];
    final attemptId = notification.extra['attempt_id'];
    
    if (quizId != null) {
      // Navigasi ke halaman kuis detail/review
      Navigator.push(
        context,
        MaterialPageRoute(
          builder: (context) => QuizDetailPage(
            quizId: quizId,
            attemptId: attemptId,
            isReview: true, // Untuk menampilkan jawaban yang sudah dikirim
          ),
        ),
      );
    }
  }
}
```

## Endpoint untuk Mengambil Notifikasi

User yang sudah autentikasi dapat mengambil daftar notifikasinya:

```bash
GET /api/notifications
Authorization: Bearer <TOKEN_FIREBASE_ATAU_SANCTUM>
```

Response:

```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "user_id": 5,
      "title": "Kuis Selesai",
      "body": "Selamat! Anda telah menyelesaikan kuis 'Kuis Matematika' dengan nilai 85.0.",
      "type": "success",
      "extra": {
        "quiz_id": 3,
        "attempt_id": 12,
        "score": 85.0
      },
      "is_read": 0,
      "created_at": "2025-12-10T10:30:00.000000Z"
    }
  ]
}
```

## Endpoint untuk Menandai Notifikasi sebagai Dibaca

Setelah user melihat/mengklik notifikasi, Anda dapat menandai sebagai sudah dibaca:

```bash
POST /api/notifications/{id}/read
Authorization: Bearer <TOKEN_FIREBASE_ATAU_SANCTUM>
```

Response:

```json
{
  "success": true
}
```

Atau untuk menandai semua notifikasi:

```bash
POST /api/notifications/read-all
Authorization: Bearer <TOKEN_FIREBASE_ATAU_SANCTUM>
```

## Catatan

- Field `extra` adalah JSON yang fleksibel; untuk tipe notifikasi lain di masa depan, Anda dapat menambahkan field berbeda.
- Field `type` dapat digunakan untuk menentukan styling (misal: "success" = hijau, "info" = biru, "warning" = kuning).
- Jika `attempt_id` disimpan, Anda juga bisa menggunakannya untuk menampilkan detail ulasan jawaban yang dikirim sebelumnya.
