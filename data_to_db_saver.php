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
class DataToDbSaver
{
    private $csvFileName;

    private $csvHeader = [];
    private $csvFileRowsData = [];
    private $countryObjects = [];

    private $totalDeaths;
    private $totalActive;
    private $totalRecovered;
    private $totalConfirmed;

    function __construct($fileName)
    {
        $this->csvFileName = $fileName;
    }

    /**
     * Main function for initialization
     */
    function init()
    {
        $this->setCsvHeader();
        $this->setRowsData();
        $this->createCountryObjects();
        $this->populateCountryObjects();
        $this->saveDataToDb();
    }

    /**
     * Retrieve the header from the csv file inside the data dir
     */
    private function setCsvHeader()
    {
        if (($handle = fopen(DATA_DIR . $this->csvFileName, "r")) !== FALSE) {
            while (($row = fgetcsv($handle, 1000, ",")) !== FALSE) {
                fclose($handle);
                $this->csvHeader = $row;
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
        $this->csvFileRowsData = $rowsData;
    }


    /**
     * Return an array with all country names
     *
     * @return array
     */
    private function getCountryNamesArray()
    {
        $countryNames = [];
        foreach ($this->csvFileRowsData as $row) {
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
            array_push($this->countryObjects, $country);
        }
    }

    /**
     * Populate all country objects with data retrieved from the csv file
     */
    private function populateCountryObjects()
    {
        foreach ($this->countryObjects as $country) {
            $countryColNum = array_search(COL_COUNTRY, $this->csvHeader);
            $deathsColNum = array_search(COL_DEATHS, $this->csvHeader);
            $confirmedColNum = array_search(COL_CONFIRMED, $this->csvHeader);
            $activeColNum = array_search(COL_ACTIVE, $this->csvHeader);
            $recoveredColNum = array_search(COL_RECOVERED, $this->csvHeader);

            foreach ($this->csvFileRowsData as $rowData) {
                if ($rowData[$countryColNum] === $country->getName()) {
                    $deaths = $rowData[$deathsColNum];
                    if ($deaths < 0) {
                        $deaths = abs($deaths);
                    }
                    $country->incrementDeaths((int)$deaths);
                    $this->totalDeaths += (int)$deaths;

                    $confirmed = $rowData[$confirmedColNum];
                    if ($confirmed < 0) {
                        $confirmed = abs($confirmed);
                    }
                    $country->incrementConfirmed((int)$confirmed);
                    $this->totalConfirmed += (int)$confirmed;

                    $active = $rowData[$activeColNum];
                    if ($active < 0) {
                        $active = abs($active);
                    }
                    $country->incrementActive((int)$active);
                    $this->totalActive += (int)$active;

                    $recovered = $rowData[$recoveredColNum];
                    if ($recovered < 0) {
                        $recovered = abs($recovered);
                    }
                    $country->incrementRecovered((int)$recovered);
                    $this->totalRecovered += (int)$recovered;
                }
            }
        }

        foreach ($this->countryObjects as $country) {
            if (WORLDWIDE === $country->getName()) {
                $country->incrementDeaths((int)$this->totalDeaths);
                $country->incrementConfirmed((int)$this->totalConfirmed);
                $country->incrementActive((int)$this->totalActive);
                $country->incrementRecovered((int)$this->totalRecovered);
                break;
            }
        }
    }

    /**
     * Return the last updated time of the data source
     *
     * @return MongoDB\BSON\UTCDateTime
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
        return new MongoDB\BSON\UTCDateTime($dateTime->getTimestamp() * 1000);
    }

    /**
     * Save each country object to a local MongoDB database
     */
    private function saveDataToDb()
    {
        $database = $_ENV["DATABASE"];
        $collection = $_ENV["COLLECTION"];
        $collection = (new MongoDB\Client)->$database->$collection;
        $collection->drop();
        foreach ($this->countryObjects as $country) {
            $collection->insertOne($country->toArray());
        }
    }
}