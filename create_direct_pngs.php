<?php
// Script to create PNG images directly without browser interaction
// Requires GD library to be enabled in PHP

// Check if GD library is available
if (!extension_loaded('gd')) {
    die("GD library is not available. Please use the HTML method instead.\n");
}

// Product image data (same as in generate_product_images.php)
$products = [
    [
        'name' => 'smartphone',
        'category' => 'electronics',
        'color' => [52, 152, 219], // RGB for #3498db
        'icon' => 'Smartphone'
    ],
    [
        'name' => 'smartwatch',
        'category' => 'electronics',
        'color' => [46, 204, 113], // RGB for #2ecc71
        'icon' => 'Smartwatch'
    ],
    [
        'name' => 'laptop',
        'category' => 'electronics',
        'color' => [155, 89, 182], // RGB for #9b59b6
        'icon' => 'Laptop'
    ],
    [
        'name' => 'headphones',
        'category' => 'electronics',
        'color' => [231, 76, 60], // RGB for #e74c3c
        'icon' => 'Headphones'
    ],
    [
        'name' => 'camera',
        'category' => 'electronics',
        'color' => [243, 156, 18], // RGB for #f39c12
        'icon' => 'Camera'
    ],
    [
        'name' => 'speaker',
        'category' => 'electronics',
        'color' => [26, 188, 156], // RGB for #1abc9c
        'icon' => 'Speaker'
    ],
    [
        'name' => 'tablet',
        'category' => 'electronics',
        'color' => [52, 73, 94], // RGB for #34495e
        'icon' => 'Tablet'
    ],
    [
        'name' => 'monitor',
        'category' => 'electronics',
        'color' => [230, 126, 34], // RGB for #e67e22
        'icon' => 'Monitor'
    ],
    [
        'name' => 'keyboard',
        'category' => 'electronics',
        'color' => [127, 140, 141], // RGB for #7f8c8d
        'icon' => 'Keyboard'
    ],
    [
        'name' => 'mouse',
        'category' => 'electronics',
        'color' => [22, 160, 133], // RGB for #16a085
        'icon' => 'Mouse'
    ],
    [
        'name' => 'gaming-console',
        'category' => 'electronics', 
        'color' => [44, 62, 80], // RGB for #2c3e50
        'icon' => 'Console'
    ],
    [
        'name' => 'wireless-earbuds',
        'category' => 'electronics',
        'color' => [142, 68, 173], // RGB for #8e44ad
        'icon' => 'Earbuds'
    ]
];

// Function to create a brand logo pattern
function createBrandPattern($width, $height) {
    $pattern = imagecreatetruecolor($width, $height);
    $background = imagecolorallocate($pattern, 255, 255, 255);
    $foreground = imagecolorallocate($pattern, 200, 200, 200);
    
    // Fill background
    imagefill($pattern, 0, 0, $background);
    
    // Draw pattern
    for ($i = 0; $i < $width; $i += 20) {
        for ($j = 0; $j < $height; $j += 20) {
            imagefilledrectangle($pattern, $i, $j, $i + 10, $j + 10, $foreground);
        }
    }
    
    return $pattern;
}

// Function to create product image
function createProductImage($product, $width = 800, $height = 800) {
    // Create image
    $image = imagecreatetruecolor($width, $height);
    
    // Colors
    $bgColor = imagecolorallocate($image, $product['color'][0], $product['color'][1], $product['color'][2]);
    $white = imagecolorallocate($image, 255, 255, 255);
    
    // Fill background
    imagefill($image, 0, 0, $bgColor);
    
    // Add pattern
    $pattern = createBrandPattern(100, 100);
    imagecopyresampled($image, $pattern, 0, 0, 0, 0, $width, $height, 100, 100);
    
    // Add text
    $font = 5; // Built-in font
    
    // Calculate positions
    $productName = strtoupper($product['name']);
    $categoryName = strtoupper($product['category']);
    
    // Center text
    $textWidth = imagefontwidth($font) * strlen($productName);
    $textX = ($width - $textWidth) / 2;
    
    // Draw product name
    imagestring($image, $font, $textX, $height / 2, $productName, $white);
    
    // Draw category
    $catWidth = imagefontwidth($font) * strlen($categoryName);
    $catX = ($width - $catWidth) / 2;
    imagestring($image, $font, $catX, $height / 2 + 40, $categoryName, $white);
    
    // Draw icon/logo
    $brandText = $product['icon'];
    $brandWidth = imagefontwidth($font) * strlen($brandText);
    $brandX = ($width - $brandWidth) / 2;
    imagestring($image, 5, $brandX, $height / 2 - 100, $brandText, $white);
    
    // Draw border
    imagerectangle($image, 0, 0, $width - 1, $height - 1, $white);
    
    return $image;
}

// Create products directory if it doesn't exist
if (!file_exists('assets/images/products')) {
    mkdir('assets/images/products', 0777, true);
    echo "Created products directory.\n";
}

// Generate PNG files
foreach ($products as $product) {
    $image = createProductImage($product);
    $filename = 'assets/images/products/' . $product['name'] . '.png';
    
    // Save as PNG
    imagepng($image, $filename);
    imagedestroy($image);
    
    echo "Created {$filename}\n";
}

echo "\nDone! Created PNG images for products directly using GD library.\n"; 