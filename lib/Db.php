<?php

namespace Kilab\Api;

use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;

class Db
{
    public static function instance(): EntityManager
    {
        $devMode = Env::get('ENVIRONMENT') === 'dev';
        $entitiesDir = BASE_DIR . '/app/' . API_VERSION . '/Model';

        $config = Setup::createAnnotationMetadataConfiguration([$entitiesDir], $devMode);

        $connectionSettings = [
            'driver'   => Config::get('Database.Driver'),
            'host'     => Config::get('Database.Host'),
            'dbname'   => Config::get('Database.Name'),
            'user'     => Config::get('Database.User'),
            'password' => Config::get('Database.Password'),
        ];

        return EntityManager::create($connectionSettings, $config);
    }
}
