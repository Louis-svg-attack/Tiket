# Sistem Manajemen Travel Umroh & Haji

> 🛒 Kode ini dibuat oleh **[Toko Source Code](https://id.shp.ee/gteU4cYe)** — https://id.shp.ee/gteU4cYe

Aplikasi web **multi-tenant (multi-cabang)** dan **white-label** untuk mengelola
operasional travel umroh & haji secara menyeluruh: dari pendaftaran jamaah,
kelengkapan dokumen, pembayaran & cicilan, manasik, manifest keberangkatan,
perlengkapan, hingga komisi agen — lengkap dengan dashboard interaktif per peran.

Dibangun dengan **Laravel 13**, **TailwindCSS**, **Alpine.js**, dan **Chart.js**,
mengikuti pola arsitektur yang konsisten (Service Layer, Form Request, Policy,
Enum, Blade Components) dan **production-ready**.

### Target Pengguna (5 Peran)

| Peran | Untuk siapa | Ringkasan akses |
|-------|-------------|-----------------|
| `super_admin` | Owner travel | Semua cabang, branding, seluruh menu |
| `admin` | Staff operasional / admin cabang | Operasional penuh dalam 1 cabang |
| `agent` | Agen / marketing | Daftarkan jamaah, lihat komisi & referral |
| `pilgrim` | Jamaah | Status pendaftaran, pembayaran, dokumen, manasik |
| `guide` | Muthawif / pembimbing | Jamaah bimbingan & jadwal/absensi manasik |

---

## Daftar Isi

1. [Status Build](#1-status-build)
2. [Daftar Fitur](#2-daftar-fitur)
3. [Stack Teknologi](#3-stack-teknologi)
4. [Arsitektur](#4-arsitektur)
5. [Ringkasan ERD](#5-ringkasan-erd)
6. [Matriks Hak Akses](#6-matriks-hak-akses)
7. [Akun Default](#7-akun-default)
8. [Cara Install](#8-cara-install)
9. [Konfigurasi .env](#9-konfigurasi-env)
10. [White-label & Multi-Cabang](#10-white-label--multi-cabang)
11. [Panduan Per Modul](#11-panduan-per-modul-alur-kerja)
12. [Cetak PDF](#12-cetak-pdf)
13. [Testing](#13-testing)
14. [Hasil Audit Sistem](#14-hasil-audit-sistem)
15. [Checklist Deploy Production](#15-checklist-deploy-production)
16. [Keamanan](#16-keamanan)
17. [Lisensi](#17-lisensi)

---

## 1. Status Build

| Status | Keterangan |
|--------|------------|
| ✅ **Selesai & teruji** | Auth + RBAC 5 peran, isolasi multi-cabang, white-label, seluruh master data, seluruh modul operasional, dashboard per-peran (Chart.js), PDF (kwitansi/manifest/kartu), seeder demo, **19 smoke test hijau (274 assertions)** |
| 🛣️ **Roadmap** | Notifikasi WhatsApp/email otomatis, payment gateway online, e-ticket & boarding pass, mobile app, ekspor Excel manifest, multi-bahasa penuh (i18n) |

Aplikasi berjalan via `php artisan serve` setelah `php artisan migrate:fresh --seed`.

---

## 2. Daftar Fitur

### 2.1 Inti & Master Data

| Modul | Akses kelola | Catatan |
|-------|--------------|---------|
| **Cabang** | super_admin | Isolasi data antar cabang |
| **Paket** (Umroh/Haji/Haji Plus) | super_admin, admin | Harga, **kuota**, durasi, tanggal & kota berangkat, hotel Mekah/Madinah, maskapai, fasilitas included/excluded, status |
| **Hotel** | super_admin, admin | Kota, bintang, jarak ke masjid |
| **Maskapai** | super_admin, admin | Kode, transit |
| **Rekening Bank** | super_admin, admin | Per cabang / global |
| **Agen** | super_admin, admin | % komisi + **buatkan akun login** |
| **Muthawif/Pembimbing** | super_admin, admin | + **buatkan akun login** |
| **Jamaah** | super_admin, admin, agent | Data pribadi, paspor + masa berlaku, NIK, mahram, kontak darurat, riwayat kesehatan + **buatkan akun login** |

> **Buatkan akun login**: pada form Jamaah/Agen/Muthawif, centang “Buatkan akun
> login” → otomatis dibuat `User` + password acak (transaksional). Password
> sementara ditampilkan sekali untuk dicatat.

### 2.2 Modul Operasional

| Modul | Siapa yang akses | Fungsi inti |
|-------|------------------|-------------|
| **Pendaftaran / Booking** | staff, agent | Daftarkan jamaah ke paket dengan **kuota `lockForUpdate` (anti over-booking)**, status pendaftaran, auto-buat komisi |
| **Dokumen Jamaah** | staff (verifikasi), pilgrim/agent (unggah) | Checklist standar + unggah ke **disk privat**, status per dokumen (pending/uploaded/verified/rejected), indikator kelengkapan |
| **Pembayaran & Cicilan** | staff (catat), pilgrim/agent (lihat) | Tagihan DP/cicilan/pelunasan per jamaah **atau generate massal per paket**, catat pembayaran (status auto), **kwitansi PDF** |
| **Manasik** | staff & guide (absensi), pilgrim (lihat) | Jadwal + **absensi kehadiran jamaah**, rekap kehadiran |
| **Manifest & Rooming** | staff | Manifest per paket, pembagian kamar/bus/kursi, **manifest PDF** |
| **Perlengkapan** | staff | Distribusi koper/seragam/ihram, tracking serah-terima |
| **Komisi Agen** | staff (kelola), agent (lihat) | Hitung otomatis per jamaah, rekap per agen & per status |
| **Pengumuman** | staff | Broadcast ke peran/paket/cabang, terjadwal (publish/expire) |

### 2.3 Dashboard Per-Peran (Chart.js)

- **Admin/Super Admin**: total jamaah, omzet diterima, outstanding; **doughnut** status pembayaran; **bar** jamaah per paket; **line** pendaftaran 12 bulan; daftar keberangkatan terdekat + sisa kuota; pengumuman terbaru.
- **Agen**: jamaah referral, total/dibayar/pending komisi; **bar** komisi per bulan; **doughnut** status bayar jamaahnya; pengumuman.
- **Jamaah**: status pendaftaran, progress bar pembayaran, checklist dokumen, jadwal manasik, **hitung mundur** keberangkatan; pengumuman.
- **Muthawif**: jamaah bimbingan, manasik hari ini, **doughnut** rekap kehadiran, manasik mendatang; pengumuman.

Semua dashboard **aman saat data kosong** (menampilkan empty-state, bukan error)
dan setiap grafik diisi penuh oleh `DemoDataSeeder`.

---

## 3. Stack Teknologi

| Komponen | Versi | Alasan |
|----------|-------|--------|
| PHP | 8.3+ (diuji 8.4) | Enum backed, readonly props, fitur modern |
| Laravel | ^13.8 (13.15) | Framework utama, skeleton ramping (middleware di `bootstrap/app.php`) |
| MySQL | 8.x (diuji via MAMP 8.0) | RDBMS produksi, dukungan FK & JSON |
| TailwindCSS | ^3.1 | Utility-first styling konsisten + white-label warna |
| Alpine.js | ^3.4 | Interaktivitas ringan (sidebar, dropdown, toggle) |
| Chart.js | ^4.5 | Grafik dashboard (line/bar/doughnut) |
| SweetAlert2 | ^11.26 | Konfirmasi hapus & toast |
| barryvdh/laravel-dompdf | ^3.1 | PDF kwitansi, manifest, kartu jamaah |
| Vite | ^8.0 | Bundling aset frontend |
| Laravel Breeze | ^2.4 (dev) | Scaffolding auth Blade (registrasi publik dinonaktifkan) |
| Laravel Pint | ^1.27 (dev) | Code style (PSR-12) |

---

## 4. Arsitektur

Pola **wajib & konsisten** di seluruh modul:
`migration → enum → model → policy → form request → service → controller → views → routes → sidebar`.

### Struktur Folder Inti

```
app/
├── Enums/                  # Enum type-safe: label(), badgeColor(), options()
│   ├── Concerns/HasEnumHelpers.php
│   ├── Role.php  PackageType.php  PackageStatus.php  RegistrationStatus.php
│   ├── PaymentType.php  PaymentStatus.php  DocumentStatus.php
│   ├── AttendanceStatus.php  EquipmentStatus.php  CommissionStatus.php
│   └── Gender.php  AnnouncementAudience.php
├── Helpers/helpers.php     # currency(), terbilang(), format_date(), wa_link(), setting(), percent(), initials()
├── Http/
│   ├── Controllers/        # 1 controller per modul (tipis, delegasi ke Service)
│   ├── Middleware/         # EnsureUserHasRole (role:), SecurityHeaders
│   └── Requests/<Modul>/   # Form Request validation + authorize()
├── Models/                 # Eloquent + relasi + $fillable + $casts (enum) + soft delete
├── Policies/               # Otorisasi granular per model (+ ChecksBranchAccess)
├── Providers/AppServiceProvider.php   # Registrasi service singleton & POLICIES
└── Services/               # Business logic (lihat tabel di bawah)
resources/views/
├── components/             # Komponen x-* reusable
├── layouts/                # app, guest, partials (sidebar, topbar)
├── dashboard/              # staff, agent, pilgrim, guide
├── pdf/                    # receipt, manifest, card
└── <modul>/                # index, create, edit, show, _form
routes/  web.php · modules.php · auth.php
database/migrations · database/seeders/DemoDataSeeder.php
tests/Feature/ RoleSmokeTest · EmptyStateSmokeTest · WriteSmokeTest
```

### Service Kunci

| Service | Tanggung jawab |
|---------|----------------|
| **BranchContext** | Otoritas isolasi multi-cabang. `scope($query)` membatasi data ke cabang yang boleh dilihat user; super_admin lintas cabang + “fokus cabang” via session. |
| **AccessContext** | Penyempit per-peran: `registrations()` & `pilgrims()` mengembalikan query yang sudah di-scope (agen→referralnya, jamaah→dirinya, muthawif→bimbingannya). Null-safe untuk akun tanpa profil. |
| **FileVault** | Unggah file ke **disk privat**, **menolak tipe berbahaya** (svg/php/exe/js/html, dll), unduh terotorisasi via controller. Hanya jpg/jpeg/png/webp/pdf. |
| **ActivityLogService** | Audit trail (user, aksi, subjek, IP, user-agent). Gagal diam-diam agar tak mengganggu request. |
| **AccountProvisioner** | Buat/tautkan akun login dari form Jamaah/Agen/Muthawif (transaksional), generate password. |
| **SettingService** | White-label branding, **cache forever + auto-flush** saat disimpan. |
| **DashboardService** | Bangun dataset & data chart per peran, defensif terhadap data kosong. |
| **Package/Agent/Guide/Pilgrim/Registration/Payment/Manasik/Manifest/Document Service** | Business logic tiap modul (kuota lock, billing, komisi, absensi, checklist). |

### Komponen Blade Reusable

`x-card`, `x-table`, `x-badge`, `x-input`, `x-select`, `x-stats-card`,
`x-page-header`, `x-empty-state`, `x-action-link`, `x-delete-form`,
`x-breadcrumb`, `x-chart`, `x-icon`, `x-nav-item`.

---

## 5. Ringkasan ERD

```
branches ──┬─< users (role, branch_id)               settings (white-label, key/value)
           ├─< packages >── hotels (makkah/madinah)   activity_logs (user, aksi, IP, UA)
           │        └────── airlines
           ├─< agents (user_id?, commission_rate) >── pilgrims
           ├─< guides (user_id?)                              │
           ├─< bank_accounts                                  │
           └─< pilgrims (user_id?, agent_id?) ────────────────┘
                   │
                   ├─< documents (status, file_path @private)
                   └─< registrations (pilgrim×package UNIK) ──┬─< payments (tagihan+pembayaran)
                          │  status, total_price              ├─── commission (1:1, otomatis)
                          │                                    ├─── manifest_entry (1:1: kamar/bus)
                          │                                    └─< equipment_distributions
                          └── guide_id, agent_id
   manasik_schedules (branch, package?, guide?) ──< manasik_attendances >── pilgrims
   announcements (branch?, package?, audience, publish/expire window)
```

**Relasi utama:**
- `registrations` unik per `(pilgrim_id, package_id)` — 1 jamaah tak bisa dobel di paket sama.
- `commissions` 1:1 dengan `registration`, dibuat otomatis saat booking bila jamaah punya agen.
- `payments` = baris tagihan (DP/cicilan/pelunasan) sekaligus menyimpan jumlah dibayar.
- Dokumen sensitif disimpan di **disk privat** (`file_path`), tak pernah di-serve langsung.
- FK memakai `cascadeOnDelete`/`nullOnDelete` yang sesuai; kolom enum NOT NULL + default + index.

---

## 6. Matriks Hak Akses

Legenda: **V**=view · **C**=create · **E**=edit · **D**=delete · **—**=tak ada akses · *(own)*=hanya miliknya/scoped.

| Modul | super_admin | admin | agent | pilgrim | guide |
|-------|:----------:|:-----:|:-----:|:-------:|:-----:|
| Cabang | V C E D | V *(own)* | — | — | — |
| Paket | V C E D | V C E D *(cabang)* | V | V *(paketnya)* | V *(paketnya)* |
| Hotel / Maskapai / Bank | V C E D | V C E D | — | — | — |
| Agen | V C E D | V C E D *(cabang)* | V *(diri)* | — | — |
| Muthawif | V C E D | V C E D *(cabang)* | — | — | V *(diri)* |
| Jamaah | V C E D | V C E D *(cabang)* | V C E *(referral)* | V *(diri)* | V *(bimbingan)* |
| Pendaftaran | V C E D | V C E D *(cabang)* | V C E *(own)* | V *(diri)* | V *(bimbingan)* |
| Dokumen | V + verifikasi | V + verifikasi *(cabang)* | V + unggah *(referral)* | V + unggah *(diri)* | — |
| Pembayaran | V + catat | V + catat *(cabang)* | V *(referral)* | V *(diri)* | — |
| Manasik | V C E D + absensi | V C E D + absensi *(cabang)* | V | V *(paketnya)* | V + absensi *(diri)* |
| Manifest & Rooming | V + kelola | V + kelola *(cabang)* | — | — | — |
| Perlengkapan | V C E D | V C E D *(cabang)* | — | V *(diri)* | — |
| Komisi | V + kelola status | V + kelola *(cabang)* | V *(diri)* | — | — |
| Pengumuman | V C E D | V C E D *(cabang)* | — | — | — |
| Pengguna | V C E D | V C E D *(cabang, non-super)* | — | — | — |
| White-label | V + simpan | — | — | — | — |
| Log Aktivitas | V (semua) | V *(cabang)* | — | — | — |

Otorisasi ditegakkan dua lapis: **middleware `role:`** (gerbang kasar) + **Policy** (granular, termasuk isolasi cabang via `ChecksBranchAccess`).

---

## 7. Akun Default

Semua akun demo memakai password **`password`**.

| Email | Peran | Akses |
|-------|-------|-------|
| `superadmin@example.com` | Super Admin | Semua cabang, branding, semua menu |
| `admin@example.com` | Admin | Operasional cabang Jakarta |
| `agent@example.com` | Agen | Referral & komisi (Hasan Basri) |
| `jamaah@example.com` | Jamaah | Pendaftaran, dokumen, pembayaran, manasik (Ahmad Fauzi) |
| `muthawif@example.com` | Muthawif | Bimbingan & absensi (Ustadz Abdullah) |

**Akun jamaah & data demo chart.** `DemoDataSeeder` (deterministik & idempotent)
membuat **12 jamaah** yang **masing-masing punya akun login** (`jamaah2@example.com`
… `jamaah12@example.com`, password `password`), 3 agen, 3 muthawif, 5 paket,
hotel/maskapai/rekening, lalu mengisi pendaftaran (tersebar 12 bulan), pembayaran
+ cicilan (campur **lunas/sebagian/belum**), absensi manasik, dokumen (berbagai
status), komisi, dan pengumuman — **agar SEMUA grafik dashboard tampil berisi**.
Menjalankan ulang seeder tidak menggandakan data.

---

## 8. Cara Install

> Prasyarat: PHP 8.3+, Composer, Node.js 18+, dan MySQL 8 (mis. lewat **MAMP**).

```bash
# 1. Masuk folder proyek (clean copy)
cd sistem-manajemen-travel-umroh-haji

# 2. Dependensi
composer install
npm install

# 3. Env & app key
cp .env.example .env        # jika .env belum ada
php artisan key:generate

# 4. Sesuaikan koneksi DB di .env (lihat bagian 9), buat database kosong:
#    CREATE DATABASE umroh_haji CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

# 5. Migrasi + seed demo (akun & data chart)
php artisan migrate:fresh --seed

# 6. Symlink storage (untuk logo/favicon white-label)
php artisan storage:link

# 7. Build aset frontend
npm run build      # atau: npm run dev (mode pengembangan)

# 8. Jalankan
php artisan serve
# buka http://127.0.0.1:8000  →  login superadmin@example.com / password
```

---

## 9. Konfigurasi `.env`

```env
APP_NAME="Travel Umroh & Haji"
APP_ENV=local            # production di server
APP_DEBUG=true           # false di production
APP_URL=http://127.0.0.1:8000
APP_LOCALE=id

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=8889             # MAMP default 8889; XAMPP/MySQL standar 3306
DB_DATABASE=umroh_haji
DB_USERNAME=root
DB_PASSWORD=root         # MAMP default root

FILESYSTEM_DISK=local
SESSION_DRIVER=database
CACHE_STORE=database     # boleh redis di production
QUEUE_CONNECTION=database
```

> **Disk privat** untuk dokumen sensitif terdaftar di `config/filesystems.php`
> sebagai disk `private` (root `storage/app/private-vault`) — tidak dapat diakses
> dari web; unduhan selalu melewati controller terotorisasi.

---

## 10. White-label & Multi-Cabang

### White-label (Super Admin → menu **White-label**)

1. Buka **Sidebar → Sistem → White-label** (`/settings`).
2. Ubah **nama aplikasi, subjudul, alamat, telepon, email, teks footer**.
3. Atur **warna utama & sekunder (hex)** — langsung dipakai di seluruh UI
   (sidebar, tombol, badge) lewat CSS variable `--brand`.
4. Unggah **logo & favicon** (PNG/JPG).
5. Simpan → setelan disimpan ke tabel `settings`, **di-cache selamanya** dan
   **otomatis di-flush** sehingga perubahan langsung tampil.

### Multi-Cabang

- Setiap entitas operasional menyimpan `branch_id`. **Admin/agen/jamaah/muthawif
  hanya melihat data cabangnya** — ditegakkan terpusat oleh `BranchContext::scope()`.
- **Super admin melihat semua cabang**, dan dapat **memfokuskan UI ke satu cabang**
  via dropdown di topbar (tersimpan di session) — berguna saat menambah data agar
  `branch_id` otomatis benar.
- Tambah cabang baru: **Sidebar → Master Data → Cabang → Tambah**.

---

## 11. Panduan Per Modul (Alur Kerja)

Alur operasional umum dari awal sampai berangkat:

```
1) Master data  → Buat Cabang, Hotel, Maskapai, Rekening, lalu Paket (set kuota & tanggal).
2) Agen/Muthawif→ Daftarkan agen & muthawif (opsional: buatkan akun login).
3) Jamaah       → Input data jamaah (paspor, NIK, mahram, darurat). Centang "Buatkan akun login".
4) Booking      → Pendaftaran → pilih jamaah + paket (kuota dicek lockForUpdate). Komisi agen auto-terbentuk.
5) Dokumen      → Buka jamaah → "Buat Checklist Standar" → unggah & verifikasi (KTP, paspor, foto, vaksin, dst).
6) Pembayaran   → Generate tagihan (DP + N cicilan) per pendaftaran atau massal per paket → catat pembayaran (status auto) → cetak kwitansi.
7) Manasik      → Buat jadwal (paket + muthawif) → catat absensi kehadiran jamaah.
8) Manifest     → Per paket: atur kamar/tipe/bus/kursi/mahram → cetak manifest PDF.
9) Perlengkapan → Distribusi koper/seragam/ihram, tandai sudah diserahkan.
10) Keberangkatan→ Cetak kartu jamaah; pantau sisa kuota & status di dashboard.
```

Setiap halaman **adaptif per-peran** dan **aman saat data kosong** (empty-state).

---

## 12. Cetak PDF

Ditenagai `barryvdh/laravel-dompdf`. Semua dokumen terotorisasi (Policy) dan
memakai branding white-label aktif.

| Dokumen | Cara akses | Rute |
|---------|-----------|------|
| **Kwitansi pembayaran** | Halaman pembayaran/tagihan → tombol cetak kwitansi pada tagihan yang sudah dibayar | `GET /pdf/payment/{payment}/receipt` |
| **Manifest jamaah** | Manifest & Rooming → buka paket → cetak (staff) | `GET /pdf/package/{package}/manifest` |
| **Kartu jamaah** | Detail pendaftaran → cetak kartu | `GET /pdf/registration/{registration}/card` |

Kwitansi memuat nominal **terbilang** (helper `terbilang()`), manifest memuat
kamar/bus/kursi/mahram, kartu memuat data paspor & kontak darurat.

---

## 13. Testing

```bash
php artisan test                     # seluruh suite
php artisan test --testsuite=Feature # hanya feature/smoke test
```

Suite memakai **database MySQL khusus uji** (`umroh_haji_test`) dan **auto-skip
bila database tidak tersedia** (aman untuk CI). Yang dicakup **smoke test**:

| Test | Membuktikan |
|------|-------------|
| **RoleSmokeTest** | Setiap halaman untuk **tiap peran** (super_admin, admin, agent, pilgrim, guide) tampil **tanpa error 500** dengan **data terisi**; halaman inti mengembalikan **200**; PDF (kwitansi/manifest/kartu) tergenerasi. |
| **EmptyStateSmokeTest** | Setiap halaman tampil tanpa 500 saat **data kosong**, untuk akun peran **tanpa profil tertaut**, dan pada **cabang baru yang kosong** (empty-state, bukan crash). |
| **WriteSmokeTest** | Jalur **simpan** berhasil & tersimpan: buat hotel/paket, **booking + uji anti over-booking (kuota penuh ditolak rapi)**, generate tagihan + **catat pembayaran (status auto)**, **absensi manasik**, **provisioning akun agen**, seed checklist dokumen. Dibungkus `RefreshDatabase` (transaksi → rollback) agar DB tidak kotor. |

Hasil terkini: **19 test hijau, 274 assertions.**

---

## 14. Hasil Audit Sistem

Audit menyeluruh dijalankan dan temuannya diperbaiki. Ringkasan status:

| Area | Yang dicek | Status |
|------|-----------|--------|
| **Route** | Nama unik (tanpa duplikat), controller+method ada, **tanpa tabrakan** (`whereNumber` mencegah `/create` tertangkap `/{id}`), proteksi `role:` tepat, tak ada route hilang (semua `route()` di view valid) | ✅ 126 named routes, **0 duplikat**, **113 referensi `route()` di view semua valid** |
| **Controller** | Memanggil `authorize()`/Policy, validasi via **Form Request**, eager-loading (anti N+1), branch-scoping konsisten, tanpa import/variabel tak terpakai | ✅ (dibersihkan Laravel Pint) |
| **View (Blade)** | **Null-safety** (`?->`, `?? default`, guard), variabel dikirim controller, `route()`/`@can` valid, komponen `x-*` prop benar, empty-state ditangani, sidebar hanya muncul untuk peran berhak | ✅ **seluruh 119 view ter-compile** (`view:cache`) |
| **Policy/Request/Model/Migration** | Setiap model terbatas punya **Policy terdaftar** di provider, `authorize()` di Form Request benar, enum NOT NULL + default + index, FK `cascade`/`nullOnDelete`, `$fillable`/`$casts` konsisten | ✅ 16 Policy terdaftar |
| **Pembuktian via test** | RoleSmokeTest, EmptyStateSmokeTest, WriteSmokeTest | ✅ **19/19 hijau** |
| **Produksi** | `config:cache`, `route:cache`, `view:cache` kompilasi bersih | ✅ |

---

## 15. Checklist Deploy Production

```bash
# 1. Mode & alamat (.env)
APP_ENV=production
APP_DEBUG=false
APP_URL=https://domain-anda.com        # HTTPS dipaksa otomatis bila APP_URL https
# TRUSTED_PROXIES=*                    # WAJIB bila di belakang Cloudflare/nginx/LB
# FORCE_HTTPS=true                     # hanya bila ingin menimpa deteksi otomatis
php artisan key:generate --force       # APP_KEY wajib terisi

# 2. Optimasi & cache
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan storage:link
npm ci && npm run build
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 3. Buat akun login pertama (WAJIB — migrate saja menghasilkan tabel users kosong)
php artisan app:create-admin
# atau non-interaktif:
# php artisan app:create-admin --name="Admin" --email=admin@domain.com --password=rahasia123 -n

# 4. Verifikasi kesiapan server (mendiagnosa penyebab gagal login)
php artisan app:check-deploy

# 5. Permission (writable oleh web server)
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# 6. Cron (scheduler — untuk pengumuman terjadwal & job)
* * * * * cd /path/app && php artisan schedule:run >> /dev/null 2>&1

# 7. Queue worker (opsional, bila pakai antrian)
php artisan queue:work --tries=3 --timeout=90
```

**Nginx (ringkas):**
```nginx
server {
    listen 443 ssl;
    server_name domain-anda.com;
    root /path/app/public;
    index index.php;
    location / { try_files $uri $uri/ /index.php?$query_string; }
    location ~ \.php$ { fastcgi_pass unix:/run/php/php8.3-fpm.sock; include fastcgi.conf; }
    location ~ /\.(?!well-known).* { deny all; }   # blokir dotfiles
}
```

### Gagal login setelah deploy — penyebab tersering

Jalankan `php artisan app:check-deploy` di server; perintah ini memeriksa
seluruh poin di bawah secara otomatis.

| Gejala di browser | Penyebab | Solusi |
|---|---|---|
| `These credentials do not match our records` padahal password benar | Tabel `users` kosong — `migrate --force` tidak membuat akun | `php artisan app:create-admin` |
| `Invalid request (Unsupported SSL request)` / `ERR_SSL_PROTOCOL_ERROR` | HTTPS dipaksa padahal server belum punya sertifikat SSL | Samakan `APP_URL` dengan alamat asli (`http://` bila belum SSL) |
| `419 Page Expired` saat menekan tombol Masuk | Sesi/CSRF tidak tersimpan: tabel `sessions` belum ada, `storage/` tidak writable, atau `SESSION_SECURE_COOKIE=true` di situs non-https | `php artisan migrate --force`, `chmod -R 775 storage bootstrap/cache`, cek `SESSION_SECURE_COOKIE` |
| Halaman login memuat ulang terus tanpa pesan error | Cookie sesi tidak terkirim — `SESSION_DOMAIN` tidak cocok dengan domain, atau `APP_KEY` kosong | Kosongkan `SESSION_DOMAIN`, jalankan `php artisan key:generate --force` |
| `Too many login attempts` padahal baru sekali coba | Di belakang proxy tanpa `TRUSTED_PROXIES`, semua pengunjung terbaca satu IP sehingga berbagi jatah 5x/menit | Set `TRUSTED_PROXIES=*` |
| Perubahan `.env` tidak berpengaruh sama sekali | Konfigurasi masih ter-cache dari nilai lama | `php artisan optimize:clear` lalu cache ulang |
| Tampilan login polos tanpa gaya | Aset Vite belum di-build | `npm ci && npm run build` |
| `Akun Anda tidak aktif. Hubungi administrator.` | Kolom `is_active` user bernilai 0 | Aktifkan lewat menu Pengguna atau `app:create-admin` untuk email yang sama |

**cPanel:** arahkan Document Root ke folder `public/`, set versi PHP ≥ 8.3, buat
database + user MySQL, jalankan migrasi via terminal/SSH, pasang cron di atas.
Folder `storage/app/private-vault` otomatis **di luar** Document Root (berada di
`storage/`), sehingga dokumen sensitif aman.

---

## 16. Keamanan

Proteksi yang diterapkan:

- **Validasi terpusat** lewat Form Request di setiap aksi tulis (tanpa validasi inline tanpa proteksi).
- **Mass-assignment**: setiap model memakai `$fillable` eksplisit.
- **SQL Injection**: seluruh akses data lewat Eloquent / query builder berparameter.
- **XSS**: Blade auto-escape; tidak ada `{!! !!}` untuk input pengguna.
- **Upload berbahaya ditolak**: `FileVault` memblokir `svg/php/exe/js/html/...`; hanya `jpg/jpeg/png/webp/pdf`.
- **Dokumen sensitif di disk privat** (`storage/app/private-vault`), tak pernah di-serve langsung; **unduhan terotorisasi** via Policy.
- **CSRF** aktif pada semua form.
- **Rate-limit login** (5x/menit per email+IP) + Breeze throttling.
- **Security headers** (`X-Content-Type-Options`, `X-Frame-Options`, `Referrer-Policy`, `Permissions-Policy`, dll) via middleware.
- **HTTPS dipaksa** di environment production.
- **Otorisasi berlapis**: middleware `role:` + Policy granular + **isolasi data per cabang** (`BranchContext`).
- **Transaksi DB + `lockForUpdate`** pada booking & pencatatan pembayaran (anti over-booking & race condition).
- **Activity log** mencatat siapa-apa-kapan beserta **IP & User-Agent**.
- **Soft delete** pada entitas inti (data dapat dipulihkan/diaudit).
- **Akun nonaktif** otomatis ditolak login & diblokir middleware.
- **Registrasi publik dinonaktifkan** — akun hanya dibuat oleh staff.

---

## 17. Lisensi

Kode ini dibuat oleh **Toko Source Code** — https://id.shp.ee/gteU4cYe

Dirilis di bawah lisensi **MIT** — lihat berkas [LICENSE.md](LICENSE.md). Bebas
digunakan, dimodifikasi, dan didistribusikan dengan mencantumkan atribusi.
Laravel adalah merek dagang milik pemiliknya masing-masing.

---

> Dibangun dengan ❤️ menggunakan Laravel 13 oleh
> **[Toko Source Code](https://id.shp.ee/gteU4cYe)**. Untuk pertanyaan teknis,
> mulai dari `docs/CONVENTIONS.md` (konvensi build) lalu telusuri modul referensi
> (Hotel) sebagai pola standar.
