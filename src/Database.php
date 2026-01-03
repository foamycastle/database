<?php
/*
 *  Author: Aaron Sollman
 *  Email:  unclepong@gmail.com
 *  Date:   12/12/25
 *  Time:   13:51
*/


namespace Foamycastle\Database;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Tools\DsnParser;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\ORMSetup;

abstract class Database
{
    protected const DB_PARAMS = [];
    protected array $params = [];
    protected EntityManager $entityManager;
    protected Connection $connection;
    protected Configuration $config;

    /**
     * @template T of EntityRepository
     * @param class-string<T> $class
     * @return null|object<T>
     */
    public function getRepo(string $class): ?object
    {
        return $this->entityManager->getRepository($class);
    }

    /**
     * @return EntityManager
     */
    public function getEntityManager(): EntityManager
    {
        return $this->entityManager;
    }

    protected function loadParams(array $loadParams = [])
    {
        if (!defined('PARSENV_LOADED')) {
            if (empty($params)) {
                throw new \Exception('Database cannot load parameters.');
            }
        }
        $params=[];
        if (!constant('PARSENV_LOADED')) {
            parsenv(constant('FOAMYCASTLE_ROOT') . DIRECTORY_SEPARATOR . '.env');
            $params = get_env_prefix("DB_");
        } elseif (defined('DATABASE_URL')) {
            $dsnParser = new DsnParser();
            $params = $dsnParser->parse(env('DATABASE_URL'));
        } else {
            $params = get_env_prefix("DB_");
        }

        foreach ($loadParams as $param) {
            $this->params[$param] = $params[$param] ?? null;
        }
    }
}