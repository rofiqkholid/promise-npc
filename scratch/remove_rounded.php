<?php

$dir = __DIR__ . '/../resources/views';

$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
$count = 0;

foreach ($iterator as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $content = file_get_contents($file->getPathname());
        
        // Regex to match tailwind rounded classes. 
        // Handles: rounded, rounded-md, md:rounded-lg, hover:rounded-full, !rounded-sm, etc.
        // It matches boundaries so it doesn't match a word inside a text like "grounded".
        $pattern = '/(?<![a-zA-Z0-9\-_])!?([a-zA-Z0-9\-]+:)*rounded(?:-[a-zA-Z0-9\-]+)?(?![a-zA-Z0-9\-_])/';
        
        $newContent = preg_replace($pattern, '', $content);
        
        // Clean up double spaces that might be left behind inside class attributes
        // But doing it globally might mess up pre/code blocks, so let's just do it simple:
        // class="  foo  bar " -> class="foo bar"
        $newContent = preg_replace_callback('/class\s*=\s*(["\'])(.*?)\1/s', function($m) {
            $cleaned = preg_replace('/\s+/', ' ', $m[2]);
            $cleaned = trim($cleaned);
            return 'class=' . $m[1] . $cleaned . $m[1];
        }, $newContent);

        if ($content !== $newContent) {
            file_put_contents($file->getPathname(), $newContent);
            $count++;
            echo "Updated: " . $file->getFilename() . "\n";
        }
    }
}

echo "\nTotal files updated: $count\n";
