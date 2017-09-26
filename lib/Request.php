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
     * The name of the desired entity.
     *
     * @var string
     */
    private $entity;

    /**
     * The name of desired entity action to call.
     *
     * @var string
     */
    private $action;

    /**
     * Element ID of the desired entity.
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
        $this->setEntity();
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
     * Return entity name.
     *
     * @return string
     */
    public function getEntity(): string
    {
        return $this->entity;
    }

    /**
     * Set request entity name.
     */
    private function setEntity(): void
    {
        $entityName = Config::get('Default.Entity');
        $entityPath = explode('/', $this->uriPath);

        unset($entityPath[0], $entityPath[1]);
        $entityPath = array_values($entityPath);

        if ($entityPath) {
            if (isset($entityPath[1]) && $entityPath[1] > 0) {
                $this->setIdentifier($entityPath[1]);
            }

            $entityName = $entityPath[0];
        }

        $this->entity = $entityName;
    }

    /**
     * Get entity action to call.
     *
     * @return string
     */
    public function getAction(): string
    {
        return $this->action;
    }

    /**
     * Set entity action to call.
     */
    private function setAction(): void
    {
        $this->action = $this->determineEntityAction();
    }

    /**
     * Get entity identifier.
     *
     * @return int
     */
    public function getIdentifier(): ?int
    {
        return $this->identifier;
    }

    /**
     * Set entity identifier.
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
    private function determineEntityAction(): string
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
                $action = 'putItem';
            } else {
                $action = 'postItem';
            }
        } elseif ($this->method === 'PUT') {
            $action = 'putItem';
        } elseif ($this->method === 'DELETE') {
            $action = 'deleteItem';
        }

        return $action;
    }
}
