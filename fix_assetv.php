<?php
$path = 'app/Providers/BladeServiceProvider.php';
$content = file_get_contents($path);
$count = 0;
$new = preg_replace_callback(
    "/Blade::directive\('assetV', function \(string \\\$expression\): string \{\s*\\\$expression = trim\(\\\$expression, \"\\(\\)'\\\"\"\);\s*return \"<\?php echo app\(.+?\); \?>\";\s*\}\);/s",
    function ($m) {
        return <<<'PHP'
Blade::directive('assetV', function (string $expression): string {
            $expression = trim($expression, "()'\" ");
            return "<?php echo e(app(\\App\\Support\\AssetVersion::class)->url('" . addslashes($expression) . "')); ?>";
        });
PHP;
    },
    $content,
    1,
    $count
);
if ($count > 0) {
    file_put_contents($path, $new);
    echo "OK: $count replacement(s)\n";
} else {
    echo "NOT_FOUND\n";
}
