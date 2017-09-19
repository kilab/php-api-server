<?php

namespace App\Controller;

use Kilab\Api\Controller;

class IndexController extends Controller
{

    public function listAction() {
        return [1, 2, 3];
    }
}
