<?php

namespace Kilab\Api;


class Env
{
    /**
     * Get value from /public/.env file.
     *
     * @param string $key
     *
     * @return string|bool|int|null
     */
    public static function get(string $key)
    {
        self::loadFile();

        $envValue = getenv($key);

        if (!$envValue) {
            return null;
        }

        if (is_numeric($envValue)) {
            $envValue = (int)$envValue;
        } elseif ($envValue === 'true' || $envValue === 'false') {
            $envValue = $envValue === 'true' ? true : false;
        }

        return $envValue;
    }

    /**
     * Load environment file from /public directory.
     */
    private static function loadFile(): void
    {
        foreach (file(BASE_DIR . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            putenv($line);
        }
    }
}
