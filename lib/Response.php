<?php

namespace Kilab\Api;


use Kilab\Api\Response\JsonResponse;

class Response
{

    /**
     * HTTP status code.
     *
     * @var int
     */
    private $statusCode;

    /**
     * Data to return in response.
     *
     * @var
     */
    private $data;

    public function __construct($data, int $statusCode = 200)
    {
        $this->statusCode = $statusCode;
        $this->data = $data;
    }

    /**
     * Return response with set HTTP status code.
     *
     * @param string $asCallback
     *
     * @return JsonResponse
     */
    public function return(string $asCallback = null): JsonResponse
    {
        return new JsonResponse($this->data, $this->statusCode, $asCallback);
    }
}
