<?php
/*
 *  Author: Aaron Sollman
 *  Email:  unclepong@gmail.com
 *  Date:   12/13/25
 *  Time:   7:08
*/
namespace Foamycastle\Database;

use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\{Configuration, EntityManager, ORMSetup, Mapping\Driver\AttributeDriver};
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Adapter\PhpFilesAdapter;

class PgSqlDatabase extends Database
{
    protected const DB_PARAMS = [
        'driver','dbname','host','user','password','port','schema','charset'
    ];

    public function __construct()
    {
        try {
            $this->loadParams(self::DB_PARAMS);
        } catch (\Exception $e) {
            exit($e->getMessage());
        }
        $devMode = env('MODE', 'DEV') === 'DEV';
        if($devMode){
            $queryCache = new ArrayAdapter();
            $metadataCache = new ArrayAdapter();
        }else{
            $queryCache = new PhpFilesAdapter('foamycastle-query-cache');
            $metadataCache = new PhpFilesAdapter('foamycastle-metadata-cache');
        }
        $this->config = ORMSetup::createAttributeMetadataConfiguration([], $devMode);
        $this->config->setMetadataCache($metadataCache);
        $pathsToEntities = array_values([env('ENTITY', '')]);
        $implementation = new AttributeDriver($pathsToEntities);
        $this->config->setMetadataDriverImpl($implementation);
        $this->config->setQueryCache($queryCache);

        if(PHP_VERSION_ID >= 80400){
            $this->config->enableNativeLazyObjects(true);
            //$this->config->setProxyDir('proxy');
        }else{
            //$this->config->setProxyDir('proxy');
            $this->config->setProxyNamespace('DoctrineDynamicProxy');
            if($devMode){
                $this->config->setAutoGenerateProxyClasses(true);
            }else{
                $this->config->setAutoGenerateProxyClasses(false);
            }
        }
        $this->connection = DriverManager::getConnection($this->params, $this->config);
        $this->entityManager = new EntityManager($this->connection, $this->config);
    }

}