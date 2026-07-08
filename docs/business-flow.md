# Business Flow — Step Shine Works

Diagram alur bisnis sistem manajemen shoe care Step Shine Works.

---

## 1. Alur Lengkap Order (Order Lifecycle)

```mermaid
flowchart TD
    A([Pelanggan Datang / Hubungi]) --> B[Admin Input Order]
    B --> C{Voucher?}
    C -->|Ya| D[Validasi Voucher]
    D -->|Valid| E[Terapkan Diskon]
    D -->|Tidak Valid| F[Order tanpa diskon + peringatan]
    C -->|Tidak| G[Hitung Total Normal]
    E --> H[Simpan Order]
    F --> H
    G --> H
    H --> I[Status: DITERIMA]
    H --> J[Buat Record Pembayaran]
    H --> K[WA: Order Masuk dikirim ke pelanggan]

    I --> L[Status: INSPEKSI]
    L --> M[Status: DICUCI]
    M --> N[WA: Mulai Dicuci dikirim ke pelanggan]
    M --> O[Status: PENGERINGAN - kering]
    O --> P[Status: FINISHING]
    P --> Q[Status: SIAP DIAMBIL]
    Q --> R[WA: Order Selesai dikirim ke pelanggan]
    Q --> S[Tambah Poin ke Pelanggan]
    Q --> T[Update Tier Pelanggan]
    Q --> U[Set selesai_pada = now]

    Q --> V{Pelanggan Ambil}
    V --> W[Status: SELESAI / Diambil]
    W --> X[Pembayaran auto-lunas jika belum]

    style I fill:#e2e8f0,stroke:#94a3b8
    style L fill:#f3e8ff,stroke:#a855f7
    style M fill:#dbeafe,stroke:#3b82f6
    style O fill:#fef9c3,stroke:#eab308
    style P fill:#fed7aa,stroke:#f97316
    style Q fill:#dcfce7,stroke:#22c55e
    style W fill:#f1f5f9,stroke:#64748b
```

---

## 2. Alur Pembayaran (Payment Flow)

```mermaid
flowchart TD
    A[Order Dibuat] --> B{Metode Bayar?}

    B -->|cash / lunas / qris| C[Status Pembayaran: LUNAS]
    C --> D[dibayar_pada = waktu input order]

    B -->|tempo / transfer| E[Status Pembayaran: BELUM]

    E --> F{Kapan Lunas?}
    F -->|Admin klik Tandai Lunas| G[Status: LUNAS + dibayar_pada = now]
    F -->|Status order = selesai| G

    G --> H[Tercatat di Laporan Bulan Ini]
    D --> H

    H --> I[Dimasukkan ke Net Sales]
    I --> J[Gross Profit = Net Sales - HPP]

    style C fill:#dcfce7,stroke:#22c55e
    style E fill:#fef2f2,stroke:#ef4444
    style G fill:#dcfce7,stroke:#22c55e
```

---

## 3. Alur Loyalitas Pelanggan (Poin & Tier)

```mermaid
flowchart TD
    A[Order Status: SIAP DIAMBIL] --> B{Ada pelanggan_id?}
    B -->|Tidak| C[Tidak ada poin diberikan]
    B -->|Ya| D{Hitung Poin}
    D --> E["poin = pembayaran.total ÷ 10.000 (dibulatkan bawah)"]
    E --> F{poin > 0?}
    F -->|Tidak| G[Tidak ada poin - order gratis atau total sangat kecil]
    F -->|Ya| H[Tambah poin ke pelanggan]
    H --> I[Catat di poin_histories - tipe: tambah]
    I --> J[Hitung ulang total belanja lunas]
    J --> K{Tier baru?}

    K -->|total < Rp 500rb| L[Tier: REGULER]
    K -->|Rp 500rb ≤ total < Rp 2jt| M[Tier: SILVER]
    K -->|Rp 2jt ≤ total < Rp 5jt| N[Tier: GOLD]
    K -->|total ≥ Rp 5jt| O[Tier: PLATINUM]

    L --> P[Simpan tier ke database]
    M --> P
    N --> P
    O --> P

    Q[Admin Kelola Poin Manual] --> R{Tipe?}
    R -->|tambah| S[Increment poin + catat history]
    R -->|kurang| T{Poin cukup?}
    T -->|Ya| U[Decrement poin + catat history]
    T -->|Tidak| V[Error: poin tidak cukup]

    style L fill:#f1f5f9,stroke:#64748b
    style M fill:#f1f5f9,stroke:#64748b
    style N fill:#fef9c3,stroke:#eab308
    style O fill:#f3e8ff,stroke:#a855f7
```

---

## 4. Trigger Notifikasi WhatsApp

```mermaid
flowchart TD
    subgraph AUTO ["Otomatis (via KirimWaJob)"]
        A1[Order Baru Dibuat] -->|Dispatch KirimWaJob| B1[Template: order_masuk]
        B1 --> C1["Variabel: nama, no_order, layanan, lokasi,\njumlah_pasang, total, metode_bayar,\nestimasi_selesai, link_status"]

        A2[Status → DICUCI] -->|Dispatch KirimWaJob| B2[Template: mulai_dicuci]
        B2 --> C2["Variabel: nama, no_order, layanan,\nlokasi, link_status"]

        A3[Status → SIAP DIAMBIL] -->|Dispatch KirimWaJob| B3[Template: order_selesai]
        B3 --> C3["Variabel: nama, no_order, layanan, lokasi,\ntotal, status_bayar, poin, link_status"]
    end

    subgraph MANUAL ["Manual (Admin klik tombol)"]
        D1[Kirim Ulang WA] --> E1{Status order?}
        E1 -->|siap_diambil| F1[Template: order_selesai]
        E1 -->|status lain| F2[Template: order_masuk]

        D2[Kirim Invoice WA] --> E2[Template: invoice]
        E2 --> G2["Variabel: nama, no_order, tanggal, layanan,\nlokasi, jenis_sepatu, jumlah_pasang,\nharga_satuan, total, metode_bayar, status_bayar"]
        E2 --> H2[Tambah: link status publik di akhir pesan]
    end

    subgraph DELIVERY ["Pengiriman"]
        W1[KirimWaJob] --> W2{WABLAS_TOKEN ada?}
        W2 -->|Tidak| W3[Log warning - tidak kirim]
        W2 -->|Ya| W4[Format nomor: 0xxx → 62xxx]
        W4 --> W5[POST ke Wablas API]
        W5 -->|Berhasil| W6[Selesai]
        W5 -->|Gagal| W7[Retry maks 3x, jeda 60 detik]
        W7 -->|Semua gagal| W8[Log error permanen]
    end

    AUTO --> DELIVERY
    MANUAL --> DELIVERY
```

---

## 5. Alur Harga Efektif Order

```mermaid
flowchart TD
    A[Form Order: pilih Layanan + Lokasi] --> B{Ada Lokasi?}

    B -->|Tidak| C[Harga = harga standar layanan]

    B -->|Ya| D{Ada override harga\nper layanan di lokasi ini?}
    D -->|Ya| E[Harga = harga override spesifik]
    D -->|Tidak| F{lokasi.harga_custom = true?}
    F -->|Ya| G[Harga = harga standar + harga_tambahan lokasi]
    F -->|Tidak| H[Harga = harga standar layanan]

    C --> I[Total = harga × jumlah_pasang]
    E --> I
    G --> I
    H --> I

    I --> J{Ada Voucher valid?}
    J -->|Tidak| K[Nominal Pembayaran = Total]
    J -->|Ya, tipe persen| L["Diskon = Total × nilai%"]
    J -->|Ya, tipe nominal| M["Diskon = min(nilai, Total)"]
    L --> N[Nominal Pembayaran = Total - Diskon]
    M --> N

    K --> O[Simpan ke pembayaran.total]
    N --> O

    style E fill:#dcfce7,stroke:#22c55e
    style G fill:#dbeafe,stroke:#3b82f6
    style L fill:#fef9c3,stroke:#eab308
    style M fill:#fef9c3,stroke:#eab308
```

---

## 6. Alur Manajemen Stok

```mermaid
flowchart TD
    A[Bahan Habis Pakai] --> B[Mutasi: KELUAR]
    C[Pembelian Bahan Baru] --> D[Mutasi: MASUK]
    E[Koreksi Fisik] --> F[Mutasi: PENYESUAIAN]

    B --> G["Stok baru = max(0, stok lama - jumlah)"]
    D --> H[Stok baru = stok lama + jumlah]
    F --> I[Stok baru = jumlah yang diinput]

    G --> J[Catat di stok_mutasis]
    H --> J
    I --> J

    J --> K{Cek Status Stok}
    K -->|stok = 0| L[Status: HABIS - badge merah]
    K -->|stok ≤ stok_minimum| M[Status: MENIPIS - badge amber]
    K -->|stok > stok_minimum| N[Status: AMAN - badge hijau]

    L --> O[Muncul di alert Dashboard]
    M --> O

    style L fill:#fef2f2,stroke:#ef4444
    style M fill:#fffbeb,stroke:#f59e0b
    style N fill:#f0fdf4,stroke:#22c55e
```

---

## 7. Alur Kalkulasi HPP dan Laporan Profit

```mermaid
flowchart TD
    A[Master HPP: komponen per layanan] --> B["Total HPP Layanan = SUM(semua komponen biaya)"]

    B --> C[HPP per Order = Total HPP Layanan × jumlah_pasang]

    D[Saat Order Dibuat] --> E{HPP override manual?}
    E -->|Ya| F[Gunakan nilai override]
    E -->|Tidak| G[Hitung otomatis dari master HPP]
    F --> H[Simpan ke orders.hpp]
    G --> H

    H --> I[Laporan HPP per Bulan]
    I --> J["Gross Sales = SUM(harga_efektif × jumlah_pasang)"]
    I --> K["Net Sales = SUM(pembayaran.total) - hanya yang lunas"]
    I --> L["Total HPP = SUM(orders.hpp)"]

    J --> M["Diskon = Gross Sales - Net Sales"]
    K --> N["Gross Profit = Net Sales - Total HPP"]
    L --> N
    N --> O["Gross Margin = (Gross Profit / Net Sales) × 100%"]

    O --> P[Rekap per Layanan]
    O --> Q[Rekap per Lokasi]
    O --> R[Laporan keseluruhan bulan]
```

---

## 8. Alur Verifikasi dan Pengiriman Review Pelanggan

```mermaid
flowchart TD
    A[Status Order → SIAP DIAMBIL] --> B[WA order_selesai dikirim]
    B --> C["Pesan berisi link: /status/{token_publik}"]

    C --> D[Pelanggan buka link di browser]
    D --> E{Order sudah selesai?}
    E -->|Tidak| F[Tampilkan status order saja - tanpa form review]
    E -->|Ya| G[Tampilkan form rating 1-5 bintang + ulasan]

    G --> H{Review sudah ada?}
    H -->|Ya| I[Tampilkan pesan: review sudah diberikan]
    H -->|Tidak| J[Pelanggan isi rating dan ulasan]

    J --> K[Submit POST /orders/{token}/review]
    K --> L[Validasi: rating 1-5, ulasan max 500 karakter]
    L --> M[Simpan ke tabel reviews]
    M --> N[Pesan: Terima kasih atas ulasannya!]

    O[Admin/Owner buka /reviews] --> P[Lihat semua review terbaru]
    P --> Q[Filter, rating, ulasan per pelanggan]

    style E fill:#f1f5f9,stroke:#64748b
    style H fill:#f1f5f9,stroke:#64748b
```

---

*Semua diagram berdasarkan kode aktual di `app/Http/Controllers/`, `app/Models/`, `app/Services/WhatsappService.php`, dan `app/Jobs/KirimWaJob.php`.*

*Terakhir diperbarui: 3 Juli 2026*
