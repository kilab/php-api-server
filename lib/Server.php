<?php

namespace Kilab\Api;

use Kilab\Api\Exception\EntityNotFoundException;
use ReflectionClass;
use ReflectionMethod;
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
     *
     * @throws EntityNotFoundException
     * @throws \LogicException
     * @throws \ReflectionException
     * @throws \InvalidArgumentException
     */
    public function run(): void
    {
        if ($this->request->getMethod() === 'OPTIONS' && $this->request->accessAllowed()) {
            $response = new JsonResponse(['status' => true], 200, Config::get('Response.Headers'));
            $response->send();
            exit;
        }

        $entityController = $this->defineControllerClass();
        $entityControllerMethod = $this->defineControllerMethod();

        if (!class_exists($entityController)) {
            throw new EntityNotFoundException(sprintf("Entity controller '%s' not found", $this->request->getEntity()));
        }
        if (!method_exists($entityController, $entityControllerMethod)) {
            throw new EntityNotFoundException(sprintf(
                'Action \'%s\' not found in \'%s\' entity',
                $entityControllerMethod,
                $entityController
            ));
        }

        $methodParams = [];

        if ($this->request->getIdentifier()) {
            $methodParams[] = $this->request->getIdentifier();
        }
        if ($this->request->getParameters()) {
            $methodParams[] = $this->request->getParameters();
        }
        if ($this->request->getRelation()) {
            $methodParams[] = $this->request->getRelation();
        }

        /** @var Controller $controller */
        $controller = new $entityController($this->request);
        $controllerMethod = new ReflectionMethod($entityController, $entityControllerMethod);
        $controllerMethod->invokeArgs($controller, $methodParams);

        $returnAsCallback = $this->request->getHeader('http_x_callback') ?? null;

        $response = new JsonResponse(
            ['status' => true, 'results' => $controller->responseData],
            $controller->responseCode,
            Config::get('Response.Headers'));

        if ($returnAsCallback) {
            $response->setCallback($returnAsCallback);
        }

        $response->setEncodingOptions(JSON_UNESCAPED_UNICODE);
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
        $entityController = sprintf('\App\\%s\\Controller\\%sController',
            ucfirst(API_VERSION),
            ucfirst($this->request->getEntity())
        );

        return $entityController;
    }

    /**
     * Define method for given request.
     *
     * @return string
     * @throws \ReflectionException
     */
    private function defineControllerMethod(): string
    {
        // check if relationship exists in entity
        if ($this->request->getMethod() === 'GET') {
            $entityName = str_singular(ucfirst($this->request->getEntity()));
            $relationship = strtolower(ltrim($this->request->getAction(), 'get'));

            $reflectionClass = new ReflectionClass('\App\\' . ucfirst(API_VERSION) . '\Entity\\' . $entityName);

            if ($reflectionClass->hasMethod($relationship)) {
                $this->request->setRelation($relationship);

                return 'getItemAction';
            }
        }

        return ucfirst($this->request->getAction()) . 'Action';
    }

}
