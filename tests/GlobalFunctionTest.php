<?php

declare(strict_types=1);

namespace ViaTests;

use Via\Via;

beforeEach(function () {
    Via::reset();
});

describe('Global via() function', function () {
    it('exists and is callable', function () {
        expect(function_exists('via'))->toBeTrue();
        expect(is_callable('via'))->toBeTrue();
    });

    it('forwards to Via::get() with single parameter', function () {
        Via::setLocal('/project');
        Via::setBase('data', '/var/data');

        expect(via('local.data'))->toBe('/project/var/data');
        expect(Via::get('local.data'))->toBe('/project/var/data');
    });

    it('forwards to Via::get() with additional path parameter', function () {
        Via::setLocal('/project');
        Via::setBase('logs', '/var/logs');

        expect(via('local.logs', 'error/today.log'))->toBe('/project/var/logs/error/today.log');
        expect(Via::get('local.logs', 'error/today.log'))->toBe('/project/var/logs/error/today.log');
    });

    it('works with all path types', function () {
        Via::setLocal('/project');
        Via::setHost('example.com');
        Via::setBases([
            ['data', '/var/data'],
            ['src', '/src']
        ]);

        expect(via('rel.data'))->toBe('/var/data');
        expect(via('local.src'))->toBe('/project/src');
        expect(via('host.data'))->toBe('//example.com/var/data');
    });

    it('works in template-like contexts', function () {
        Via::setLocal('/project');
        Via::setBases([['assets', '/public/assets']]);
        Via::assignToBases([['css', '/styles', 'assets']]);

        // Simulate template usage
        $cssPath   = via('local.assets.css', 'main.css');
        $imagePath = via('rel.assets', 'images/logo.png');

        expect($cssPath)->toBe('/project/public/assets/styles/main.css');
        expect($imagePath)->toBe('/public/assets/images/logo.png');
    });
});

describe('Global via_local() and via_host() functions', function () {
    it('global functions exist and are callable', function () {
        expect(function_exists('via_local'))->toBeTrue();
        expect(function_exists('via_host'))->toBeTrue();
        expect(is_callable('via_local'))->toBeTrue();
        expect(is_callable('via_host'))->toBeTrue();
    });

    it('via_local() forwards to Via::getLocal()', function () {
        Via::setLocal('/test/project');

        expect(via_local())->toBe('/test/project');
        expect(via_local())->toBe(Via::getLocal());
    });

    it('via_host() forwards to Via::getHost()', function () {
        Via::setHost('example.test');

        expect(via_host())->toBe('example.test');
        expect(via_host())->toBe(Via::getHost());
    });

    it('global functions return null when not set', function () {
        Via::reset();

        expect(via_local())->toBeNull();
        expect(via_host())->toBeNull();
    });

    it('works with path configuration changes', function () {
        Via::setLocal('/initial/path');
        Via::setHost('initial.com');

        expect(via_local())->toBe('/initial/path');
        expect(via_host())->toBe('initial.com');

        // Change paths
        Via::setLocal('/updated/path');
        Via::setHost('updated.com');

        expect(via_local())->toBe('/updated/path');
        expect(via_host())->toBe('updated.com');
    });

    it('via_local() and via_host() accept additional path parameters', function () {
        Via::setLocal('/test/project');
        Via::setHost('example.com');

        expect(via_local('config/database.php'))->toBe('/test/project/config/database.php');
        expect(via_local('uploads/images'))->toBe('/test/project/uploads/images');

        expect(via_host('api/v1/users'))->toBe('example.com/api/v1/users');
        expect(via_host('assets/styles'))->toBe('example.com/assets/styles');

        // Test canonicalization
        expect(via_local('cache/../temp//file.txt'))->toBe('/test/project/temp/file.txt');
        expect(via_host('dir1/./dir2/../final/'))->toBe('example.com/dir1/final');
    });

    it('global functions work with null and empty additional paths', function () {
        Via::setLocal('/test/project');
        Via::setHost('example.com');

        expect(via_local(null))->toBe('/test/project');
        expect(via_local(''))->toBe('/test/project');
        expect(via_host(null))->toBe('example.com');
        expect(via_host(''))->toBe('example.com');
    });

    it('global functions forward additional paths correctly', function () {
        Via::setLocal('/test/project');
        Via::setHost('example.com');

        $additionalPath = 'some/nested/path.txt';

        expect(via_local($additionalPath))->toBe(Via::getLocal($additionalPath));
        expect(via_host($additionalPath))->toBe(Via::getHost($additionalPath));
    });
});

describe('Global via_join() function', function () {
    it('exists and is callable', function () {
        expect(function_exists('via_join'))->toBeTrue();
        expect(is_callable('via_join'))->toBeTrue();
    });

    it('forwards to Via::j() with identical results', function () {
        $basePath       = '/base/path';
        $additionalPath = 'subdir/file.txt';

        expect(via_join($basePath, $additionalPath))->toBe(Via::j($basePath, $additionalPath));
        expect(via_join($basePath, $additionalPath))->toBe('/base/path/subdir/file.txt');
    });

    it('joins paths correctly', function () {
        expect(via_join('/usr/local', 'bin/php'))->toBe('/usr/local/bin/php');
        expect(via_join('relative/path', 'more/segments'))->toBe('relative/path/more/segments');
        expect(via_join('/project/data', 'uploads/images'))->toBe('/project/data/uploads/images');
    });

    it('handles null additional path', function () {
        expect(via_join('/base/path', null))->toBe('/base/path');
        expect(via_join('relative/path', null))->toBe('relative/path');
        expect(via_join('/', null))->toBe('/');
    });

    it('handles empty additional path', function () {
        expect(via_join('/base/path', ''))->toBe('/base/path');
        expect(via_join('relative/path', ''))->toBe('relative/path');
        expect(via_join('/', ''))->toBe('/');
    });

    it('canonicalizes joined paths', function () {
        expect(via_join('/base/path', '../parent/file.txt'))->toBe('/base/parent/file.txt');
        expect(via_join('/base/path', './current//file.txt'))->toBe('/base/path/current/file.txt');
        expect(via_join('/base/path', 'sub/../final/'))->toBe('/base/path/final');
    });

    it('handles various path separators', function () {
        expect(via_join('/base/path', 'subdir\\file.txt'))->toBe('/base/path/subdir/file.txt');
        expect(via_join('C:\\base\\path', 'subdir/file.txt'))->toBe('C:/base/path/subdir/file.txt');
        expect(via_join('/base/path/', '\\subdir\\file.txt'))->toBe('/base/path/subdir/file.txt');
    });

    it('works with complex path structures', function () {
        expect(via_join('/project/src', 'components/ui/Button.php'))->toBe('/project/src/components/ui/Button.php');
        expect(via_join('/var/www', '../logs/error.log'))->toBe('/var/logs/error.log');
        expect(via_join('/base', 'dir1/./dir2/../dir3/file.txt'))->toBe('/base/dir1/dir3/file.txt');
    });

    it('works with URL-style paths', function () {
        expect(via_join('//example.com/path', 'subdir/file.txt'))->toBe('/example.com/path/subdir/file.txt');
        expect(via_join('//cdn.example.com', 'assets/images/logo.png'))->toBe('/cdn.example.com/assets/images/logo.png');
    });

    it('handles special characters in paths', function () {
        expect(via_join('/base/path', 'file with spaces.txt'))->toBe('/base/path/file with spaces.txt');
        expect(via_join('/base/path', 'file-with-dashes.txt'))->toBe('/base/path/file-with-dashes.txt');
        expect(via_join('/base/path', 'file_with_underscores.txt'))->toBe('/base/path/file_with_underscores.txt');
    });

    it('can be used independently of Via configuration', function () {
        expect(via_join('/tmp', 'cache/sessions'))->toBe('/tmp/cache/sessions');
        expect(via_join('/usr/local/bin', '../lib/python3.9'))->toBe('/usr/local/lib/python3.9');
        // Symfony's Path canonicalization expands ~ to actual home directory
        expect(via_join('~/Documents', 'Projects/MyApp'))->toContain('Documents/Projects/MyApp');
    });

    it('is perfect for template usage', function () {
        $baseAssets = '/public/assets';
        $cssFile    = 'css/main.css';
        $jsFile     = 'js/app.min.js';

        expect(via_join($baseAssets, $cssFile))->toBe('/public/assets/css/main.css');
        expect(via_join($baseAssets, $jsFile))->toBe('/public/assets/js/app.min.js');

        $baseUploads = '/storage/uploads';
        $userFolder  = 'users/' . md5('user123');
        $avatarFile  = 'avatar.jpg';

        $userPath   = via_join($baseUploads, $userFolder);
        $avatarPath = via_join($userPath, $avatarFile);

        $expectedUserHash = md5('user123');
        expect($userPath)->toBe("/storage/uploads/users/{$expectedUserHash}");
        expect($avatarPath)->toBe("/storage/uploads/users/{$expectedUserHash}/avatar.jpg");
    });

    it('maintains equivalence with Via::j() across all scenarios', function () {
        $testCases = [
            ['/base/path', 'subdir/file.txt'],
            ['/usr/local', 'bin/php'],
            ['relative/path', 'more/segments'],
            ['/base/path', null],
            ['relative/path', ''],
            ['/base/path', '../parent/file.txt'],
            ['/base/path', 'subdir\\file.txt'],
            ['//example.com/path', 'subdir/file.txt'],
            ['/tmp', 'cache/sessions']
        ];

        foreach ($testCases as [$base, $additional]) {
            $viaJoinResult = via_join($base, $additional);
            $viaJResult    = Via::j($base, $additional);

            expect($viaJoinResult)->toBe($viaJResult, "Failed for base: '{$base}', additional: '{$additional}'");
        }
    });

    it('works in practical scenarios like the other global functions', function () {
        $projectRoot = '/Users/demo/myapp';
        $dataDir     = 'data';
        $configDir   = 'config';
        $logDir      = 'logs';

        $configPath = via_join(via_join($projectRoot, $dataDir), $configDir);
        $logPath    = via_join(via_join($projectRoot, $dataDir), $logDir);

        expect($configPath)->toBe('/Users/demo/myapp/data/config');
        expect($logPath)->toBe('/Users/demo/myapp/data/logs');

        $todayLog = via_join($logPath, date('Y-m-d') . '.log');
        expect($todayLog)->toMatch('#^/Users/demo/myapp/data/logs/\d{4}-\d{2}-\d{2}\.log$#');

        $expectedDate = date('Y-m-d');
        expect($todayLog)->toBe("/Users/demo/myapp/data/logs/{$expectedDate}.log");
    });

    it('is safe from function redefinition conflicts', function () {
        expect(function_exists('via_join'))->toBeTrue();

        $result = via_join('/test/path', 'subdir');
        expect($result)->toBe('/test/path/subdir');

        expect(is_callable('via_join'))->toBeTrue();
    });
});
