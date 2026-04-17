<?php
/**
 * Script to add read-only restrictions for super_admin on hardware resources.
 * Adds canCreate, canEdit, canDelete, canDeleteAny methods and Model import.
 */

$resourceDir = __DIR__ . '/app/Filament/Resources';

$hardwareFiles = [
    'MonitorResource.php',
    'MotherboardResource.php',
    'ProcessorResource.php',
    'RAMResource.php',
    'VGAResource.php',
    'PenyimpananResource.php',
    'DVDResource.php',
    'PSUResource.php',
    'KeyboardResource.php',
    'MouseResource.php',
    'HeadphoneResource.php',
    'BarangMasukResource.php',
    'BarangKeluarResource.php',
];

$methodsToAdd = <<<'PHP'

    public static function canCreate(): bool
    {
        return !auth()->user()->hasRole('super_admin');
    }

    public static function canEdit(Model $record): bool
    {
        return !auth()->user()->hasRole('super_admin');
    }

    public static function canDelete(Model $record): bool
    {
        return !auth()->user()->hasRole('super_admin');
    }

    public static function canDeleteAny(): bool
    {
        return !auth()->user()->hasRole('super_admin');
    }
PHP;

$count = 0;
foreach ($hardwareFiles as $file) {
    $path = $resourceDir . '/' . $file;
    if (!file_exists($path)) {
        echo "SKIP (not found): $file\n";
        continue;
    }

    $content = file_get_contents($path);

    // Skip if already has canCreate
    if (str_contains($content, 'canCreate')) {
        echo "SKIP (already patched): $file\n";
        continue;
    }

    // Add Model import if not present
    if (!str_contains($content, 'use Illuminate\Database\Eloquent\Model;')) {
        // Add after the last 'use' statement before the class definition
        $content = preg_replace(
            '/(use Illuminate\\\\Database\\\\Eloquent\\\\[^;]+;)\n/',
            "$1\nuse Illuminate\\Database\\Eloquent\\Model;\n",
            $content,
            1
        );
    }

    // Find the class opening and the first property/method, add our methods after opening brace
    // Pattern: class XXX extends Resource\n{\n    protected static ...
    $content = preg_replace(
        '/(class \w+ extends Resource\s*\{)\s*\n(\s*protected static)/',
        "$1" . $methodsToAdd . "\n\n\$2",
        $content,
        1
    );

    file_put_contents($path, $content);
    echo "PATCHED: $file\n";
    $count++;
}

echo "\nDone! Patched $count files.\n";
