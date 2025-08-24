<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Via\Via;

echo "=== PathsPHP Usage Examples ===\n\n";

echo "PathsPHP enforces strict hierarchical path structure:\n";
echo "- type.base_alias.assignment_alias (e.g., 'rel.data.logs')\n";
echo "- Assignments must be accessed through their base\n";
echo "- All paths are validated and normalized for cross-platform compatibility\n\n";

// Example 1: Basic Setup
echo "1. Basic Setup\n";
echo "--------------\n";

Via::setLocal('/Users/me/Projects/my-app');
Via::setHost('myapp.local.test');

echo "Local path set: " . Via::getLocal() . "\n";
echo "Host set: " . Via::getHost() . "\n\n";

// Example 2: Setting Bases
echo "2. Setting Base Paths\n";
echo "--------------------\n";

Via::setBase('data', 'data');
Via::setBase('src', 'src');
Via::setBase('assets', 'public/assets');

echo "Base paths configured:\n";
echo "- data: " . Via::p('rel.data') . "\n";
echo "- src: " . Via::p('rel.src') . "\n";
echo "- assets: " . Via::p('rel.assets') . "\n\n";

// Example 3: Multiple Bases at Once (with positional arrays)
echo "3. Setting Multiple Bases\n";
echo "------------------------\n";

Via::setBases([
    ['uploads', 'storage/uploads'],        // Positional format
    ['cache', 'storage/cache'],            // Positional format
    ['alias' => 'logs', 'path' => 'storage/logs']  // Associative format
]);

echo "Multiple bases set (using both positional and associative formats):\n";
echo "- uploads: " . Via::p('rel.uploads') . "\n";
echo "- cache: " . Via::p('rel.cache') . "\n";
echo "- logs: " . Via::p('rel.logs') . "\n\n";

// Example 4: Assigning Sub-paths to Bases
echo "4. Assigning Sub-paths to Bases\n";
echo "-------------------------------\n";

Via::assignToBase('user_uploads', 'users', 'uploads');
Via::assignToBase('temp_uploads', 'temp', 'uploads');
Via::assignToBase('components', 'components', 'src');
Via::assignToBase('models', 'models', 'src');

echo "Assignments to bases:\n";
echo "- user_uploads: " . Via::p('rel.uploads.user_uploads') . "\n";
echo "- temp_uploads: " . Via::p('rel.uploads.temp_uploads') . "\n";
echo "- components: " . Via::p('rel.src.components') . "\n";
echo "- models: " . Via::p('rel.src.models') . "\n\n";

// Example 5: Multiple Assignments at Once (with positional arrays)
echo "5. Multiple Assignments\n";
echo "----------------------\n";

Via::assignToBases([
    ['error_logs', 'errors', 'logs'],      // Positional format
    ['access_logs', 'access', 'logs'],     // Positional format
    ['alias' => 'images', 'path' => 'images', 'baseAlias' => 'assets'], // Associative format
    ['css', 'css', 'assets']               // Positional format
]);

echo "Multiple assignments (using both positional and associative formats):\n";
echo "- error_logs: " . Via::p('rel.logs.error_logs') . "\n";
echo "- access_logs: " . Via::p('rel.logs.access_logs') . "\n";
echo "- images: " . Via::p('rel.assets.images') . "\n";
echo "- css: " . Via::p('rel.assets.css') . "\n\n";

// Example 5b: Positional Array Format Showcase
echo "5b. Positional Array Format Showcase\n";
echo "------------------------------------\n";

echo "Positional arrays make configuration more concise:\n\n";

// Reset for clean demo
Via::reset();
Via::setLocal('/Users/demo/myapp');
Via::setHost('myapp.local');

echo "// Bases with positional arrays:\n";
echo "Via::setBases([\n";
echo "    ['data', 'data'],\n";
echo "    ['public', 'public'],\n";
echo "    ['src', 'src']\n";
echo "]);\n\n";

Via::setBases([
    ['data', 'data'],
    ['public', 'public'],
    ['src', 'src']
]);

echo "// Assignments with positional arrays:\n";
echo "Via::assignToBases([\n";
echo "    ['uploads', 'uploads', 'data'],\n";
echo "    ['assets', 'assets', 'public'],\n";
echo "    ['components', 'components', 'src']\n";
echo "]);\n\n";

Via::assignToBases([
    ['uploads', 'uploads', 'data'],
    ['assets', 'assets', 'public'],
    ['components', 'components', 'src']
]);

echo "Results:\n";
echo "- " . Via::p('rel.data.uploads') . "\n";
echo "- " . Via::p('local.public.assets') . "\n";
echo "- " . Via::p('host.src.components') . "\n\n";

// Example 6: Path Types - Relative, Local, and Host
echo "6. Different Path Types\n";
echo "----------------------\n";

echo "Relative paths (project-relative):\n";
echo "- Data directory: " . Via::p('rel.data') . "\n";
echo "- Data uploads: " . Via::p('rel.data.uploads') . "\n";
echo "- Public assets: " . Via::p('rel.public.assets') . "\n\n";

echo "Local filesystem paths (absolute):\n";
echo "- Data directory: " . Via::p('local.data') . "\n";
echo "- Data uploads: " . Via::p('local.data.uploads') . "\n";
echo "- Public assets: " . Via::p('local.public.assets') . "\n\n";

echo "Host URLs:\n";
echo "- Data directory: " . Via::p('host.data') . "\n";
echo "- Data uploads: " . Via::p('host.data.uploads') . "\n";
echo "- Public assets: " . Via::p('host.public.assets') . "\n\n";

// Example 7: Nested Path Configuration and Access
echo "7. Nested Path Configuration and Access\n";
echo "---------------------------------------\n";

// Add a logs base for demonstration
Via::setBase('logs', 'storage/logs');

// Configure nested paths for demonstration
Via::assignToBase('app_logs', 'app', 'logs');
Via::assignToBase('avatars', 'avatars', 'data');
Via::assignToBase('main_css', 'main.css', 'public');
Via::assignToBase('error_logs', 'errors', 'logs');

echo "Nested path configuration:\n";
echo "- App logs within logs base: " . Via::p('rel.logs.app_logs') . "\n";
echo "- Avatars within data base: " . Via::p('local.data.avatars') . "\n";
echo "- Main CSS file URL: " . Via::p('host.public.main_css') . "\n\n";

echo "Path Validation - These will work:\n";
echo "- Valid nested: " . Via::p('rel.logs.app_logs') . "\n";
echo "- Valid nested: " . Via::p('local.data.avatars') . "\n\n";

echo "Path Validation - These will fail (demonstration):\n";
try {
    Via::p('rel.logs.error_logs.nonexistent');
    echo "- This should not print\n";
} catch (\InvalidArgumentException $e) {
    echo "- ✗ Invalid path: " . $e->getMessage() . "\n";
}

try {
    Via::p('rel.data.arbitrary.path');
    echo "- This should not print\n";
} catch (\InvalidArgumentException $e) {
    echo "- ✗ Invalid path: " . $e->getMessage() . "\n";
}
echo "\n";

// Example 8: Complete Initialization
echo "8. Complete Initialization with init()\n";
echo "-------------------------------------\n";

// Reset static properties for demo
Via::reset();

$config = [
    'Local'          => '/Users/me/Projects/ecommerce',
    'absoluteDomain' => 'shop.example.com',
    'bases'          => [
        ['data', 'data'],           // Positional format
        ['web', 'public'],          // Positional format
        ['alias' => 'app', 'path' => 'src']  // Associative format
    ],
    'assignments' => [
        ['products', 'products', 'data'],    // Positional format
        ['orders', 'orders', 'data'],        // Positional format
        ['alias' => 'assets', 'path' => 'assets', 'baseAlias' => 'web'],     // Associative format
        ['controllers', 'Controllers', 'app'], // Positional format
        ['services', 'Services', 'app']       // Positional format
    ]
];

Via::init($config);

echo "Initialized with complete config (using mixed positional/associative arrays):\n";
echo "- Local path: " . Via::getLocal() . "\n";
echo "- Host: " . Via::getHost() . "\n";
echo "- Products data: " . Via::p('local.data.products') . "\n";
echo "- Controllers: " . Via::p('rel.app.controllers') . "\n";

// Note: For nested paths, we use the proper hierarchy
echo "- Assets URL: " . Via::p('host.web.assets') . "\n\n";

print_r(Via::all());

// Example 9: Practical Use Cases
echo "9. Practical Use Cases\n";
echo "---------------------\n";

// Set up some additional paths for the practical examples
Via::assignToBase('config_files', 'config', 'data');

// File operations
echo "File operations:\n";
$configPath = Via::p('local.data.config_files');
echo "- Config directory: {$configPath}\n";

$dataPath = Via::p('local.data.products');
echo "- Products data: {$dataPath}\n";

// URL generation
echo "\nURL generation:\n";
$assetsUrl = Via::p('host.web.assets');
echo "- Assets base URL: {$assetsUrl}\n";

// Template includes
echo "\nTemplate includes:\n";
$controllersPath = Via::p('rel.app.controllers');
echo "- Controllers path: {$controllersPath}\n";

$servicesPath = Via::p('rel.app.services');
echo "- Services path: {$servicesPath}\n\n";

// Example 10: Cross-Platform Path Handling
echo "10. Cross-Platform Path Handling\n";
echo "--------------------------------\n";

// Reset static properties for clean demo
Via::reset();

Via::setLocal('/Users/demo/project');
Via::setBase('src', 'src');

// Demonstrate robust path handling
echo "Multi-level paths with various separators:\n";

// Your edge case example with forward slashes
Via::assignToBase('coremods', 'core/modules', 'src');
echo "- Core modules: " . Via::p('rel.src.coremods') . "\n";

// Mixed separators (Windows-style backslashes)
Via::assignToBase('winpath', 'windows\\style\\path', 'src');
echo "- Windows path: " . Via::p('rel.src.winpath') . "\n";

// Messy paths that get cleaned up
Via::assignToBase('messypath', 'dir1//dir2/../dir2/final', 'src');
echo "- Cleaned messy path: " . Via::p('rel.src.messypath') . "\n";

// Complex real-world example
Via::assignToBase('deepnest', 'components\\ui//forms/../forms/inputs', 'src');
echo "- Deep nested path: " . Via::p('rel.src.deepnest') . "\n";

echo "\nAll paths are normalized using Symfony's Path class for cross-OS compatibility!\n\n";

// Example 11: Introspection with all() method
echo "11. Introspection with all() Method\n";
echo "-----------------------------------\n";

Via::setLocal('/Users/demo/myapp');
Via::setHost('myapp.local');
Via::setBase('data', 'data');
Via::setBase('public', 'public');
Via::assignToBase('uploads', 'uploads', 'data');
Via::assignToBase('assets', 'assets', 'public');

echo "All configured paths:\n";
$allPaths = Via::all();
foreach ($allPaths as $alias => $paths) {
    echo "- {$alias}:\n";
    foreach ($paths as $type => $path) {
        echo "  {$type}: {$path}\n";
    }
}
echo "\n";

echo "=== Examples Complete ===\n";

// Example 12: Dynamic Path Appending (New Feature)
echo "12. Dynamic Path Appending with Additional Parameter\n";
echo "---------------------------------------------------\n";

// Reset and setup for clean demo
Via::reset();
Via::setLocal('/Users/demo/webapp');
Via::setHost('webapp.local');
Via::setBases([
    ['data', 'data'],
    ['uploads', 'storage/uploads'],
    ['src', 'src']
]);
Via::assignToBases([
    ['logs', 'logs', 'data'],
    ['user_files', 'users', 'uploads'],
    ['components', 'components', 'src']
]);

echo "Now you can append dynamic paths to any configured alias:\n\n";

echo "Base paths with additional segments:\n";
echo "- Data config file: " . Via::p('rel.data', 'config/app.json') . "\n";
echo "- Source utilities: " . Via::p('rel.src', 'utils/helpers.php') . "\n";
echo "- Upload temp files: " . Via::p('rel.uploads', 'temp/processing') . "\n\n";

echo "Assignment paths with additional segments:\n";
echo "- Specific log file: " . Via::p('rel.data.logs', 'error/2024-01-15.log') . "\n";
echo "- User avatar: " . Via::p('rel.uploads.user_files', 'avatars/user123.jpg') . "\n";
echo "- UI component: " . Via::p('rel.src.components', 'forms/ContactForm.php') . "\n\n";

echo "Works with all path types:\n";
echo "- Local file: " . Via::p('local.data', 'cache/compiled.php') . "\n";
echo "- Local user upload: " . Via::p('local.uploads.user_files', 'documents/invoice.pdf') . "\n";
echo "- Host asset URL: " . Via::p('host.src.components', 'ui/styles.css') . "\n";
echo "- Host log URL: " . Via::p('host.data.logs', 'api/requests.json') . "\n\n";

echo "Path canonicalization works on additional paths too:\n";
echo "- Messy path: " . Via::p('rel.data', 'cache/../temp//file.txt') . "\n";
echo "- Mixed separators: " . Via::p('local.src', 'modules\\auth/controllers') . "\n";
echo "- Complex path: " . Via::p('host.uploads.user_files', 'gallery/../thumbnails/image.jpg') . "\n\n";

echo "Both get() and p() methods support the additional parameter:\n";
echo "- Via::get(): " . Via::get('rel.data.logs', 'debug/trace.log') . "\n";
echo "- Via::p(): " . Via::p('rel.data.logs', 'debug/trace.log') . "\n\n";

echo "Backwards compatibility - these still work as before:\n";
echo "- Without additional path: " . Via::p('rel.data.logs') . "\n";
echo "- With null parameter: " . Via::p('rel.src.components', null) . "\n";
echo "- With empty string: " . Via::p('rel.uploads', '') . "\n\n";

echo "Real-world usage examples:\n";

// File operations
$logFile = Via::p('local.data.logs', 'app/' . date('Y-m-d') . '.log');
echo "- Today's log file: {$logFile}\n";

$userAvatar = Via::p('local.uploads.user_files', "avatars/user_123.jpg");
echo "- User avatar path: {$userAvatar}\n";

// URL generation
$assetUrl = Via::p('host.src.components', 'ui/button.css');
echo "- CSS asset URL: {$assetUrl}\n";

$apiLogUrl = Via::p('host.data.logs', 'api/' . date('Y/m') . '/requests.log');
echo "- API log URL: {$apiLogUrl}\n";

// Template includes
$componentPath = Via::p('rel.src.components', 'widgets/Calendar.php');
echo "- Component include: {$componentPath}\n";

$configFile = Via::p('rel.data', 'config/database.php');
echo "- Config include: {$configFile}\n\n";

echo "This feature makes ViaPHP much more flexible while maintaining strict validation!\n\n";

echo "=== Examples Complete ===\n";

// Demonstration that invalid paths now fail (as they should)
echo "\nDemonstration of strict validation:\n";
echo "These arbitrary paths will now fail validation:\n";
try {
    echo Via::p('rel.src.some.arbitrary.path') . PHP_EOL;
} catch (\InvalidArgumentException $e) {
    echo "- ✗ rel.src.some.arbitrary.path: " . $e->getMessage() . "\n";
}
try {
    echo Via::p('local.src.some.arbitrary.path') . PHP_EOL;
} catch (\InvalidArgumentException $e) {
    echo "- ✗ local.src.some.arbitrary.path: " . $e->getMessage() . "\n";
}

// Set host for final demo
Via::setHost('demo.local');
try {
    echo Via::p('host.src.some.arbitrary.path') . PHP_EOL;
} catch (\InvalidArgumentException $e) {
    echo "- ✗ host.src.some.arbitrary.path: " . $e->getMessage() . "\n";
}

echo "\nBut additional path appending still works with valid configured aliases:\n";
echo "- ✓ " . Via::p('rel.src.components', 'dynamic/path/segment.php') . "\n";
echo "- ✓ " . Via::p('local.data.logs', 'runtime/error.log') . "\n";
echo "- ✓ " . Via::p('host.uploads.user_files', 'gallery/photos.zip') . "\n";
