<?php

/**
 * PHP version 7.4
 *
 * @author      Zairon Jacobs <zaironjacobs@gmail.com>
 */

use MongoDB\Client;
use MongoDB\Collection;

/**
 * MongoDB
 *
 * @author      Zairon Jacobs <zaironjacobs@gmail.com>
 */
class MongoDatabase
{
    private Collection $collection;

    function __construct()
    {
        $database_name = $_ENV["DATABASE"];
        $collection_name = $_ENV["COLLECTION"];

        $client = new Client($_ENV["CONNECTION_STRING"]);
        $db = $client->$database_name;
        $this->collection = $db->$collection_name;
    }

    /**
     * Insert data into the collection
     *
     * @param array $data
     */
    function insert(array $data)
    {
        $this->collection->insertOne($data);
    }

    /**
     * Drop the collection from the MongoDB database
     */
    function dropCollection()
    {
        $this->collection->drop();
    }
}