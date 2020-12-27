<?php

/**
 * PHP version 7.4
 *
 * @author      Zairon Jacobs <zaironjacobs@gmail.com>
 */


require("country.php");
require("mongo_database.php");

use MongoDB\BSON\UTCDateTime;

/**
 * Save data from the downloaded csv file inside the data dir to a MongoDB database
 *
 * @author      Zairon Jacobs <zaironjacobs@gmail.com>
 */
class App
{
    private string $csvFileName;

    private array $csvHeader = [];
    private array $csvRows = [];
    private array $countryObjects = [];

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

        echo "Saving data to database..." . "\n";
        $this->setCsvHeader();
        $this->setRowsData();
        $this->createCountryObjects();
        $this->populateCountryObjects();
        $this->saveDataToDb();

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
     * Save each country object to a MongoDB database
     */
    private function saveDataToDb()
    {
        $this->mongoDatabase->dropCollection();
        foreach ($this->countryObjects as $country) {
            $this->mongoDatabase->insert($country->toArray());
        }
    }

}

