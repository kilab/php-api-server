<?php

namespace App\Entity\Schema;

use Illuminate\Database\Schema\Blueprint;

class Doctor
{

    /**
     * Entity structure object.
     *
     * @var Blueprint
     */
    public $structure;

    /**
     * Doctor entity structure.
     */
    public function __construct()
    {
        $table = new Blueprint('doctors');

        $table->increments('id');
        $table->string('first_name', 30);
        $table->string('last_name', 30);
        $table->string('email', 50);
        $table->timestamp('updated_at');
        $table->timestamp('created_at')->useCurrent();

        $this->structure = $table;
    }

}
