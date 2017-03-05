<?php
namespace Jetiny\Cache;

use \MongoBinData;
use \MongoCollection;
use \MongoDate;
use \Mongo;

class MongoDBCache extends CacheHandler
{
    
    public function setup($options)
    {
        if (isset($options['db'])) {
            $this->setDbName($options['db']);
        }
        if (isset($options['collection'])) {
            $this->setCollectionName($options['collection']);
        }
        $server =  isset($options['connection']) ? $options['connection'] : "mongodb://localhost:27017";
        $this->setHandler(new Mongo($server));
    }
    protected function doFetch($id)
    {
        $document = $this->collection->findOne(array('_id' => $id), array(self::DATA_FIELD, self::EXPIRATION_FIELD));
        if ($document === null) {
            return false;
        }
        if ($this->isExpired($document)) {
            $this->doDelete($id);
            return false;
        }
        return unserialize($document[self::DATA_FIELD]->bin);
    }
    protected function doContains($id)
    {
        $document = $this->collection->findOne(array('_id' => $id), array(self::EXPIRATION_FIELD));
        if ($document === null) {
            return false;
        }
        if ($this->isExpired($document)) {
            $this->doDelete($id);
            return false;
        }
        return true;
    }
    protected function doSave($id, $data, $lifeTime = 0)
    {
        $result = $this->collection->update(
            array('_id' => $id),
            array('$set' => array(
                self::EXPIRATION_FIELD => ($lifeTime > 0 ? new MongoDate(time() + $lifeTime) : null),
                self::DATA_FIELD => new MongoBinData(serialize($data), MongoBinData::BYTE_ARRAY),
            )),
            array('upsert' => true, 'multiple' => false)
        );
        return isset($result['ok']) ? $result['ok'] == 1 : true;
    }
    protected function doDelete($id)
    {
        $result = $this->collection->remove(array('_id' => $id));
        return isset($result['n']) ? $result['n'] == 1 : true;
    }
    protected function doFlush()
    {
        $result = $this->collection->remove();
        return isset($result['ok']) ? $result['ok'] == 1 : true;
    }
    private function isExpired(array $document)
    {
        return isset($document[self::EXPIRATION_FIELD]) &&
            $document[self::EXPIRATION_FIELD] instanceof MongoDate &&
            $document[self::EXPIRATION_FIELD]->sec < time();
    }
    
    public function setHandler($handler)
    {
        parent::setHandler($handler);
        $this->collection = $handler->selectCollection($this->dbName, $this->collectionName);
    }
    
    public function setDbName($dbName)
    {
        $this->dbName = $dbName;
    }
    
    public function setCollectionName($collectionName)
    {
        $this->collectionName = $collectionName;
    }
    
    const DATA_FIELD = 'd';
    const EXPIRATION_FIELD = 'e';
    protected $collection;
    protected $dbName;
    protected $collectionName;
}
