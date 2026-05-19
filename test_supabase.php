<?php
require 'vendor/autoload.php';

// Bootstrap Laravel
$app = require 'bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Http\Kernel');
$kernel->handle(Illuminate\Http\Request::capture());

use App\Services\SupabaseStorageService;

echo "Testing Supabase Storage Service...\n\n";

try {
    $storage = $app->make(SupabaseStorageService::class);

    echo "1. Configuration:\n";
    echo "   URL: " . config('services.supabase.url') . "\n";
    echo "   Bucket: " . config('services.supabase.bucket') . "\n";
    echo "   Token: " . (config('services.supabase.anon_key') ? 'Set' : 'NOT SET') . "\n\n";

    echo "2. Creating test file...\n";
    $testFile = tmpfile();
    fwrite($testFile, 'Hello from Supabase Storage!');
    rewind($testFile);

    $metadata = stream_get_meta_data($testFile);
    echo "   Test file created at: " . $metadata['uri'] . "\n\n";

    echo "3. Testing upload...\n";
    $path = $storage->upload(
        new \Illuminate\Http\UploadedFile(
            $metadata['uri'],
            'test.txt',
            'text/plain',
            null,
            true
        ),
        'tests'
    );

    if ($path) {
        echo "   Upload successful!\n";
        echo "   File path: " . $path . "\n\n";

        echo "4. Getting public URL...\n";
        $url = $storage->getPublicUrl($path);
        echo "   URL: " . $url . "\n\n";

        echo "5. Testing delete...\n";
        $deleted = $storage->delete($path);
        echo "   Delete result: " . ($deleted ? 'Success' : 'Failed') . "\n";
    } else {
        echo "   Upload failed!\n";
    }

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
    echo "\nFull trace:\n";
    echo $e->getTraceAsString();
}
