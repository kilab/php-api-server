<?php

namespace Kilab\Api;

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Query\Builder;

class Db extends Builder
{
    private static $classInstance;

    /**
     * @return Capsule
     * @throws \LogicException
     */
    public static function instance(): Capsule
    {
        if (!self::$classInstance) {
            self::$classInstance = new Capsule();

            self::$classInstance->addConnection([
                'driver'   => Config::get('Database.Driver'),
                'host'     => Config::get('Database.Host'),
                'database' => Config::get('Database.Name'),
                'username' => Config::get('Database.User'),
                'password' => Config::get('Database.Password'),
            ]);

            self::$classInstance->setAsGlobal();
            self::$classInstance->bootEloquent();
        }

        return self::$classInstance;
    }
}
