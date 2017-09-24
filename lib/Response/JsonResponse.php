<?php

namespace Kilab\Api\Response;


use Kilab\Api\Config;

class JsonResponse
{

    /**
     * JsonResponse constructor.
     *
     * @param        $content
     * @param int $statusCode
     * @param string $asCallback
     */
    public function __construct($content, int $statusCode = 200, string $asCallback = null)
    {
        http_response_code($statusCode);
        header('Content-type: application/json');

        foreach (Config::get('Response.Headers') as $header => $value) {
            if ($value !== '') {
                header($header . ': ' . $value);
            }
        }

        $jsonContent = json_encode($content);

        if ($asCallback) {
            $jsonContent = $asCallback . '(' . $jsonContent . ')';
        }

        echo $jsonContent;
        exit;
    }

}
