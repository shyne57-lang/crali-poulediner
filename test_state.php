<?php
require_once 'config.php';
echo "STATE_DIR = " . STATE_DIR . "<br>";
echo "is_dir = " . (is_dir(STATE_DIR) ? 'YES' : 'NO') . "<br>";
echo "writable = " . (is_writable(STATE_DIR) ? 'YES' : 'NO') . "<br>";
$testFile = STATE_DIR . '/test.json';
echo file_put_contents($testFile, '{"test":1}') ? 'WRITE OK' : 'WRITE FAILED';