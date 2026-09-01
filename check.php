<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

function scanDirectory($dir, &$files) {
    if (!is_dir($dir)) return;
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') {
            $files[] = $file->getPathname();
        }
    }
}

$files = [];
scanDirectory(__DIR__ . '/app/Http/Controllers', $files);
scanDirectory(__DIR__ . '/app/APIServices', $files);
scanDirectory(__DIR__ . '/app/Services', $files);

$errors = [];

foreach ($files as $file) {
    $content = file_get_contents($file);
    // Find namespace
    if (preg_match('/namespace\s+([^;]+);/', $content, $matches)) {
        $namespace = trim($matches[1]);
    } else {
        continue;
    }
    // Find class name
    if (preg_match('/class\s+([A-Za-z0-9_]+)/', $content, $matches)) {
        $class = trim($matches[1]);
    } else {
        continue;
    }

    $fullClass = $namespace . '\\' . $class;
    
    // Check if the file path matches the namespace and class
    $expectedPathStart = __DIR__ . '/app/';
    $expectedRelativePath = str_replace(['App\\', '\\'], ['', '/'], $fullClass) . '.php';
    if (!str_ends_with($file, $expectedRelativePath)) {
        $errors[] = "File path mismatch: $file does not match class $fullClass";
    }

    // Now let's try to load the class
    try {
        if (!class_exists($fullClass) && !interface_exists($fullClass) && !trait_exists($fullClass)) {
            $errors[] = "Class $fullClass cannot be loaded from $file";
            continue;
        }
    } catch (\Throwable $e) {
        $errors[] = "Error loading class $fullClass: " . $e->getMessage();
    }
}

// Now let's check for method calls.
// A common issue is ClassName::execute(...) where ClassName is imported via use, but might not exist or method doesn't exist.
foreach ($files as $file) {
    $content = file_get_contents($file);
    
    // get all imported classes
    $uses = [];
    if (preg_match_all('/use\s+([^;]+);/', $content, $matches)) {
        foreach ($matches[1] as $use) {
            $parts = explode(' as ', $use);
            $fqcn = trim($parts[0]);
            $alias = isset($parts[1]) ? trim($parts[1]) : basename(str_replace('\\', '/', $fqcn));
            $uses[$alias] = $fqcn;
        }
    }

    // check static method calls like SomeClass::method(
    if (preg_match_all('/([A-Za-z0-9_]+)::([A-Za-z0-9_]+)\(/', $content, $matches)) {
        foreach ($matches[1] as $index => $calledClass) {
            $method = $matches[2][$index];
            if ($calledClass === 'self' || $calledClass === 'static' || $calledClass === 'parent' || $calledClass === 'Route') continue;

            $fqcn = $uses[$calledClass] ?? null;
            if ($fqcn) {
                if (!class_exists($fqcn)) {
                    $errors[] = "In $file, class $fqcn (alias $calledClass) is called but does not exist.";
                } else {
                    if (!method_exists($fqcn, $method) && !method_exists($fqcn, '__callStatic')) {
                        $errors[] = "In $file, method $method does not exist on $fqcn.";
                    }
                }
            }
        }
    }
}

echo "Scan complete.\n";
if (empty($errors)) {
    echo "No errors found.\n";
} else {
    echo "Errors:\n";
    foreach (array_unique($errors) as $err) {
        echo "- $err\n";
    }
}

