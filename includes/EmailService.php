<?php
/**
 * Email Service Class
 * Wrapper untuk PHPMailer dengan konfigurasi dari email_config.php
 */

// Include PHPMailer - akan di-download manual atau via composer
// Untuk sementara, kita gunakan fungsi mail() dengan perbaikan
// User perlu download PHPMailer dari: https://github.com/PHPMailer/PHPMailer

class EmailService {
    private static $config;
    
    /**
     * Initialize configuration
     */
    private static function init() {
        if (self::$config === null) {
            self::$config = require __DIR__ . '/../config/email_config.php';
        }
    }
    
    /**
     * Kirim Email OTP Verifikasi
     * 
     * @param string $to Email tujuan
     * @param string $name Nama penerima
     * @param string $otp_code Kode OTP 6 digit
     * @return array ['success' => bool, 'message' => string]
     */
    public static function sendOTPEmail($to, $name, $otp_code) {
        self::init();
        
        try {
            // Cek jika PHPMailer tersedia
            if (file_exists(__DIR__ . '/PHPMailer/PHPMailer.php')) {
                return self::sendWithPHPMailer($to, $name, $otp_code, 'otp');
            } else {
                // Fallback ke mail() function dengan logging
                return self::sendWithMailFunction($to, $name, $otp_code, 'otp');
            }
        } catch (Exception $e) {
            error_log("EmailService Error: " . $e->getMessage());
            // Return false but include OTP for fallback display
            return [
                'success' => false, 
                'message' => 'Gagal mengirim email: ' . $e->getMessage(),
                'debug_otp' => $otp_code
            ];
        }
    }
    
    /**
     * Kirim Email Kredensial Karyawan
     * 
     * @param string $to Email tujuan
     * @param string $name Nama karyawan
     * @param string $username Username
     * @param string $password Password
     * @return array ['success' => bool, 'message' => string]
     */
    public static function sendPasswordEmail($to, $name, $username, $password) {
        self::init();
        
        if (file_exists(__DIR__ . '/PHPMailer/PHPMailer.php')) {
            return self::sendWithPHPMailer($to, $name, ['username' => $username, 'password' => $password], 'password');
        } else {
            return self::sendWithMailFunction($to, $name, ['username' => $username, 'password' => $password], 'password');
        }
    }
    
    /**
     * Kirim email menggunakan PHPMailer
     */
    private static function sendWithPHPMailer($to, $name, $data, $type) {
        try {
            require_once __DIR__ . '/PHPMailer/PHPMailer.php';
            require_once __DIR__ . '/PHPMailer/SMTP.php';
            require_once __DIR__ . '/PHPMailer/Exception.php';
            
            $mail = new PHPMailer\PHPMailer\PHPMailer(true);
            
            // Server settings
            $mail->isSMTP();
            $mail->Host       = self::$config['smtp_host'];
            $mail->SMTPAuth   = self::$config['smtp_auth'];
            $mail->Username   = self::$config['smtp_username'];
            $mail->Password   = self::$config['smtp_password'];
            $mail->SMTPSecure = self::$config['smtp_secure'];
            $mail->Port       = self::$config['smtp_port'];
            $mail->CharSet    = self::$config['charset'];
            
            // Recipients
            $mail->setFrom(self::$config['from_email'], self::$config['from_name']);
            $mail->addAddress($to, $name);
            
            // Content
            if ($type === 'otp') {
                $mail->Subject = 'Verifikasi Akun MBG System';
                $mail->Body    = self::getOTPEmailBody($name, $data);
            } else if ($type === 'password') {
                $mail->Subject = 'Kredensial Login MBG System';
                $mail->Body    = self::getPasswordEmailBody($name, $data['username'], $data['password']);
            }
            
            $mail->send();
            return ['success' => true, 'message' => 'Email berhasil dikirim'];
            
        } catch (Exception $e) {
            // Log error
            error_log("Email Error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Email gagal dikirim: ' . $e->getMessage()];
        }
    }
    
    /**
     * Fallback: Kirim email menggunakan mail() function
     */
    private static function sendWithMailFunction($to, $name, $data, $type) {
        try {
            // Cek apakah fungsi mail() tersedia/diaktifkan
            if (!function_exists('mail')) {
                return [
                    'success' => false, 
                    'message' => 'Fungsi mail() tidak tersedia di server ini.',
                    'debug_otp' => ($type === 'otp') ? $data : null
                ];
            }

            $headers = "From: " . self::$config['from_name'] . " <" . self::$config['from_email'] . ">\r\n";
            $headers .= "Reply-To: " . self::$config['from_email'] . "\r\n";
            $headers .= "Content-Type: text/plain; charset=" . self::$config['charset'] . "\r\n";
            
            if ($type === 'otp') {
                $subject = 'Verifikasi Akun MBG System';
                $message = self::getOTPEmailBody($name, $data);
            } else if ($type === 'password') {
                $subject = 'Kredensial Login MBG System';
                $message = self::getPasswordEmailBody($name, $data['username'], $data['password']);
            }
            
            // Gunakan @ untuk suppress warning, tapi cek return value
            $sent = @mail($to, $subject, $message, $headers);
            
            if ($sent) {
                return ['success' => true, 'message' => 'Email berhasil dikirim'];
            } else {
                $error = error_get_last();
                $errorMsg = $error ? $error['message'] : 'Unknown error';
                error_log("Mail function failed: " . $errorMsg);
                
                return [
                    'success' => false, 
                    'message' => 'Email gagal dikirim. Silakan hubungi administrator untuk setup SMTP.',
                    'debug_otp' => ($type === 'otp') ? $data : null
                ];
            }
            
        } catch (Exception $e) {
            error_log("Email Error: " . $e->getMessage());
            return [
                'success' => false, 
                'message' => 'Terjadi kesalahan saat mengirim email',
                'debug_otp' => ($type === 'otp') ? $data : null
            ];
        }
    }
    
    /**
     * Template body email OTP
     */
    private static function getOTPEmailBody($name, $otp_code) {
        return "Halo $name,\n\n"
            . "Terima kasih telah mendaftar di MBG System.\n\n"
            . "Kode verifikasi Anda adalah:\n\n"
            . "════════════════════\n"
            . "    $otp_code\n"
            . "════════════════════\n\n"
            . "Kode ini berlaku selama 24 jam.\n\n"
            . "Jika Anda tidak melakukan pendaftaran, abaikan email ini.\n\n"
            . "Salam,\n"
            . "Tim MBG System";
    }
    
    /**
     * Template body email kredensial
     */
    private static function getPasswordEmailBody($name, $username, $password) {
        return "Halo $name,\n\n"
            . "Akun Anda telah dibuat di MBG System.\n\n"
            . "Berikut adalah kredensial login Anda:\n\n"
            . "════════════════════\n"
            . "Username: $username\n"
            . "Password: $password\n"
            . "════════════════════\n\n"
            . "Silakan login menggunakan kredensial di atas.\n"
            . "Untuk keamanan, kami sarankan Anda mengganti password setelah login pertama kali.\n\n"
            . "Salam,\n"
            . "Tim MBG System";
    }
    
    /**
     * Test koneksi SMTP
     */
    public static function testConnection() {
        self::init();
        
        if (!file_exists(__DIR__ . '/PHPMailer/PHPMailer.php')) {
            return [
                'success' => false,
                'message' => 'PHPMailer belum terinstall. Silakan download dari https://github.com/PHPMailer/PHPMailer'
            ];
        }
        
        try {
            require_once __DIR__ . '/PHPMailer/PHPMailer.php';
            require_once __DIR__ . '/PHPMailer/SMTP.php';
            require_once __DIR__ . '/PHPMailer/Exception.php';
            
            $mail = new PHPMailer\PHPMailer\PHPMailer(true);
            $mail->isSMTP();
            $mail->Host       = self::$config['smtp_host'];
            $mail->SMTPAuth   = self::$config['smtp_auth'];
            $mail->Username   = self::$config['smtp_username'];
            $mail->Password   = self::$config['smtp_password'];
            $mail->SMTPSecure = self::$config['smtp_secure'];
            $mail->Port       = self::$config['smtp_port'];
            
            // Test connection (tidak mengirim email)
            $mail->SMTPDebug = 0;
            $mail->smtpConnect();
            $mail->smtpClose();
            
            return ['success' => true, 'message' => 'Koneksi SMTP berhasil!'];
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Koneksi gagal: ' . $e->getMessage()];
        }
    }
}
