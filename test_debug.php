<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Http\Kernel')->handle(
    Illuminate\Http\Request::capture()
);

echo "Debugging Supabase Configuration...\n\n";

// Mostrar configuração
$config = config('filesystems.disks.supabase');
echo "Supabase Disk Config:\n";
echo "Driver: " . $config['driver'] . "\n";
echo "Key: " . substr($config['key'], 0, 20) . "...\n";
echo "Secret: " . substr($config['secret'], 0, 20) . "...\n";
echo "Region: " . $config['region'] . "\n";
echo "Bucket: " . $config['bucket'] . "\n";
echo "Endpoint: " . $config['endpoint'] . "\n";
echo "Use Path Style: " . var_export($config['use_path_style_endpoint'], true) . "\n\n";

// Testar cliente S3
echo "Testing S3 Client...\n";
try {
    $disk = Storage::disk('supabase');
    $client = $disk->getClient();
    echo "Client created successfully\n\n";
    
    // Listar buckets
    echo "Listing buckets...\n";
    $buckets = $client->listBuckets();
    echo "Buckets found:\n";
    foreach ($buckets['Buckets'] as $bucket) {
        echo "- " . $bucket['Name'] . "\n";
    }
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
