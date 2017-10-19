<?php

namespace App\V1\Controller;

use Kilab\Api\Controller;

class InfoController extends Controller
{

    public function getListAction(): void
    {
        $this->responseData = ['availableEndpoints' => $this->getEndPoints()];
    }

    /**
     * Find all endpoint controllers in app directory.
     *
     * @return array
     */
    private function getEndPoints(): array
    {
        $endpoints = [];
        $controllerRepository = BASE_DIR . 'app/' . strtoupper(API_VERSION) . '/Controller';
        $controllers = scandir($controllerRepository, SCANDIR_SORT_ASCENDING);

        foreach ($controllers as $controller) {
            if ($controller === '.' || $controller === '..') {
                continue;
            }

            $endpoints[] = strtolower(substr($controller, 0, -14));
        }

        return $endpoints;
    }
}
