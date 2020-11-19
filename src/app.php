<?php

/**
 * PHP version 7.4
 *
 * @author      Zairon Jacobs <zaironjacobs@gmail.com>
 */


require("country.php");

/**
 * Save data from the downloaded csv file inside the data dir to a local MongoDB database
 *
 * @author      Zairon Jacobs <zaironjacobs@gmail.com>
 */


use MongoDB\BSON\UTCDateTime;

class App
{
    private string $csvFileName;

    private array $csvHeader = [];
    private array $csvRowsData = [];
    private array $countryObjects = [];

    private int $totalDeaths = 0;
    private int $totalActive = 0;
    private int $totalRecovered = 0;
    private int $totalConfirmed = 0;

    /**
     * Main function for initialization
     */
    function init()
    {
        echo "Downloading data..." . "\n";
        $this->downloadCsvFile();

        echo "Saving data to database..." . "\n";
        $this->setCsvHeader();
        $this->setRowsData();
        $this->createCountryObjects();
        $this->populateCountryObjects();
        $this->saveDataToDb();

        echo "Finished" . "\n";
    }

    /**
     * Retrieve the header from the csv file inside the data dir
     */
    private function setCsvHeader()
    {
        if (($handle = fopen(DATA_DIR . $this->csvFileName, "r")) !== FALSE) {
            while (($row = fgetcsv($handle, 1000, ",")) !== FALSE) {
                fclose($handle);
                $this->csvHeader = (array)$row;
                return;
            }
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
            while (($row = fgetcsv($handle, 1000, ",")) !== FALSE) {
                if ($counter === 0) {
                    $counter++;
                    continue;
                }
                array_push($rowsData, $row);
                $counter++;
            }
            fclose($handle);
        }
        $this->csvRowsData = $rowsData;
    }

    /**
     * Return an array with all country names
     *
     * @return array
     */
    private function getCountryNamesArray()
    {
        $countryNames = [];
        foreach ($this->csvRowsData as $row) {
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
     * Populate all country objects with data retrieved from the csv file
     */
    private function populateCountryObjects()
    {
        $countryColNum = array_search(COL_COUNTRY, $this->csvHeader);
        $deathsColNum = array_search(COL_DEATHS, $this->csvHeader);
        $confirmedColNum = array_search(COL_CONFIRMED, $this->csvHeader);
        $activeColNum = array_search(COL_ACTIVE, $this->csvHeader);
        $recoveredColNum = array_search(COL_RECOVERED, $this->csvHeader);

        foreach ($this->csvRowsData as $rowData) {
            $countryName = $rowData[$countryColNum];

            $deaths = $rowData[$deathsColNum];
            if ($deaths < 0) {
                $deaths = abs($deaths);
            }
            $this->totalDeaths += (int)$deaths;

            $confirmed = $rowData[$confirmedColNum];
            if ($confirmed < 0) {
                $confirmed = abs($confirmed);
            }
            $this->totalConfirmed += (int)$confirmed;

            $active = $rowData[$activeColNum];
            if ($active < 0) {
                $active = abs($active);
            }
            $this->totalActive += (int)$active;

            $recovered = $rowData[$recoveredColNum];
            if ($recovered < 0) {
                $recovered = abs($recovered);
            }
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
     * Return the last updated time of the data source
     *
     * @return UTCDateTime
     */
    private function getLastUpdatedBySourceTime()
    {
        $url = sprintf(DATA_URL_INFO, $this->csvFileName);
        $opts = [
            'http' => [
                'method' => 'GET',
                'header' => [
                    'User-Agent: PHP'
                ]
            ]
        ];

        $context = stream_context_create($opts);
        $content = json_decode(file_get_contents($url, false, $context));
        $date = $content[0]->commit->committer->date;
        $dateTime = new DateTime(date('Y-m-dTh:i:s', strtotime($date)));
        return new UTCDateTime($dateTime->getTimestamp() * 1000);
    }

    /**
     * Save each country object to a local MongoDB database
     */
    private function saveDataToDb()
    {
        $database_name = $_ENV["DATABASE"];
        $collection_name = $_ENV["COLLECTION"];

        $client = new MongoDB\Client($_ENV["CONNECTION_STRING"]);
        $db = $client->$database_name;
        $collection = $db->$collection_name;
        $collection->drop();

        foreach ($this->countryObjects as $country) {
            $collection->insertOne($country->toArray());
        }
    }

    /**
     * Download the csv file
     */
    private function downloadCsvFile()
    {
        function download(string $url)
        {
            if (!is_dir(DATA_DIR)) {
                mkdir(DATA_DIR);
            }

            $file_name = basename($url);
            $content = @file_get_contents($url);
            if ($content === False) {
                return False;
            } else {
                file_put_contents(DATA_DIR . $file_name, $content);
                return True;
            }
        }

        $tries = 30;
        for ($i = 0; $i < $tries; $i++) {
            $date = date('m-d-Y', strtotime("-" . $i . "days"));
            $this->csvFileName = $date . ".csv";
            $url = sprintf(DATA_URL, $this->csvFileName);
            if (download($url) === True) {
                echo "Download completed: " . $this->csvFileName . "\n";
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
}