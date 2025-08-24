<?php

declare(strict_types=1);

namespace Via;

use Dflydev\DotAccessData\Data;
use Symfony\Component\Filesystem\Path;

class Via
{
    private static ?Data $data = null;

    private static ?string $local = null;

    private static ?string $host = null;

    private static function initData(): void
    {
        if (self::$data === null) {
            self::$data = new Data();
        }
    }

    /**
     * Reset all static state - useful for testing
     */
    public static function reset(): void
    {
        self::$data      = null;
        self::$local = null;
        self::$host      = null;
    }

    /**
     * Get all configured path aliases with their resolved paths
     *
     * @return array<string, array{rel: string, local?: string, host?: string}>
     */
    public static function all(): array
    {
        self::initData();

        $result = [];

        // Get all bases
        $bases = self::$data->get('bases', []);
        foreach ($bases as $baseAlias => $_) {
            $result[$baseAlias] = [
                'rel' => self::get("rel.{$baseAlias}")
            ];

            // Add local path if available
            if (self::$local !== null) {
                $result[$baseAlias]['local'] = self::get("local.{$baseAlias}");
            }

            // Add host path if available
            if (self::$host !== null) {
                $result[$baseAlias]['host'] = self::get("host.{$baseAlias}");
            }
        }

        // Get all assignments
        $assignments = self::$data->get('assignments', []);
        foreach ($assignments as $assignmentAlias => $assignmentData) {
            $baseAlias = $assignmentData['baseAlias'];
            $fullAlias = "{$baseAlias}.{$assignmentAlias}";

            $result[$fullAlias] = [
                'rel' => self::get("rel.{$fullAlias}")
            ];

            // Add local path if available
            if (self::$local !== null) {
                $result[$fullAlias]['local'] = self::get("local.{$fullAlias}");
            }

            // Add host path if available
            if (self::$host !== null) {
                $result[$fullAlias]['host'] = self::get("host.{$fullAlias}");
            }
        }

        return $result;
    }

    public static function setLocal(string $path): void
    {
        self::$local = Path::canonicalize($path);
    }

    public static function setHost(string $host): void
    {
        self::$host = $host;
    }

    public static function getLocal(): ?string
    {
        return self::$local;
    }

    public static function getHost(): ?string
    {
        return self::$host;
    }

    public static function setBase(string $alias, string $path): void
    {
        self::initData();
        $canonicalPath = Path::canonicalize($path);
        self::$data->set("bases.{$alias}", $canonicalPath);
    }

    /**
     * @param array<array{alias: string, path: string}|array{0: string, 1: string}> $bases
     */
    public static function setBases(array $bases): void
    {
        foreach ($bases as $base) {
            // Handle positional arrays [alias, path] or associative arrays ['alias' => alias, 'path' => path]
            if (isset($base[0], $base[1]) && !isset($base['alias'], $base['path'])) {
                // Positional array
                self::setBase($base[0], $base[1]);
            } elseif (isset($base['alias'], $base['path'])) {
                // Associative array
                self::setBase($base['alias'], $base['path']);
            } else {
                throw new \InvalidArgumentException('Each base must have "alias" and "path" keys or be a positional array [alias, path]');
            }
        }
    }

    public static function assignToBase(string $alias, string $path, string $baseAlias): void
    {
        self::initData();

        $basePath = self::$data->get("bases.{$baseAlias}", null);
        if ($basePath === null) {
            throw new \InvalidArgumentException("Base alias '{$baseAlias}' does not exist");
        }

        $fullPath      = Path::join($basePath, $path);
        $canonicalPath = Path::canonicalize($fullPath);
        self::$data->set("assignments.{$alias}", [
            'path'          => $canonicalPath,
            'baseAlias'     => $baseAlias,
            'relativePath'  => $path
        ]);
    }

    /**
     * @param array<array{alias: string, path: string, baseAlias: string}|array{0: string, 1: string, 2: string}> $assignments
     */
    public static function assignToBases(array $assignments): void
    {
        foreach ($assignments as $assignment) {
            // Handle positional arrays [alias, path, baseAlias] or associative arrays
            if (isset($assignment[0], $assignment[1], $assignment[2]) && !isset($assignment['alias'], $assignment['path'], $assignment['baseAlias'])) {
                // Positional array
                self::assignToBase($assignment[0], $assignment[1], $assignment[2]);
            } elseif (isset($assignment['alias'], $assignment['path'], $assignment['baseAlias'])) {
                // Associative array
                self::assignToBase($assignment['alias'], $assignment['path'], $assignment['baseAlias']);
            } else {
                throw new \InvalidArgumentException('Each assignment must have "alias", "path", and "baseAlias" keys or be a positional array [alias, path, baseAlias]');
            }
        }
    }

    /**
     * @param array{Local?: string, absoluteDomain?: string, bases?: array<array{alias: string, path: string}|array{0: string, 1: string}>, assignments?: array<array{alias: string, path: string, baseAlias: string}|array{0: string, 1: string, 2: string}>} $config
     */
    public static function init(array $config): void
    {
        if (isset($config['Local'])) {
            self::setLocal($config['Local']);
        }

        if (isset($config['absoluteDomain'])) {
            self::setHost($config['absoluteDomain']);
        }

        if (isset($config['bases']) && is_array($config['bases'])) {
            self::setBases($config['bases']);
        }

        if (isset($config['assignments']) && is_array($config['assignments'])) {
            self::assignToBases($config['assignments']);
        }
    }

    /**
     * Retrieve a configured path by dot notation
     *
     * @param string $dotPath Path in dot notation (e.g., "rel.data.logs")
     * @param string|null $additionalPath Optional additional path to append
     * @return string The resolved path
     */
    public static function get(string $dotPath, ?string $additionalPath = null): string
    {
        $parts = explode('.', $dotPath);

        if (count($parts) < 2) {
            throw new \InvalidArgumentException('Path must contain at least type and alias (e.g., "rel.data")');
        }

        $type      = array_shift($parts);
        $alias     = array_shift($parts);
        $subParts  = $parts;

        switch ($type) {
            case 'rel':
                return self::buildRelativePath($alias, $subParts, $additionalPath);
            case 'local':
                return self::buildLocal($alias, $subParts, $additionalPath);
            case 'host':
                return self::buildHostPath($alias, $subParts, $additionalPath);
            default:
                throw new \InvalidArgumentException("Invalid path type '{$type}'. Must be 'rel', 'local', or 'host'");
        }
    }

    /**
     * Convenience shorthand forwarding method
     * - "p" for "path"
     *
     * @param string $dotPath Path in dot notation (e.g., "rel.data.logs")
     * @param string|null $additionalPath Optional additional path to append
     * @return string The resolved path
     */
    public static function p(string $dotPath, ?string $additionalPath = null): string
    {
        return self::get($dotPath, $additionalPath);
    }

    /**
     * @param array<string> $subParts
     */
    private static function buildRelativePath(string $alias, array $subParts, ?string $additionalPath = null): string
    {
        self::initData();

        // First, check if this is a base (bases are accessed directly)
        $basePath = self::$data->get("bases.{$alias}", null);
        if ($basePath !== null) {
            $normalizedBasePath = Path::canonicalize($basePath);
            $fullPath           = Path::join('/', $normalizedBasePath);

            // If there are sub-parts, we need to validate the hierarchy
            if (!empty($subParts)) {
                $currentPath = $alias;
                foreach ($subParts as $part) {
                    // Check if this part is a valid assignment under the current base
                    $assignment = self::$data->get("assignments.{$part}", null);
                    if ($assignment !== null && $assignment['baseAlias'] === $currentPath) {
                        $currentPath = $part; // Move to the assignment alias
                        continue;
                    }

                    // Check if this forms a valid nested base under current path
                    $nextAlias = $currentPath . '.' . $part;
                    $nextBase  = self::$data->get("bases.{$nextAlias}", null);
                    if ($nextBase !== null) {
                        $currentPath = $nextAlias;
                        continue;
                    }

                    // If we get here, the path segment is not configured properly
                    throw new \InvalidArgumentException("Path segment '{$part}' not found as assignment under '{$currentPath}'");
                }

                // Build the final path using the validated segments
                $finalAssignment = self::$data->get("assignments.{$currentPath}", null);
                if ($finalAssignment !== null) {
                    $finalPath        = Path::canonicalize($finalAssignment['relativePath']);
                    $finalBaseAlias   = $finalAssignment['baseAlias'];
                    $finalBasePathRaw = self::$data->get("bases.{$finalBaseAlias}", null);
                    if ($finalBasePathRaw !== null) {
                        $finalBasePath = Path::canonicalize($finalBasePathRaw);
                        $fullPath      = Path::join('/', $finalBasePath, $finalPath);
                    }
                } else {
                    $finalBasePathRaw = self::$data->get("bases.{$currentPath}", null);
                    if ($finalBasePathRaw !== null) {
                        $finalBasePath = Path::canonicalize($finalBasePathRaw);
                        $fullPath      = Path::join('/', $finalBasePath);
                    }
                }
            }

            $finalPath = Path::canonicalize($fullPath);

            if ($additionalPath !== null) {
                $canonicalAdditionalPath = Path::canonicalize($additionalPath);
                $finalPath               = Path::join($finalPath, $canonicalAdditionalPath);
            }

            return $finalPath;
        }

        // If we get here, the alias is not a base, so it's invalid
        // Assignments can only be accessed through their base: base.assignment
        throw new \InvalidArgumentException("Alias '{$alias}' must be a base. Assignments must be accessed via base.assignment format");
    }

    /**
     * @param array<string> $subParts
     */
    private static function buildLocal(string $alias, array $subParts, ?string $additionalPath = null): string
    {
        if (self::$local === null) {
            throw new \RuntimeException('Local path not set. Call setLocal() first.');
        }

        $relativePath = self::buildRelativePath($alias, $subParts, $additionalPath);
        return Path::join(self::$local, ltrim($relativePath, '/'));
    }

    /**
     * @param array<string> $subParts
     */
    private static function buildHostPath(string $alias, array $subParts, ?string $additionalPath = null): string
    {
        if (self::$host === null) {
            throw new \RuntimeException('Host not set. Call setHost() first.');
        }

        $relativePath = self::buildRelativePath($alias, $subParts, $additionalPath);
        return '//' . self::$host . $relativePath;
    }
}
