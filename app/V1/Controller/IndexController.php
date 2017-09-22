<?php

namespace App\V1\Controller;

use Kilab\Api\Controller;

class IndexController extends Controller
{

    public function HelloAction() {
        return 'Hello World!';
    }
}
