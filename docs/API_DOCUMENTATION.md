# 📖 Sebaya API Summary (Technical Report)

Ringkasan seluruh endpoint API yang tersedia pada proyek **Sebaya** sesuai dengan `routes/api.php`. 
Semua endpoint dengan tanda `*` (Protected) memerlukan header `Authorization: Bearer {token}`.

---

### 1. Autentikasi & Akun
| Method | Endpoint | Deskripsi |
|:--- |:--- |:--- |
| `POST` | `/login` | Login siswa/umum |
| `POST` | `/auth/google` | Login via Google ID Token |
| `POST` | `/register` | Registrasi siswa baru |
| `POST` | `/send-otp` | Kirim ulang OTP (Siswa) |
| `POST` | `/verify-otp` | Verifikasi akun OTP |
| `POST` | `/forgot-password` | Request link reset password |
| `GET` | `/user` `*` | Ambil data user mentah |
| `GET` | `/user-data` `*` | Profil lengkap & info sekolah/kelas |
| `POST` | `/classes/join` `*` | Gabung ke kelas via Kode Kelas |

### 2. Mood Tracking (AI Supported) `*`
| Method | Endpoint | Deskripsi |
|:--- |:--- |:--- |
| `POST` | `/mood-check` | Simpan mood & dapatkan respon AI |
| `GET` | `/mood-check/today` | Cek input mood hari ini |
| `GET` | `/mood-check/history` | Riwayat mood terakhir |
| `DELETE` | `/mood-check/reset` | Hapus input mood hari ini |

### 3. Jurnal, Todo & Habit Tracker `*`
| Method | Endpoint | Deskripsi |
|:--- |:--- |:--- |
| `GET` | `/journal` | List entri (Teks/Todo/Habit) |
| `POST` | `/journal` | Buat entri baru |
| `GET` | `/journal/{id}` | Detail isi jurnal |
| `PUT` | `/journal/{id}` | Update isi/status item |
| `DELETE` | `/journal/{id}` | Hapus entri jurnal |
| `PATCH` | `/habits/{id}/check-in` `*` | Toggle check-in habit harian |

### 4. Artikel (Publik)
| Method | Endpoint | Deskripsi |
|:--- |:--- |:--- |
| `GET` | `/articles` | List semua artikel (support search & pagination) |
| `GET` | `/articles/{slug}` | Detail artikel + HTML WebView untuk mobile |

### 5. Screening & Assessment (DASS-21) `*`
| Method | Endpoint | Deskripsi |
|:--- |:--- |:--- |
| `GET` | `/screening/packages` | List paket tes tersedia |
| `GET` | `/screening/packages/{id}` | Detail paket & semua soal |
| `POST` | `/screening/sessions` | Mulai sesi tes baru |
| `GET` | `/screening/sessions` | Riwayat sesi tes user |
| `GET` | `/screening/sessions/{id}` | Ambil daftar soal sesi (paginated) |
| `POST` | `/screening/sessions/{id}/answers` | Simpan jawaban per butir |
| `POST` | `/screening/sessions/{id}/submit` | Selesaikan sesi tes |
| `GET` | `/screening/sessions/{id}/result` | Hasil skor & rekomendasi AI |

### 6. Bantuan AI & Jadwal `*`
| Method | Endpoint | Deskripsi |
|:--- |:--- |:--- |
| `GET` | `/chat` `*` | Riwayat percakapan bot |
| `POST` | `/chat` `*` | Kirim pesan & respon AI |
| `GET` | `/schedules` `*` | Daftar jadwal/tugas dari guru |

---
**Base URL:** `https://sebaya.team/api`  
**Standard Response:** JSON  
**Auth:** JWT Bearer Token  
**Detail Docs:** Lihat [`HABIT_API.md`](./HABIT_API.md) dan [`ARTICLE_API.md`](./ARTICLE_API.md)
