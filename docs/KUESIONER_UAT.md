# KUESIONER USER ACCEPTANCE TESTING (UAT)

## Fitur Penjadwalan Otomatis — Sistem Informasi Operasional Laboratorium Komputer (SIOPAL)

---

## Identitas Responden

| No  | Keterangan        | Isian                            |
| :-: | ----------------- | -------------------------------- |
|  1  | Nama              | **************\_\_************** |
|  2  | Jabatan/Posisi    | **************\_\_************** |
|  3  | Instansi          | Laboratorium Komputer FIK UDINUS |
|  4  | Tanggal Pengisian | **************\_\_************** |

> **Target Responden:** Administrator/Laboran Laboratorium Komputer Fakultas Ilmu Komputer Universitas Dian Nuswantoro (UDINUS).

---

## Petunjuk Pengisian

Berikan penilaian Anda terhadap fitur **Penjadwalan Otomatis** pada sistem SIOPAL dengan memberikan tanda centang (✓) pada salah satu kolom skor yang tersedia.

**Skala Penilaian (Likert):**

| Skala | Keterangan          | Skor |
| :---: | ------------------- | :--: |
|  SS   | Sangat Setuju       |  5   |
|   S   | Setuju              |  4   |
|  CS   | Cukup Setuju        |  3   |
|  TS   | Tidak Setuju        |  2   |
|  STS  | Sangat Tidak Setuju |  1   |

Sumber: (Aliyah et al., 2025)

---

## Daftar Pertanyaan Kuesioner

### Aspek 1: Fungsionalitas (_Functionality_)

| No  | Pernyataan                                                                                                                  | STS (1) | TS (2) | CS (3) | S (4) | SS (5) |
| :-: | --------------------------------------------------------------------------------------------------------------------------- | :-----: | :----: | :----: | :---: | :----: |
|  1  | Fitur penjadwalan otomatis dapat merekomendasikan laboratorium yang sesuai dengan kebutuhan _software_ mata kuliah.         |         |        |        |       |        |
|  2  | Fitur penjadwalan otomatis dapat merekomendasikan slot waktu yang tidak bertabrakan (bentrok) dengan jadwal yang sudah ada. |         |        |        |       |        |
|  3  | Fitur _import_ massal via Excel dapat memproses banyak permintaan jadwal sekaligus dan menampilkan hasilnya dengan benar.   |         |        |        |       |        |

### Aspek 2: Kemudahan Penggunaan (_Usability_)

| No  | Pernyataan                                                                                       | STS (1) | TS (2) | CS (3) | S (4) | SS (5) |
| :-: | ------------------------------------------------------------------------------------------------ | :-----: | :----: | :----: | :---: | :----: |
|  4  | Antarmuka halaman Penjadwalan Otomatis (_Schedule Wizard_) mudah dipahami dan dioperasikan.      |         |        |        |       |        |
|  5  | Formulir input (Program Studi, Mata Kuliah, Jumlah Siswa, Sesi Waktu) mudah diisi dan responsif. |         |        |        |       |        |
|  6  | Tampilan kartu rekomendasi jadwal (Laboratorium, Hari, Waktu) mudah dibaca dan informatif.       |         |        |        |       |        |

### Aspek 3: Keandalan (_Reliability_)

| No  | Pernyataan                                                                                                  | STS (1) | TS (2) | CS (3) | S (4) | SS (5) |
| :-: | ----------------------------------------------------------------------------------------------------------- | :-----: | :----: | :----: | :---: | :----: |
|  7  | Sistem tidak pernah merekomendasikan jadwal yang bentrok (tumpang tindih) dengan jadwal yang sudah ada.     |         |        |        |       |        |
|  8  | Sistem menampilkan pesan yang jelas apabila tidak ada slot waktu yang tersedia (misalnya karena lab penuh). |         |        |        |       |        |

### Aspek 4: Kesesuaian Kebutuhan (_Relevance_)

| No  | Pernyataan                                                                                                           | STS (1) | TS (2) | CS (3) | S (4) | SS (5) |
| :-: | -------------------------------------------------------------------------------------------------------------------- | :-----: | :----: | :----: | :---: | :----: |
|  9  | Fitur penjadwalan otomatis ini sesuai dengan kebutuhan operasional penjadwalan laboratorium sehari-hari.             |         |        |        |       |        |
| 10  | Fitur penjadwalan otomatis ini dapat mengurangi waktu dan usaha yang diperlukan untuk menyusun jadwal secara manual. |         |        |        |       |        |

---

## Saran dan Masukan (Opsional)

Silakan tuliskan saran, kritik, atau masukan untuk pengembangan fitur penjadwalan otomatis di masa mendatang:

> ---
>
> ---
>
> ---

---

## Template Perhitungan Hasil UAT

Setelah kuesioner diisi oleh responden, hitung hasilnya menggunakan rumus berikut (sesuai Bab 3):

```
P = (S / Skor Ideal) × 100%
```

Di mana:

- **P** = Persentase kelayakan
- **S** = Total skor seluruh jawaban responden
- **Skor Ideal** = Skor tertinggi (5) × Jumlah pertanyaan (10) × Jumlah responden

### Contoh Perhitungan (1 responden)

| No  | Pernyataan                            |   Skor   |
| :-: | ------------------------------------- | :------: |
|  1  | Rekomendasi lab sesuai software       |   ...    |
|  2  | Rekomendasi slot tidak bentrok        |   ...    |
|  3  | Import massal berfungsi               |   ...    |
|  4  | Antarmuka mudah dipahami              |   ...    |
|  5  | Formulir mudah diisi                  |   ...    |
|  6  | Kartu rekomendasi informatif          |   ...    |
|  7  | Tidak pernah merekomendasikan bentrok |   ...    |
|  8  | Pesan error jelas                     |   ...    |
|  9  | Sesuai kebutuhan operasional          |   ...    |
| 10  | Mengurangi waktu penyusunan           |   ...    |
|     | **Total Skor (S)**                    | **...**  |
|     | **Skor Ideal**                        |  **50**  |
|     | **Persentase (P)**                    | **...%** |

### Interpretasi Hasil

| Persentase | Kategori           |       Status       |
| :--------: | ------------------ | :----------------: |
| 81% – 100% | Sangat Layak       |    ✅ Diterima     |
| 61% – 80%  | Layak              |    ✅ Diterima     |
| 41% – 60%  | Cukup Layak        | ⚠️ Perlu Perbaikan |
| 21% – 40%  | Tidak Layak        |     ❌ Ditolak     |
|  0% – 20%  | Sangat Tidak Layak |     ❌ Ditolak     |

> **Batas minimum keberhasilan** yang ditetapkan dalam penelitian ini (Bab 3): Kategori **"Layak"** atau **"Baik"** (skor > 61%).
> Apabila persentase ≥ 61%, maka fitur penjadwalan otomatis dinyatakan **layak** dan **diterima** oleh pengguna.

---

## Catatan untuk Peneliti

- [ ] Cetak kuesioner ini dan berikan kepada **minimal 1-3 laboran** Laboratorium Komputer FIK UDINUS
- [ ] Minta responden mencoba langsung fitur penjadwalan otomatis sebelum mengisi kuesioner
- [ ] Kumpulkan kuesioner dan hitung persentase menggunakan template di atas
- [ ] Masukkan hasil ke dalam **Sub-bab 4.5 Hasil UAT** pada dokumen skripsi
