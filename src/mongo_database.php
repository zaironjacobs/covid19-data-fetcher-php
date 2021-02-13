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
    private Collection $countryCollection;
    private Collection $newsCollection;

    function __construct()
    {
        $database_name = $_ENV["DATABASE"];
        $collection_country = $_ENV["COLLECTION_COUNTRY"];
        $collection_news = $_ENV["COLLECTION_NEWS"];
        $client = new Client($_ENV["CONNECTION_STRING"]);
        $db = $client->$database_name;
        $this->countryCollection = $db->$collection_country;
        $this->newsCollection = $db->$collection_news;
    }

    /**
     * Insert data into the country collection
     *
     * @param array $data
     */
    function insertCountry(array $data)
    {
        $this->countryCollection->insertOne($data);
    }

    /**
     * Insert data into the news collection
     *
     * @param array $data
     */
    function insertNews(array $data)
    {
        $this->newsCollection->insertOne($data);
    }

    /**
     * Drop the country collection from the MongoDB database
     */
    function dropCountryCollection()
    {
        $this->countryCollection->drop();
    }

    /**
     * Drop the news collection from the MongoDB database
     */
    function dropNewsCollection()
    {
        $this->newsCollection->drop();
    }
}