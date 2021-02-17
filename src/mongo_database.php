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
    private Collection $articleCollection;

    function __construct()
    {
        $database_name = $_ENV["DATABASE"];
        $collection_country = $_ENV["COLLECTION_COUNTRY"];
        $collection_article = $_ENV["COLLECTION_ARTICLE"];
        $client = new Client($_ENV["CONNECTION_STRING"]);
        $db = $client->$database_name;
        $this->countryCollection = $db->$collection_country;
        $this->articleCollection = $db->$collection_article;
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
     * Insert data into the article collection
     *
     * @param array $data
     */
    function insertArticle(array $data)
    {
        $this->articleCollection->insertOne($data);
    }

    /**
     * Drop the country collection from the MongoDB database
     */
    function dropCountryCollection()
    {
        $this->countryCollection->drop();
    }

    /**
     * Drop the article collection from the MongoDB database
     */
    function dropArticleCollection()
    {
        $this->articleCollection->drop();
    }
}