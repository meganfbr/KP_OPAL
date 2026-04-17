<?php
/**
 * Script to add LogsActivity to hardware models.
 */

$modelDir = __DIR__ . '/app/Models';

$models = [
    'Monitor.php' => 'monitor',
    'Motherboard.php' => 'hardware',
    'Processor.php' => 'hardware',
    'RAM.php' => 'hardware',
    'VGA.php' => 'hardware',
    'Penyimpanan.php' => 'hardware',
    'DVD.php' => 'hardware',
    'PSU.php' => 'hardware',
    'Keyboard.php' => 'hardware',
    'Mouse.php' => 'hardware',
    'Headphone.php' => 'hardware',
];

$count = 0;
foreach ($models as $file => $logName) {
    $path = $modelDir . '/' . $file;
    if (!file_exists($path)) {
        echo "SKIP (not found): $file\n";
        continue;
    }

    $content = file_get_contents($path);

    // Skip if already has LogsActivity
    if (str_contains($content, 'LogsActivity')) {
        echo "SKIP (already patched): $file\n";
        continue;
    }

    // Get model class name from file
    $className = str_replace('.php', '', $file);

    // Add imports before class declaration
    $content = str_replace(
        "use Illuminate\\Database\\Eloquent\\Model;\n",
        "use Illuminate\\Database\\Eloquent\\Model;\nuse Spatie\\Activitylog\\Traits\\LogsActivity;\nuse Spatie\\Activitylog\\LogOptions;\n",
        $content
    );

    // Add trait usage - find 'protected $fillable' and add trait + method before it
    $traitAndMethod = <<<PHP
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn(string \$eventName) => "{$className} telah di-{\$eventName}")
            ->useLogName('{$logName}');
    }

    
PHP;

    // Insert right after the class opening brace, before existing content
    $content = preg_replace(
        '/(class \w+ extends Model\s*\{)\s*\n/',
        "$1\n" . $traitAndMethod,
        $content,
        1
    );

    file_put_contents($path, $content);
    echo "PATCHED: $file\n";
    $count++;
}

echo "\nDone! Patched $count model files.\n";
