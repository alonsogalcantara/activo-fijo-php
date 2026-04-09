<?php

/**
 * Config — Singleton that reads the application's .yml configuration file
 * and exposes OS-aware path helpers derived from the [paths] section.
 *
 * Secrets (passwords, API keys, etc.) should NOT be stored in .yml.
 * Use ${VAR} placeholders in .yml and define the real values in .env:
 *
 *   .yml  →  password: "${DB_PASS}"
 *   .env  →  DB_PASS='/!rkkN2R5jtT17ZJ'
 *
 * Config will automatically substitute ${VAR} tokens at parse time.
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

    /** @var array<string,string>|null Variables loaded from .env (cached) */
    private static ?array $env = null;

    /** Absolute path to the .yml file — one level above /config */
    private static string $yamlPath = __DIR__ . '/../.yml';

    /** Absolute path to the .env file — one level above /config */
    private static string $envPath  = __DIR__ . '/../.env';

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
            // Fallback: derive dynamically so the app still works universally without YAML paths
            $base = dirname(__DIR__);
            $fallbacks = [
                'base_dir'   => $base,
                'public_dir' => $base . DIRECTORY_SEPARATOR . 'public',
                'uploads'    => $base . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'uploads',
            ];
            return $fallbacks[$key] ?? $base;
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

    /** Strip quotes; cast numeric strings to int; expand ${VAR} tokens from .env. */
    private static function castValue(string $raw): int|string
    {
        $value = trim($raw, '"\'');

        // Expand environment placeholders like ${DB_PASS}
        $value = self::expandEnv($value);

        if (is_numeric($value) && !preg_match('/["\']/', $raw)) {
            return (int)$value;
        }
        return $value;
    }

    // -------------------------------------------------------------------------
    // .env loader & placeholder expander
    // -------------------------------------------------------------------------

    /**
     * Replaces every ${VAR_NAME} token in $text with the corresponding value
     * loaded from the .env file.  Unknown tokens are left as-is.
     */
    private static function expandEnv(string $text): string
    {
        if (strpos($text, '${') === false) {
            return $text; // Fast path — nothing to expand
        }

        $env = self::loadEnv();

        return preg_replace_callback(
            '/\$\{([A-Z_][A-Z0-9_]*)\}/i',
            static fn(array $m) => $env[$m[1]] ?? $m[0],
            $text
        );
    }

    /**
     * Parses the .env file into a key → value map (cached after first call).
     * Supports:
     *   KEY=value
     *   KEY='value with spaces'
     *   KEY="value with spaces"
     *   # comment lines (ignored)
     */
    private static function loadEnv(): array
    {
        if (self::$env !== null) {
            return self::$env;
        }

        self::$env = [];

        if (!file_exists(self::$envPath)) {
            return self::$env;
        }

        $lines = file(self::$envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            $line = trim($line);

            // Skip comments and empty lines
            if ($line === '' || $line[0] === '#') {
                continue;
            }

            // Must contain '='
            if (!str_contains($line, '=')) {
                continue;
            }

            [$key, $value] = explode('=', $line, 2);
            $key   = trim($key);
            $value = trim($value);

            // Strip surrounding quotes (' or ")
            if (strlen($value) >= 2
                && (($value[0] === '\'' && $value[-1] === '\'') ||
                    ($value[0] === '"'  && $value[-1] === '"'))
            ) {
                $value = substr($value, 1, -1);
            }

            self::$env[$key] = $value;
        }

        return self::$env;
    }
}
