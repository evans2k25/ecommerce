<?php
// Write a tiny transparent PNG as placeholder logo to assets/images/logo.png
$dir = __DIR__ . '/../assets/images';
if (!is_dir($dir)) {
    mkdir($dir, 0755, true);
}
$path = $dir . '/logo.png';
// 1x1 transparent PNG
$base64 = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR4nGNgYAAAAAMAASsJTYQAAAAASUVORK5CYII=';
file_put_contents($path, base64_decode($base64));
echo "Wrote placeholder logo to: $path\n";
