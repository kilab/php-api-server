<?php

namespace Kilab\Api;

use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

class Request extends SymfonyRequest
{

    /**
     * The name of the desired entity.
     *
     * @var string
     */
    protected $entity;

    /**
     * The name of desired entity action to call.
     *
     * @var string
     */
    protected $action;

    /**
     * Element ID of the desired entity.
     *
     * @var int
     */
    protected $identifier;

    /**
     * Current entity relation to include.
     *
     * @var string
     */
    protected $relation;

    /**
     * Request constructor.
     *
     * @param array $get
     * @param array $post
     * @param array $attr
     * @param array $cookie
     * @param array $files
     * @param array $serverInfo
     *
     * @throws \LogicException
     */
    public function __construct($get, $post, $attr, $cookie, $files, $serverInfo)
    {
        $content = file_get_contents('php://input');

        parent::__construct($get, $post, [], $cookie, $files, $serverInfo, $content);
        parent::enableHttpMethodParameterOverride();

        if ($this->headers->get('Content-Type') === 'application/json') {
            if ($content !== '') {
                $this->request->replace(json_decode($content, true));
            }
        }

        $this->setEntity();
        $this->setAction();
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
     *
     * @throws \LogicException
     */
    private function setEntity(): void
    {
        $entityName = Config::get('Default.Entity');
        $entityPath = explode('/', $this->getPathInfo());

        unset($entityPath[0]);
        $entityPath = array_values($entityPath);

        if ($entityPath) {
            if (isset($entityPath[1]) && $entityPath[1] > 0) {
                $this->setIdentifier($entityPath[1]);
            }

            $entityName = $entityPath[0];
        } else {
            header('Location: /' . $entityName);
            exit(0);
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
     * Get entity relation name.
     *
     * @return string
     */
    public function getRelation(): ?string
    {
        return $this->relation;
    }

    /**
     * Set entity relation name.
     *
     * @param string $relation
     */
    public function setRelation(string $relation): void
    {
        $this->relation = $relation;
    }

    /**
     * Determine whether current CORS request is allowed.
     *
     * @return bool
     * @throws \LogicException
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
        $actionPath = explode('/', $this->getPathInfo());

        unset($actionPath[0]);
        $actionPath = array_values($actionPath);

        if (isset($actionPath[2])) {
            return strtolower($this->method) . str_replace('-', '', ucwords($actionPath[2], '-'));
        }

        if ($this->getMethod() === 'GET') {
            $action = 'getList';

            if ($this->getIdentifier()) {
                $action = 'getItem';
            } elseif (isset($actionPath[1])) {
                $action = $actionPath[1];
            }
        } elseif ($this->getMethod() === 'POST') {
            $action = 'postItem';

            if ($this->getIdentifier()) {
                $action = 'putItem';
            }
        } elseif ($this->getMethod() === 'PUT') {
            $action = 'putItem';
        } elseif ($this->getMethod() === 'DELETE') {
            $action = 'deleteItem';
        }

        return $action;
    }
}
