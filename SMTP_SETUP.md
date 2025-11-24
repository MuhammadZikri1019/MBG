# Setup Email SMTP - Instruksi Lengkap

## 📋 Panduan Setup Email OTP

Sistem MBG telah diupdate untuk menggunakan EmailService yang lebih reliable. Ikuti langkah-langkah berikut:

---

## 1️⃣ Download PHPMailer

### Opsi A: Download Manual (Recommended)

1. Kunjungi: https://github.com/PHPMailer/PHPMailer/releases/latest
2. Download file ZIP (contoh: `PHPMailer-6.9.1.zip`)
3. Extract file ZIP tersebut
4. Copy folder `src` dari dalam ZIP ke `c:\xampp\htdocs\mbg\includes\PHPMailer\`
5. Pastikan struktur foldernya seperti ini:
   ```
   mbg/
   ├── includes/
   │   ├── EmailService.php
   │   └── PHPMailer/
   │       ├── PHPMailer.php
   │       ├── SMTP.php
   │       ├── Exception.php
   │       └── ... (file lainnya)
   ```

### Opsi B: Menggunakan Composer (jika sudah install Composer)

```bash
cd c:\xampp\htdocs\mbg
composer require phpmailer/phpmailer
```

---

## 2️⃣ Konfigurasi SMTP

### Jika Menggunakan Gmail:

1. **Buat App Password Gmail:**
   - Login ke Google Account: https://myaccount.google.com/
   - Masuk ke **Security** > **2-Step Verification** (harus aktif dulu)
   - Scroll ke bawah, cari **App passwords**
   - Pilih app: "Mail"
   - Pilih device: "Windows Computer" atau "Other"
   - Copy 16-digit app password yang muncul

2. **Edit file `config/email_config.php`:**
   ```php
   'smtp_host' => 'smtp.gmail.com',
   'smtp_port' => 587,
   'smtp_secure' => 'tls',
   'smtp_username' => 'emailanda@gmail.com',  // Ganti dengan email Anda
   'smtp_password' => 'xxxx xxxx xxxx xxxx',  // Paste App Password
   'from_email' => 'emailanda@gmail.com',     // Sama dengan username
   'from_name' => 'MBG System',
   ```

### Jika Menggunakan Provider Lain:

#### Mailgun:
```php
'smtp_host' => 'smtp.mailgun.org',
'smtp_port' => 587,
'smtp_secure' => 'tls',
'smtp_username' => 'postmaster@YOUR_DOMAIN',
'smtp_password' => 'YOUR_MAILGUN_PASSWORD',
```

#### SendGrid:
```php
'smtp_host' => 'smtp.sendgrid.net',
'smtp_port' => 587,
'smtp_secure' => 'tls',
'smtp_username' => 'apikey',
'smtp_password' => 'YOUR_SENDGRID_API_KEY',
```

#### Hosting Email (cPanel):
```php
'smtp_host' => 'mail.yourdomain.com',
'smtp_port' => 587,  // atau 465 untuk SSL
'smtp_secure' => 'tls',  // atau 'ssl'
'smtp_username' => 'noreply@yourdomain.com',
'smtp_password' => 'YOUR_EMAIL_PASSWORD',
```

---

## 3️⃣ Testing Email

### Test 1: Script Test Sederhana

Buat file `test_email.php` di root folder:

```php
<?php
require_once 'includes/EmailService.php';

$result = EmailService::sendOTPEmail(
    'email_tujuan@gmail.com',  // Ganti dengan email test Anda
    'Test User',
    '123456'
);

if ($result['success']) {
    echo "✅ Email berhasil dikirim!";
} else {
    echo "❌ Error: " . $result['message'];
}
?>
```

Akses: `http://localhost/mbg/test_email.php`

### Test 2: Registrasi App

1. Buka: `http://localhost/mbg/login.php`
2. Klik **Sign Up**
3. Isi form registrasi dengan email valid Anda
4. Submit
5. Cek inbox email Anda (termasuk folder spam)
6. Masukkan kode OTP yang diterima

---

## 4️⃣ Upload ke Wasmer

### Persiapan:

1. **Pastikan semua file sudah ada:**
   - ✅ `config/email_config.php` dengan kredensial yang sudah diisi
   - ✅ `includes/EmailService.php`
   - ✅ `includes/PHPMailer/` folder dengan semua file PHPMailer
   - ✅ `login.php` yang sudah diupdate
   - ✅ `pengelola/karyawan.php` yang sudah diupdate

2. **Upload via FTP/cPanel:**
   - Upload semua file ke hosting Wasmer Anda
   - Pastikan file permissions correct (biasanya 644 untuk file, 755 untuk folder)

3. **Test di Wasmer:**
   - Akses website Anda di Wasmer
   - Coba registrasi user baru
   - Cek apakah email OTP terkirim

---

## 🔍 Troubleshooting

### Email tidak terkirim?

1. **Cek error log:**
   - Di localhost: cek `c:\xampp\php\logs\php_error_log`
   - Di Wasmer: cek error log di cPanel

2. **Masalah umum:**

   ❌ **"Connection refused"**
   - Port salah (coba ganti 587 dengan 465, atau sebaliknya)
   - Firewall blocking (matikan antivirus sementara untuk test)

   ❌ **"Authentication failed"**
   - Username/password salah
   - Untuk Gmail: pastikan menggunakan App Password, bukan password biasa
   - Pastikan 2-Step Verification aktif (untuk Gmail)

   ❌ **"Could not instantiate mail function"**
   - PHPMailer belum terinstall dengan benar
   - Cek path folder PHPMailer

   ❌ **Email masuk ke spam**
   - Normal untuk SMTP baru
   - Beritahu user untuk cek folder spam
   - Untuk production, gunakan domain email sendiri (bukan Gmail)

3. **Test koneksi SMTP:**
   ```php
   <?php
   require_once 'includes/EmailService.php';
   $result = EmailService::testConnection();
   echo $result['message'];
   ?>
   ```

---

## ⚠️ Catatan Penting

1. **App Password Gmail:**
   - HARUS mengaktifkan 2-Step Verification dulu
   - Gunakan App Password, BUKAN password Gmail biasa
   - App Password tidak ada spasi (16 karakter terus)

2. **Security:**
   - Jangan commit file `email_config.php` ke Git
   - Tambahkan ke `.gitignore`:
     ```
     config/email_config.php
     ```

3. **Gmail Limitations:**
   - Max 500 email per hari untuk free account
   - Max 100 recipients per email
   - Untuk production besar, gunakan SendGrid/Mailgun

4. **Alternative untuk Development:**
   - Jika hanya untuk testing, display OTP di screen (sudah ada fallback)
   - Atau gunakan [Mailtrap.io](https://mailtrap.io) untuk fake SMTP testing

---

## ✅ Checklist

Sebelum production, pastikan:

- [ ] PHPMailer sudah terinstall dengan benar
- [ ] `config/email_config.php` sudah diisi dengan kredensial yang benar
- [ ] Test email berhasil terkirim
- [ ] Email OTP registrasi berfungsi
- [ ] Email kredensial karyawan berfungsi
- [ ] Tidak ada error di error log
- [ ] File config tidak di-commit ke Git

---

**Need help?** Check error log atau test dengan script test di atas untuk detail error message.
