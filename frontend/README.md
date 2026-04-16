# Frontend

Frontend aplikasi MBG Platform. Setup framework pilihan Anda di folder ini.

## Struktur Proyek

```
lomba/
├── backend/    ← Laravel API (seluruh kode backend)
└── frontend/   ← Frontend app (folder ini)
```

## Koneksi ke Backend

Backend API tersedia di `http://localhost:8000/api/` (default Laravel).

Endpoint utama:
- `POST /api/dispatches/{sppg_id}` — Laporan pengiriman (auth + geofence)
- `POST /api/receipts/{school_id}` — Laporan penerimaan (auth + geofence)
- `POST /api/feedback/{school_id}` — Feedback anonim (ZKP + geofence)
- `GET  /api/health` — Health check
