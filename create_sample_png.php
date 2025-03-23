<?php
// Simple script to create a PNG file using GD library
header('Content-Type: image/png');

// Create image
$width = 800;
$height = 800;
$image = imagecreatetruecolor($width, $height);

// Colors
$blue = imagecolorallocate($image, 52, 152, 219); // #3498db
$white = imagecolorallocate($image, 255, 255, 255);
$black = imagecolorallocate($image, 0, 0, 0);

// Fill background
imagefill($image, 0, 0, $blue);

// Add pattern (checkerboard)
for ($i = 0; $i < $width; $i += 20) {
    for ($j = 0; $j < $height; $j += 20) {
        if (($i + $j) % 40 == 0) {
            imagefilledrectangle($image, $i, $j, $i + 10, $j + 10, $white);
        }
    }
}

// Add text
$text = "PRODUCT";
$font = 5; // Built-in font
$textWidth = imagefontwidth($font) * strlen($text);
$textX = ($width - $textWidth) / 2;
$textY = $height / 2;
imagestring($image, $font, $textX, $textY, $text, $white);

// Add a curved line
imagearc($image, $width/2, $height/2 - 100, 200, 100, 0, 360, $white);

// Save as PNG
$filename = 'assets/images/products/sample.png';
imagepng($image, $filename);
imagedestroy($image);

echo "Created {$filename}";
?> 