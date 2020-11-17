<?php

/**
 * PHP version 7.4
 *
 * @author      Zairon Jacobs <zaironjacobs@gmail.com>
 */

define("DATA_URL", "https://raw.githubusercontent.com/CSSEGISandData/COVID-19/master/csse_covid_19_data" .
    "/csse_covid_19_daily_reports/%s");

define("DATA_URL_INFO", "https://api.github.com/repos/CSSEGISandData/COVID-19/commits?path=" .
    "csse_covid_19_data/csse_covid_19_daily_reports/%s");

define("DATA_DIR", "data/");

define("COL_COUNTRY", "Country_Region");
define("COL_DEATHS", "Deaths");
define("COL_CONFIRMED", "Confirmed");
define("COL_ACTIVE", "Active");
define("COL_RECOVERED", "Recovered");

define("WORLDWIDE", "Worldwide");