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
class Country
{
    private $name;
    private $confirmed;
    private $deaths;
    private $active;
    private $recovered;
    private $last_updated_by_source_at;

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