<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$product = App\Models\Product::has('mappedCheckpoints')->first();
if ($product) {
    echo "Product ID: " . $product->id . "\n";
    $detailData = [
        'master_checksheet_status' => 'WAITING_APPROVAL',
        'checksheet_approved_by' => null,
        'checksheet_approved_at' => null,
    ];
    $d = App\Models\NpcProductDetail::updateOrCreate(
        ['product_id' => $product->id],
        $detailData
    );
    echo "Status: " . $d->master_checksheet_status . "\n";
    
    // verify from DB
    $fresh = App\Models\NpcProductDetail::where('product_id', $product->id)->first();
    echo "Fresh Status: " . $fresh->master_checksheet_status . "\n";
} else {
    echo "No product found\n";
}
