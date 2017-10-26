<?php

use PHPUnit\Framework\TestCase;

final class ConsoleTest extends TestCase
{
    public function testValidArgumentParse(): void
    {
        $sampleCommandParams = explode(' ', 'bin/console.php generate all --name=Order');
        $parsedParams = \Kilab\Api\Console::parseArguments($sampleCommandParams);
        $expectedParams = [
            'command'   => 'generate',
            'operation' => 'all',
            'name'      => 'Order',
        ];

        $this->assertEquals($expectedParams, $parsedParams);
    }

    public function testGetColorCode(): void
    {
        $consoleColor = \Kilab\Api\Console::colorCode('red');

        $this->assertEquals(31, $consoleColor);
    }

    public function testWrite(): void
    {
        $this->expectOutputString("\033[39mTest\033[39m");

        \Kilab\Api\Console::write('Test');
    }

    public function testWriteColor(): void
    {
        $this->expectOutputString("\033[31mTest\033[39m");

        \Kilab\Api\Console::write('Test', 'red');
    }

    public function testWriteLine(): void
    {
        $this->expectOutputString("\033[39mTest\n\033[39m");

        \Kilab\Api\Console::writeLine('Test');
    }

    public function testWriteLineColor(): void
    {
        $this->expectOutputString("\033[31mTest\n\033[39m");

        \Kilab\Api\Console::writeLine('Test', 'red');
    }
}
