<?php
require 'vendor/autoload.php';

// Bootstrap Laravel
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Http\Kernel')->handle(
    Illuminate\Http\Request::capture()
);

use Illuminate\Support\Facades\Storage;

echo "Testing Supabase Storage...\n\n";

try {
    echo "1. Testing put()...\n";
    $result = Storage::disk('supabase')->put('test.txt', 'Hello World', 'public');
    echo "Result: " . var_export($result, true) . "\n\n";
    
    echo "2. Getting URL...\n";
    $url = Storage::disk('supabase')->url('test.txt');
    echo "URL: " . $url . "\n\n";
    
    echo "3. Checking if file exists...\n";
    $exists = Storage::disk('supabase')->exists('test.txt');
    echo "Exists: " . var_export($exists, true) . "\n\n";
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
    echo "\nFull trace:\n";
    echo $e->getTraceAsString();
}
