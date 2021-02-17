<?php

/**
 * PHP version 7.4
 *
 * @author      Zairon Jacobs <zaironjacobs@gmail.com>
 */


require("models/country.php");
require("models/article.php");
require("mongo_database.php");

use MongoDB\BSON\UTCDateTime;

/**
 * Fetch and save data of each country to a MongoDB database.
 * Fetch and save articles related to COVID-19 to a MongoDB database.
 *
 * @author      Zairon Jacobs <zaironjacobs@gmail.com>
 */
class App
{
    private string $csvFileName;

    private array $csvHeader = [];
    private array $csvRows = [];
    private array $countryObjects = [];
    private array $articleObjects = [];

    private int $totalDeaths = 0;
    private int $totalActive = 0;
    private int $totalRecovered = 0;
    private int $totalConfirmed = 0;

    private MongoDatabase $mongoDatabase;

    function __construct()
    {
        $this->mongoDatabase = new MongoDatabase();
    }

    /**
     * Main function for initialization
     */
    function init()
    {
        echo "Downloading data..." . "\n";
        $this->downloadCsvFile();
        $this->fetchArticles();

        echo "Saving data to database..." . "\n";
        $this->setCsvHeader();
        $this->setRowsData();
        $this->createCountryObjects();
        $this->populateCountryObjects();
        $this->saveArticleDataToDb();
        $this->saveCountryDataToDb();

        echo "Finished" . "\n";
    }

    /**
     * Download any file to the data dir
     *
     * @param string $url
     * @return bool
     */
    private function download(string $url): bool
    {
        if (is_dir(DATA_DIR)) {
            $files = glob(DATA_DIR . '/*');
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
            rmdir(DATA_DIR);
        }
        mkdir(DATA_DIR);

        $file_name = basename($url);
        $content = @file_get_contents($url);
        if ($content === FALSE) {
            return FALSE;
        } else {
            file_put_contents(DATA_DIR . $file_name, $content);
            return TRUE;
        }
    }

    /**
     * Download the csv file
     */
    private function downloadCsvFile()
    {
        $tries = 90;
        for ($i = 0; $i < $tries; $i++) {
            $date = date('m-d-Y', strtotime("-" . $i . "days"));
            $this->csvFileName = $date . ".csv";
            $url = sprintf(DATA_URL, $this->csvFileName);
            if ($this->download($url) === TRUE) {
                break;
            } else {
                if ($i === $tries - 1) {
                    echo "Download failed: Unable to find the latest csv file for the last " . $tries . " days";
                    exit;
                }
                continue;
            }
        }
    }

    /**
     * Return an array with all country names
     *
     * @return array
     */
    private function getCountryNamesArray(): array
    {
        $countryNames = [];
        foreach ($this->csvRows as $row) {
            for ($i = 0; $i <= count($row); $i++) {
                $countryColNum = array_search(COL_COUNTRY, $this->csvHeader);
                array_push($countryNames, $row[$countryColNum]);
            }
        }
        array_push($countryNames, WORLDWIDE);
        return array_unique($countryNames);
    }

    /**
     * Create country objects of all countries
     */
    private function createCountryObjects()
    {
        $lastUpdatedBySourceTime = $this->getLastUpdatedBySourceTime();
        $countryNames = $this->getCountryNamesArray();
        foreach ($countryNames as $countryName) {
            $country = new Country();
            $country->setName($countryName);
            $country->setLastUpdatedBySourceAt($lastUpdatedBySourceTime);

            $tmp_array = array($countryName => $country);
            $this->countryObjects = array_merge($this->countryObjects, $tmp_array);
        }
    }

    /**
     * Retrieve the header from the csv file inside the data dir
     */
    private function setCsvHeader()
    {
        if (($handle = fopen(DATA_DIR . $this->csvFileName, "r")) !== FALSE) {
            $row = fgetcsv($handle);
            $this->csvHeader = (array)$row;
            fclose($handle);
        } else {
            die("Error reading the csv file's header");
        }
    }

    /**
     * Retrieve all rows from the csv file inside the data dir
     */
    private function setRowsData()
    {
        $rowsData = [];
        if (($handle = fopen(DATA_DIR . $this->csvFileName, "r")) !== FALSE) {
            $counter = 0;
            while (($row = fgetcsv($handle, 1000)) !== FALSE) {
                if ($counter === 0) {
                    $counter++;
                    continue;
                }
                array_push($rowsData, $row);
                $counter++;
            }
            fclose($handle);
        }
        $this->csvRows = $rowsData;
    }

    /**
     * Populate all country objects with data retrieved from the csv file
     */
    private function populateCountryObjects()
    {
        $countryColNum = array_search(COL_COUNTRY, $this->csvHeader);
        $deathsColNum = array_search(COL_DEATHS, $this->csvHeader);
        $confirmedColNum = array_search(COL_CONFIRMED, $this->csvHeader);
        $activeColNum = array_search(COL_ACTIVE, $this->csvHeader);
        $recoveredColNum = array_search(COL_RECOVERED, $this->csvHeader);

        function getCaseCount($rowData, $colNum)
        {
            $case = $rowData[$colNum];
            if ($case < 0) {
                $case = abs($case);
            }
            return $case;
        }

        foreach ($this->csvRows as $rowData) {
            $countryName = $rowData[$countryColNum];

            $deaths = getCaseCount($rowData, $deathsColNum);
            $this->totalDeaths += (int)$deaths;

            $confirmed = getCaseCount($rowData, $confirmedColNum);
            $this->totalConfirmed += (int)$confirmed;

            $active = getCaseCount($rowData, $activeColNum);
            $this->totalActive += (int)$active;

            $recovered = getCaseCount($rowData, $recoveredColNum);
            $this->totalRecovered += (int)$recovered;

            $country = $this->countryObjects[$countryName];
            $country->incrementDeaths((int)$deaths);
            $country->incrementConfirmed((int)$confirmed);
            $country->incrementActive((int)$active);
            $country->incrementRecovered((int)$recovered);
        }

        $country_worldwide = $this->countryObjects[WORLDWIDE];
        $country_worldwide->incrementDeaths((int)$this->totalDeaths);
        $country_worldwide->incrementConfirmed((int)$this->totalConfirmed);
        $country_worldwide->incrementActive((int)$this->totalActive);
        $country_worldwide->incrementRecovered((int)$this->totalRecovered);
    }

    /**
     * Return the last updated time of the data
     *
     * @return UTCDateTime
     */
    private function getLastUpdatedBySourceTime(): UTCDateTime
    {
        $lastUpdateColNum = array_search(COL_LAST_UPDATE, $this->csvHeader);
        $dateString = $this->csvRows[0][$lastUpdateColNum];
        try {
            $dateTime = new DateTime(date('Y-m-dTh:i:s', strtotime($dateString)));
        } catch (Exception $e) {
            echo "Error retrieving the last updated time of the data";
            exit;
        }
        return new UTCDateTime($dateTime->getTimestamp() * 1000);
    }

    /**
     * Fetch articles and save them to an array
     */
    private function fetchArticles()
    {
        $url = sprintf(NEWS_API_URL, $_ENV["NEWS_API_KEY"], $_ENV["NEWS_PAGE_SIZE"]);
        $articles = json_decode(file_get_contents($url))->articles;
        foreach ($articles as $article) {
            $articleObj = new Article();

            $title = '-';
            if (!is_null($article->title)) {
                $title = $article->title;
            }
            $articleObj->setTitle($title);

            $sourceName = '-';
            if (!is_null($article->source->name)) {
                $sourceName = $article->source->name;
            }
            $articleObj->setSourceName($sourceName);

            $author = '-';
            if (!is_null($article->author)) {
                $author = $article->author;
            }
            $articleObj->setAuthor($author);

            $description = '-';
            if (!is_null($article->description)) {
                $description = $article->description;
            }
            $articleObj->setDescription($description);

            $url = '-';
            if (!is_null($article->url)) {
                $url = $article->url;
            }
            $articleObj->setUrl($url);

            $publishedAt = $article->publishedAt;
            try {
                $dateTime = new DateTime(date('Y-m-dTh:i:s', strtotime($publishedAt)));
            } catch (Exception $e) {
                echo "Error retrieving the article date";
                exit;
            }
            $articleObj->setPublishedAt(new UTCDateTime($dateTime->getTimestamp() * 1000));

            array_push($this->articleObjects, $articleObj);
        }
    }

    /**
     * Save each country object to a MongoDB database
     */
    private function saveCountryDataToDb()
    {
        $this->mongoDatabase->dropCountryCollection();
        foreach ($this->countryObjects as $country) {
            $this->mongoDatabase->insertCountry($country->toArray());
        }
    }

    /**
     * Save each article object to a MongoDB database
     */
    private function saveArticleDataToDb()
    {
        $this->mongoDatabase->dropArticleCollection();
        foreach ($this->articleObjects as $article) {
            $this->mongoDatabase->insertArticle($article->toArray());
        }
    }
}

