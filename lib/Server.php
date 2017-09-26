<?php

namespace Kilab\Api;

use ReflectionMethod;
use Kilab\Api\Exception\ResourceNotFoundException;
use Symfony\Component\HttpFoundation\JsonResponse;

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
     * @throws ResourceNotFoundException
     */
    public function run(): void
    {
        if ($this->request->getMethod() === 'OPTIONS' && $this->request->accessAllowed()) {
            $response = new JsonResponse(['status' => true], 200, Config::get('Response.Headers'));
            $response->send();
            exit;
        }

        $resourceController = $this->defineControllerClass();
        $resourceControllerMethod = $this->defineControllerMethod();

        if (!class_exists($resourceController)) {
            throw new ResourceNotFoundException(sprintf('Resource \'%s\' not found', $this->request->getResource()));
        }
        if (!method_exists($resourceController, $resourceControllerMethod)) {
            throw new ResourceNotFoundException(sprintf(
                'Action \'%s\' not found in \'%s\' resource',
                $resourceControllerMethod,
                $this->request->getResource()
            ));
        }

        $methodParams = [];

        if ($this->request->getIdentifier()) {
            $methodParams[] = $this->request->getIdentifier();
        }
        if ($this->request->getParameters()) {
            $methodParams[] = $this->request->getParameters();
        }

        /** @var Controller $controller */
        $controller = new $resourceController($this->request);
        $controllerMethod = new ReflectionMethod($resourceController, $resourceControllerMethod);
        $controllerMethod->invokeArgs($controller, $methodParams);

        $returnAsCallback = $this->request->getHeader('http_x_callback') ?? null;

        $response = new JsonResponse($controller->responseData,
            $controller->responseCode,
            Config::get('Response.Headers'));

        if ($returnAsCallback) {
            $response->setCallback($returnAsCallback);
        }

        $response->send();
        exit;
    }

    /**
     * Define path to controller class.
     *
     * @return string
     */
    private function defineControllerClass(): string
    {
        $resourceController = sprintf('\App\\%s\\Controller\\%sController',
            ucfirst(API_VERSION),
            ucfirst($this->request->getResource())
        );

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
