<?php
echo 'PHP version: ' . phpversion() . '<br>';
echo 'Sodium loaded: ' . (extension_loaded('sodium') ? 'YES' : 'NO') . '<br>';
echo 'OpenSSL loaded: ' . (extension_loaded('openssl') ? 'YES' : 'NO') . '<br>';

$hash = password_hash('password', PASSWORD_BCRYPT);
echo 'Generated hash: ' . $hash . '<br>';
echo 'Verify result: ' . (password_verify('password', $hash) ? 'MATCH' : 'NO MATCH') . '<br>';
?>