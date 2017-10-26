<?php

use PHPUnit\Framework\TestCase;

final class ControllerTest extends TestCase
{

    /**
     * @var \Kilab\Api\Request
     */
    protected $request;

    public function setUp()
    {
        $this->request = new \Kilab\Api\Request([], [], [], [], [], []);
    }

    public function testCanGetList(): void
    {
        $expectedResponse = ['The Astounding Gladiator', 'Professor Astounding', 'The Ice Puma'];
        $controller = new \App\Controller\TestController($this->request);
        $controller->getListAction();

        $this->assertEquals($expectedResponse, $controller->responseData);
    }

    public function testCanGetItem(): void
    {
        $expectedResponse = ['itemDetails for ID 69' => ['name' => 'Captain Neutron']];
        $controller = new \App\Controller\TestController($this->request);
        $controller->getItemAction(69);

        $this->assertEquals($expectedResponse, $controller->responseData);
    }

    public function testCanPostItem(): void
    {
        $expectedResponse = ['name' => 'Bob', 'surname' => 'DeLonge'];
        $controller = new \App\Controller\TestController($this->request);
        $controller->postItemAction($expectedResponse);

        $this->assertEquals($expectedResponse, $controller->responseData);
        $this->assertEquals(201, $controller->responseCode);
    }

    public function testCanPutItem(): void
    {
        $expectedResponse = ['name' => 'Bob', 'surname' => 'DeLonge'];
        $controller = new \App\Controller\TestController($this->request);
        $controller->putItemAction(69, $expectedResponse);

        $this->assertEquals(['item ID 69' => $expectedResponse], $controller->responseData);
    }

    public function testCanDeleteItem(): void
    {
        $controller = new \App\Controller\TestController($this->request);
        $controller->deleteItemAction(69);
        $this->assertEquals(204, $controller->responseCode);
    }

}
