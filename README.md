# ViaPHP

A PHP library for setting and retrieving filesystem paths in your project.

ViaPHP provides a singleton static class for setting nested system paths for easy access throughout your codebase, without hardcoding them all and passing around arrays or constsants.

## Usage

### Initialization


### Setting & Assigning

**Note:** Internally we use `/` as the project-relative root path for constructing full filesystem and host paths. This package does not provide methods for handling arbitrary relative paths. All paths returned will always be absolute, based on the project root, local root or hosted root.


#### Local Path
The absolute local filesystem path of the project, used for absolute filesystem paths.

```
Via::setLocalPath(path: "/User/me/Projects/foo");
```

In real world usage, this would be populated dynamically, based on the system.
I.e.: the local system's full path will be different on local development and various deployment systems.

So you would do something like:

```
Via::setLocalPath(path: __DIR__); // if you're calling this from the project root.
```

#### Host Domain
The domain/hostname/IP addreess, for absolute URL paths

```
Via::setHost(host: "foo.local.test");
```
Here too, you will want to set this dynamically somehow based on the context.


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
(Calls `self::setLocalPath()`, `self::setHost()`, `self::setBases()` and `self:assignToBases()` internally as needed)

```
Via::init(
    [
        "LocalPath" => "/User/me/Projects/foo",
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

```
Via::f('rel.data.logs');   // (string) '/data/logs'
Via::f('local.data.logs'); // (string) '/User/me/Projects/foo/data/logs'
Via::f('host.data.logs');  // (string) '://foo.local.test/data/logs'

Via::f('rel.src');   // (string) '/src'
Via::f('local.src'); // (string) '/User/me/Projects/foo/src'
Via::f('host.src');  // (string) '://foo.local.test/src'

Via::f('rel.src.frontend_js');   // (string) '/src/frontend/js'
Via::f('local.src.frontend_js'); // (string) '/User/me/Projects/foo/src/frontend/js'
Via::f('host.src.frontend_js');  // (string) '://foo.local.test/src/frontend/js'
```

---

## Implemetation Notes

Under the hood we use 
- [`symfony/filesystem`](https://symfony.com/doc/current/components/filesystem.html#path-manipulation-utilities)'s [`Path::class`](https://github.com/symfony/symfony/blob/7.3/src/Symfony/Component/Filesystem/Path.php) methods for concatenating and cannonicalizing paths when assigning/setting and before returning them.
- [`dflydev/dot-access-data`](https://github.com/dflydev/dflydev-dot-access-data) [`Data` methods](https://github.com/dflydev/dflydev-dot-access-data/blob/main/src/Data.php) for setting an internal representation and parsing and resolving `Via::f()` requests from it.


---

## Testing

This package uses the Pest PHP testing framework.

- ["Writing Tests" Guide](https://pestphp.com/docs/writing-tests) 
- [Source](https://github.com/pestphp/pest)
- [Documentation source](https://github.com/pestphp/docs)

Tests are located in `./tests`

