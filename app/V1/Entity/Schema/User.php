<?php

namespace App\V1\Entity\Schema;

use Illuminate\Database\Schema\Blueprint;

class User
{

    /**
     * Entity structure object.
     *
     * @var Blueprint
     */
    public $structure;

    /**
     * User entity structure.
     */
    public function __construct()
    {
        $table = new Blueprint('employees');

        $table->increments('id');
        $table->string('first_name', 30);
        $table->string('last_name', 30);
        $table->string('email', 50);
        $table->string('phone', 9);
        $table->string('password', 60);
        $table->boolean('active')->default(0);
        $table->timestamp('updated_at');
        $table->timestamp('created_at')->useCurrent();

        $this->structure = $table;
    }

}
