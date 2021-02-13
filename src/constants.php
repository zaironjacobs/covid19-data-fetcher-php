<?php

/**
 * PHP version 7.4
 *
 * @author      Zairon Jacobs <zaironjacobs@gmail.com>
 */

define("DATA_URL", "https://raw.githubusercontent.com/CSSEGISandData/COVID-19/master/csse_covid_19_data" .
    "/csse_covid_19_daily_reports/%s");

define("NEWS_API_URL", "https://newsapi.org/v2/everything?qInTitle=covid+OR+corona&apiKey=%s" .
    "&language=en&sortBy=publishedAt&pageSize=%s");

define("DATA_DIR", "data/");

define("COL_LAST_UPDATE", "Last_Update");
define("COL_COUNTRY", "Country_Region");
define("COL_DEATHS", "Deaths");
define("COL_CONFIRMED", "Confirmed");
define("COL_ACTIVE", "Active");
define("COL_RECOVERED", "Recovered");

define("WORLDWIDE", "Worldwide");