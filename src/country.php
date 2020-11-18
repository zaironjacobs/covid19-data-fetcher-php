<?php
/**
 * PHP version 7.4
 *
 * @author      Zairon Jacobs <zaironjacobs@gmail.com>
 */

/**
 * Country class to store data of a country
 *
 * @author      Zairon Jacobs <zaironjacobs@gmail.com>
 */

use MongoDB\BSON\UTCDateTime;

class Country
{
    private string $name;
    private int $confirmed = 0;
    private int $deaths = 0;
    private int $active = 0;
    private int $recovered = 0;
    private UTCDateTime $last_updated_by_source_at;

    function setName($name)
    {
        $this->name = $name;
    }

    function getName()
    {
        return $this->name;
    }

    function incrementConfirmed($confirmed)
    {
        $this->confirmed += $confirmed;
    }

    function incrementDeaths($deaths)
    {
        $this->deaths += $deaths;
    }

    function incrementActive($active)
    {
        $this->active += $active;
    }

    function incrementRecovered($recovered)
    {
        $this->recovered += $recovered;
    }

    function setLastUpdatedBySourceAt($lastUpdatedBySourceAt)
    {
        $this->last_updated_by_source_at = $lastUpdatedBySourceAt;
    }

    public function toArray()
    {
        return get_object_vars($this);
    }
}