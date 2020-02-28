<?php

namespace App\Service;

use PDO;
use UnexpectedValueException;
use Illuminate\Container\Container;
use Illuminate\Support\Fluent;
use Illuminate\Database\DatabaseManager;
use Illuminate\Database\Connectors\ConnectionFactory;
use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use ReflectionClass;

class EloquentService
{
    private $container;
    private $manager;
    
    public function __construct(array $config)
    {
        $this->container = new Container;
        $this->container->instance('config', new Fluent);
        $this->container['config']['database.fetch'] = PDO::FETCH_OBJ;
        $this->container['config']['database.default'] = 'default';
        $this->manager = new DatabaseManager(
            $this->container, new ConnectionFactory($this->container)
        );        
        $this->addConnection($config);
        Eloquent::setConnectionResolver($this->manager);
        if (isset($config['morphMap'])) {
            Relation::morphMap($config['morphMap']);
        }
    }
    
    public function addConnection(array $config, $name = 'default')
    {
        $connections = $this->container['config']['database.connections'];
        $connections[$name] = $config;
        $this->container['config']['database.connections'] = $connections;
    }
    
    public function getConnection($name = null)
    {
        return $this->manager->connection($name);
    }
    
    public function table($table, $connection = null)
    {
        return $this->getConnection($connection)->table($table);
    }
    
    public function schema($connection = null)
    {
        return $this->getConnection($connection)->getSchemaBuilder();
    }
    
    public function query($model, $with = null)
    {
        $refl = new ReflectionClass(str_replace(':', '\\Model\\', '\\'.$model));
        if (!$refl->isSubclassOf(Eloquent::class)) {
            throw new UnexpectedValueException('Unsupported Model');
        }
        if (is_null($with)) {
            return $refl->newInstance()->newQuery();
        } else {
            return $refl->newInstance()->with($with);
        }
    }

    public function create($model, $attributes = [])
    {
        $refl = new ReflectionClass(str_replace(':', '\\Model\\', '\\'.$model));
        if (!$refl->isSubclassOf(Eloquent::class)) {
            throw new UnexpectedValueException('Unsupported Model');
        }
        $entity = $refl->newInstance($attributes);
        return $entity;
    }

    public function createAndSave($model, $attributes = [])
    {
        $entity = $this->create($model, $attributes);
        $entity->save();
        return $entity;
    }

    public function findDuplicatedFields($model, $instance, $fields = [])
    {
        $dupFields = [];
        $qry = $this->query($model);
        if (is_array($instance)) {
            $queryFields = $instance;
            $fields = array_keys($instance);
        } else {
            if ($instance->exists) {
                $qry = $qry->where('id', '!=', $instance->id);
            }
            $queryFields = array_intersect_key(
                $instance->toArray(), array_flip($fields)
            );
        }
        $qry = $qry->where(function ($q) use ($queryFields) {
            $q->where($queryFields, null, null, 'or');
        });
        $dupli = $qry->first();
        if (isset($dupli)) {
            $dupli->setVisible($fields);
            $dupFields = array_keys(
                array_intersect_assoc($queryFields, $dupli->toArray())
            );
        }
        return $dupFields;
    }

    public function retrieve($model, $key, $params = null, $with = null)
    {
        $id = isset($params[$key]) ?? $key;
        if (ctype_alnum($id)) {
            return $this->db->query($model, $with)->find($id);
        }
        return null;
    }

    public function retrieveOrFail($model, $key, $params = null, $with = null)
    {
        $id = isset($params[$key]) ?? $key;
        if (ctype_alnum($id)) {
            return $this->db->query($model, $with)->findOrFail($id);
        }
        throw (new ModelNotFoundException)->setModel($model, $id);
    }
    
    public function getContainer()
    {
        return $this->container;
    }
    
    public function setContainer(Container $container)
    {
        $this->container = $container;
    }
    
    public function getEventDispatcher()
    {
        if ($this->container->bound('events')) {
            return $this->container['events'];
        }
    }
    
    public function setEventDispatcher(Dispatcher $dispatcher)
    {
        $this->container->instance('events', $dispatcher);
    }
    
    public function setFetchMode($fetchMode)
    {
        $this->container['config']['database.fetch'] = $fetchMode;
        return $this;
    }
    
    public function getDatabaseManager()
    {
        return $this->manager;
    }
}
