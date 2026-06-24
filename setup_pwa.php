<?php
// 1. Create Icons Directory
if (!is_dir('icons')) {
    mkdir('icons', 0777, true);
}

// Function to generate a simple colored square icon with text
function generateIcon($size, $filename) {
    $image = imagecreatetruecolor($size, $size);
    $bg_color = imagecolorallocate($image, 30, 64, 175); // Tailwind blue-800
    $text_color = imagecolorallocate($image, 255, 255, 255); // White
    
    // Fill background
    imagefill($image, 0, 0, $bg_color);
    
    // Add simple text (Using built-in font)
    $text = "PTL";
    $font_size = $size > 200 ? 5 : 4; 
    $font_width = imagefontwidth($font_size);
    $font_height = imagefontheight($font_size);
    $text_x = ($size - ($font_width * strlen($text))) / 2;
    $text_y = ($size - $font_height) / 2;
    
    imagestring($image, $font_size, $text_x, $text_y, $text, $text_color);
    
    // Save image
    imagepng($image, $filename);
    imagedestroy($image);
}

// Generate icons
if (!file_exists('icons/icon-192.png')) generateIcon(192, 'icons/icon-192.png');
if (!file_exists('icons/icon-512.png')) generateIcon(512, 'icons/icon-512.png');

echo "Icons generated.\n";

// 2. Inject PWA code into <head> of PHP files
$pwa_code = <<<EOD
    <!-- PWA Meta Tags -->
    <link rel="manifest" href="manifest.json">
    <meta name="theme-color" content="#1e40af">
    <link rel="apple-touch-icon" href="icons/icon-192.png">
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('sw.js');
            });
        }
    </script>
</head>
EOD;

$files = [
    'index.php',
    'dashboard.php',
    'add_location.php',
    'add_target.php',
    'manage_users.php',
    'my_reports.php',
    'user_form.php',
    'login.php'
];

foreach ($files as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        
        // Check if already injected
        if (strpos($content, 'manifest.json') === false) {
            $new_content = str_replace('</head>', $pwa_code, $content);
            file_put_contents($file, $new_content);
            echo "Updated $file\n";
        } else {
            echo "Already updated: $file\n";
        }
    }
}
echo "PWA setup complete.\n";
?>
