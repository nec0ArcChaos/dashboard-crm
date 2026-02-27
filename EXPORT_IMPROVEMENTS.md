# Dokumentasi Perbaikan Fungsi Export

## Ringkasan Perubahan
Semua fungsi export di dashboard CRM telah diperbaiki dan disesuaikan untuk konsistensi, keamanan, dan fungsionalitas yang lebih baik.

---

## 1. Endpoint Export Ketepatan Waktu
**File:** `application/controllers/dashboard.php`  
**Fungsi Baru:** `export_ketepatan_data()`

### Apa yang berubah:
- ✅ Membuat endpoint terpisah khusus untuk export ketepatan waktu
- ✅ Mengembalikan file CSV dengan proper headers untuk download
- ✅ Support filter ketepatan (all, ontime, late)
- ✅ Support filter tanggal, sumber, dan divisi
- ✅ Menambahkan BOM UTF-8 untuk encoding yang benar
- ✅ Nama file dinamis dengan timestamp: `export_ketepatan_{tipe}_{timestamp}.csv`

### Kolom CSV:
| Kolom | Keterangan |
|-------|-----------|
| ID Komplain | ID task unik |
| Konsumen | Nama konsumen |
| Lokasi | Nama project/lokasi |
| Blok | Nomor blok |
| Divisi | Divisi penanggung jawab |
| Jenis | Kategori komplain |
| Due Date | Tanggal deadline (format: dd-mm-yyyy) |
| Done Date | Tanggal selesai (format: dd-mm-yyyy HH:ii) |
| Status Ketepatan | On Time / Late / Done / - |

### Request Parameter:
```
GET /dashboard/export_ketepatan_data?
  ketepatan=all|ontime|late           # Filter ketepatan (default: all)
  date_from=YYYY-MM-DD               # Tanggal mulai (default: 2025-01-01)
  date_to=YYYY-MM-DD                 # Tanggal akhir (default: hari ini)
  sumber=all|konsumen|sosmed         # Filter sumber (default: all)
  divisi=all|{divisi_name}           # Filter divisi (default: all)
```

---

## 2. Endpoint Export Modal Detail (Improved)
**File:** `application/controllers/dashboard.php`  
**Fungsi:** `export_modal_data()` (diperbarui)

### Apa yang berubah:
- ✅ Support dynamic headers berdasarkan tipe export
- ✅ Support filter ketepatan untuk export divisi/ketepatan
- ✅ Menambahkan kolom ketepatan waktu (Due Date, Done Date, Status Ketepatan)
- ✅ Improved sanitization dan error handling
- ✅ Cache headers yang proper untuk mencegah caching
- ✅ Support lebih banyak parameter filter

### Tipe Export yang Didukung:
| Tipe | Deskripsi | Kolom |
|------|-----------|-------|
| verif_terverifikasi | Komplain terverifikasi | standar |
| verif_belum | Komplain belum terverifikasi | standar |
| esk_sudah | Komplain sudah dieskalasi | standar |
| esk_belum | Komplain belum dieskalasi | standar |
| divisi | Detail ketepatan per divisi | ketepatan |
| status | Detail per status | standar |

### Request Parameter:
```
GET /dashboard/export_modal_data?
  type=verif_terverifikasi|verif_belum|esk_sudah|divisi|status    # Tipe export
  status_id=                         # ID status (untuk type=status)
  divisi=                            # Nama divisi (untuk type=divisi)
  date_from=YYYY-MM-DD              # Filter tanggal
  date_to=YYYY-MM-DD                # Filter tanggal
  sumber=all|konsumen|sosmed        # Filter sumber
  modal_sumber=all|konsumen|sosmed  # Filter sumber modal
  modal_eskalasi=all|sudah|belum    # Filter eskalasi modal
  modal_ketepatan=all|ontime|late   # Filter ketepatan untuk kolom tambahan
```

---

## 3. Update JavaScript Frontend
**File:** `application/views/partials/footer.php`

### Perubahan pada Event Listener Export:

#### A. Export Ketepatan Global (baru)
```javascript
// Sebelum: Menggunakan window.open dengan parameter export=excel (tidak bekerja)
// Sesudah: Menggunakan window.location.href ke endpoint export_ketepatan_data()

document.getElementById('btnKetepatanExport').addEventListener('click', () => {
  const params = new URLSearchParams({
    ketepatan: _ketepatanGlobalFilter,
    date_from: filterGlobal.date_from,
    date_to:   filterGlobal.date_to,
    sumber:    filterGlobal.sumber,
    divisi:    filterGlobal.divisi,
  });
  window.location.href = BASE_URL + 'dashboard/export_ketepatan_data?' + params.toString();
});
```

#### B. Export Modal Detail (improved)
```javascript
// Perubahan: Menambahkan parameter modal_ketepatan dan menggunakan endpoint export_modal_data
document.getElementById('btnExport').addEventListener('click', () => {
  const params = new URLSearchParams({
    type:      _currentModal.type,
    status_id: _currentModal.extra?.status_id || '',
    divisi:    _currentModal.extra?.divisi    || '',
    date_from: filterGlobal.date_from,
    date_to:   filterGlobal.date_to,
    sumber:    filterGlobal.sumber,
    divisi_filter: filterGlobal.divisi,
    modal_sumber: _modalSumberFilter,
    modal_eskalasi: _modalEskalasiFilter,
    modal_ketepatan: _modalKetepatanFilter,  // ← Parameter baru
  });
  window.location.href = BASE_URL + 'dashboard/export_modal_data?' + params.toString();
});
```

---

## 4. Best Practices yang Diimplementasikan

### Security:
- ✅ Input validation dengan regex (format tanggal)
- ✅ Sanitization dengan `htmlspecialchars()` dan `strip_tags()`
- ✅ Proper error handling dengan try-catch
- ✅ SQL injection prevention (menggunakan CodeIgniter query builder)

### Performance:
- ✅ Tanpa pagination untuk export (full data)
- ✅ Efficient CSV generation (streaming)
- ✅ Proper header caching untuk mencegah cached export data

### User Experience:
- ✅ Format file konsisten (CSV dengan UTF-8 BOM)
- ✅ Nama file deskriptif dengan timestamp
- ✅ Automatic file download (bukan popup window)
- ✅ Support filter komplain untuk export yang specific

### Code Quality:
- ✅ DRY principle (reusable sanitization function)
- ✅ Consistent naming convention
- ✅ Clear separation of concerns (endpoint khusus untuk export)
- ✅ Proper documentation

---

## 5. Testing Checklist

### Export Ketepatan Waktu:
- [ ] Export semua data ketepatan
- [ ] Export hanya data "On Time"
- [ ] Export hanya data "Late"
- [ ] Export dengan filter tanggal
- [ ] Export dengan filter sumber (konsumen/sosmed)
- [ ] Export dengan filter divisi
- [ ] Verifikasi format CSV dengan Excel/Calc
- [ ] Verifikasi UTF-8 encoding (tidak ada character corruption)

### Export Modal Detail:
- [ ] Export dari modal verifikasi
- [ ] Export dari modal eskalasi
- [ ] Export dari modal status
- [ ] Export dari modal ketepatan per divisi
- [ ] Export dengan filter sumber di modal
- [ ] Export dengan filter ketepatan
- [ ] Verifikasi nama file dan timestamp
- [ ] Verifikasi jumlah row sesuai filter

### File Format:
- [ ] Header CSV konsisten
- [ ] Special characters (quote, comma) di-escape dengan benar
- [ ] BOM UTF-8 ada di awal file
- [ ] No extra newlines di akhir file
- [ ] Filename tidak mengandung character invalid

---

## 6. Troubleshooting

### Export tidak download, malahan tampil JSON:
**Solusi:** Pastikan menggunakan `endpoint /export_ketepatan_data` atau `/export_modal_data`, bukan parameter `?export=excel` di endpoint lain.

### File CSV tidak terbuka di Excel:
**Solusi:** Verifikasi BOM UTF-8 dengan hex editor. File harus dimulai dengan `EF BB BF`.

### Characters corruption (misalnya: "???" di Excel):
**Solusi:** Gunakan encoding UTF-8 dengan BOM. Browser harus mengenali header `Content-Type: text/csv; charset=utf-8`.

### Filter tidak bekerja di export:
**Solusi:** Verifikasi parameter GET sesuai dokumentasi di atas. Pastikan value parameter tidak null/empty.

---

## 7. Future Enhancements

- [ ] Support XLSX export (lebih user-friendly)
- [ ] Add custom column selection di frontend
- [ ] Add export scheduling (scheduled auto-export)
- [ ] Add data transformation options (pivot, grouping)
- [ ] Audit logging untuk setiap export action
- [ ] Performance optimization untuk dataset besar (> 10K rows)
