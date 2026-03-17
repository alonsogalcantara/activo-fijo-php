<?php

/**
 * Config — Singleton that reads the application's .yml configuration file
 * and exposes OS-aware path helpers derived from the [paths] section.
 *
 * Usage:
 *   $uploadsDir = Config::uploadsPath();   // absolute path to /public/uploads/
 *   $publicDir  = Config::publicPath();    // absolute path to /public
 *   $baseDir    = Config::basePath();      // absolute path to project root
 */
class Config
{
    /** @var array<string,mixed>|null Parsed YAML data (cached after first load) */
    private static ?array $data = null;

    /** Absolute path to the .yml file — one level above /config */
    private static string $yamlPath = __DIR__ . '/../.yml';

    // -------------------------------------------------------------------------
    // Public path helpers
    // -------------------------------------------------------------------------

    /**
     * Returns the absolute filesystem path to the uploads directory,
     * with a trailing directory separator.
     */
    public static function uploadsPath(): string
    {
        return self::resolvePath('uploads') . DIRECTORY_SEPARATOR;
    }

    /**
     * Returns the absolute filesystem path to the public directory (no trailing slash).
     */
    public static function publicPath(): string
    {
        return self::resolvePath('public_dir');
    }

    /**
     * Returns the absolute filesystem path to the project root (no trailing slash).
     */
    public static function basePath(): string
    {
        return self::resolvePath('base_dir');
    }

    // -------------------------------------------------------------------------
    // Generic config access
    // -------------------------------------------------------------------------

    /**
     * Returns the full parsed config array (lazy-loaded singleton).
     */
    public static function all(): array
    {
        if (self::$data === null) {
            self::$data = self::loadYaml(self::$yamlPath);
        }
        return self::$data;
    }

    /**
     * Returns a top-level config section (e.g. 'database', 'app', 'paths').
     */
    public static function section(string $key): array
    {
        return self::all()[$key] ?? [];
    }

    // -------------------------------------------------------------------------
    // Internals
    // -------------------------------------------------------------------------

    /**
     * Resolves an OS-aware path key from the [paths] section.
     * On Windows it picks ['windows'], on all other OS it picks ['linux'].
     * The path is normalised to native DIRECTORY_SEPARATOR.
     *
     * Keys understood: 'base_dir', 'public_dir', 'uploads'
     */
    private static function resolvePath(string $key): string
    {
        $paths = self::section('paths');

        if (!isset($paths[$key])) {
            // Fallback: derive from __DIR__ so the app still works without YAML paths
            $fallbacks = [
                'base_dir'   => realpath(__DIR__ . '/..'),
                'public_dir' => realpath(__DIR__ . '/../public'),
                'uploads'    => realpath(__DIR__ . '/../public/uploads'),
            ];
            return $fallbacks[$key] ?? __DIR__;
        }

        $entry = $paths[$key];

        // The YAML nests 'windows' / 'linux' sub-keys under each path entry.
        // Support both flat string and nested-array styles.
        if (is_array($entry)) {
            $osKey  = (DIRECTORY_SEPARATOR === '\\') ? 'windows' : 'linux';
            $raw    = $entry[$osKey] ?? reset($entry);
        } else {
            $raw = (string)$entry;
        }

        // Normalise double-slashes used in the YAML (C://wamp64//... or /var//...)
        // and convert to the native separator.
        $normalised = str_replace(['\\\\', '//', '\\', '/'], DIRECTORY_SEPARATOR, $raw);

        // Remove trailing separator so callers can append consistently.
        return rtrim($normalised, DIRECTORY_SEPARATOR);
    }

    /**
     * Lightweight YAML parser — identical logic to the one in db.php but
     * now supports TWO levels of nesting (section → key → sub-key).
     */
    private static function loadYaml(string $path): array
    {
        if (!file_exists($path)) {
            die("Config error: .yml not found at $path");
        }

        // Prefer the native PHP YAML extension when available.
        if (function_exists('yaml_parse_file')) {
            return yaml_parse_file($path);
        }

        $lines   = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $result  = [];
        $section = null;   // Top-level key  (e.g. 'paths')
        $subKey  = null;   // Second-level key (e.g. 'uploads')

        foreach ($lines as $line) {
            // Skip comments
            if (ltrim($line)[0] === '#') {
                continue;
            }

            $indent = strlen($line) - strlen(ltrim($line));

            // Top-level section — no indentation, ends with colon, no value
            if ($indent === 0 && preg_match('/^([a-zA-Z_][a-zA-Z0-9_]*):\s*$/', $line, $m)) {
                $section          = $m[1];
                $subKey           = null;
                $result[$section] = $result[$section] ?? [];
                continue;
            }

            // Second-level key (2 spaces) — may be a sub-section (no value) or key: value
            if ($indent === 2 && $section !== null && preg_match('/^\s{2}([a-zA-Z_][a-zA-Z0-9_]*):\s*(.*)$/', $line, $m)) {
                $key   = $m[1];
                $value = trim($m[2]);

                if ($value === '') {
                    // Sub-section header  (e.g. "  uploads:")
                    $subKey                   = $key;
                    $result[$section][$subKey] = [];
                } else {
                    // Plain key: value pair at depth-2
                    $subKey = null;
                    $result[$section][$key] = self::castValue($value);
                }
                continue;
            }

            // Third-level key (4 spaces) — leaf values under a sub-section
            if ($indent === 4 && $section !== null && $subKey !== null
                && preg_match('/^\s{4}([a-zA-Z_][a-zA-Z0-9_]*):\s*(.*)$/', $line, $m)
            ) {
                $key   = $m[1];
                $value = trim($m[2]);
                $result[$section][$subKey][$key] = self::castValue($value);
                continue;
            }

            // Depth-2 plain key when sub-section context is active (reset subKey)
            if ($indent === 2 && $section !== null && preg_match('/^\s{2}([a-zA-Z_][a-zA-Z0-9_]*):\s*(.+)$/', $line, $m)) {
                $subKey = null;
                $result[$section][$m[1]] = self::castValue(trim($m[2]));
            }
        }

        return $result;
    }

    /** Strip quotes; cast numeric strings to int. */
    private static function castValue(string $raw): int|string
    {
        $value = trim($raw, '"\'');
        if (is_numeric($value) && !preg_match('/["\']/', $raw)) {
            return (int)$value;
        }
        return $value;
    }
}
