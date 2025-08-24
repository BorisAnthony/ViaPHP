<?php

declare(strict_types=1);

namespace ViaTests;

use Via\Via;

beforeEach(function () {
    Via::reset();
});

describe('Via Static Class', function () {
    it('works as a static class', function () {
        Via::setLocal('/test/path');
        expect(Via::getLocal())->toBe('/test/path');

        Via::setHost('example.com');
        expect(Via::getHost())->toBe('example.com');
    });

    it('returns all configured aliases with all() method', function () {
        Via::setLocal('/Users/test/project');
        Via::setHost('example.com');
        Via::setBase('data', 'data');
        Via::setBase('src', 'src');
        Via::assignToBase('logs', 'logs', 'data');
        Via::assignToBase('components', 'components', 'src');

        $all = Via::all();

        expect($all)->toHaveKeys(['data', 'src', 'data.logs', 'src.components']);

        expect($all['data'])->toBe([
            'rel'   => '/data',
            'local' => '/Users/test/project/data',
            'host'  => '//example.com/data'
        ]);

        expect($all['data.logs'])->toBe([
            'rel'   => '/data/logs',
            'local' => '/Users/test/project/data/logs',
            'host'  => '//example.com/data/logs'
        ]);

        expect($all['src.components'])->toBe([
            'rel'   => '/src/components',
            'local' => '/Users/test/project/src/components',
            'host'  => '//example.com/src/components'
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
        Via::setLocal('/Users/test/project');

        expect(Via::getLocal())->toBe('/Users/test/project');
    });

    it('canonicalizes local path', function () {
        Via::setLocal('/Users/test/../test/project/./');

        expect(Via::getLocal())->toBe('/Users/test/project');
    });

    it('sets and gets host', function () {
        Via::setHost('example.com');

        expect(Via::getHost())->toBe('example.com');
    });

    it('returns null when paths not set', function () {
        expect(Via::getLocal())->toBeNull();
        expect(Via::getHost())->toBeNull();
    });
});

describe('Base Management', function () {
    it('sets a single base', function () {
        Via::setBase('data', 'data');

        expect(Via::p('rel.data'))->toBe('/data');
    });

    it('sets multiple bases', function () {
        Via::setBases([
            ['alias' => 'data', 'path' => 'data'],
            ['alias' => 'src', 'path' => 'src'],
            ['alias' => 'images', 'path' => 'assets/images']
        ]);

        expect(Via::p('rel.data'))->toBe('/data');
        expect(Via::p('rel.src'))->toBe('/src');
        expect(Via::p('rel.images'))->toBe('/assets/images');
    });

    it('validates bases array structure', function () {
        expect(fn () => Via::setBases([['alias' => 'data']]))
            ->toThrow(\InvalidArgumentException::class, 'Each base must have "alias" and "path" keys or be a positional array [alias, path]');

        expect(fn () => Via::setBases([['path' => 'data']]))
            ->toThrow(\InvalidArgumentException::class, 'Each base must have "alias" and "path" keys or be a positional array [alias, path]');
    });

    it('sets bases using positional arrays', function () {
        Via::setBases([
            ['data', 'data'],
            ['src', 'src'],
            ['images', 'assets/images']
        ]);

        expect(Via::p('rel.data'))->toBe('/data');
        expect(Via::p('rel.src'))->toBe('/src');
        expect(Via::p('rel.images'))->toBe('/assets/images');
    });

    it('sets bases using mixed array formats', function () {
        Via::setBases([
            ['data', 'data'], // positional
            ['alias' => 'src', 'path' => 'src'], // associative
            ['assets', 'public/assets'] // positional
        ]);

        expect(Via::p('rel.data'))->toBe('/data');
        expect(Via::p('rel.src'))->toBe('/src');
        expect(Via::p('rel.assets'))->toBe('/public/assets');
    });
});

describe('Assignment Management', function () {
    beforeEach(function () {
        Via::setBase('data', 'data');
        Via::setBase('src', 'src');
    });

    it('assigns to a base', function () {
        Via::assignToBase('logs', 'logs', 'data');

        expect(Via::p('rel.data.logs'))->toBe('/data/logs');
    });

    it('assigns multiple to bases', function () {
        Via::assignToBases([
            ['alias' => 'logs', 'path' => 'logs', 'baseAlias' => 'data'],
            ['alias' => 'cache', 'path' => 'cache', 'baseAlias' => 'data'],
            ['alias' => 'modules', 'path' => 'modules', 'baseAlias' => 'src']
        ]);

        expect(Via::p('rel.data.logs'))->toBe('/data/logs');
        expect(Via::p('rel.data.cache'))->toBe('/data/cache');
        expect(Via::p('rel.src.modules'))->toBe('/src/modules');
    });

    it('validates base exists when assigning', function () {
        expect(fn () => Via::assignToBase('logs', 'logs', 'nonexistent'))
            ->toThrow(\InvalidArgumentException::class, "Base alias 'nonexistent' does not exist");
    });

    it('validates assignments array structure', function () {
        expect(fn () => Via::assignToBases([['alias' => 'logs', 'path' => 'logs']]))
            ->toThrow(\InvalidArgumentException::class, 'Each assignment must have "alias", "path", and "baseAlias" keys or be a positional array [alias, path, baseAlias]');
    });

    it('assigns using positional arrays', function () {
        Via::assignToBases([
            ['logs', 'logs', 'data'],
            ['cache', 'cache', 'data'],
            ['modules', 'modules', 'src']
        ]);

        expect(Via::p('rel.data.logs'))->toBe('/data/logs');
        expect(Via::p('rel.data.cache'))->toBe('/data/cache');
        expect(Via::p('rel.src.modules'))->toBe('/src/modules');
    });

    it('assigns using mixed array formats', function () {
        Via::assignToBases([
            ['logs', 'logs', 'data'], // positional
            ['alias' => 'cache', 'path' => 'cache', 'baseAlias' => 'data'], // associative
            ['modules', 'modules', 'src'] // positional
        ]);

        expect(Via::p('rel.data.logs'))->toBe('/data/logs');
        expect(Via::p('rel.data.cache'))->toBe('/data/cache');
        expect(Via::p('rel.src.modules'))->toBe('/src/modules');
    });
});

describe('Initialization', function () {
    it('initializes with full config', function () {
        $config = [
            'Local'      => '/Users/test/project',
            'absoluteDomain' => 'test.local',
            'bases'          => [
                ['alias' => 'data', 'path' => 'data'],
                ['alias' => 'src', 'path' => 'src']
            ],
            'assignments' => [
                ['alias' => 'logs', 'path' => 'logs', 'baseAlias' => 'data'],
                ['alias' => 'modules', 'path' => 'modules', 'baseAlias' => 'src']
            ]
        ];

        Via::init($config);

        expect(Via::getLocal())->toBe('/Users/test/project');
        expect(Via::getHost())->toBe('test.local');
        expect(Via::p('rel.data'))->toBe('/data');
        expect(Via::p('rel.data.logs'))->toBe('/data/logs');
        expect(Via::p('local.data.logs'))->toBe('/Users/test/project/data/logs');
        expect(Via::p('host.data.logs'))->toBe('//test.local/data/logs');
    });

    it('handles partial config', function () {
        Via::init(['bases' => [['alias' => 'data', 'path' => 'data']]]);

        expect(Via::p('rel.data'))->toBe('/data');
        expect(Via::getLocal())->toBeNull();
        expect(Via::getHost())->toBeNull();
    });
});

describe('Path Retrieval', function () {
    beforeEach(function () {
        Via::setLocal('/Users/test/project');
        Via::setHost('example.com');
        Via::setBase('data', 'data');
        Via::setBase('src', 'src');
        Via::assignToBase('logs', 'logs', 'data');
        Via::assignToBase('frontend_js', 'frontend/js', 'src');
    });

    it('retrieves relative paths', function () {
        expect(Via::p('rel.data'))->toBe('/data');
        expect(Via::p('rel.src'))->toBe('/src');
        expect(Via::p('rel.data.logs'))->toBe('/data/logs');
        expect(Via::p('rel.src.frontend_js'))->toBe('/src/frontend/js');
    });

    it('retrieves local paths', function () {
        expect(Via::p('local.data'))->toBe('/Users/test/project/data');
        expect(Via::p('local.data.logs'))->toBe('/Users/test/project/data/logs');
        expect(Via::p('local.src.frontend_js'))->toBe('/Users/test/project/src/frontend/js');
    });

    it('retrieves host paths', function () {
        expect(Via::p('host.data'))->toBe('//example.com/data');
        expect(Via::p('host.data.logs'))->toBe('//example.com/data/logs');
        expect(Via::p('host.src.frontend_js'))->toBe('//example.com/src/frontend/js');
    });

    it('handles configured nested paths properly', function () {
        // Set up nested configurations for the test
        Via::assignToBase('subdir', 'subdir', 'data');

        expect(Via::p('rel.data.subdir'))->toBe('/data/subdir');
        expect(Via::p('local.data.subdir'))->toBe('/Users/test/project/data/subdir');
        expect(Via::p('host.data.subdir'))->toBe('//example.com/data/subdir');
    });

    it('validates path format', function () {
        expect(fn () => Via::p('invalid'))
            ->toThrow(\InvalidArgumentException::class, 'Path must contain at least type and alias');

        expect(fn () => Via::p('invalid.type.alias'))
            ->toThrow(\InvalidArgumentException::class, "Invalid path type 'invalid'. Must be 'rel', 'local', or 'host'");
    });

    it('validates alias exists', function () {
        expect(fn () => Via::p('rel.nonexistent'))
            ->toThrow(\InvalidArgumentException::class, "Alias 'nonexistent' must be a base. Assignments must be accessed via base.assignment format");
    });

    it('requires local path for local type', function () {
        Via::reset(); // Ensure local is null

        expect(fn () => Via::p('local.data'))
            ->toThrow(\RuntimeException::class, 'Local path not set. Call setLocal() first.');
    });

    it('requires host for host type', function () {
        Via::reset(); // Ensure host is null

        expect(fn () => Via::p('host.data'))
            ->toThrow(\RuntimeException::class, 'Host not set. Call setHost() first.');
    });
});

describe('Strict Path Validation', function () {
    beforeEach(function () {
        Via::setLocal('/Users/test/project');
        Via::setHost('example.com');
        Via::setBase('data', 'data');
        Via::setBase('src', 'src');
        Via::setBase('logs', 'logs');
        Via::assignToBase('app_data', 'app', 'data');
        Via::assignToBase('components', 'components', 'src');
    });

    it('allows valid single-level paths', function () {
        expect(Via::p('rel.data'))->toBe('/data');
        expect(Via::p('rel.logs'))->toBe('/logs');
        expect(Via::p('rel.data.app_data'))->toBe('/data/app');
    });

    it('allows valid nested configured paths', function () {
        // Set up nested assignments to proper bases
        Via::assignToBase('error_logs', 'errors', 'logs');
        Via::assignToBase('ui_components', 'ui', 'src');

        expect(Via::p('rel.logs.error_logs'))->toBe('/logs/errors');
        expect(Via::p('local.src.ui_components'))->toBe('/Users/test/project/src/ui');
        expect(Via::p('host.logs.error_logs'))->toBe('//example.com/logs/errors');
    });

    it('rejects arbitrary path segments', function () {
        expect(fn () => Via::p('rel.data.arbitrary'))
            ->toThrow(\InvalidArgumentException::class, "Path segment 'arbitrary' not found as assignment under 'data'");

        expect(fn () => Via::p('local.src.random.path'))
            ->toThrow(\InvalidArgumentException::class, "Path segment 'random' not found as assignment under 'src'");

        expect(fn () => Via::p('host.logs.nonexistent'))
            ->toThrow(\InvalidArgumentException::class, "Path segment 'nonexistent' not found as assignment under 'logs'");
    });

    it('validates each segment in a multi-level path', function () {
        // Set up a configuration
        Via::assignToBase('app_logs', 'app', 'logs');

        expect(Via::p('rel.logs.app_logs'))->toBe('/logs/app');

        // This should fail because 'invalid' is not configured at any level
        expect(fn () => Via::p('rel.logs.app_logs.invalid'))
            ->toThrow(\InvalidArgumentException::class, "Path segment 'invalid' not found as assignment under 'app_logs'");
    });

    it('maintains validation across all path types', function () {
        $invalidPath      = 'rel.data.invalid.path';
        $localInvalidPath = str_replace('rel.', 'local.', $invalidPath);
        $hostInvalidPath  = str_replace('rel.', 'host.', $invalidPath);

        expect(fn () => Via::p($invalidPath))
            ->toThrow(\InvalidArgumentException::class);

        expect(fn () => Via::p($localInvalidPath))
            ->toThrow(\InvalidArgumentException::class);

        expect(fn () => Via::p($hostInvalidPath))
            ->toThrow(\InvalidArgumentException::class);
    });

    it('provides clear error messages for invalid segments', function () {
        expect(fn () => Via::p('rel.data.invalid.segment'))
            ->toThrow(\InvalidArgumentException::class, "Path segment 'invalid' not found as assignment under 'data'");
    });
});

describe('Cross-Platform Path Handling', function () {
    beforeEach(function () {
        Via::setLocal('/Users/test/project');
        Via::setBase('src', 'src');
    });

    it('handles multi-level paths with forward slashes', function () {
        Via::assignToBase('coremods', 'core/modules', 'src');

        expect(Via::p('rel.src.coremods'))->toBe('/src/core/modules');
        expect(Via::p('local.src.coremods'))->toBe('/Users/test/project/src/core/modules');
    });

    it('handles multi-level paths with mixed separators', function () {
        // Test with various path separators that might come from different sources
        Via::assignToBase('deeppath', 'level1\level2/level3', 'src');

        expect(Via::p('rel.src.deeppath'))->toBe('/src/level1/level2/level3');
        expect(Via::p('local.src.deeppath'))->toBe('/Users/test/project/src/level1/level2/level3');
    });

    it('canonicalizes paths with redundant separators', function () {
        Via::assignToBase('messypath', 'dir1\/\dir2/../dir2/dir3', 'src');

        expect(Via::p('rel.src.messypath'))->toBe('/src/dir1/dir2/dir3');
        expect(Via::p('local.src.messypath'))->toBe('/Users/test/project/src/dir1/dir2/dir3');
    });

    it('handles base paths with various separators', function () {
        Via::setBase('assets', 'public\assets');
        Via::assignToBase('images', 'img\gallery', 'assets');

        expect(Via::p('rel.assets.images'))->toBe('/public/assets/img/gallery');
    });
});

describe('Additional Path Parameter', function () {
    beforeEach(function () {
        Via::setLocal('/Users/test/project');
        Via::setHost('example.com');
        Via::setBase('data', 'data');
        Via::setBase('src', 'src');
        Via::assignToBase('logs', 'logs', 'data');
        Via::assignToBase('components', 'components', 'src');
    });

    it('appends additional path to base paths', function () {
        expect(Via::p('rel.data', 'config/settings.json'))->toBe('/data/config/settings.json');
        expect(Via::p('rel.src', 'utils/helpers.php'))->toBe('/src/utils/helpers.php');
    });

    it('appends additional path to assignment paths', function () {
        expect(Via::p('rel.data.logs', 'error/2024-01-01.log'))->toBe('/data/logs/error/2024-01-01.log');
        expect(Via::p('rel.src.components', 'ui/Button.php'))->toBe('/src/components/ui/Button.php');
    });

    it('works with local paths', function () {
        expect(Via::p('local.data', 'uploads/images'))->toBe('/Users/test/project/data/uploads/images');
        expect(Via::p('local.data.logs', 'debug/trace.log'))->toBe('/Users/test/project/data/logs/debug/trace.log');
        expect(Via::p('local.src.components', 'forms/LoginForm.php'))->toBe('/Users/test/project/src/components/forms/LoginForm.php');
    });

    it('works with host paths', function () {
        expect(Via::p('host.data', 'public/assets'))->toBe('//example.com/data/public/assets');
        expect(Via::p('host.data.logs', 'api/requests.log'))->toBe('//example.com/data/logs/api/requests.log');
        expect(Via::p('host.src.components', 'widgets/Calendar.php'))->toBe('//example.com/src/components/widgets/Calendar.php');
    });

    it('canonicalizes additional paths', function () {
        expect(Via::p('rel.data', 'uploads/../temp/file.txt'))->toBe('/data/temp/file.txt');
        expect(Via::p('local.src', './utils//helpers.php'))->toBe('/Users/test/project/src/utils/helpers.php');
        expect(Via::p('host.data.logs', 'dir1/./dir2/../final/'))->toBe('//example.com/data/logs/dir1/final');
    });

    it('handles various path separators in additional paths', function () {
        expect(Via::p('rel.data', 'uploads\\images/gallery'))->toBe('/data/uploads/images/gallery');
        expect(Via::p('local.src', 'modules\\auth/controllers'))->toBe('/Users/test/project/src/modules/auth/controllers');
        expect(Via::p('host.data.logs', 'app\\errors/critical.log'))->toBe('//example.com/data/logs/app/errors/critical.log');
    });

    it('works with null additional path (backwards compatibility)', function () {
        expect(Via::p('rel.data', null))->toBe('/data');
        expect(Via::p('local.src.components', null))->toBe('/Users/test/project/src/components');
        expect(Via::p('host.data.logs'))->toBe('//example.com/data/logs');
    });

    it('works with get() method as well as p() shorthand', function () {
        expect(Via::get('rel.data', 'config/app.php'))->toBe('/data/config/app.php');
        expect(Via::get('local.data.logs', 'error.log'))->toBe('/Users/test/project/data/logs/error.log');
        expect(Via::get('host.src.components', 'ui/Modal.php'))->toBe('//example.com/src/components/ui/Modal.php');
    });

    it('handles empty additional path strings', function () {
        expect(Via::p('rel.data', ''))->toBe('/data');
        expect(Via::p('local.src', ''))->toBe('/Users/test/project/src');
        expect(Via::p('host.data.logs', ''))->toBe('//example.com/data/logs');
    });

    it('preserves original path validation with additional paths', function () {
        expect(fn () => Via::p('rel.nonexistent', 'some/file.txt'))
            ->toThrow(\InvalidArgumentException::class, "Alias 'nonexistent' must be a base. Assignments must be accessed via base.assignment format");

        expect(fn () => Via::p('invalid.data', 'some/file.txt'))
            ->toThrow(\InvalidArgumentException::class, "Invalid path type 'invalid'. Must be 'rel', 'local', or 'host'");
    });

    it('requires local path for local type even with additional path', function () {
        Via::reset();
        Via::setBase('data', 'data');

        expect(fn () => Via::p('local.data', 'file.txt'))
            ->toThrow(\RuntimeException::class, 'Local path not set. Call setLocal() first.');
    });

    it('requires host for host type even with additional path', function () {
        Via::reset();
        Via::setBase('data', 'data');

        expect(fn () => Via::p('host.data', 'file.txt'))
            ->toThrow(\RuntimeException::class, 'Host not set. Call setHost() first.');
    });
});
