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

    function setName(string $name)
    {
        $this->name = $name;
    }

    function getName(): string
    {
        return $this->name;
    }

    function incrementConfirmed(int $confirmed)
    {
        $this->confirmed += $confirmed;
    }

    function incrementDeaths(int $deaths)
    {
        $this->deaths += $deaths;
    }

    function incrementActive(int $active)
    {
        $this->active += $active;
    }

    function incrementRecovered(int $recovered)
    {
        $this->recovered += $recovered;
    }

    function setLastUpdatedBySourceAt(UTCDateTime $lastUpdatedBySourceAt)
    {
        $this->last_updated_by_source_at = $lastUpdatedBySourceAt;
    }

    public function toArray(): array
    {
        return get_object_vars($this);
    }
}