<?php

$loadedConfig = php_ini_loaded_file() ?: 'tidak diketahui';

echo "php.ini: {$loadedConfig}".PHP_EOL;

if (! extension_loaded('imagick') || ! class_exists(Imagick::class)) {
    fwrite(STDERR, 'Imagick belum aktif.'.PHP_EOL);
    exit(1);
}

$formats = array_map('strtoupper', Imagick::queryFormats('HEI*'));
$supportedFormats = array_values(array_intersect(['HEIC', 'HEIF'], $formats));

if ($supportedFormats === []) {
    fwrite(STDERR, 'Imagick aktif, tetapi codec HEIC/HEIF belum tersedia.'.PHP_EOL);
    exit(2);
}

echo 'Format tersedia: '.implode(', ', $supportedFormats).PHP_EOL;
exit(0);
