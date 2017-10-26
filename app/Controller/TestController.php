<?php

namespace App\Controller;

use Kilab\Api\Controller;
use Symfony\Component\HttpFoundation\Response;

/**
 * This class is using to call unit tests on Controller class.
 *
 * @package App\Controller
 */
class TestController extends Controller
{

    public function getListAction(): void
    {
        $this->responseData = ['The Astounding Gladiator', 'Professor Astounding', 'The Ice Puma'];
    }

    public function getItemAction(int $id, string $relation = null): void
    {
        $this->responseData = ['itemDetails for ID ' . $id => ['name' => 'Captain Neutron']];
    }

    public function postItemAction(array $data): void
    {
        $this->responseData = $data;
        $this->responseCode = Response::HTTP_CREATED;
    }

    public function putItemAction(int $id, array $data): void
    {
        $this->responseData = ['item ID ' . $id => $data];
        $this->responseCode = Response::HTTP_OK;
    }

    public function deleteItemAction(int $id): void
    {
        $this->responseCode = Response::HTTP_NO_CONTENT;
    }
}
