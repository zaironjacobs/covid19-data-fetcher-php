<?php

/**
 * PHP version 7.4
 *
 * @author      Zairon Jacobs <zaironjacobs@gmail.com>
 */

/**
 * Download data
 *
 * @author      Zairon Jacobs <zaironjacobs@gmail.com>
 */
class DataDownloader
{

    /**
     * Download a csv file to the data dir
     *
     * @param string $url url to the csv file
     *
     * @return boolean
     */
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
}
