COVID-19 Data Fetcher
=================

Fetch and save data of each country to a MongoDB database. 

Source of data: https://github.com/CSSEGISandData/COVID-19

An example of a document:

```javascript
{
    "_id": {
        "$oid": "60250f5b1ae25397d00c706c"
    },
    "name": "Netherlands",
        "confirmed": 1027023,
        "deaths": 14710,
        "active": 998836,
        "recovered": 13477,
        "last_updated_by_source_at": {
        "$date": "2021-02-11T05:23:55.000Z"
    }
}
```

## Dependencies
- [MongoDB](https://www.mongodb.com/)
- [PHP >= 7.4](https://www.php.net/downloads.php)
- [MongoDB PHP Driver](https://pecl.php.net/package/mongodb)

## Download
```console
$ git clone https://github.com/zaironjacobs/covid19-data-fetcher-php
```

## Usage

Copy the file .env.example to .env and fill in the environment variables.
A local connection example:
```
DATABASE=covid19
COLLECTION=country
CONNECTION_STRING=mongodb://localhost:27017
```

To use:
```console
$ cd covid19-data-fetcher-php
$ composer install
$ composer run-script start
```

## Crontab
At minute 0 and 30:

```
0,30 * * * * cd ~/covid19-data-fetcher-php && /usr/local/bin/composer run-script start
```
