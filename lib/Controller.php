<?php

namespace Kilab\Api;

use Doctrine\Common\Inflector\Inflector;
use Kilab\Api\Exception\ResourceNotFoundException;
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
            $plainEntities[] = $entity->getWholeEntity();
        }

        $this->responseData = $plainEntities;
    }

    /**
     * Get entity details.
     *
     * @param int $id
     *
     * @throws ResourceNotFoundException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     */
    public function getItemAction(int $id)
    {
        $entity = $this->repository->find($id);

        if ($entity === null) {
            $repositoryClassName = explode('\\', $this->repository->getClassName());

            throw new ResourceNotFoundException(sprintf('%s record for ID: %s not found',
                end($repositoryClassName),
                $id
            ));
        }

        $this->responseData = $entity->getWholeEntity();
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
        $resourceClassName = sprintf('\App\\%s\\Model\\%s',
            API_VERSION,
            ucfirst(Inflector::singularize($this->request->getResource()))
        );

        /** @var Resource $resourceEntity */
        $resourceEntity = new $resourceClassName();
        $resourceEntity->setWholeEntity($data);

        Db::instance()->persist($resourceEntity);
        Db::instance()->flush();

        $this->responseData = $resourceEntity;
        $this->responseCode = Response::HTTP_CREATED;
    }

    /**
     * Update existing entity object.
     *
     * @param int   $id
     * @param array $data
     */
    public function putItemAction(int $id, array $data)
    {
        $this->responseData = 'PUT//3333' . $id;
    }

    /**
     * Delete existing entity object.
     *
     * @param int $id
     *
     * @throws ResourceNotFoundException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     */
    public function deleteItemAction(int $id)
    {
        $entity = $this->repository->find($id);

        if ($entity === null) {
            throw new ResourceNotFoundException('Record for given ID not found');
        }

        // $entityRef = Db::instance()->getReference($this->repository->getClassName(), $id);
        //
        // Db::instance()->merge($entityRef);
        // Db::instance()->remove($entityRef);
        // Db::instance()->flush();

        $this->responseCode = Response::HTTP_NO_CONTENT;
    }

}
