---
name: Step Shine Works
description: Internal management system for a premium shoe care business — presisi, bersih, terpercaya.
colors:
  deep-ink: "#111827"
  workshop-canvas: "#F9FAFB"
  clean-bench: "#FFFFFF"
  shelf-divider: "#F3F4F6"
  hairline: "#E5E7EB"
  chalk-trace: "#9CA3AF"
  worn-leather: "#6B7280"
  clean-receipt: "#15803D"
  caution-tan: "#B45309"
  caution-surface: "#FFFBEB"
  error-mark: "#F87171"
  info-surface: "#EFF6FF"
  info-ink: "#1D4ED8"
typography:
  display:
    fontFamily: "Figtree, ui-sans-serif, system-ui, sans-serif"
    fontSize: "1.25rem"
    fontWeight: 600
    lineHeight: 1.2
    letterSpacing: "normal"
  headline:
    fontFamily: "Figtree, ui-sans-serif, system-ui, sans-serif"
    fontSize: "0.875rem"
    fontWeight: 600
    lineHeight: 1.4
  body:
    fontFamily: "Figtree, ui-sans-serif, system-ui, sans-serif"
    fontSize: "0.875rem"
    fontWeight: 400
    lineHeight: 1.5
  label:
    fontFamily: "Figtree, ui-sans-serif, system-ui, sans-serif"
    fontSize: "0.75rem"
    fontWeight: 600
    letterSpacing: "0.05em"
  caption:
    fontFamily: "Figtree, ui-sans-serif, system-ui, sans-serif"
    fontSize: "0.75rem"
    fontWeight: 400
    lineHeight: 1.4
rounded:
  sm: "8px"
  md: "12px"
spacing:
  xs: "6px"
  sm: "12px"
  md: "16px"
  lg: "20px"
  xl: "24px"
components:
  button-primary:
    backgroundColor: "{colors.deep-ink}"
    textColor: "{colors.clean-bench}"
    rounded: "{rounded.sm}"
    padding: "8px 16px"
  button-primary-hover:
    backgroundColor: "#374151"
    textColor: "{colors.clean-bench}"
    rounded: "{rounded.sm}"
    padding: "8px 16px"
  button-ghost:
    backgroundColor: "{colors.clean-bench}"
    textColor: "{colors.worn-leather}"
    rounded: "{rounded.sm}"
    padding: "8px 16px"
  card:
    backgroundColor: "{colors.clean-bench}"
    rounded: "{rounded.md}"
    padding: "20px"
  input:
    backgroundColor: "{colors.clean-bench}"
    textColor: "{colors.deep-ink}"
    rounded: "{rounded.sm}"
    padding: "8px 12px"
---

# Design System: Step Shine Works

## 1. Overview

**Creative North Star: "The Trusted Workbench"**

Seperti meja kerja tukang sepatu senior yang sudah bertahun-tahun dipakai — setiap alat ada tempatnya, setiap gerakan punya tujuan. Tidak ada yang berlebihan, tidak ada dekorasi yang sia-sia. Yang ada adalah presisi dan kepercayaan yang terbentuk dari konsistensi.

Ini bukan dashboard startup yang mencoba terlihat menarik. Ini adalah alat kerja profesional yang menghormati waktu penggunanya. Staf kasir di antara melayani pelanggan fisik harus bisa menemukan informasi dalam satu tatapan, bukan dua. Warna dipakai untuk komunikasi, bukan estetika. Ruang kosong adalah ketenangan yang dipilih dengan sengaja.

Sistem ini menolak semua yang terasa seperti template — block warna abu-abu yang sama di setiap produk SaaS, grid kartu seragam tanpa hierarki, gradient ungu-biru yang sudah jutaan kali terlihat. Identitas Step Shine Works harus terasa dibangun khusus, bukan diunduh dari Tailwind UI dan dicat ulang.

**Key Characteristics:**
- Monokromatik dominan dengan warna semantik yang ditakar ketat
- Tipografi yang bekerja — bukan tampil
- Flat by default, elevasi hanya saat state berubah
- Kerapatan informasi yang dihormati: data bisa padat, tapi tidak sesak
- Setiap elemen interaktif terasa responsif dan presisi

## 2. Colors: The Deep Ink Palette

Palet ini tidak mencari perhatian. Satu warna primer yang tegas, satu kanvas yang tenang, dan warna semantik yang muncul hanya saat ada sesuatu yang perlu dikomunikasikan.

### Primary
- **Deep Ink** (#111827): Warna kerja utama. Dipakai untuk tombol primary, nav item aktif, dan elemen interaktif paling penting. Kemunculannya yang langka membuatnya punya otoritas — ketika Deep Ink muncul, pengguna tahu ini yang harus diklik.

### Neutral
- **Workshop Canvas** (#F9FAFB): Latar halaman. Cukup berbeda dari putih untuk memberikan depth, tidak cukup gelap untuk menonjol. Ini adalah permukaan yang menghilang.
- **Clean Bench** (#FFFFFF): Latar kartu dan form. Kontras tipis terhadap Workshop Canvas menciptakan hierarki layering tanpa bayangan.
- **Shelf Divider** (#F3F4F6): Border kartu dan container. Jarak yang ada tapi tidak terlihat — seperti garis pensil yang hampir terhapus.
- **Hairline** (#E5E7EB): Border input dan tabel. Satu tingkat lebih tegas dari Shelf Divider, untuk elemen yang butuh definisi yang lebih jelas.
- **Chalk Trace** (#9CA3AF): Label sekunder, metadata, placeholder. Cukup baca, tidak cukup penting untuk bersaing dengan konten utama.
- **Worn Leather** (#6B7280): Teks tersier, ikon muted, tombol ghost. Lebih hangat dari Chalk Trace tapi tetap di jalur netral.

### Tertiary (Semantic — Status Colors)
- **Clean Receipt** (#15803D): Profit positif, status selesai, konfirmasi sukses. Hijau yang matang — bukan neon, bukan pastel.
- **Caution Tan** (#B45309): Order terlambat, status pending pembayaran, peringatan. Amber yang sudah terpanggang, bukan kuning muda.
- **Caution Surface** (#FFFBEB): Latar banner peringatan. Selalu dipasangkan dengan Caution Tan.
- **Error Mark** (#F87171): Validasi form, tanda required field. Merah yang tidak histeris.
- **Info Surface** (#EFF6FF): Latar badge status informasi (dicuci, inspeksi). Dipasangkan dengan Info Ink.
- **Info Ink** (#1D4ED8): Teks badge status informasi.

### Named Rules

**The Earned Color Rule.** Warna non-netral hanya muncul untuk alasan semantik — status, peringatan, konfirmasi. Jika tidak ada pesan yang perlu disampaikan, tidak ada warna yang perlu ditambahkan. Jangan pakai warna untuk dekorasi atau untuk membuat halaman "terasa lebih hidup."

**The One Authority Rule.** Deep Ink (#111827) adalah satu-satunya warna primary. Jangan tambahkan accent color biru, teal, atau brand color lain di atas palette ini tanpa keputusan eksplisit dari sistem. Polanya yang konsisten adalah identitasnya.

## 3. Typography

**Body & UI Font:** Figtree (dengan fallback ui-sans-serif, system-ui, sans-serif)

**Character:** Humanis dan mudah dibaca dalam ukuran kecil — sifat yang dibutuhkan tool kerja yang information-dense. Tidak ada serif display yang dramatis, tidak ada mono yang terlalu techy. Figtree bekerja keras tanpa terlihat bekerja keras.

### Hierarchy

- **Display** (600, 1.25rem/20px, line-height 1.2): Angka metrik dashboard — pendapatan, jumlah order, margin. Hanya muncul di konteks metric cards. Tidak untuk heading halaman.
- **Headline** (600, 0.875rem/14px, line-height 1.4): Judul halaman (via `@yield('title')`), heading section dalam form. Ukuran body tapi weight-nya yang membedakan.
- **Body** (400, 0.875rem/14px, line-height 1.5): Konten utama — nama pelanggan, detail order, isi tabel. Ukuran default hampir semua teks interaktif.
- **Label** (600, 0.75rem/12px, letter-spacing 0.05em, UPPERCASE): Section headers dalam form dan dashboard ("DATA PELANGGAN", "HARI INI"). Selalu uppercase, selalu Chalk Trace (#9CA3AF). Fungsinya seperti tab divider — tidak dibaca, tapi mengorientasikan.
- **Caption** (400, 0.75rem/12px, line-height 1.4): Metadata, timestamp, helper text, error messages. Warna Chalk Trace atau Worn Leather tergantung konteks.

### Named Rules

**The Label Discipline Rule.** Label section wajib uppercase + letter-spacing + Chalk Trace. Jangan pakai label style ini untuk konten yang perlu dibaca — jika pengguna perlu membaca teksnya, itu bukan label.

**The Size Floor Rule.** Tidak ada teks di bawah 12px (0.75rem). Layar kerja bukan mobile app — tapi 10px adalah kecelakaan, bukan desain.

## 4. Elevation

Sistem ini flat by default. Tidak ada box-shadow di state default kartu, nav, atau input. Depth dibentuk dari dua sumber: **tonal layering** (Workshop Canvas → Clean Bench menciptakan satu tingkat kedalaman) dan **border hairline** (Shelf Divider dan Hairline mendefinisikan edge tanpa bayangan).

Pendekatan ini bukan karena flat itu trendi — tapi karena tool kerja yang penuh shadow terasa seperti tumpukan kertas, bukan workbench yang rapi.

### Shadow Vocabulary

- **State Shadow** (`0 0 0 2px #111827`): Focus ring pada input dan elemen interaktif. Ini satu-satunya "bayangan" dalam sistem — bukan ambient, tapi sinyal state yang presisi.
- **Dropdown Lift** (`0 4px 12px rgba(0,0,0,0.08)`): Hanya untuk dropdown, popover, dan modal — elemen yang secara literal "mengambang" di atas konten.

### Named Rules

**The Flat-By-Default Rule.** Kartu, sidebar, header, dan form container selalu flat di state default. Shadow hanya muncul untuk dua alasan: (1) komponen yang secara konseptual mengambang di atas halaman (modal, dropdown), atau (2) focus ring yang mengkomunikasikan state.

## 5. Components

### Buttons

Tombol dalam sistem ini bukan CTA marketing — ini adalah pemicu aksi kerja. Ukurannya kecil (teks xs/sm), tidak ada padding besar-besaran.

- **Shape:** Gently rounded (8px / `rounded-lg`)
- **Primary:** Deep Ink background (#111827), teks putih, padding 8px 16px, font-medium 12px. Hover: gray-700 (#374151), transition-colors 150ms.
- **Secondary / Ghost:** White background, border Hairline (#E5E7EB), teks Worn Leather, sama rounded dan padding. Hover: Workshop Canvas background.
- **Danger:** Hanya dalam konteks destructive action yang eksplisit. Red border + teks, background putih. Tidak ada tombol merah solid.

### Status Badges (Chip)

Terdapat 10 status order, masing-masing punya semantic color pair. Semua badge menggunakan ring-1 (bukan border solid), background light, teks gelap dari hue yang sama.

| Status | Background | Text | Ring |
|---|---|---|---|
| diterima | slate-100 | slate-700 | slate-200 |
| inspeksi | purple-50 | purple-700 | purple-200 |
| dicuci | blue-50 | blue-700 | blue-200 |
| kering | yellow-50 | yellow-700 | yellow-200 |
| finishing | orange-50 | orange-700 | orange-200 |
| siap_diambil | green-50 | green-700 | green-200 |
| selesai | green-50 | green-800 | green-200 |
| antri | amber-50 | amber-800 | amber-200 |
| proses | blue-50 | blue-800 | blue-200 |
| diambil | gray-100 | gray-600 | gray-200 |

**The Label-Plus-Color Rule.** Setiap badge harus selalu menampilkan label teks status, bukan hanya warna. Staf yang baru masuk atau yang buta warna parsial harus bisa membaca status tanpa bergantung pada hue.

### Cards / Containers

- **Corner Style:** Gently rounded (12px / `rounded-xl`)
- **Background:** Clean Bench (#FFFFFF)
- **Shadow Strategy:** Flat. Tidak ada shadow di default state.
- **Border:** Shelf Divider (#F3F4F6) — `border border-gray-100`
- **Internal Padding:** 20px (`p-5`) untuk metric cards, 24px (`p-6`) untuk form containers

Jangan nest kartu di dalam kartu. Jika butuh grouping di dalam kartu, gunakan background Workshop Canvas + rounded-lg, bukan kartu baru.

### Inputs / Fields

- **Style:** Border Hairline (#E5E7EB), background putih, rounded-lg (8px)
- **Focus:** `ring-2 ring-gray-900` — State Shadow (Deep Ink 2px ring). Tidak ada glow, tidak ada color shift. Presisi seperti crosshair.
- **Error:** Border merah (`border-red-400`), helper text error-mark merah di bawah input
- **Disabled:** opacity-50, cursor-not-allowed
- **Label:** Caption style — 12px, Worn Leather, `mb-1.5`
- **Required marker:** Error Mark (#F87171) `*` setelah label text

### Navigation (Sidebar)

- **Container:** White background, border-r Shelf Divider, fixed left, lebar 52 (208px)
- **Brand area:** Deep Ink logo square (28×28px rounded-lg), nama "Step Shine Works" font-semibold sm
- **Nav item default:** px-3 py-2, rounded-lg, text-sm, Chalk Trace — terasa hampir tidak ada sebelum di-hover
- **Nav item hover:** Workshop Canvas background, gray-900 teks
- **Nav item active:** Deep Ink background (#111827), putih teks — satu-satunya warna primary yang dipakai nav
- **Section separator:** Label style (uppercase xs tracking-wider Chalk Trace) untuk memisahkan grup menu

### Metric Cards (Dashboard)

Komponen khas yang muncul di dashboard. Bukan hero-metric template SaaS — tidak ada gradient accent, tidak ada ikon besar.

- Background Workshop Canvas (`bg-gray-50`) untuk secondary metrics, Clean Bench untuk primary metrics
- Label: Caption uppercase Chalk Trace di atas
- Value: Display type (text-xl font-semibold gray-900)
- Warna value hanya berubah untuk data yang punya makna semantik: margin hijau/amber/merah berdasarkan threshold performa

## 6. Do's and Don'ts

### Do:

- **Do** gunakan Deep Ink (#111827) sebagai satu-satunya primary action color. Konsistensinya adalah otoritasnya.
- **Do** pakai warna semantic (amber, green, blue, red) hanya untuk status atau state — bukan dekorasi.
- **Do** pertahankan hierarki tonal: Workshop Canvas (page) → Clean Bench (card) → Workshop Canvas (nested section). Tiga level sudah cukup.
- **Do** tulis semua section label dengan uppercase + letter-spacing + Chalk Trace. Ini adalah satu-satunya konteks teks uppercase dalam sistem.
- **Do** tampilkan label teks di semua badge status — tidak pernah warna saja.
- **Do** pastikan semua body text melewati WCAG AA contrast (≥4.5:1 terhadap backgroundnya).
- **Do** gunakan `ring-2 ring-gray-900` untuk focus state semua elemen interaktif — konsisten, presisi.

### Don't:

- **Don't** pakai template admin abu-abu generik — grid kartu seragam, sidebar biru, tabel tanpa karakter. Ini adalah anti-referensi utama sistem.
- **Don't** tambahkan accent color baru (teal, indigo, orange brand) di luar palet semantik yang sudah ada.
- **Don't** nest kartu di dalam kartu. Jika butuh grouping, gunakan background tonal, bukan shadow + border baru.
- **Don't** pakai gradient sebagai elemen visual — tidak ada `bg-gradient-to-*` untuk background dekoratif.
- **Don't** pakai box-shadow di state default komponen apapun kecuali dropdown dan modal.
- **Don't** tampilkan status order hanya dengan warna tanpa label teks — accessibility requirement.
- **Don't** pakai font di bawah 12px (0.75rem) di konteks apapun.
- **Don't** gunakan border-left berwarna tebal (>1px) sebagai aksen kartu atau callout. Ganti dengan background tint atau border penuh.
- **Don't** pakai warna solid merah untuk tombol — danger action selalu outlined, bukan solid.
- **Don't** tambahkan ikon dekoratif di atas setiap section heading. Ikon hanya untuk fungsi (action, status indicator), bukan ornamen.
