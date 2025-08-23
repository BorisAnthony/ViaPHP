<?php

declare(strict_types=1);

namespace ViaTests;

use Via\Via;

beforeEach(function () {
    Via::reset();
});

describe('Via Static Class', function () {
    it('works as a static class', function () {
        Via::setLocalPath('/test/path');
        expect(Via::getLocalPath())->toBe('/test/path');
        
        Via::setHost('example.com');
        expect(Via::getHost())->toBe('example.com');
    });

    it('returns all configured aliases with all() method', function () {
        Via::setLocalPath('/Users/test/project');
        Via::setHost('example.com');
        Via::setBase('data', 'data');
        Via::setBase('src', 'src');
        Via::assignToBase('logs', 'logs', 'data');
        Via::assignToBase('components', 'components', 'src');

        $all = Via::all();

        expect($all)->toHaveKeys(['data', 'src', 'data.logs', 'src.components']);
        
        expect($all['data'])->toBe([
            'rel' => '/data',
            'local' => '/Users/test/project/data',
            'host' => '//example.com/data'
        ]);
        
        expect($all['data.logs'])->toBe([
            'rel' => '/data/logs',
            'local' => '/Users/test/project/data/logs',
            'host' => '//example.com/data/logs'
        ]);
        
        expect($all['src.components'])->toBe([
            'rel' => '/src/components',
            'local' => '/Users/test/project/src/components',
            'host' => '//example.com/src/components'
        ]);
    });

    it('returns only rel paths when local/host not set', function () {
        Via::setBase('data', 'data');
        Via::assignToBase('logs', 'logs', 'data');

        $all = Via::all();

        expect($all['data'])->toBe(['rel' => '/data']);
        expect($all['data.logs'])->toBe(['rel' => '/data/logs']);
    });
});

describe('Local Path and Host Management', function () {
    it('sets and gets local path', function () {
        Via::setLocalPath('/Users/test/project');

        expect(Via::getLocalPath())->toBe('/Users/test/project');
    });

    it('canonicalizes local path', function () {
        Via::setLocalPath('/Users/test/../test/project/./');

        expect(Via::getLocalPath())->toBe('/Users/test/project');
    });

    it('sets and gets host', function () {
        Via::setHost('example.com');

        expect(Via::getHost())->toBe('example.com');
    });

    it('returns null when paths not set', function () {
        expect(Via::getLocalPath())->toBeNull();
        expect(Via::getHost())->toBeNull();
    });
});

describe('Base Management', function () {
    it('sets a single base', function () {
        Via::setBase('data', 'data');

        expect(Via::f('rel.data'))->toBe('/data');
    });

    it('sets multiple bases', function () {
        Via::setBases([
            ['role' => 'data', 'path' => 'data'],
            ['role' => 'src', 'path' => 'src'],
            ['role' => 'images', 'path' => 'assets/images']
        ]);

        expect(Via::f('rel.data'))->toBe('/data');
        expect(Via::f('rel.src'))->toBe('/src');
        expect(Via::f('rel.images'))->toBe('/assets/images');
    });

    it('validates bases array structure', function () {
        expect(fn () => Via::setBases([['role' => 'data']]))
            ->toThrow(\InvalidArgumentException::class, 'Each base must have "role" and "path" keys');

        expect(fn () => Via::setBases([['path' => 'data']]))
            ->toThrow(\InvalidArgumentException::class, 'Each base must have "role" and "path" keys');
    });
});

describe('Assignment Management', function () {
    beforeEach(function () {
        Via::setBase('data', 'data');
        Via::setBase('src', 'src');
    });

    it('assigns to a base', function () {
        Via::assignToBase('logs', 'logs', 'data');

        expect(Via::f('rel.data.logs'))->toBe('/data/logs');
    });

    it('assigns multiple to bases', function () {
        Via::assignToBases([
            ['role' => 'logs', 'path' => 'logs', 'baseRole' => 'data'],
            ['role' => 'cache', 'path' => 'cache', 'baseRole' => 'data'],
            ['role' => 'modules', 'path' => 'modules', 'baseRole' => 'src']
        ]);

        expect(Via::f('rel.data.logs'))->toBe('/data/logs');
        expect(Via::f('rel.data.cache'))->toBe('/data/cache');
        expect(Via::f('rel.src.modules'))->toBe('/src/modules');
    });

    it('validates base exists when assigning', function () {
        expect(fn () => Via::assignToBase('logs', 'logs', 'nonexistent'))
            ->toThrow(\InvalidArgumentException::class, "Base role 'nonexistent' does not exist");
    });

    it('validates assignments array structure', function () {
        expect(fn () => Via::assignToBases([['role' => 'logs', 'path' => 'logs']]))
            ->toThrow(\InvalidArgumentException::class, 'Each assignment must have "role", "path", and "baseRole" keys');
    });
});

describe('Initialization', function () {
    it('initializes with full config', function () {
        $config = [
            'LocalPath'      => '/Users/test/project',
            'absoluteDomain' => 'test.local',
            'bases'          => [
                ['role' => 'data', 'path' => 'data'],
                ['role' => 'src', 'path' => 'src']
            ],
            'assignments' => [
                ['role' => 'logs', 'path' => 'logs', 'baseRole' => 'data'],
                ['role' => 'modules', 'path' => 'modules', 'baseRole' => 'src']
            ]
        ];

        Via::init($config);

        expect(Via::getLocalPath())->toBe('/Users/test/project');
        expect(Via::getHost())->toBe('test.local');
        expect(Via::f('rel.data'))->toBe('/data');
        expect(Via::f('rel.data.logs'))->toBe('/data/logs');
        expect(Via::f('local.data.logs'))->toBe('/Users/test/project/data/logs');
        expect(Via::f('host.data.logs'))->toBe('//test.local/data/logs');
    });

    it('handles partial config', function () {
        Via::init(['bases' => [['role' => 'data', 'path' => 'data']]]);

        expect(Via::f('rel.data'))->toBe('/data');
        expect(Via::getLocalPath())->toBeNull();
        expect(Via::getHost())->toBeNull();
    });
});

describe('Path Retrieval', function () {
    beforeEach(function () {
        Via::setLocalPath('/Users/test/project');
        Via::setHost('example.com');
        Via::setBase('data', 'data');
        Via::setBase('src', 'src');
        Via::assignToBase('logs', 'logs', 'data');
        Via::assignToBase('frontend_js', 'frontend/js', 'src');
    });

    it('retrieves relative paths', function () {
        expect(Via::f('rel.data'))->toBe('/data');
        expect(Via::f('rel.src'))->toBe('/src');
        expect(Via::f('rel.data.logs'))->toBe('/data/logs');
        expect(Via::f('rel.src.frontend_js'))->toBe('/src/frontend/js');
    });

    it('retrieves local paths', function () {
        expect(Via::f('local.data'))->toBe('/Users/test/project/data');
        expect(Via::f('local.data.logs'))->toBe('/Users/test/project/data/logs');
        expect(Via::f('local.src.frontend_js'))->toBe('/Users/test/project/src/frontend/js');
    });

    it('retrieves host paths', function () {
        expect(Via::f('host.data'))->toBe('//example.com/data');
        expect(Via::f('host.data.logs'))->toBe('//example.com/data/logs');
        expect(Via::f('host.src.frontend_js'))->toBe('//example.com/src/frontend/js');
    });

    it('handles configured nested paths properly', function () {
        // Set up nested configurations for the test
        Via::assignToBase('subdir', 'subdir', 'data');

        expect(Via::f('rel.data.subdir'))->toBe('/data/subdir');
        expect(Via::f('local.data.subdir'))->toBe('/Users/test/project/data/subdir');
        expect(Via::f('host.data.subdir'))->toBe('//example.com/data/subdir');
    });

    it('validates path format', function () {
        expect(fn () => Via::f('invalid'))
            ->toThrow(\InvalidArgumentException::class, 'Path must contain at least type and role');

        expect(fn () => Via::f('invalid.type.role'))
            ->toThrow(\InvalidArgumentException::class, "Invalid path type 'invalid'. Must be 'rel', 'local', or 'host'");
    });

    it('validates role exists', function () {
        expect(fn () => Via::f('rel.nonexistent'))
            ->toThrow(\InvalidArgumentException::class, "Role 'nonexistent' must be a base. Assignments must be accessed via base.assignment format");
    });

    it('requires local path for local type', function () {
        Via::reset(); // Ensure localPath is null

        expect(fn () => Via::f('local.data'))
            ->toThrow(\RuntimeException::class, 'Local path not set. Call setLocalPath() first.');
    });

    it('requires host for host type', function () {
        Via::reset(); // Ensure host is null

        expect(fn () => Via::f('host.data'))
            ->toThrow(\RuntimeException::class, 'Host not set. Call setHost() first.');
    });
});

describe('Strict Path Validation', function () {
    beforeEach(function () {
        Via::setLocalPath('/Users/test/project');
        Via::setHost('example.com');
        Via::setBase('data', 'data');
        Via::setBase('src', 'src');
        Via::setBase('logs', 'logs');
        Via::assignToBase('app_data', 'app', 'data');
        Via::assignToBase('components', 'components', 'src');
    });

    it('allows valid single-level paths', function () {
        expect(Via::f('rel.data'))->toBe('/data');
        expect(Via::f('rel.logs'))->toBe('/logs');
        expect(Via::f('rel.data.app_data'))->toBe('/data/app');
    });

    it('allows valid nested configured paths', function () {
        // Set up nested assignments to proper bases
        Via::assignToBase('error_logs', 'errors', 'logs');
        Via::assignToBase('ui_components', 'ui', 'src');

        expect(Via::f('rel.logs.error_logs'))->toBe('/logs/errors');
        expect(Via::f('local.src.ui_components'))->toBe('/Users/test/project/src/ui');
        expect(Via::f('host.logs.error_logs'))->toBe('//example.com/logs/errors');
    });

    it('rejects arbitrary path segments', function () {
        expect(fn () => Via::f('rel.data.arbitrary'))
            ->toThrow(\InvalidArgumentException::class, "Path segment 'arbitrary' not found as assignment under 'data'");

        expect(fn () => Via::f('local.src.random.path'))
            ->toThrow(\InvalidArgumentException::class, "Path segment 'random' not found as assignment under 'src'");

        expect(fn () => Via::f('host.logs.nonexistent'))
            ->toThrow(\InvalidArgumentException::class, "Path segment 'nonexistent' not found as assignment under 'logs'");
    });

    it('validates each segment in a multi-level path', function () {
        // Set up a configuration
        Via::assignToBase('app_logs', 'app', 'logs');

        expect(Via::f('rel.logs.app_logs'))->toBe('/logs/app');

        // This should fail because 'invalid' is not configured at any level
        expect(fn () => Via::f('rel.logs.app_logs.invalid'))
            ->toThrow(\InvalidArgumentException::class, "Path segment 'invalid' not found as assignment under 'app_logs'");
    });

    it('maintains validation across all path types', function () {
        $invalidPath      = 'rel.data.invalid.path';
        $localInvalidPath = str_replace('rel.', 'local.', $invalidPath);
        $hostInvalidPath  = str_replace('rel.', 'host.', $invalidPath);

        expect(fn () => Via::f($invalidPath))
            ->toThrow(\InvalidArgumentException::class);

        expect(fn () => Via::f($localInvalidPath))
            ->toThrow(\InvalidArgumentException::class);

        expect(fn () => Via::f($hostInvalidPath))
            ->toThrow(\InvalidArgumentException::class);
    });

    it('provides clear error messages for invalid segments', function () {
        expect(fn () => Via::f('rel.data.invalid.segment'))
            ->toThrow(\InvalidArgumentException::class, "Path segment 'invalid' not found as assignment under 'data'");
    });
});

describe('Cross-Platform Path Handling', function () {
    beforeEach(function () {
        Via::setLocalPath('/Users/test/project');
        Via::setBase('src', 'src');
    });

    it('handles multi-level paths with forward slashes', function () {
        Via::assignToBase('coremods', 'core/modules', 'src');
        
        expect(Via::f('rel.src.coremods'))->toBe('/src/core/modules');
        expect(Via::f('local.src.coremods'))->toBe('/Users/test/project/src/core/modules');
    });

    it('handles multi-level paths with mixed separators', function () {
        // Test with various path separators that might come from different sources
        Via::assignToBase('deeppath', 'level1\level2/level3', 'src');
        
        expect(Via::f('rel.src.deeppath'))->toBe('/src/level1/level2/level3');
        expect(Via::f('local.src.deeppath'))->toBe('/Users/test/project/src/level1/level2/level3');
    });

    it('canonicalizes paths with redundant separators', function () {
        Via::assignToBase('messypath', 'dir1\/\dir2/../dir2/dir3', 'src');
        
        expect(Via::f('rel.src.messypath'))->toBe('/src/dir1/dir2/dir3');
        expect(Via::f('local.src.messypath'))->toBe('/Users/test/project/src/dir1/dir2/dir3');
    });

    it('handles base paths with various separators', function () {
        Via::setBase('assets', 'public\assets');
        Via::assignToBase('images', 'img\gallery', 'assets');
        
        expect(Via::f('rel.assets.images'))->toBe('/public/assets/img/gallery');
    });
});
