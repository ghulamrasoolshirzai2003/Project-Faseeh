<?php
header('Content-Type: text/plain');
echo 'php_ini_loaded_file=' . php_ini_loaded_file() . "\n";
echo 'pdo_mysql=' . (extension_loaded('pdo_mysql') ? 'yes' : 'no') . "\n";
echo 'mysqli=' . (extension_loaded('mysqli') ? 'yes' : 'no') . "\n";
echo 'pdo=' . (extension_loaded('pdo') ? 'yes' : 'no') . "\n";
