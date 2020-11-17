COVID-19 Data Fetcher
=================

Fetch and save data of each country to a local MongoDB database. 

Data will be fetched from: https://github.com/CSSEGISandData/COVID-19

## Download
```console
$ git clone https://github.com/zaironjacobs/covid19-php-data-fetcher
```

## Usage

Make sure you have MongoDB installed on your system before running the script.

Copy the file .env.example to .env and add a database name and collection name to the environment variables.

To use:
```console
$ cd covid19-php-data-fetcher
$ php run.php
```