<?php

namespace Kilab\Api;

use Doctrine\Common\Inflector\Inflector;
use Kilab\Api\Exception\EntityNotFoundException;
use JMS\Serializer\SerializerBuilder;
use Symfony\Component\HttpFoundation\Response;

class Controller
{

    /**
     * Incming request object.
     *
     * @var Request
     */
    protected $request;

    /**
     * Patient repository.
     *
     * @var \Doctrine\ORM\EntityRepository
     */
    protected $repository;

    /**
     * Data sending in response body.
     *
     * @var mixed
     */
    public $responseData = null;

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
    }

    /**
     * Get list of entities.
     */
    public function getListAction()
    {
        $entities = $this->repository->findAll();
        $plainEntities = [];

        foreach ($entities as $entity) {
            $plainEntities[] = $this->serializeToArray($entity->getWholeEntity());
        }

        $this->responseData = $plainEntities;
    }

    /**
     * Get entity details.
     *
     * @param int $id
     *
     * @throws EntityNotFoundException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     */
    public function getItemAction(int $id)
    {
        $entity = $this->repository->find($id);

        if ($entity === null) {
            $repositoryClassName = explode('\\', $this->repository->getClassName());

            throw new EntityNotFoundException(sprintf('%s record for ID: %s not found',
                end($repositoryClassName),
                $id
            ));
        }

        $this->responseData = $this->serializeToArray($entity);
    }

    /**
     * Create new object of entity.
     *
     * @param array $data
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function postItemAction(array $data)
    {
        $entityClassName = sprintf('\App\\%s\\Entity\\%s',
            API_VERSION,
            ucfirst(Inflector::singularize($this->request->getEntity()))
        );

        $entity = new $entityClassName();
        $entity->setWholeEntity($data);

        $em = Db::instance();
        $em->persist($entity);
        $em->flush();

        $this->responseData = $this->serializeToArray($entity);
        $this->responseCode = Response::HTTP_CREATED;
    }

    /**
     * Update existing entity object.
     *
     * @param int   $id
     * @param array $data
     *
     * @throws EntityNotFoundException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     */
    public function putItemAction(int $id, array $data)
    {
        $em = Db::instance();
        $entity = $em->getRepository($this->repository->getClassName())->find($id);

        if ($entity === null) {
            $repositoryClassName = explode('\\', $this->repository->getClassName());

            throw new EntityNotFoundException(sprintf('%s record for ID: %s not found',
                end($repositoryClassName),
                $id
            ));
        }

        $entity->setWholeEntity($data);

        $em->flush();

        $this->responseData = $this->serializeToArray($entity);
        $this->responseCode = Response::HTTP_OK;
    }

    /**
     * Delete existing entity object.
     *
     * @param int $id
     *
     * @throws EntityNotFoundException
     * @throws \Doctrine\Common\Persistence\Mapping\MappingException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     * @throws \ReflectionException
     */
    public function deleteItemAction(int $id)
    {
        $entity = $this->repository->find($id);

        if ($entity === null) {
            $repositoryClassName = explode('\\', $this->repository->getClassName());

            throw new EntityNotFoundException(sprintf('%s record for ID: %s not found',
                end($repositoryClassName),
                $id
            ));
        }

        $em = Db::instance();

        $entityRef = $em->getReference($this->repository->getClassName(), $id);

        $em->remove($entityRef);
        $em->flush();

        $this->responseCode = Response::HTTP_NO_CONTENT;
    }

    /**
     * Serialize entity object to array.
     *
     * @param $entity
     *
     * @return array
     */
    protected function serializeToArray($entity): array
    {
        $serializer = SerializerBuilder::create()->build();

        return $serializer->toArray($entity);
    }

}
