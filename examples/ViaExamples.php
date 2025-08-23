<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Via\Via;

echo "=== PathsPHP Usage Examples ===\n\n";

echo "PathsPHP enforces strict hierarchical path structure:\n";
echo "- type.base_role.assignment_role (e.g., 'rel.data.logs')\n";
echo "- Assignments must be accessed through their base\n";
echo "- All paths are validated and normalized for cross-platform compatibility\n\n";

// Example 1: Basic Setup
echo "1. Basic Setup\n";
echo "--------------\n";

Via::setLocalPath('/Users/me/Projects/my-app');
Via::setHost('myapp.local.test');

echo "Local path set: " . Via::getLocalPath() . "\n";
echo "Host set: " . Via::getHost() . "\n\n";

// Example 2: Setting Bases
echo "2. Setting Base Paths\n";
echo "--------------------\n";

Via::setBase('data', 'data');
Via::setBase('src', 'src');
Via::setBase('assets', 'public/assets');

echo "Base paths configured:\n";
echo "- data: " . Via::f('rel.data') . "\n";
echo "- src: " . Via::f('rel.src') . "\n";
echo "- assets: " . Via::f('rel.assets') . "\n\n";

// Example 3: Multiple Bases at Once
echo "3. Setting Multiple Bases\n";
echo "------------------------\n";

Via::setBases([
    ['role' => 'uploads', 'path' => 'storage/uploads'],
    ['role' => 'cache', 'path' => 'storage/cache'],
    ['role' => 'logs', 'path' => 'storage/logs']
]);

echo "Multiple bases set:\n";
echo "- uploads: " . Via::f('rel.uploads') . "\n";
echo "- cache: " . Via::f('rel.cache') . "\n";
echo "- logs: " . Via::f('rel.logs') . "\n\n";

// Example 4: Assigning Sub-paths to Bases
echo "4. Assigning Sub-paths to Bases\n";
echo "-------------------------------\n";

Via::assignToBase('user_uploads', 'users', 'uploads');
Via::assignToBase('temp_uploads', 'temp', 'uploads');
Via::assignToBase('components', 'components', 'src');
Via::assignToBase('models', 'models', 'src');

echo "Assignments to bases:\n";
echo "- user_uploads: " . Via::f('rel.uploads.user_uploads') . "\n";
echo "- temp_uploads: " . Via::f('rel.uploads.temp_uploads') . "\n";
echo "- components: " . Via::f('rel.src.components') . "\n";
echo "- models: " . Via::f('rel.src.models') . "\n\n";

// Example 5: Multiple Assignments at Once
echo "5. Multiple Assignments\n";
echo "----------------------\n";

Via::assignToBases([
    ['role' => 'error_logs', 'path' => 'errors', 'baseRole' => 'logs'],
    ['role' => 'access_logs', 'path' => 'access', 'baseRole' => 'logs'],
    ['role' => 'images', 'path' => 'images', 'baseRole' => 'assets'],
    ['role' => 'css', 'path' => 'css', 'baseRole' => 'assets']
]);

echo "Multiple assignments:\n";
echo "- error_logs: " . Via::f('rel.logs.error_logs') . "\n";
echo "- access_logs: " . Via::f('rel.logs.access_logs') . "\n";
echo "- images: " . Via::f('rel.assets.images') . "\n";
echo "- css: " . Via::f('rel.assets.css') . "\n\n";

// Example 6: Path Types - Relative, Local, and Host
echo "6. Different Path Types\n";
echo "----------------------\n";

echo "Relative paths (project-relative):\n";
echo "- Data directory: " . Via::f('rel.data') . "\n";
echo "- User uploads: " . Via::f('rel.uploads.user_uploads') . "\n";
echo "- CSS assets: " . Via::f('rel.assets.css') . "\n\n";

echo "Local filesystem paths (absolute):\n";
echo "- Data directory: " . Via::f('local.data') . "\n";
echo "- User uploads: " . Via::f('local.uploads.user_uploads') . "\n";
echo "- CSS assets: " . Via::f('local.assets.css') . "\n\n";

echo "Host URLs:\n";
echo "- Data directory: " . Via::f('host.data') . "\n";
echo "- User uploads: " . Via::f('host.uploads.user_uploads') . "\n";
echo "- CSS assets: " . Via::f('host.assets.css') . "\n\n";

// Example 7: Nested Path Configuration and Access
echo "7. Nested Path Configuration and Access\n";
echo "---------------------------------------\n";

// Configure nested paths for demonstration
Via::assignToBase('app_logs', 'app', 'logs');
Via::assignToBase('avatars', 'avatars', 'uploads');
Via::assignToBase('main_css', 'main.css', 'assets');
Via::assignToBase('error_logs', 'errors', 'logs');

echo "Nested path configuration:\n";
echo "- App logs within logs base: " . Via::f('rel.logs.app_logs') . "\n";
echo "- Avatars within uploads base: " . Via::f('local.uploads.avatars') . "\n";
echo "- Main CSS file URL: " . Via::f('host.assets.main_css') . "\n\n";

echo "Path Validation - These will work:\n";
echo "- Valid nested: " . Via::f('rel.logs.app_logs') . "\n";
echo "- Valid nested: " . Via::f('local.uploads.avatars') . "\n\n";

echo "Path Validation - These will fail (demonstration):\n";
try {
    Via::f('rel.logs.error_logs.nonexistent');
    echo "- This should not print\n";
} catch (\InvalidArgumentException $e) {
    echo "- ✗ Invalid path: " . $e->getMessage() . "\n";
}

try {
    Via::f('rel.data.arbitrary.path');
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
    'LocalPath'      => '/Users/me/Projects/ecommerce',
    'absoluteDomain' => 'shop.example.com',
    'bases'          => [
        ['role' => 'data', 'path' => 'data'],
        ['role' => 'web', 'path' => 'public'],
        ['role' => 'app', 'path' => 'src']
    ],
    'assignments' => [
        ['role' => 'products', 'path' => 'products', 'baseRole' => 'data'],
        ['role' => 'orders', 'path' => 'orders', 'baseRole' => 'data'],
        ['role' => 'assets', 'path' => 'assets', 'baseRole' => 'web'],
        ['role' => 'controllers', 'path' => 'Controllers', 'baseRole' => 'app'],
        ['role' => 'services', 'path' => 'Services', 'baseRole' => 'app']
    ]
];

Via::init($config);

echo "Initialized with complete config:\n";
echo "- Local path: " . Via::getLocalPath() . "\n";
echo "- Host: " . Via::getHost() . "\n";
echo "- Products data: " . Via::f('local.data.products') . "\n";
echo "- Controllers: " . Via::f('rel.app.controllers') . "\n";

// Note: For nested paths, we use the proper hierarchy
echo "- Assets URL: " . Via::f('host.web.assets') . "\n\n";

print_r(Via::all());


// Example 9: Practical Use Cases
echo "9. Practical Use Cases\n";
echo "---------------------\n";

// Set up some additional paths for the practical examples
Via::assignToBase('config_files', 'config', 'data');

// File operations
echo "File operations:\n";
$configPath = Via::f('local.data.config_files');
echo "- Config directory: {$configPath}\n";

$dataPath = Via::f('local.data.products');
echo "- Products data: {$dataPath}\n";

// URL generation
echo "\nURL generation:\n";
$assetsUrl = Via::f('host.web.assets');
echo "- Assets base URL: {$assetsUrl}\n";

// Template includes
echo "\nTemplate includes:\n";
$controllersPath = Via::f('rel.app.controllers');
echo "- Controllers path: {$controllersPath}\n";

$servicesPath = Via::f('rel.app.services');
echo "- Services path: {$servicesPath}\n\n";

// Example 10: Cross-Platform Path Handling
echo "10. Cross-Platform Path Handling\n";
echo "--------------------------------\n";

// Reset static properties for clean demo
Via::reset();

Via::setLocalPath('/Users/demo/project');
Via::setBase('src', 'src');

// Demonstrate robust path handling
echo "Multi-level paths with various separators:\n";

// Your edge case example with forward slashes
Via::assignToBase('coremods', 'core/modules', 'src');
echo "- Core modules: " . Via::f('rel.src.coremods') . "\n";

// Mixed separators (Windows-style backslashes)
Via::assignToBase('winpath', 'windows\\style\\path', 'src');
echo "- Windows path: " . Via::f('rel.src.winpath') . "\n";

// Messy paths that get cleaned up
Via::assignToBase('messypath', 'dir1//dir2/../dir2/final', 'src');
echo "- Cleaned messy path: " . Via::f('rel.src.messypath') . "\n";

// Complex real-world example
Via::assignToBase('deepnest', 'components\\ui//forms/../forms/inputs', 'src');
echo "- Deep nested path: " . Via::f('rel.src.deepnest') . "\n";

echo "\nAll paths are normalized using Symfony's Path class for cross-OS compatibility!\n\n";

// Example 11: Introspection with all() method
echo "11. Introspection with all() Method\n";
echo "-----------------------------------\n";

Via::setLocalPath('/Users/demo/myapp');
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

// Demonstration that invalid paths now fail (as they should)
echo "\nDemonstration of strict validation:\n";
echo "These arbitrary paths will now fail validation:\n";
try {
    echo Via::f('rel.src.some.arbitrary.path') . PHP_EOL;
} catch (\InvalidArgumentException $e) {
    echo "- ✗ rel.src.some.arbitrary.path: " . $e->getMessage() . "\n";
}
try {
    echo Via::f('local.src.some.arbitrary.path') . PHP_EOL;
} catch (\InvalidArgumentException $e) {
    echo "- ✗ local.src.some.arbitrary.path: " . $e->getMessage() . "\n";
}

// Set host for final demo
Via::setHost('demo.local');
try {
    echo Via::f('host.src.some.arbitrary.path') . PHP_EOL;
} catch (\InvalidArgumentException $e) {
    echo "- ✗ host.src.some.arbitrary.path: " . $e->getMessage() . "\n";
}
