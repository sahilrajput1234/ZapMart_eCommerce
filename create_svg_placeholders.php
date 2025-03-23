<?php
// Script to create placeholder brand logos as SVG files
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
    echo "Created assets/images/brands directory\n";
}

// Create patterns directory if it doesn't exist
if (!file_exists('assets/images/patterns')) {
    mkdir('assets/images/patterns', 0777, true);
    echo "Created assets/images/patterns directory\n";
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
echo "Created brand pattern SVG at assets/images/patterns/brand-pattern.svg\n";

// Generate placeholder SVG images for each brand
foreach ($brands as $brand => $color) {
    $initial = strtoupper($brand[0]);
    
    $svg = <<<SVG
<svg width="300" height="300" xmlns="http://www.w3.org/2000/svg">
  <rect width="300" height="300" fill="white"/>
  <circle cx="150" cy="150" r="100" fill="{$color}"/>
  <text x="150" y="180" font-family="Arial" font-size="100" text-anchor="middle" fill="white">{$initial}</text>
  <text x="150" y="240" font-family="Arial" font-size="30" text-anchor="middle" fill="{$color}">{$brand}</text>
</svg>
SVG;
    
    // Save the SVG
    $filename = "assets/images/brands/{$brand}.svg";
    file_put_contents($filename, $svg);
    
    echo "Created {$filename}\n";
}

// Also create PNG files with similar names to ensure compatibility with database references
foreach ($brands as $brand => $color) {
    $png_filename = "assets/images/brands/{$brand}.png";
    // Since we can't convert SVG to PNG without GD, we just create a simple HTML file that embeds the SVG
    // and instruct the user to open it in a browser and save it as PNG
    $html = <<<HTML
<!DOCTYPE html>
<html>
<head>
  <title>{$brand} Logo</title>
  <style>
    body { margin: 0; padding: 0; display: flex; justify-content: center; align-items: center; height: 100vh; }
    img { max-width: 300px; max-height: 300px; }
  </style>
</head>
<body>
  <img src="{$brand}.svg" alt="{$brand} Logo">
</body>
</html>
HTML;
    
    file_put_contents("assets/images/brands/{$brand}.html", $html);
    echo "Created {$brand}.html (to view SVG in browser)\n";
    
    // Create a dummy PNG file with minimal content as a placeholder
    file_put_contents($png_filename, "PNG placeholder for {$brand}");
    echo "Created placeholder {$png_filename}\n";
}

echo "\nAll placeholder images created successfully!\n";
echo "\nNOTE: The PNG files created are just placeholders. To create proper PNG files:\n";
echo "1. Open the HTML files in your browser\n";
echo "2. Right-click on each image and select 'Save Image As...'\n";
echo "3. Save with the same filename but .png extension\n";
?> 