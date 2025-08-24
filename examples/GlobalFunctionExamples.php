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

Via::setLocalPath('/Users/demo/myapp');
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

$cssUrl = via('host.public.assets', 'styles/main.css');
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
$errorLog = via('local.data.logs', 'errors/' . date('Y-m-d') . '.log');
$userAvatar = via('local.data.user_files', 'avatars/user_123.jpg');
$configFile = via('rel.data', 'config/app.json');

echo "File operations:\n";
echo "- Error log: {$errorLog}\n";
echo "- User avatar: {$userAvatar}\n";  
echo "- Config file: {$configFile}\n\n";

// URL generation
$jsAsset = via('host.public.assets', 'js/app.min.js');
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
$dbConfig = via('local.data', 'config/database.php');
$appConfig = via('local.data', 'config/app.php');
echo "- DB config: {$dbConfig}\n";
echo "- App config: {$appConfig}\n\n";

echo "Runtime files:\n"; 
$cacheFile = via('local.data.cache', 'views/' . md5('homepage') . '.cache');
$sessionFile = via('local.data.sessions', 'sess_' . session_id());
echo "- Cache file: {$cacheFile}\n";
echo "- Session file: {$sessionFile}\n\n";

echo "Template rendering:\n";
$mailTemplate = via('local.src.mail_templates', 'welcome.html');
$userWelcome = via('local.src.mail_templates', 'users/activation.html');
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

echo "=== Global Function Examples Complete ===\n";