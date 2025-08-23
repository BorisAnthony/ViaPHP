# PathsPHP

A PHP library for setting and retrieving filesystem paths in your project.

PathsPHP provides a singleton static class for setting nested system paths for easy access throughout your codebase, without hardcoding them all and passing around arrays or constsants.

## Usage

### Initialization


### Setting & Assigning

**Note:** Internally we use `/` as the project-relative root path for constructing full filesystem and host paths. This package does not provide methods for handling arbitrary relative paths. All paths returned will always be absolute, based on the project root, local root or hosted root.


#### Local Path
The absolute local filesystem path of the project, used for absolute filesystem paths.

```
Via::setLocalPath(path: "/User/me/Projects/foo");
```


#### Host Domain
The domain/hostname/IP addreess, for absolute URL paths

```
Via::setHost(path: "foo.local.test");
```


#### Set Base

```
Via::setBase(role: "data", label: "data");
Via::setBase(role: "images", label: "images");
Via::setBase(role: "src", label: "src");
```

#### Set Bases

Set mutliple bases at once with an array
(Calls `self::setBase()` internally)

```
Via::setBases(
    [
        [role => "data", path => "data"],
        [role => "images", path => "images"],
        [role => "src", path => "src"]
    ]
);
```

#### Assign to Base

Assign a sub-path to a Base

```
Paths:assignToBase(role: "modules", path: "modules", baseRole: "src" );
```


#### Assign to Bases

Assign multiple sub-paths to Bases with an array
(Calls `self::assignToBase()` internally)

```
Paths:assignToBases(
    [role: "caches", path: "caches", baseRole: "data"],
    [role: "logs", path: "logs", baseRole: "data"],
    [role: "modules", path: "modules", baseRole: "src"],
    [role: "frontend_js", path: "frontend/js", baseRole: "src"]
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
            [role => "data", path => "data"],
            [role => "images", path => "images"],
            [role => "src", path => "src"]
        ],
        "assignments": [
            [role: "caches", path: "caches", baseRole: "data"],
            [role: "logs", path: "logs", baseRole: "data"],
            [role: "modules", path: "modules", baseRole: "src"],
            [role: "frontend_js", path: "frontend/js", baseRole: "src"]
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

