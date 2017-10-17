<?php

namespace Kilab\Api;

use Illuminate\Database\Eloquent\Model;
use Kilab\Api\Exception\EntityNotFoundException;
use Symfony\Component\HttpFoundation\Response;

class Controller
{

    /**
     * Repository Entity name.
     *
     * @var string
     */
    protected $entityName;

    /**
     * Incoming request object.
     *
     * @var Request
     */
    protected $request;

    /**
     * Entity repository.
     *
     * @var Model
     */
    protected $repository;

    /**
     * Data sending in response body.
     *
     * @var mixed
     */
    public $responseData;

    /**
     * HTTP status code for response.
     *
     * @var int
     */
    public $responseCode = 200;

    /**
     * Controller constructor.
     *
     * @param Request $request
     */
    public function __construct(Request $request)
    {

        $this->request = $request;
        $entityClassName = explode('\\', get_class($this->repository->getModel()));
        $this->entityName = end($entityClassName);
    }

    /**
     * Get list of entities.
     *
     * @throws \LogicException
     */
    public function getListAction(): void
    {
        $entities = $this->repository->get()->toArray();

        if (Config::get('Entity.CamelCaseFieldNames')) {
            $entities = $this->toCamelCase($entities);
        }

        $this->responseData = $entities;
    }

    /**
     * Get entity details.
     *
     * @param int         $id
     * @param string|null $relation
     *
     * @throws EntityNotFoundException
     * @throws \LogicException
     */
    public function getItemAction(int $id, string $relation = null): void
    {
        $entity = $this->repository->find($id);

        if ($entity === null) {
            throw new EntityNotFoundException(sprintf('%s record for ID: %s not found', $this->entityName, $id));
        }

        $entityData = $entity->toArray();

        if ($relation) {
            $relationData = $entity->{$relation}->toArray();

            if (Config::get('Entity.CamelCaseFieldNames')) {
                $relationData = $this->toCamelCase($relationData);
            }

            $entityData[$relation] = $relationData;
        }

        if (Config::get('Entity.CamelCaseFieldNames')) {
            $entityData = $this->toCamelCase($entityData);
        }

        $this->responseData = $entityData;
    }

    /**
     * Create new object of entity.
     *
     * @param array $data
     *
     * @throws \LogicException
     */
    public function postItemAction(array $data): void
    {
        if (Config::get('Entity.CamelCaseFieldNames')) {
            $data = $this->toSnakeCase($data);
        }

        $entity = $this->repository->create($data);

        $this->responseData = $entity;
        $this->responseCode = Response::HTTP_CREATED;
    }

    /**
     * Update existing entity object.
     *
     * @param int   $id
     * @param array $data
     *
     * @throws EntityNotFoundException
     * @throws \LogicException
     */
    public function putItemAction(int $id, array $data): void
    {
        if (Config::get('Entity.CamelCaseFieldNames')) {
            $data = $this->toSnakeCase($data);
        }

        $entity = $this->repository->find($id);

        if ($entity === null) {
            throw new EntityNotFoundException(sprintf('%s record for ID: %s not found', $this->entityName, $id));
        }

        $entity->update($data);

        $this->responseData = $entity;
        $this->responseCode = Response::HTTP_OK;
    }

    /**
     * Delete existing entity object.
     *
     * @param int $id
     *
     * @throws EntityNotFoundException
     * @throws \ReflectionException
     * @throws \Exception
     */
    public function deleteItemAction(int $id): void
    {
        $entity = $this->repository->find($id);

        if ($entity === null) {
            throw new EntityNotFoundException(sprintf('%s record for ID: %s not found', $this->entityName, $id));
        }

        $entity->delete();

        $this->responseCode = Response::HTTP_NO_CONTENT;
    }

    /**
     * Convert entity fields to camelCase notation.
     *
     * @param array $entity
     *
     * @return array
     */
    protected function toCamelCase(array $entity): array
    {
        $convertedEntity = [];

        if (is_array(current($entity))) {
            foreach ($entity as $ent) {
                $convertedEntity[] = $this->toCamelCase($ent);
            }
        } else {
            foreach ($entity as $field => $value) {
                $convertedEntity[camel_case($field)] = $value;
            }
        }

        return $convertedEntity;
    }

    /**
     * Convert entity fields to snake_case notation.
     *
     * @param array $entity
     *
     * @return array
     */
    protected function toSnakeCase(array $entity): array
    {
        $convertedEntity = [];

        if (is_array(current($entity))) {
            foreach ($entity as $ent) {
                $convertedEntity[] = $this->toSnakeCase($ent);
            }
        } else {
            foreach ($entity as $field => $value) {
                $convertedEntity[snake_case($field)] = $value;
            }
        }

        return $convertedEntity;
    }
}
