<?php

namespace App\V1\Entity;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{

    protected $fillable = ['first_name', 'last_name', 'email', 'phone', 'active'];

}
