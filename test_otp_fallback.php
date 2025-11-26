<?php
// test_otp_fallback.php
require_once 'includes/EmailService.php';

echo "<h2>Testing EmailService Fallback</h2>";

// Mock data
$to = "test@example.com";
$name = "Test User";
$otp = "123456";

echo "<p>Attempting to send OTP to $to...</p>";

// Call the function
$result = EmailService::sendOTPEmail($to, $name, $otp);

echo "<h3>Result:</h3>";
echo "<pre>";
print_r($result);
echo "</pre>";

if (isset($result['debug_otp']) && $result['debug_otp'] === $otp) {
    echo "<h3 style='color: green'>PASS: OTP code returned in debug_otp</h3>";
} else {
    echo "<h3 style='color: red'>FAIL: OTP code NOT returned in debug_otp</h3>";
}
?>
