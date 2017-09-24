<?php

namespace Kilab\Api\Exception;

use Exception;
use Throwable;

class ResourceNotFoundException extends Exception
{
    public function __construct($message = "", $code = 404, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
