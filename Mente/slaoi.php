<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<strong>Debugging `slaoi.php`</strong><br>";

$hash = password_hash("123", PASSWORD_DEFAULT);
echo "Hash: " . $hash . "<br>";
echo "Verify result: " . (password_verify("123", $hash) ? "TRUE" : "FALSE");

// If you still get a blank page, uncomment the following line:
// phpinfo();
?>