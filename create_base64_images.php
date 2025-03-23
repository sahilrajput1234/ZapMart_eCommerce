<?php
// Script to create base64 embedded images that can be saved as PNG

// Define colors for products
$colors = [
    'smartphone' => '#3498db',
    'smartwatch' => '#2ecc71',
    'laptop' => '#9b59b6',
    'headphones' => '#e74c3c',
    'camera' => '#f39c12',
    'speaker' => '#1abc9c',
    'tablet' => '#34495e',
    'monitor' => '#e67e22',
    'keyboard' => '#7f8c8d',
    'mouse' => '#16a085',
    'gaming-console' => '#2c3e50',
    'wireless-earbuds' => '#8e44ad'
];

// Create product directory if it doesn't exist
if (!file_exists('assets/images/products')) {
    mkdir('assets/images/products', 0777, true);
    echo "Created products directory.\n";
}

// Create HTML file with embedded data URI images
$html = '<!DOCTYPE html>
<html>
<head>
    <title>Product Images - Save these images</title>
    <style>
        body { 
            font-family: Arial, sans-serif;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            background: #f5f7fa;
        }
        h1 { 
            color: #2c3e50;
            text-align: center;
        }
        .instructions {
            background: #e74c3c;
            color: white;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-weight: bold;
        }
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }
        .product {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        }
        .product h2 {
            margin-top: 0;
            color: #2c3e50;
        }
        .product img {
            width: 100%;
            height: auto;
            border-radius: 5px;
            margin-bottom: 10px;
        }
        .save-btn {
            display: block;
            background: #3498db;
            color: white;
            padding: 10px;
            text-align: center;
            border-radius: 5px;
            text-decoration: none;
            margin-top: 10px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <h1>Product Images for New Arrivals</h1>
    
    <div class="instructions">
        To use these images: Right-click each image and select "Save Image As...".<br>
        Save them to the assets/images/products/ directory with the filename shown.
    </div>
    
    <div class="grid">';

foreach ($colors as $product => $color) {
    $html .= '
        <div class="product">
            <h2>' . ucfirst(str_replace('-', ' ', $product)) . '</h2>
            <img src="data:image/svg+xml;base64,' . base64_encode(createSvg($product, $color)) . '" alt="' . $product . '">
            <a href="#" class="save-btn" onclick="saveImage(this, \'' . $product . '.svg\')">Save as ' . $product . '.svg</a>
        </div>';
}

$html .= '
    </div>
    
    <script>
        function saveImage(link, filename) {
            const img = link.previousElementSibling;
            const a = document.createElement("a");
            a.href = img.src;
            a.download = filename;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            return false;
        }
    </script>
</body>
</html>';

// Create the HTML file
file_put_contents('product_images.html', $html);
echo "Created product_images.html\n";
echo "Please open this file in your browser and save the images.\n";

// Function to create SVG
function createSvg($product, $color) {
    $name = str_replace('-', ' ', $product);
    $svg = <<<SVG
<svg width="800" height="800" xmlns="http://www.w3.org/2000/svg">
    <rect width="100%" height="100%" fill="$color" />
    <rect width="100%" height="100%" fill="url(#pattern)" opacity="0.2" />
    <text x="50%" y="50%" font-family="Arial, sans-serif" font-size="60" text-anchor="middle" dominant-baseline="middle" fill="white">$name</text>
    <text x="50%" y="65%" font-family="Arial, sans-serif" font-size="40" text-anchor="middle" fill="white">PRODUCT</text>
    <defs>
        <pattern id="pattern" patternUnits="userSpaceOnUse" width="40" height="40" patternTransform="rotate(45)">
            <rect width="20" height="20" fill="white" fill-opacity="0.2" />
        </pattern>
    </defs>
</svg>
SVG;

    return $svg;
} 