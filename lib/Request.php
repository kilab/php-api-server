<?php

namespace Kilab\Api;


class Request
{

    /**
     * $_SERVER superglobal array content.
     *
     * @var array
     */
    private $serverInfo = [];

    /**
     * HTTP method.
     *
     * @var string
     */
    private $method;

    /**
     * Request parameters depending on HTTP method ($_GET or $_POST data).
     *
     * @var array
     */
    private $parameters;

    /**
     * The name of the desired resource.
     *
     * @var string
     */
    private $resource;

    /**
     * The name of desired resource action to call.
     *
     * @var string
     */
    private $action;

    /**
     * Element ID of the desired resource.
     *
     * @var int
     */
    private $identifier;

    /**
     * URI path in given request.
     *
     * @var string
     */
    private $uriPath;

    /**
     * Request constructor.
     *
     * @param array $serverInfo
     */
    public function __construct(array $serverInfo)
    {
        $this->serverInfo = $serverInfo;
        $this->uriPath = isset($this->serverInfo['PATH_INFO']) ? rtrim($this->serverInfo['PATH_INFO'], '/') : null;

        $this->setMethod();
        $this->setParameters();
        $this->setResource();
        $this->setAction();
    }

    /**
     * Get HTTP request method.
     *
     * @return string
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * Set HTTP request method.
     */
    private function setMethod(): void
    {
        $httpMethod = $this->serverInfo['REQUEST_METHOD'];

        if (isset($this->serverInfo['HTTP_X_HTTP_METHOD_OVERRIDE'])) {
            $methodOverride = strtoupper($this->serverInfo['HTTP_X_HTTP_METHOD_OVERRIDE']);
            $allowedMethods = explode(', ', Config::get('Response.Headers.Access-Control-Allow-Methods'));

            if (in_array($methodOverride, $allowedMethods)) {
                $httpMethod = $methodOverride;
            }
        }

        $this->method = $httpMethod;
    }

    /**
     * Get all request parameters.
     *
     * @return array
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * Get request parameter by his name.
     *
     * @param string $key
     *
     * @return mixed|null
     */
    public function getParameter(string $key)
    {
        return $this->parameters[$key] ?? null;
    }

    /**
     * Set request parameters.
     */
    private function setParameters(): void
    {
        $requestContent = file_get_contents("php://input");
        $parameters = [];

        if ($requestContent) {
            $parameters = (array)json_decode(file_get_contents("php://input"), true);
        }

        $this->parameters = $parameters;
    }

    /**
     * Return resource name.
     *
     * @return string
     */
    public function getResource(): string
    {
        return $this->resource;
    }

    /**
     * Set request resource name.
     */
    private function setResource(): void
    {
        $resourceName = Config::get('Default.Resource');
        $resourcePath = explode('/', $this->uriPath);

        unset($resourcePath[0], $resourcePath[1]);
        $resourcePath = array_values($resourcePath);

        if ($resourcePath) {
            if (isset($resourcePath[1]) && $resourcePath[1] > 0) {
                $this->setIdentifier($resourcePath[1]);
            }

            $resourceName = $resourcePath[0];
        }

        $this->resource = $resourceName;
    }

    /**
     * Get resource action to call.
     *
     * @return string
     */
    public function getAction(): string
    {
        return $this->action;
    }

    /**
     * Set resource action to call.
     */
    private function setAction(): void
    {
        $this->action = $this->determineResourceAction();
    }

    /**
     * Get resource identifier.
     *
     * @return int
     */
    public function getIdentifier(): ?int
    {
        return $this->identifier;
    }

    /**
     * Set resource identifier.
     *
     * @param int $id
     */
    private function setIdentifier(int $id): void
    {
        $this->identifier = $id;
    }

    /**
     * Get value from HTTP header.
     *
     * @param string $key
     *
     * @return mixed|null
     */
    public function getHeader(string $key)
    {
        $value = null;

        if (isset($this->serverInfo[strtoupper($key)])) {
            $value = $this->serverInfo[strtoupper($key)];
        }

        return $value;
    }

    /**
     * Determine whether current CORS request is allowed.
     *
     * @return bool
     */
    public function accessAllowed(): bool
    {
        $allowedOrigin = Config::get('Response.Headers.Access-Control-Allow-Origin');
        $requestOrigin = $this->serverInfo['HTTP_ORIGIN'] ?? null;

        if (Config::get('Response.Headers.Access-Control-Allow-Origin') === '*') {
            return true;
        }
        if ($requestOrigin && $requestOrigin === $allowedOrigin) {
            return true;
        }

        return false;
    }

    /**
     * Determine controller action to call.
     *
     * @return string
     */
    private function determineResourceAction(): string
    {
        $action = '';
        $actionPath = explode('/', $this->uriPath);

        unset($actionPath[0], $actionPath[1]);
        $actionPath = array_values($actionPath);

        if (isset($actionPath[2])) {
            return strtolower($this->method) . str_replace('-', '', ucwords($actionPath[2], '-'));
        }

        if ($this->method === 'GET') {
            if ($this->getIdentifier()) {
                $action = 'getItem';
            } else {
                $action = 'getList';
            }
        } elseif ($this->method === 'POST') {
            if ($this->getIdentifier()) {
                $action = 'updateItem';
            } else {
                $action = 'addItem';
            }
        } elseif ($this->method === 'PUT') {
            $action = 'updateItem';
        } elseif ($this->method === 'DELETE') {
            $action = 'deleteItem';
        }

        return $action;
    }
}
