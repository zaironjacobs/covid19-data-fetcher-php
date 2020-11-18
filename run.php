<?php

/**
 * PHP version 7.4
 *
 * @author      Zairon Jacobs <zaironjacobs@gmail.com>
 */


require_once("vendor/autoload.php");

Dotenv\Dotenv::createImmutable(__DIR__)->load();

require("constants.php");
require("data_downloader.php");
require("data_to_db_saver.php");

echo "Downloading data..." . "\n";
$dataDownloader = new DataDownloader();
$fileName = '';
$tries = 30;
for ($i = 0; $i < $tries; $i++) {
    $date = date('m-d-Y', strtotime("-" . $i . "days"));
    $fileName = $date . ".csv";
    $url = sprintf(DATA_URL, $fileName);
    if ($dataDownloader->download($url) === True) {
        echo "Download completed: " . $fileName . "\n";
        break;
    } else {
        if ($i === $tries - 1) {
            echo "Download failed: Unable to find the latest csv file for the last " . $tries . " days";
            exit;
        }
        continue;
    }
}

echo "Saving data to database..." . "\n";
$dataToDbSaver = new DataToDbSaver($fileName);
$dataToDbSaver->init();
echo "Finished";