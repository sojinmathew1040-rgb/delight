<?php
$filepath = 'C:\\Users\\sojin\\.gemini\\antigravity-ide\\brain\\a0f4f362-9bbe-41fd-a41c-ce55e20c57fe\\uploaded_media_1782240219420.img';
if (file_exists($filepath)) {
    $f = fopen($filepath, 'rb');
    $header = fread($f, 32);
    fclose($f);
    echo "Header (hex): " . bin2hex($header) . "\n";
    echo "Header (text): " . $header . "\n";
    echo "Size: " . filesize($filepath) . " bytes\n";
} else {
    echo "File not found\n";
}
