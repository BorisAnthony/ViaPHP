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
});
