# hidup-mbg

Platform Enterprise untuk Pelaporan & Analisis Makan Bergizi Gratis (MBG).

## Tech Stack
- **Backend:** Laravel 13 + PHP 8.3
- **Database:** MySQL/MariaDB (Spatial Queries, Immutable Triggers)
- **Auth:** Laravel Sanctum + ZKP Anonymous Identity

## Arsitektur Keamanan (7 Layer)
1. Security Headers (CSP, HSTS, X-Frame, nosniff)
2. Input Sanitizer (XSS, SQLi, Path Traversal, CRLF, Null Byte)
3. Rate Limiting (5 tier: login, reporting, feedback, upload, general)
4. Anti Fake-GPS (Geofencing ST_Distance_Sphere + Device Fingerprint + Velocity Check)
5. Auth & Authorization (Password Policy, Sanctum Token Abilities, Session Hardening)
6. Secure File Upload (MIME sniff, double ext, PHP injection scan, private storage)
7. Database Immutability (MySQL BEFORE UPDATE/DELETE triggers on append-only tables)

## Struktur Folder
```
lomba/
├── backend/     ← Laravel API
│   ├── app/Domains/        ← DDD business logic
│   ├── app/Http/Middleware/ ← Security middleware stack
│   └── app/Models/         ← Eloquent + AppendOnly trait
└── frontend/    ← (to be setup)
```

## Setup
```bash
cd backend
cp .env.example .env
composer install
php artisan key:generate
php artisan migrate
php artisan serve
```
