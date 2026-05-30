<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;

echo "=== Railway DB Check ===\n";

$users = DB::table('users')->get(['id', 'name', 'username', 'security_question_id', 'security_question_answer']);
echo "Users found: " . count($users) . "\n";

foreach ($users as $u) {
    echo "\n--- User: {$u->username} ({$u->name}) ---\n";
    echo "ID: {$u->id}\n";
    echo "security_question_id: {$u->security_question_id}\n";
    echo "raw answer length: " . strlen($u->security_question_answer ?? '') . "\n";
    echo "raw answer (first 20): " . substr($u->security_question_answer ?? '', 0, 20) . "\n";

    // Try to decrypt
    try {
        $decrypted = Crypt::decryptString($u->security_question_answer);
        echo "Decrypted: {$decrypted}\n";
    } catch (\Exception $e) {
        echo "Decrypt FAILED: " . $e->getMessage() . "\n";
        echo "Raw value (length=" . strlen($u->security_question_answer) . "): " . $u->security_question_answer . "\n";
    }
}

echo "\n=== APP_KEY ===\n";
echo env('APP_KEY') . "\n";