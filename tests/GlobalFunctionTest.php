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
