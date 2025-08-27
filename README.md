# ViaPHP

Via is a path alias management library.

It provides a static class for setting shorthand, dot-notation aliases to paths for easy access throughout your codebase, without hardcoding them all and passing around arrays or constants.

Given your project's full root path, and a hostname/IP, Via gives you access to system root, project root and URL root paths.

e.g.:
```
Via::p('host.data.logs') // '//local.test/data/logs'
Via::p('local.data.logs') // '/Users/me/Projects/foo/data/logs'
Via::p('rel.data.logs') // '/data/logs'
```

Paths are sanitized and canonicalized by Symfony's Filesystem component (`Path::class`), and dot-notation is enabled by 


## Usage


### Setting & Assigning

**Note:** Internally we use `/` as the project-relative root path for constructing full filesystem and host paths. This package does not provide methods for handling arbitrary relative paths. All paths returned will always be absolute, based on the project root, local root or hosted root.


#### Local Path
The absolute local filesystem path of the project, used for absolute filesystem paths.

```
Via::setLocal(path: "/User/me/Projects/foo");
```

In real world usage, this would be populated dynamically, based on the system.
I.e.: the local system's full path will be different on local development and various deployment systems.

So you would do something like:

```
Via::setLocal(path: __DIR__); // if you're calling this from the project root.
```

You can retrieve the current local path with:
```
Via::getLocal(); // Returns: '/User/me/Projects/foo'
Via::l();        // Shorthand method - same result
via_local();     // Global function - same result

// With additional path parameters
Via::getLocal('config/app.php');     // Returns: '/User/me/Projects/foo/config/app.php'
Via::l('storage/logs');              // Returns: '/User/me/Projects/foo/storage/logs'
via_local('uploads/images');         // Returns: '/User/me/Projects/foo/uploads/images'
```

#### Host Domain
The domain/hostname/IP addreess, for absolute URL paths

```
Via::setHost(host: "foo.local.test");
```
Here too, you will want to set this dynamically somehow based on the context.

You can retrieve the current host with:
```
Via::getHost();  // Returns: 'foo.local.test'
Via::h();        // Shorthand method - same result
via_host();      // Global function - same result

// With additional path parameters
Via::getHost('api/users');           // Returns: 'foo.local.test/api/users'
Via::h('assets/css');                // Returns: 'foo.local.test/assets/css'
via_host('cdn/images');              // Returns: 'foo.local.test/cdn/images'
```


#### Set Base

```
Via::setBase(alias: "data", label: "data");
Via::setBase(alias: "images", label: "images");
Via::setBase(alias: "src", label: "src");
```

#### Set Bases

Set mutliple bases at once with an array
(Calls `self::setBase()` internally)

```
Via::setBases(
    [
        [alias => "data", path => "data"],
        [alias => "images", path => "images"],
        [alias => "src", path => "src"]
    ]
);
```

#### Assign to Base

Assign a sub-path to a Base

```
Via::assignToBase(alias: "modules", path: "modules", baseAlias: "src" );
```


#### Assign to Bases

Assign multiple sub-paths to Bases with an array
(Calls `self::assignToBase()` internally)

```
Via::assignToBases(
    [alias: "caches", path: "caches", baseAlias: "data"],
    [alias: "logs", path: "logs", baseAlias: "data"],
    [alias: "modules", path: "modules", baseAlias: "src"],
    [alias: "frontend_js", path: "frontend/js", baseAlias: "src"]
);
```


#### Init

Set and Assign a whole config of bases and assignments from a given array.
(Calls `self::setLocal()`, `self::setHost()`, `self::setBases()` and `self:assignToBases()` internally as needed)

```
Via::init(
    [
        "Local" => "/User/me/Projects/foo",
        "absoluteDomain" => "foo.local.test",
        "bases" => [
            [alias => "data", path => "data"],
            [alias => "images", path => "images"],
            [alias => "src", path => "src"]
        ],
        "assignments": [
            [alias: "caches", path: "caches", baseAlias: "data"],
            [alias: "logs", path: "logs", baseAlias: "data"],
            [alias: "modules", path: "modules", baseAlias: "src"],
            [alias: "frontend_js", path: "frontend/js", baseAlias: "src"]
        ]
    ]
)

```

### Getting

Paths are accessed using dot-notation, and assembled at retrieval time (lazy loaded).

The accessing method is `Via::get()` but a `Via::p()` (read: "Via path") forwarder is provided for convenient shorthand notation. ( *I use `Via::p()` myself.* )

For even more convenience in templates and view files, a global `via()` function is also available:

```
Via::get('rel.data.logs');  // (string) '/data/logs'
Via::p('rel.data.logs');    // (string) '/data/logs' - same as above
via('rel.data.logs');       // (string) '/data/logs' - global function

Via::p('local.data.logs');  // (string) '/User/me/Projects/foo/data/logs'
Via::p('host.data.logs');   // (string) '://foo.local.test/data/logs'

Via::p('rel.src');          // (string) '/src'
Via::p('local.src');        // (string) '/User/me/Projects/foo/src'
Via::p('host.src');         // (string) '://foo.local.test/src'

Via::p('rel.src.frontend_js');   // (string) '/src/frontend/js'
Via::p('local.src.frontend_js'); // (string) '/User/me/Projects/foo/src/frontend/js'
Via::p('host.src.frontend_js');  // (string) '://foo.local.test/src/frontend/js'
```

#### Dynamic Path Appending

Both `get()`, `p()`, and the global `via()` function accept an optional second parameter to append additional path segments:

```
Via::p('rel.data', 'config/settings.json');        // '/data/config/settings.json'
Via::p('local.src', 'utils/helpers.php');          // '/User/me/Projects/foo/src/utils/helpers.php'
via('host.images', 'gallery/photo.jpg');           // '://foo.local.test/images/gallery/photo.jpg'
```

#### Global Functions

For ultimate convenience, especially in templates, four global functions are available:

```
via('rel.data.logs');                    // Path retrieval
via_local();                             // Get local filesystem root
via_host();                              // Get host domain
via_join('/base/path', 'subdir');        // Arbitrary path joining
```

These global functions are automatically available when you include the ViaPHP package via Composer's autoloader.

## Path Joining Utility

ViaPHP includes a powerful path joining utility that works independently of the configured path system:

### Via::j() Method

The `Via::j()` method provides arbitrary path joining with cross-platform canonicalization:

```php
// Basic path joining
Via::j('/base/path', 'subdir/file.txt');  // → '/base/path/subdir/file.txt'

// Path canonicalization (cleans messy paths)
Via::j('/base/path', '../parent/file.txt');     // → '/base/parent/file.txt'
Via::j('/base/path', './current//file.txt');    // → '/base/path/current/file.txt'

// Cross-platform separator handling
Via::j('/base/path', 'subdir\\file.txt');       // → '/base/path/subdir/file.txt'

// Null and empty handling
Via::j('/base/path', null);  // → '/base/path'
Via::j('/base/path', '');    // → '/base/path'
```

### Global Function: via_join()

For maximum template convenience, use the global `via_join()` function:

```php
// Identical to Via::j() but more concise in templates
$cssPath = via_join('/public/assets', 'css/main.css');
$logFile = via_join('/var/log/app', date('Y-m-d') . '.log');

// Perfect for HTML templates
echo '<link rel="stylesheet" href="' . via_join($assetBase, 'css/main.css') . '">';
```

**Key Features:**
- **Independent Operation**: Works without any Via configuration
- **Cross-Platform**: Handles various path separators (/, \, mixed)
- **Path Canonicalization**: Cleans up messy paths with `..`, `./`, `//`
- **Null Safety**: Gracefully handles null and empty additional paths
- **Symfony Powered**: Uses Symfony Path component for reliable canonicalization

This utility complements the configured path system by providing flexible arbitrary path joining for dynamic path construction scenarios.

---

## Implemetation Notes

Under the hood we use 
- [`symfony/filesystem`](https://symfony.com/doc/current/components/filesystem.html#path-manipulation-utilities)'s [`Path::class`](https://github.com/symfony/symfony/blob/7.3/src/Symfony/Component/Filesystem/Path.php) methods for concatenating and cannonicalizing paths when assigning/setting and before returning them.
- [`dflydev/dot-access-data`](https://github.com/dflydev/dflydev-dot-access-data) [`Data` methods](https://github.com/dflydev/dflydev-dot-access-data/blob/main/src/Data.php) for setting an internal representation and parsing and resolving `Via::p()` requests from it.


---

## Testing

This package uses the Pest PHP testing framework.

- ["Writing Tests" Guide](https://pestphp.com/docs/writing-tests) 
- [Source](https://github.com/pestphp/pest)
- [Documentation source](https://github.com/pestphp/docs)

Tests are located in `./tests`

