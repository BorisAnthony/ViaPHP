<?php

declare(strict_types=1);

namespace Via;

use Dflydev\DotAccessData\Data;
use Symfony\Component\Filesystem\Path;

class Via
{
    private static ?Data $data = null;

    private static ?string $localPath = null;

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
        self::$data = null;
        self::$localPath = null;
        self::$host = null;
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
        foreach ($bases as $baseRole => $_) {
            $result[$baseRole] = [
                'rel' => self::f("rel.{$baseRole}")
            ];
            
            // Add local path if available
            if (self::$localPath !== null) {
                $result[$baseRole]['local'] = self::f("local.{$baseRole}");
            }
            
            // Add host path if available
            if (self::$host !== null) {
                $result[$baseRole]['host'] = self::f("host.{$baseRole}");
            }
        }
        
        // Get all assignments
        $assignments = self::$data->get('assignments', []);
        foreach ($assignments as $assignmentRole => $assignmentData) {
            $baseRole = $assignmentData['baseRole'];
            $fullAlias = "{$baseRole}.{$assignmentRole}";
            
            $result[$fullAlias] = [
                'rel' => self::f("rel.{$fullAlias}")
            ];
            
            // Add local path if available
            if (self::$localPath !== null) {
                $result[$fullAlias]['local'] = self::f("local.{$fullAlias}");
            }
            
            // Add host path if available
            if (self::$host !== null) {
                $result[$fullAlias]['host'] = self::f("host.{$fullAlias}");
            }
        }
        
        return $result;
    }

    public static function setLocalPath(string $path): void
    {
        self::$localPath = Path::canonicalize($path);
    }

    public static function setHost(string $host): void
    {
        self::$host = $host;
    }

    public static function getLocalPath(): ?string
    {
        return self::$localPath;
    }

    public static function getHost(): ?string
    {
        return self::$host;
    }

    public static function setBase(string $role, string $path): void
    {
        self::initData();
        $canonicalPath = Path::canonicalize($path);
        self::$data->set("bases.{$role}", $canonicalPath);
    }

    /**
     * @param array<array{role: string, path: string}> $bases
     */
    public static function setBases(array $bases): void
    {
        foreach ($bases as $base) {
            if (!isset($base['role']) || !isset($base['path'])) {
                throw new \InvalidArgumentException('Each base must have "role" and "path" keys');
            }
            self::setBase($base['role'], $base['path']);
        }
    }

    public static function assignToBase(string $role, string $path, string $baseRole): void
    {
        self::initData();

        $basePath = self::$data->get("bases.{$baseRole}", null);
        if ($basePath === null) {
            throw new \InvalidArgumentException("Base role '{$baseRole}' does not exist");
        }

        $fullPath      = Path::join($basePath, $path);
        $canonicalPath = Path::canonicalize($fullPath);
        self::$data->set("assignments.{$role}", [
            'path'         => $canonicalPath,
            'baseRole'     => $baseRole,
            'relativePath' => $path
        ]);
    }

    /**
     * @param array<array{role: string, path: string, baseRole: string}> $assignments
     */
    public static function assignToBases(array $assignments): void
    {
        foreach ($assignments as $assignment) {
            if (!isset($assignment['role']) || !isset($assignment['path']) || !isset($assignment['baseRole'])) {
                throw new \InvalidArgumentException('Each assignment must have "role", "path", and "baseRole" keys');
            }
            self::assignToBase($assignment['role'], $assignment['path'], $assignment['baseRole']);
        }
    }

    /**
     * @param array{LocalPath?: string, absoluteDomain?: string, bases?: array<array{role: string, path: string}>, assignments?: array<array{role: string, path: string, baseRole: string}>} $config
     */
    public static function init(array $config): void
    {
        if (isset($config['LocalPath'])) {
            self::setLocalPath($config['LocalPath']);
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

    public static function f(string $dotPath): string
    {
        $parts = explode('.', $dotPath);

        if (count($parts) < 2) {
            throw new \InvalidArgumentException('Path must contain at least type and role (e.g., "rel.data")');
        }

        $type     = array_shift($parts);
        $role     = array_shift($parts);
        $subParts = $parts;

        switch ($type) {
            case 'rel':
                return self::buildRelativePath($role, $subParts);
            case 'local':
                return self::buildLocalPath($role, $subParts);
            case 'host':
                return self::buildHostPath($role, $subParts);
            default:
                throw new \InvalidArgumentException("Invalid path type '{$type}'. Must be 'rel', 'local', or 'host'");
        }
    }

    /**
     * @param array<string> $subParts
     */
    private static function buildRelativePath(string $role, array $subParts): string
    {
        self::initData();

        // First, check if this is a base (bases are accessed directly)
        $basePath = self::$data->get("bases.{$role}", null);
        if ($basePath !== null) {
            $normalizedBasePath = Path::canonicalize($basePath);
            $fullPath = Path::join('/', $normalizedBasePath);

            // If there are sub-parts, we need to validate the hierarchy
            if (!empty($subParts)) {
                $currentPath = $role;
                foreach ($subParts as $part) {
                    // Check if this part is a valid assignment under the current base
                    $assignment = self::$data->get("assignments.{$part}", null);
                    if ($assignment !== null && $assignment['baseRole'] === $currentPath) {
                        $currentPath = $part; // Move to the assignment role
                        continue;
                    }

                    // Check if this forms a valid nested base under current path
                    $nextRole = $currentPath . '.' . $part;
                    $nextBase = self::$data->get("bases.{$nextRole}", null);
                    if ($nextBase !== null) {
                        $currentPath = $nextRole;
                        continue;
                    }

                    // If we get here, the path segment is not configured properly
                    throw new \InvalidArgumentException("Path segment '{$part}' not found as assignment under '{$currentPath}'");
                }

                // Build the final path using the validated segments
                $finalAssignment = self::$data->get("assignments.{$currentPath}", null);
                if ($finalAssignment !== null) {
                    $finalPath     = Path::canonicalize($finalAssignment['relativePath']);
                    $finalBaseRole = $finalAssignment['baseRole'];
                    $finalBasePathRaw = self::$data->get("bases.{$finalBaseRole}", null);
                    if ($finalBasePathRaw !== null) {
                        $finalBasePath = Path::canonicalize($finalBasePathRaw);
                        $fullPath      = Path::join('/', $finalBasePath, $finalPath);
                    }
                } else {
                    $finalBasePathRaw = self::$data->get("bases.{$currentPath}", null);
                    if ($finalBasePathRaw !== null) {
                        $finalBasePath = Path::canonicalize($finalBasePathRaw);
                        $fullPath = Path::join('/', $finalBasePath);
                    }
                }
            }

            return Path::canonicalize($fullPath);
        }

        // If we get here, the role is not a base, so it's invalid
        // Assignments can only be accessed through their base: base.assignment
        throw new \InvalidArgumentException("Role '{$role}' must be a base. Assignments must be accessed via base.assignment format");
    }

    /**
     * @param array<string> $subParts
     */
    private static function buildLocalPath(string $role, array $subParts): string
    {
        if (self::$localPath === null) {
            throw new \RuntimeException('Local path not set. Call setLocalPath() first.');
        }

        $relativePath = self::buildRelativePath($role, $subParts);
        return Path::join(self::$localPath, ltrim($relativePath, '/'));
    }

    /**
     * @param array<string> $subParts
     */
    private static function buildHostPath(string $role, array $subParts): string
    {
        if (self::$host === null) {
            throw new \RuntimeException('Host not set. Call setHost() first.');
        }

        $relativePath = self::buildRelativePath($role, $subParts);
        return '//' . self::$host . $relativePath;
    }
}
