<?php
// Script to create placeholder brand logos
$brands = [
    'apple' => '#999999',
    'samsung' => '#1428a0',
    'nike' => '#111111',
    'adidas' => '#000000',
    'sony' => '#000000',
    'lg' => '#a50034',
    'dell' => '#007db8',
    'hp' => '#0096d6'
];

// Create brands directory if it doesn't exist
if (!file_exists('assets/images/brands')) {
    mkdir('assets/images/brands', 0777, true);
}

// Create brand pattern SVG
$patternSvg = <<<SVG
<svg width="100" height="100" xmlns="http://www.w3.org/2000/svg">
  <pattern id="pattern" width="10" height="10" patternUnits="userSpaceOnUse">
    <circle cx="2" cy="2" r="1" fill="#3498db" opacity="0.3" />
  </pattern>
  <rect width="100" height="100" fill="url(#pattern)" />
</svg>
SVG;

file_put_contents('assets/images/patterns/brand-pattern.svg', $patternSvg);
echo "Created brand pattern SVG\n";

// Generate placeholder images for each brand
foreach ($brands as $brand => $color) {
    // Create a 300x300 image with brand color
    $image = imagecreatetruecolor(300, 300);
    
    // Make the background white
    $white = imagecolorallocate($image, 255, 255, 255);
    imagefill($image, 0, 0, $white);
    
    // Convert hex color to RGB
    list($r, $g, $b) = sscanf($color, "#%02x%02x%02x");
    $brandColor = imagecolorallocate($image, $r, $g, $b);
    
    // Draw brand initial as text
    $initial = strtoupper($brand[0]);
    $font = 5; // Built-in font
    $text_width = imagefontwidth($font) * strlen($initial);
    $text_height = imagefontheight($font);
    
    $x = (300 - $text_width) / 2;
    $y = (300 - $text_height) / 2;
    
    // Draw a filled circle with brand color
    imagefilledellipse($image, 150, 150, 200, 200, $brandColor);
    
    // Write the initial in white
    $white = imagecolorallocate($image, 255, 255, 255);
    imagestring($image, $font, $x, $y, $initial, $white);
    
    // Save the image
    $filename = "assets/images/brands/{$brand}.png";
    imagepng($image, $filename);
    imagedestroy($image);
    
    echo "Created {$filename}\n";
}

echo "All placeholder images created successfully!\n";
?> 