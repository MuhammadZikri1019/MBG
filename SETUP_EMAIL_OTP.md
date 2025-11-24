# 📧 Panduan Setup Email OTP - Langkah Demi Langkah

## ✅ Apa Yang Sudah Selesai:

### 1. **UI OTP Card** - ✅ SELESAI
- Form verifikasi OTP dengan 6 kotak input modern
- Auto-focus ke kotak berikutnya
- Support paste kode OTP
- Auto-submit setelah 6 digit terisi
- Animasi dan visual feedback

### 2. **Email Service** - ✅ SELESAI
- EmailService class untuk kirim email
- Support PHPMailer (lebih reliable)
- Fallback ke mail() jika PHPMailer tidak ada
- Template email professional

---

## 🚀 Yang Perlu Anda Lakukan:

### Langkah 1: Install PHPMailer

#### **Opsi A: Otomatis (Jalankan script)**
1. Buka Command Prompt/Terminal
2. Navigate ke folder MBG:
   ```cmd
   cd c:\xampp\htdocs\mbg
   ```
3. Jalankan installer:
   ```cmd
   install_phpmailer.bat
   ```
4. Tunggu sampai selesai download & extract

#### **Opsi B: Manual Download**
1. Kunjungi: https://github.com/PHPMailer/PHPMailer/releases/latest
2. Download file ZIP (contoh: `PHPMailer-6.9.1.zip`)
3. Extract ZIP
4. Copy folder `src` dari dalam ZIP
5. Paste ke `c:\xampp\htdocs\mbg\includes\PHPMailer\`

**Struktur folder yang benar:**
```
mbg/
├── includes/
│   ├── EmailService.php
│   └── PHPMailer/
│       ├── PHPMailer.php      ✅
│       ├── SMTP.php           ✅
│       ├── Exception.php      ✅
│       └── (file lainnya...)
```

---

### Langkah 2: Setup Gmail SMTP

#### **2.1. Aktifkan 2-Step Verification**
1. Buka: https://myaccount.google.com/security
2. Scroll ke "2-Step Verification"
3. Klik "Get Started" dan ikuti instruksi
4. Verifikasi dengan nomor HP Anda

#### **2.2. Generate App Password**
1. Setelah 2-Step aktif, scroll ke bawah
2. Cari "App passwords"
3. Klik "App passwords"
4. Login ulang jika diminta
5. Pilih:
   - **Select app**: "Mail"
   - **Select device**: "Windows Computer" atau "Other (Custom name)"
   - Klik "Generate"
6. **COPY** kode 16 digit yang muncul (contoh: `abcd efgh ijkl mnop`)
7. **SIMPAN** kode ini, tidak bisa dilihat lagi!

#### **2.3. Edit Konfigurasi SMTP**
1. Buka file: `c:\xampp\htdocs\mbg\config\email_config.php`
2. Edit bagian berikut:

```php
'smtp_username' => 'emailanda@gmail.com',        // ← GANTI dengan email Gmail Anda
'smtp_password' => 'abcd efgh ijkl mnop',        // ← PASTE App Password (16 digit)
'from_email' => 'emailanda@gmail.com',            // ← GANTI dengan email Gmail Anda yang sama
'from_name' => 'MBG System',                      // ← Bisa diganti nama lain
```

3. **PENTING**: Jangan ada spasi di App Password!
   - ❌ Salah: `'abcd efgh ijkl mnop'`
   - ✅ Benar: `'abcdefghijklmnop'`

4. Save file (Ctrl+S)

---

### Langkah 3: Test Email

#### **3.1. Test via Web Interface**
1. Buka browser
2. Akses: `http://localhost/mbg/test_email.php`
3. Isi:
   - **Email Tujuan**: Email Anda untuk test
   - **Tipe Email**: Pilih "Email OTP Verifikasi"
4. Klik "Kirim Test Email"
5. Cek inbox email Anda (atau folder spam)

**Jika Berhasil:**
- ✅ Alert hijau muncul
- ✅ Email masuk dalam 1-2 menit
- ✅ Kode OTP 6 digit tampil di email

**Jika Gagal:**
- Lihat error message yang muncul
- Cek bagian Troubleshooting di bawah

#### **3.2. Test Registrasi Lengkap**
1. Buka: `http://localhost/mbg/login.php`
2. Klik "Sign Up"
3. Isi form registrasi:
   - **Nama**: Test User
   - **Email**: Email valid Anda
   - **Password**: password123
   - **Konfirmasi**: password123
4. Klik "Daftar Sekarang"

**Yang Terjadi:**
1. Form registrasi hilang
2. **Card OTP muncul** dengan 6 kotak input
3. Alert hijau: "Kode verifikasi telah dikirim ke email Anda"
4. Jika email gagal: kode OTP ditampilkan di alert
5. Cek inbox email Anda
6. Masukkan 6 digit kode OTP (otomatis pindah ke kotak berikutnya)
7. Klik "Verifikasi" atau tunggu auto-submit
8. Redirect ke halaman login jika berhasil!

---

### Langkah 4: Upload ke Wasmer

#### **4.1. File Yang Harus Di-Upload:**
```
✅ config/email_config.php (yang sudah diisi)
✅ includes/EmailService.php
✅ includes/PHPMailer/ (folder lengkap)
✅ login.php (sudah diupdate)
✅ pengelola/karyawan.php (sudah diupdate)
✅ assets/css/style.css (added OTP styles)
✅ assets/js/auth.js (added OTP handling)
```

#### **4.2. Via FTP / File Manager cPanel:**
1. Login ke Wasmer
2. Buka File Manager atau FTP Client (FileZilla)
3. Upload semua file di atas
4. Set permissions (jika diperlukan):
   - Files: `644`
   - Folders: `755`

#### **4.3. Test di Production:**
1. Akses website Wasmer Anda
2. Coba registrasi dengan email valid
3. Cek apakah OTP terkirim
4. Test verifikasi OTP

---

## 🔧 Troubleshooting

### ❌ Error: "PHPMailer class not found"

**Solusi:**
1. Cek folder `includes/PHPMailer/PHPMailer.php` ada tidak
2. Jika tidak ada, install ulang PHPMailer (langkah 1)
3. Pastikan path benar: `includes/PHPMailer/PHPMailer.php`

---

### ❌ Error: "SMTP connect() failed"

**Kemungkinan Penyebab:**

**1. Firewall Blocking**
- Matikan antivirus/firewall sementara untuk test
- Atau whitelist port 587/465

**2. Port Salah**
- Coba ganti port di `email_config.php`:
  ```php
  'smtp_port' => 465,     // Coba 465 jika 587 tidak work
  'smtp_secure' => 'ssl', // Ganti ke 'ssl' jika pakai port  465
  ```

**3. XAMPP PHP tidak support OpenSSL**
- Edit `php.ini` (C:\xampp\php\php.ini)
- Cari: `;extension=openssl`
- Hapus `;` (uncomment)
- Restart Apache

---

### ❌ Error: "Authentication failed"

**Untuk Gmail users:**

**Cek:**
1. ✅ Sudah aktifkan 2-Step Verification?
2. ✅ Menggunakan App Password (bukan password Gmail biasa)?
3. ✅ App Password 16 digit tanpa spasi?
4. ✅ Email di `smtp_username` dan `from_email` sama persis?

**Generate App Password Baru:**
- Hapus App Password lama di Google Account
- Generate App Password baru
- Update di `email_config.php`

---

### ❌ Email Tidak Masuk / Masuk ke Spam

**Normal!** Ini sering terjadi untuk:
- SMTP baru
- Gmail personal (bukan Google Workspace)
- Email dengan format HTML yang kompleks

**Solusi:**
1. **Cek folder Spam** di email
2. Tandai email sebagai "Not Spam"
3. Add sender ke contact

**Untuk Production:**
- Gunakan domain email sendiri (bukan Gmail)
- Setup SPF, DKIM, DMARC records
- Atau gunakan service seperti SendGrid/Mailgun

---

### ❌ Card OTP Tidak Muncul

**Cek:**
1. Buka browser console (F12)
2. Lihat ada error JavaScript?
3. Cek file `assets/js/auth.js` sudah terupdate?
4. Hard refresh browser (Ctrl+Shift+R)
5. Clear browser cache

---

### ❌ OTP Expired

Kode OTP berlaku 24 jam. Jika expired:
1. Register ulang dengan email yang sama
2. Atau hapus entry lama di database:
   ```sql
   DELETE FROM tbl_pengelola_dapur WHERE email = 'test@email.com' AND is_verified = 0;
   ```

---

## 📊 Checklist Final

Sebelum production, pastikan:

- [ ] **PHPMailer terinstall** di `includes/PHPMailer/`
- [ ] **Email config** sudah diisi dengan App Password yang benar
- [ ] **Test email berhasil** via `test_email.php`
- [ ] **Registrasi OTP berfungsi** di localhost
- [ ] **Card OTP muncul** dengan 6 kotak input
- [ ] **Auto-focus & auto-submit** bekerja
- [ ] **Email received** dalam 1-2 menit
- [ ] **Verifikasi berhasil** dan bisa login
- [ ] **Upload ke Wasmer** semua file yang diupdate
- [ ] **Test di production** Wasmer
- [ ] **Config file** tidak di-commit ke Git

---

## 💡 Tips & Best Practices

### Gmail Limitations:
- ❗ Max **500 email/hari** untuk free account
- ❗ Max **100 recipients** per email
- ❗ Untuk volume besar, pakai SendGrid/Mailgun

### Alternative SMTP Providers:

**Free Tier:**
- **SendGrid**: 100 email/day free forever
- **Mailgun**: 100 email/day free (first 3 months)
- **Mailtrap**: Unlimited fake emails (testing only)

**Paid (Recommended for Production):**
- **SendGrid**: $15/month (dari 40k email)
- **Mailgun**: Pay as you go ($0.80 per 1000)
- **Amazon SES**: $0.10 per 1000 emails

### Security:
- ✅ Jangan commit `email_config.php` ke Git
- ✅ Gunakan environment variables untuk production
- ✅ Rotate App Password secara berkala

---

## 🎉 Selesai!

Jika semua langkah diikuti dengan benar:
- ✅ OTP terkirim ke email
- ✅ Card OTP muncul dengan cantik
- ✅ User bisa verify dan login
- ✅ System ready untuk production!

**Butuh bantuan?** 
- Cek error log di `C:\xampp\php\logs\php_error_log`
- Test dengan `test_email.php`
- Lihat console browser (F12) untuk JavaScript errors

**Sukses! 🚀**
