<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== DEBUG AUTH SYSTEM ===\n\n";

// 1. Cek database connection
echo "1. Database Connection:\n";
try {
    DB::connection()->getPdo();
    echo "   ✓ Database connected: " . DB::connection()->getDatabaseName() . "\n\n";
} catch (\Exception $e) {
    echo "   ✗ Database error: " . $e->getMessage() . "\n\n";
    exit;
}

// 2. Cek users
echo "2. Users in Database:\n";
$users = \App\Models\User::all(['id', 'username', 'name', 'email', 'created_at']);
foreach ($users as $u) {
    echo "   ID: {$u->id}\n";
    echo "   Username: {$u->username}\n";
    echo "   Name: {$u->name}\n";
    echo "   Email: {$u->email}\n";
    echo "   Created: {$u->created_at}\n\n";
}

// 3. Cek roles
echo "3. Roles in Database:\n";
$roles = \Spatie\Permission\Models\Role::all(['id', 'name']);
foreach ($roles as $r) {
    echo "   ID: {$r->id}, Name: {$r->name}\n";
}
echo "\n";

// 4. Cek user roles
echo "4. User-Role Relationships:\n";
$usersWithRoles = \App\Models\User::with('roles')->get();
foreach ($usersWithRoles as $u) {
    echo "   User: {$u->username} -> Roles: " . $u->roles->pluck('name')->implode(', ') . "\n";
}
echo "\n";

// 5. Test password verification
echo "5. Password Verification Test:\n";
$adminUser = \App\Models\User::where('username', 'admin')->first();
if ($adminUser) {
    echo "   Admin user found\n";
    echo "   Testing password 'admin123': " . (\Illuminate\Support\Facades\Hash::check('admin123', $adminUser->password) ? '✓ VALID' : '✗ INVALID') . "\n";
} else {
    echo "   ✗ Admin user NOT found\n";
}

$keagamaanUser = \App\Models\User::where('username', 'keagamaan')->first();
if ($keagamaanUser) {
    echo "   Keagamaan user found\n";
    echo "   Testing password 'keagamaan123': " . (\Illuminate\Support\Facades\Hash::check('keagamaan123', $keagamaanUser->password) ? '✓ VALID' : '✗ INVALID') . "\n";
} else {
    echo "   ✗ Keagamaan user NOT found\n";
}
echo "\n";

// 6. Test authentication manually
echo "6. Manual Authentication Test:\n";
$credentials = [
    'username' => 'admin',
    'password' => 'admin123',
];

$testUser = \App\Models\User::where('username', $credentials['username'])->first();
if ($testUser && \Illuminate\Support\Facades\Hash::check($credentials['password'], $testUser->password)) {
    echo "   ✓ Manual authentication SUCCESS\n";
    echo "   User can login with: username={$credentials['username']}, password={$credentials['password']}\n";
} else {
    if (!$testUser) {
        echo "   ✗ User not found with username: {$credentials['username']}\n";
    } else {
        echo "   ✗ Password mismatch for user: {$credentials['username']}\n";
    }
}
echo "\n";

echo "=== DEBUG COMPLETE ===\n";
