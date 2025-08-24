<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Via\Via;

echo "=== Global via() Function Examples ===\n\n";

echo "The global via() function provides a convenient shorthand for Via::get()\n";
echo "Perfect for template files and contexts where brevity is preferred.\n\n";

// Example 1: Basic Setup and Usage
echo "1. Basic Setup and Global Function Usage\n";
echo "---------------------------------------\n";

Via::setLocal('/Users/demo/myapp');
Via::setHost('myapp.local');
Via::setBases([
    ['data', 'data'],
    ['public', 'public'],
    ['src', 'src']
]);

echo "Using the global via() function instead of Via::get():\n";
echo "- Data dir (via): " . via('rel.data') . "\n";
echo "- Data dir (Via::get): " . Via::get('rel.data') . "\n";
echo "- Public dir: " . via('rel.public') . "\n";
echo "- Source dir: " . via('rel.src') . "\n\n";

// Example 2: Template-Style Usage
echo "2. Template-Style Usage\n";
echo "----------------------\n";

Via::assignToBases([
    ['uploads', 'uploads', 'data'],
    ['assets', 'assets', 'public'],
    ['components', 'components', 'src']
]);

echo "In template files, the global function is much cleaner:\n\n";

echo "<?php\n";
echo "// Instead of this verbose syntax:\n";
echo '// $cssUrl = \\Via\\Via::get(\'host.public.assets\', \'styles/main.css\');' . "\n";
echo '// $logoPath = \\Via\\Via::get(\'local.data.uploads\', \'images/logo.png\');' . "\n\n";

echo "// Use this clean syntax:\n";
echo '$cssUrl = via(\'host.public.assets\', \'styles/main.css\');' . "\n";
echo '$logoPath = via(\'local.data.uploads\', \'images/logo.png\');' . "\n";
echo "?>\n\n";

$cssUrl   = via('host.public.assets', 'styles/main.css');
$logoPath = via('local.data.uploads', 'images/logo.png');

echo "Results:\n";
echo "- CSS URL: {$cssUrl}\n";
echo "- Logo path: {$logoPath}\n\n";

// Example 3: Path Types with Global Function
echo "3. All Path Types Work with Global Function\n";
echo "------------------------------------------\n";

echo "Relative paths:\n";
echo "- Components: " . via('rel.src.components') . "\n";
echo "- Assets: " . via('rel.public.assets') . "\n\n";

echo "Local filesystem paths:\n";
echo "- Components: " . via('local.src.components') . "\n";
echo "- Uploads: " . via('local.data.uploads') . "\n\n";

echo "Host URLs:\n";
echo "- Components: " . via('host.src.components') . "\n";
echo "- Assets: " . via('host.public.assets') . "\n\n";

// Example 4: Dynamic Path Appending
echo "4. Dynamic Path Appending with Global Function\n";
echo "----------------------------------------------\n";

Via::assignToBases([
    ['logs', 'logs', 'data'],
    ['user_files', 'users', 'data']
]);

echo "The global function supports additional path parameters too:\n\n";

// File operations
$errorLog   = via('local.data.logs', 'errors/' . date('Y-m-d') . '.log');
$userAvatar = via('local.data.user_files', 'avatars/user_123.jpg');
$configFile = via('rel.data', 'config/app.json');

echo "File operations:\n";
echo "- Error log: {$errorLog}\n";
echo "- User avatar: {$userAvatar}\n";
echo "- Config file: {$configFile}\n\n";

// URL generation
$jsAsset     = via('host.public.assets', 'js/app.min.js');
$apiEndpoint = via('host.src.components', 'api/UserController.php');

echo "URL generation:\n";
echo "- JS asset: {$jsAsset}\n";
echo "- API endpoint: {$apiEndpoint}\n\n";

// Example 5: HTML Template Simulation
echo "5. HTML Template Simulation\n";
echo "---------------------------\n";

echo "Perfect for HTML templates and view files:\n\n";

echo "<!DOCTYPE html>\n";
echo "<html>\n";
echo "<head>\n";
echo '    <link rel="stylesheet" href="' . via('host.public.assets', 'css/main.css') . '">' . "\n";
echo '    <script src="' . via('host.public.assets', 'js/app.js') . '"></script>' . "\n";
echo "</head>\n";
echo "<body>\n";
echo '    <img src="' . via('host.data.uploads', 'images/banner.jpg') . '" alt="Banner">' . "\n";
echo '    <img src="' . via('host.data.user_files', 'avatars/default.png') . '" alt="Avatar">' . "\n";
echo "</body>\n";
echo "</html>\n\n";

// Example 6: PHP Include/Require Simulation
echo "6. PHP Include/Require Simulation\n";
echo "---------------------------------\n";

echo "Clean includes in PHP files:\n\n";

echo "<?php\n";
echo '// Include configuration' . "\n";
echo 'require_once via(\'local.data\', \'config/database.php\');' . "\n\n";

echo '// Include components' . "\n";
echo 'include via(\'local.src.components\', \'auth/LoginForm.php\');' . "\n";
echo 'include via(\'local.src.components\', \'widgets/Sidebar.php\');' . "\n\n";

echo '// Include utilities' . "\n";
echo 'require_once via(\'local.src\', \'utils/helpers.php\');' . "\n";
echo "?>\n\n";

// Example 7: Error Handling
echo "7. Error Handling\n";
echo "----------------\n";

echo "The global function has the same validation as Via::get():\n\n";

try {
    $invalidPath = via('rel.nonexistent.path');
    echo "This shouldn't print\n";
} catch (\InvalidArgumentException $e) {
    echo "- ✗ Invalid path caught: " . $e->getMessage() . "\n";
}

try {
    $invalidPath = via('local.src.invalid.nested');
    echo "This shouldn't print\n";
} catch (\InvalidArgumentException $e) {
    echo "- ✗ Invalid nested path caught: " . $e->getMessage() . "\n";
}

echo "\nBut valid paths with additional segments work fine:\n";
echo "- ✓ Valid: " . via('rel.src.components', 'forms/ContactForm.php') . "\n";
echo "- ✓ Valid: " . via('local.data.logs', 'app/debug.log') . "\n\n";

// Example 8: Comparison with Via::get()
echo "8. Function Equivalence\n";
echo "----------------------\n";

echo "The global via() function is identical to Via::get():\n\n";

$path1 = via('rel.src.components', 'ui/Button.php');
$path2 = Via::get('rel.src.components', 'ui/Button.php');
$path3 = Via::p('rel.src.components', 'ui/Button.php');

echo "- via(): {$path1}\n";
echo "- Via::get(): {$path2}\n";
echo "- Via::p(): {$path3}\n";
echo "- All identical: " . ($path1 === $path2 && $path2 === $path3 ? 'Yes' : 'No') . "\n\n";

// Example 9: Real-World Use Cases
echo "9. Real-World Use Cases\n";
echo "----------------------\n";

Via::assignToBases([
    ['cache', 'cache', 'data'],
    ['sessions', 'sessions', 'data'],
    ['mail_templates', 'mail', 'src']
]);

echo "Configuration files:\n";
$dbConfig  = via('local.data', 'config/database.php');
$appConfig = via('local.data', 'config/app.php');
echo "- DB config: {$dbConfig}\n";
echo "- App config: {$appConfig}\n\n";

echo "Runtime files:\n";
$cacheFile   = via('local.data.cache', 'views/' . md5('homepage') . '.cache');
$sessionFile = via('local.data.sessions', 'sess_' . session_id());
echo "- Cache file: {$cacheFile}\n";
echo "- Session file: {$sessionFile}\n\n";

echo "Template rendering:\n";
$mailTemplate = via('local.src.mail_templates', 'welcome.html');
$userWelcome  = via('local.src.mail_templates', 'users/activation.html');
echo "- Mail template: {$mailTemplate}\n";
echo "- User template: {$userWelcome}\n\n";

// Example 10: Performance Note
echo "10. Performance and Safety\n";
echo "-------------------------\n";

echo "The global via() function:\n";
echo "- Uses function_exists() guard to prevent conflicts\n";
echo "- Simply forwards to Via::get() - no performance overhead\n";
echo "- Maintains all validation and error handling\n";
echo "- Available immediately after composer autoload\n";
echo "- Safe to use in any context where Via class is available\n\n";

echo "Function exists: " . (function_exists('via') ? 'Yes' : 'No') . "\n";
echo "Function callable: " . (is_callable('via') ? 'Yes' : 'No') . "\n\n";

// Example 11: Shorthand Methods and Additional Global Functions
echo "11. Shorthand Methods and Additional Global Functions\n";
echo "----------------------------------------------------\n";

echo "ViaPHP provides shorthand methods for even more concise code:\n\n";

Via::setLocal('/Users/demo/shorthand-test');
Via::setHost('shorthand.local');

echo "Class shorthand methods:\n";
echo "- Via::l() instead of Via::getLocal(): " . Via::l() . "\n";
echo "- Via::h() instead of Via::getHost(): " . Via::h() . "\n\n";

echo "Global functions for ultimate brevity:\n";
echo "- via_local() instead of Via::getLocal(): " . via_local() . "\n";
echo "- via_host() instead of Via::getHost(): " . via_host() . "\n\n";

echo "All methods are equivalent:\n";
$localPath1 = Via::getLocal();
$localPath2 = Via::l();
$localPath3 = via_local();

$hostPath1 = Via::getHost();
$hostPath2 = Via::h();
$hostPath3 = via_host();

echo "- Via::getLocal(): {$localPath1}\n";
echo "- Via::l(): {$localPath2}\n";
echo "- via_local(): {$localPath3}\n";
echo "- All local paths identical: " . ($localPath1 === $localPath2 && $localPath2 === $localPath3 ? 'Yes' : 'No') . "\n\n";

echo "- Via::getHost(): {$hostPath1}\n";
echo "- Via::h(): {$hostPath2}\n";
echo "- via_host(): {$hostPath3}\n";
echo "- All host paths identical: " . ($hostPath1 === $hostPath2 && $hostPath2 === $hostPath3 ? 'Yes' : 'No') . "\n\n";

// Example 12: Template Usage with All Global Functions
echo "12. Complete Template Usage Example\n";
echo "----------------------------------\n";

echo "Perfect for templates where you want maximum brevity:\n\n";

Via::setBases([
    ['assets', 'public/assets'],
    ['uploads', 'storage/uploads']
]);

Via::assignToBases([
    ['css', 'css', 'assets'],
    ['js', 'js', 'assets'],
    ['images', 'images', 'uploads']
]);

echo "HTML template with all global functions:\n\n";

echo "<!DOCTYPE html>\n";
echo "<html>\n";
echo "<head>\n";
echo '    <base href="//'. via_host() . '/">' . "\n";
echo '    <link rel="stylesheet" href="' . via('host.assets.css', 'main.css') . '">' . "\n";
echo '    <script src="' . via('host.assets.js', 'app.min.js') . '"></script>' . "\n";
echo "</head>\n";
echo "<body>\n";
echo '    <!-- Local filesystem operations -->' . "\n";
echo '    <?php include "' . via('local.assets.js', 'config.php') . '"; ?>' . "\n";
echo '    ' . "\n";
echo '    <!-- URL generation -->' . "\n";
echo '    <img src="' . via('host.uploads.images', 'banner.jpg') . '" alt="Banner">' . "\n";
echo '    ' . "\n";
echo '    <!-- Configuration info -->' . "\n";
echo '    <!-- Local path: ' . via_local() . ' -->' . "\n";
echo '    <!-- Host: ' . via_host() . ' -->' . "\n";
echo "</body>\n";
echo "</html>\n\n";

// Example 13: PHP Configuration File Example
echo "13. PHP Configuration File Example\n";
echo "----------------------------------\n";

echo "<?php\n";
echo "// Configuration using global functions\n";
echo '$config = [' . "\n";
echo '    \'local_path\' => via_local(),' . "\n";
echo '    \'host\' => via_host(),' . "\n";
echo '    \'assets_url\' => via(\'host.assets\'),' . "\n";
echo '    \'uploads_path\' => via(\'local.uploads\'),' . "\n";
echo '    \'css_path\' => via(\'local.assets.css\'),' . "\n";
echo "];'\n\n";

// Show actual execution
$config = [
    'local_path'   => via_local(),
    'host'         => via_host(),
    'assets_url'   => via('host.assets'),
    'uploads_path' => via('local.uploads'),
    'css_path'     => via('local.assets.css'),
];

echo "Actual config array generated:\n";
foreach ($config as $key => $value) {
    echo "- {$key}: {$value}\n";
}
echo "\n";

// Example 14: Error Handling and Null Safety
echo "14. Error Handling and Null Safety\n";
echo "----------------------------------\n";

Via::reset();

echo "Global functions handle null states safely:\n";
echo "- via_local() when not set: " . (via_local() ?? 'NULL') . "\n";
echo "- via_host() when not set: " . (via_host() ?? 'NULL') . "\n";
echo "- Via::l() when not set: " . (Via::l() ?? 'NULL') . "\n";
echo "- Via::h() when not set: " . (Via::h() ?? 'NULL') . "\n\n";

echo "Conditional usage in templates:\n";
echo "<?php\n";
echo 'if (via_local()) {' . "\n";
echo '    echo "Local path configured: " . via_local();' . "\n";
echo '}' . "\n";
echo 'if (via_host()) {' . "\n";
echo '    echo "Host configured: " . via_host();' . "\n";
echo '}' . "\n";
echo "?>\n\n";

echo "Function availability checks:\n";
$functions = ['via', 'via_local', 'via_host'];
foreach ($functions as $func) {
    echo "- {$func}(): " . (function_exists($func) ? 'Available' : 'Not available') . "\n";
}
echo "\n";

// Example 15: Additional Path Parameters for getLocal() and getHost()
echo "15. Additional Path Parameters for Local and Host Methods\n";
echo "-------------------------------------------------------\n";

Via::setLocal('/Users/demo/project');
Via::setHost('app.local');

echo "New feature: getLocal() and getHost() now accept additional path parameters!\n\n";

echo "Class methods with additional paths:\n";
echo "- Via::getLocal(): " . Via::getLocal() . "\n";
echo "- Via::getLocal('config/app.php'): " . Via::getLocal('config/app.php') . "\n";
echo "- Via::getLocal('uploads/images'): " . Via::getLocal('uploads/images') . "\n\n";

echo "- Via::getHost(): " . Via::getHost() . "\n";
echo "- Via::getHost('api/users'): " . Via::getHost('api/users') . "\n";
echo "- Via::getHost('assets/css/main.css'): " . Via::getHost('assets/css/main.css') . "\n\n";

echo "Shorthand methods with additional paths:\n";
echo "- Via::l('data/cache'): " . Via::l('data/cache') . "\n";
echo "- Via::h('static/js/app.js'): " . Via::h('static/js/app.js') . "\n\n";

echo "Global functions with additional paths:\n";
echo "- via_local('logs/error.log'): " . via_local('logs/error.log') . "\n";
echo "- via_host('media/videos'): " . via_host('media/videos') . "\n\n";

echo "Path canonicalization works here too:\n";
echo "- Via::getLocal('cache/../temp//file.txt'): " . Via::getLocal('cache/../temp//file.txt') . "\n";
echo "- Via::getHost('assets/./css/../js/app.js'): " . Via::getHost('assets/./css/../js/app.js') . "\n";
echo "- via_local('dir1\\\\dir2/final/'): " . via_local('dir1\\dir2/final/') . "\n\n";

// Example 16: Practical Usage Scenarios
echo "16. Practical Usage Scenarios\n";
echo "-----------------------------\n";

echo "Perfect for configuration files:\n";
$dbConfigPath = Via::getLocal('config/database.php');
$logPath      = via_local('storage/logs/app.log');
$apiUrl       = Via::getHost('api/v1/endpoint');
$cdnUrl       = via_host('cdn/assets/logo.png');

echo "- Database config: {$dbConfigPath}\n";
echo "- Application log: {$logPath}\n";
echo "- API endpoint: {$apiUrl}\n";
echo "- CDN asset: {$cdnUrl}\n\n";

echo "Template usage with direct path building:\n";
echo "<?php\n";
echo '// Build paths directly without configuring aliases' . "\n";
echo '$configPath = via_local(\'config/\' . $env . \'.php\');' . "\n";
echo '$assetUrl = via_host(\'assets/\' . $version . \'/app.js\');' . "\n";
echo '$logFile = Via::getLocal(\'logs/\' . date(\'Y-m-d\') . \'.log\');' . "\n";
echo '$apiEndpoint = Via::getHost(\'api/\' . $apiVersion . \'/users\');' . "\n";
echo "?>\n\n";

// Show actual execution
$env         = 'production';
$version     = 'v2.1';
$apiVersion  = 'v1';
$configPath  = via_local('config/' . $env . '.php');
$assetUrl    = via_host('assets/' . $version . '/app.js');
$logFile     = Via::getLocal('logs/' . date('Y-m-d') . '.log');
$apiEndpoint = Via::getHost('api/' . $apiVersion . '/users');

echo "Actual generated paths:\n";
echo "- Config path: {$configPath}\n";
echo "- Asset URL: {$assetUrl}\n";
echo "- Log file: {$logFile}\n";
echo "- API endpoint: {$apiEndpoint}\n\n";

echo "This gives you the flexibility to build paths dynamically\n";
echo "without having to configure every possible alias combination!\n\n";

echo "\n=== Global Function Examples Complete ===\n";
