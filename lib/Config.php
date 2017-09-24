<?php

namespace Kilab\Api;

use InvalidArgumentException;
use LogicException;

class Config
{

    /**
     * Get value from config file by given key.
     *
     * @param string $key
     *
     * @return mixed
     * @throws LogicException
     */
    public static function get(string $key)
    {
        if (!file_exists(BASE_DIR . 'app/' . ucfirst(API_VERSION))) {
            throw new LogicException('Invalid API version.');
        }

        if (!self::configFileExists()) {
            throw new LogicException('Config file does not exist. Please copy app/Config.sample.php to app/' . ucfirst(API_VERSION) . '/Config');
        }

        $configFile = include(self::getConfigFilePath());
        $keyIndex = explode('.', $key);

        return self::getValue($keyIndex, $configFile);
    }

    /**
     * Get value from config array.
     *
     * @param array $keyPath
     * @param array $configArray
     *
     * @return mixed
     */
    private static function getValue(array $keyPath, array $configArray)
    {
        if (is_array($keyPath) && count($keyPath) > 0) {
            $current_index = array_shift($keyPath);
        }

        if (is_array($keyPath) && count($keyPath) && is_array($configArray[$current_index]) && count($configArray[$current_index])) {
            return self::getValue($keyPath, $configArray[$current_index]);
        } else {
            if (!isset($configArray[$current_index])) {
                throw new InvalidArgumentException('Config for key: ' . $current_index . ' not found');
            }

            return $configArray[$current_index];
        }
    }

    /**
     * Check whether config file exist in app directory.
     *
     * @return bool
     */
    private static function configFileExists(): bool
    {
        return file_exists(self::getConfigFilePath());
    }

    /**
     * Get path to config file for request API version.
     *
     * @return string
     */
    private static function getConfigFilePath(): string
    {
        return BASE_DIR . 'app/' . ucfirst(API_VERSION) . '/Config/' . Env::get('ENVIRONMENT') . '.php';
    }
}