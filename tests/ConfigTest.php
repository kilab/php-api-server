<?php

use PHPUnit\Framework\TestCase;

final class ConfigTest extends TestCase
{
    public function testCanReadValue(): void
    {
        $configValue = \Kilab\Api\Config::get('Debug');

        $this->assertInternalType('bool', $configValue);
    }

}
