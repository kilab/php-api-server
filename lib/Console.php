<?php

namespace Kilab\Api;

class Console
{

    private static $colors = [
        'black'   => ['set' => 30, 'unset' => 39],
        'red'     => ['set' => 31, 'unset' => 39],
        'green'   => ['set' => 32, 'unset' => 39],
        'yellow'  => ['set' => 33, 'unset' => 39],
        'blue'    => ['set' => 34, 'unset' => 39],
        'magenta' => ['set' => 35, 'unset' => 39],
        'cyan'    => ['set' => 36, 'unset' => 39],
        'white'   => ['set' => 37, 'unset' => 39],
        'default' => ['set' => 39, 'unset' => 39],
    ];
    private static $backgroundColors = [
        'black'   => ['set' => 40, 'unset' => 49],
        'red'     => ['set' => 41, 'unset' => 49],
        'green'   => ['set' => 42, 'unset' => 49],
        'yellow'  => ['set' => 43, 'unset' => 49],
        'blue'    => ['set' => 44, 'unset' => 49],
        'magenta' => ['set' => 45, 'unset' => 49],
        'cyan'    => ['set' => 46, 'unset' => 49],
        'white'   => ['set' => 47, 'unset' => 49],
        'default' => ['set' => 49, 'unset' => 49],
    ];

    /**
     * Parse arguments passed in CLI to easy use in application.
     *
     * @param array $arguments
     *
     * @return array
     */
    public static function parseArguments(array $arguments): array
    {
        $params = [];

        if (isset($arguments[1])) {
            $params['command'] = $arguments[1];

            unset($arguments[0], $arguments[1]);
            $arguments = array_values($arguments);
        }

        if (isset($arguments[0]) && strpos($arguments[0], '-') !== 0) {
            $params['operation'] = $arguments[0];

            unset($arguments[0]);
            $arguments = array_values($arguments);
        }

        foreach ($arguments as $arg) {
            $argument = explode('=', $arg);

            $params[ltrim($argument[0], '-')] = $argument[1];
        }

        return $params;
    }

    /**
     * Write text to CLI output.
     *
     * @param             $text
     * @param string|null $color
     * @param string|null $backgroundColor
     */
    public static function write($text, string $color = 'default', string $backgroundColor = null): void
    {
        $setCodes = [];
        $unsetCodes = [];

        if (null !== $color) {
            $setCodes[] = self::$colors[$color]['set'];
            $unsetCodes[] = self::$colors[$color]['unset'];
        }
        if (null !== $backgroundColor) {
            $setCodes[] = self::$backgroundColors[$backgroundColor]['set'];
            $unsetCodes[] = self::$backgroundColors[$backgroundColor]['unset'];
        }

        if (0 === count($setCodes)) {
            echo $text;
        }

        echo sprintf("\033[%sm%s\033[%sm", implode(';', $setCodes), $text, implode(';', $unsetCodes));
    }

    /**
     * Write line text to CLI output.
     *
     * @param             $string
     * @param string      $color
     * @param string|null $backgroundColor
     */
    public static function writeLine($string, string $color = 'default', string $backgroundColor = null): void
    {
        self::write($string . PHP_EOL, $color, $backgroundColor);
    }

    /**
     * Get color code for given color name.
     *
     * @param string $colorName
     *
     * @return string
     */
    public static function colorCode(string $colorName): string
    {
        if (!isset(self::$colors[$colorName])) {
            self::fatal('Color "' . $colorName . '" is not supported yet.');
        }

        return self::$colors[$colorName]['set'];
    }

    /**
     * Write error text to CLI output.
     *
     * @param $string
     */
    public static function error($string): void
    {
        self::write($string, 'default', 'red');
    }

    /**
     * Write fatal error text to CLI output and kill process.
     *
     * @param $string
     */
    public static function fatal($string): void
    {
        self::write(PHP_EOL);
        self::write($string, 'default', 'red');
        self::write(PHP_EOL);
        exit(1);
    }

    /**
     * Write success text to CLI output.
     *
     * @param $string
     */
    public static function success($string): void
    {
        self::write($string, 'green');
    }

    /**
     * Write warning text to CLI output.
     *
     * @param $string
     */
    public static function warning($string): void
    {
        self::write($string, 'yellow');
    }

    /**
     * Write info text to CLI output.
     *
     * @param $string
     */
    public static function info($string): void
    {
        self::write($string, 'blue');
    }

    /**
     * Read answer from user.
     *
     * @param string $prompt
     *
     * @return string
     */
    public static function input(string $prompt = ''): string
    {
        return readline($prompt);
    }
}
