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
        $cssPath = via('local.assets.css', 'main.css');
        $imagePath = via('rel.assets', 'images/logo.png');
        
        expect($cssPath)->toBe('/project/public/assets/styles/main.css');
        expect($imagePath)->toBe('/public/assets/images/logo.png');
    });
});