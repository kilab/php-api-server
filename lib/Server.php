<?php

namespace Kilab\Api;

use ReflectionMethod;
use Kilab\Api\Exception\ResourceNotFoundException;

class Server
{

    /**
     * @var Request
     */
    private $request;

    /**
     * Api constructor.
     *
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Run server app.
     *
     * @throws ResourceNotFoundException
     */
    public function run(): void
    {
        if ($this->request->getMethod() === 'OPTIONS' && $this->request->accessAllowed()) {
            $response = new Response(['status' => true ]);
            $response->return();
        }

        $resourceController = $this->defineControllerClass();
        $resourceControllerMethod = $this->defineControllerMethod();

        if (!class_exists($resourceController)) {
            throw new ResourceNotFoundException('Resource \'' . $this->request->getResource() . '\' not found');
        }
        if (!method_exists($resourceController, $resourceControllerMethod)) {
            throw new ResourceNotFoundException('Requested action not found in ' . $this->request->getResource() . ' resource');
        }

        $methodParams = [];

        if ($this->request->getIdentifier()) {
            $methodParams[] = $this->request->getIdentifier();
        }
        if ($this->request->getParameters()) {
            $methodParams[] = $this->request->getParameters();
        }

        $controllerMethod = new ReflectionMethod($resourceController, $resourceControllerMethod);
        $controllerResponse = $controllerMethod->invokeArgs(new $resourceController, $methodParams);

        $returnAsCallback = $this->request->getHeader('http_x_callback') ?? null;

        $response = new Response($controllerResponse);
        $response->return($returnAsCallback);
    }

    /**
     * Define path to controller class.
     *
     * @return string
     */
    private function defineControllerClass(): string
    {
        $resourceController = '\App\\' . ucfirst(API_VERSION) . '\\Controller\\' . ucfirst($this->request->getResource()) . 'Controller';

        return $resourceController;
    }

    /**
     * Define method for given request.
     *
     * @return string
     */
    private function defineControllerMethod(): string
    {
        return ucfirst($this->request->getAction()) . 'Action';
    }

}
