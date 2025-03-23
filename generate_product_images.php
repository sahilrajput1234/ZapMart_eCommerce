<?php
// Script to generate product images and logos for the new arrivals page

// Create products directory if it doesn't exist
if (!file_exists('assets/images/products')) {
    mkdir('assets/images/products', 0777, true);
    echo "Created products directory.\n";
}

// Product image data
$products = [
    [
        'name' => 'smartphone',
        'category' => 'electronics',
        'color' => '#3498db',
        'icon' => '📱'
    ],
    [
        'name' => 'smartwatch',
        'category' => 'electronics',
        'color' => '#2ecc71',
        'icon' => '⌚'
    ],
    [
        'name' => 'laptop',
        'category' => 'electronics',
        'color' => '#9b59b6',
        'icon' => '💻'
    ],
    [
        'name' => 'headphones',
        'category' => 'electronics',
        'color' => '#e74c3c',
        'icon' => '🎧'
    ],
    [
        'name' => 'camera',
        'category' => 'electronics',
        'color' => '#f39c12',
        'icon' => '📷'
    ],
    [
        'name' => 'speaker',
        'category' => 'electronics',
        'color' => '#1abc9c',
        'icon' => '🔊'
    ],
    [
        'name' => 'tablet',
        'category' => 'electronics',
        'color' => '#34495e',
        'icon' => '📱'
    ],
    [
        'name' => 'monitor',
        'category' => 'electronics',
        'color' => '#e67e22',
        'icon' => '🖥️'
    ],
    [
        'name' => 'keyboard',
        'category' => 'electronics',
        'color' => '#7f8c8d',
        'icon' => '⌨️'
    ],
    [
        'name' => 'mouse',
        'category' => 'electronics',
        'color' => '#16a085',
        'icon' => '🖱️'
    ],
    [
        'name' => 'gaming-console',
        'category' => 'electronics',
        'color' => '#2c3e50',
        'icon' => '🎮'
    ],
    [
        'name' => 'wireless-earbuds',
        'category' => 'electronics',
        'color' => '#8e44ad',
        'icon' => '🎧'
    ]
];

// Function to create SVG placeholders
function createSvgPlaceholder($product, $width = 800, $height = 800) {
    $name = $product['name'];
    $color = $product['color'];
    $icon = $product['icon'];
    $category = $product['category'];
    
    $svg = <<<SVG
<svg width="$width" height="$height" xmlns="http://www.w3.org/2000/svg">
    <rect width="100%" height="100%" fill="$color" />
    <rect width="100%" height="100%" fill="url(#pattern)" opacity="0.1" />
    <text x="50%" y="50%" font-family="Arial, sans-serif" font-size="120" text-anchor="middle" dominant-baseline="middle" fill="white">$icon</text>
    <text x="50%" y="65%" font-family="Arial, sans-serif" font-size="40" text-anchor="middle" fill="white">$name</text>
    <text x="50%" y="75%" font-family="Arial, sans-serif" font-size="30" text-anchor="middle" fill="white">$category</text>
    <defs>
        <pattern id="pattern" patternUnits="userSpaceOnUse" width="60" height="60" patternTransform="rotate(45)">
            <rect width="30" height="30" fill="white" fill-opacity="0.2" />
        </pattern>
    </defs>
</svg>
SVG;

    return $svg;
}

// Generate and save product images
foreach ($products as $product) {
    $svg = createSvgPlaceholder($product);
    $filename = 'assets/images/products/' . $product['name'] . '.svg';
    file_put_contents($filename, $svg);
    echo "Created {$filename}\n";
    
    // Create HTML file that displays SVG (for browsers to easily save as PNG)
    $html = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <title>{$product['name']} Image</title>
    <style>
        body { margin: 0; padding: 0; display: flex; justify-content: center; align-items: center; height: 100vh; }
        img { max-width: 800px; max-height: 800px; }
    </style>
</head>
<body>
    <img src="{$product['name']}.svg" alt="{$product['name']}" id="productImage">
    <script>
        // Auto download as PNG
        const canvas = document.createElement('canvas');
        const img = document.getElementById('productImage');
        img.onload = function() {
            canvas.width = img.width;
            canvas.height = img.height;
            const ctx = canvas.getContext('2d');
            ctx.drawImage(img, 0, 0);
            
            // Convert canvas to PNG
            const pngUrl = canvas.toDataURL('image/png');
            
            // Create a link to download
            const a = document.createElement('a');
            a.download = '{$product['name']}.png';
            a.href = pngUrl;
            a.click();
        };
    </script>
</body>
</html>
HTML;
    
    $htmlFilename = 'assets/images/products/' . $product['name'] . '.html';
    file_put_contents($htmlFilename, $html);
    echo "Created {$htmlFilename} for PNG conversion\n";
}

// Create PNG versions manually if SVG doesn't automatically convert
echo "\nDone! Created SVG images for products.\n";
echo "You may need to manually save the PNG versions by opening the HTML files in your browser.\n";
echo "Or use the HTML files to save them as PNGs if your server doesn't have GD library for PHP.\n"; 