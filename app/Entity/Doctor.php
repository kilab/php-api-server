<?php

namespace App\Entity;

use Illuminate\Database\Eloquent\Model;

class Doctor extends Model
{
    protected $fillable = ['first_name', 'last_name', 'email'];

    protected $visible = ['id', 'first_name', 'last_name', 'email', 'updated_at', 'created_at'];

    public function visits()
    {
        return $this->hasMany(Visit::class);
    }
}
