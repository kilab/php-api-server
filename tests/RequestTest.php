<?php

use PHPUnit\Framework\TestCase;

final class RequestTest extends TestCase
{

    public function testCanGetEntity(): void
    {
        $server = ['REQUEST_URI' => '/test'];
        $request = new \Kilab\Api\Request([], [], [], [], [], $server);

        $this->assertEquals('test', $request->getEntity());
    }

    public function testCanGetAction(): void
    {
        $server = ['REQUEST_URI' => '/test'];
        $request = new \Kilab\Api\Request([], [], [], [], [], $server);

        $this->assertEquals('getList', $request->getAction());

        $server = ['REQUEST_URI' => '/test/1'];
        $request = new \Kilab\Api\Request([], [], [], [], [], $server);

        $this->assertEquals('getItem', $request->getAction());

        $server = ['REQUEST_URI' => '/test', 'REQUEST_METHOD' => 'POST'];
        $request = new \Kilab\Api\Request([], [], [], [], [], $server);

        $this->assertEquals('postItem', $request->getAction());

        $server = ['REQUEST_URI' => '/test/1', 'REQUEST_METHOD' => 'POST'];
        $request = new \Kilab\Api\Request([], [], [], [], [], $server);

        $this->assertEquals('putItem', $request->getAction());

        $server = ['REQUEST_URI' => '/test/1', 'REQUEST_METHOD' => 'PUT'];
        $request = new \Kilab\Api\Request([], [], [], [], [], $server);

        $this->assertEquals('putItem', $request->getAction());

        $server = ['REQUEST_URI' => '/test/1/customAction'];
        $request = new \Kilab\Api\Request([], [], [], [], [], $server);

        $this->assertEquals('test', $request->getEntity());
        $this->assertEquals('CustomAction', $request->getAction());
    }

    public function testCanGetIdentifier(): void
    {
        $server = ['REQUEST_URI' => '/test/1/relation'];
        $request = new \Kilab\Api\Request([], [], [], [], [], $server);

        $this->assertEquals(1, $request->getIdentifier());
    }
}
