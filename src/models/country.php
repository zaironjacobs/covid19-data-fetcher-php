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


    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return int
     */
    public function getConfirmed(): int
    {
        return $this->confirmed;
    }

    /**
     * @param int $confirmed
     */
    public function incrementConfirmed(int $confirmed): void
    {
        $this->confirmed += $confirmed;
    }

    /**
     * @return int
     */
    public function getDeaths(): int
    {
        return $this->deaths;
    }

    /**
     * @param int $deaths
     */
    public function incrementDeaths(int $deaths): void
    {
        $this->deaths += $deaths;
    }

    /**
     * @return int
     */
    public function getActive(): int
    {
        return $this->active;
    }

    /**
     * @param int $active
     */
    public function incrementActive(int $active): void
    {
        $this->active += $active;
    }

    /**
     * @return int
     */
    public function getRecovered(): int
    {
        return $this->recovered;
    }

    /**
     * @param int $recovered
     */
    public function incrementRecovered(int $recovered): void
    {
        $this->recovered += $recovered;
    }

    /**
     * @return UTCDateTime
     */
    public function getLastUpdatedBySourceAt(): UTCDateTime
    {
        return $this->last_updated_by_source_at;
    }

    /**
     * @param UTCDateTime $last_updated_by_source_at
     */
    public function setLastUpdatedBySourceAt(UTCDateTime $last_updated_by_source_at): void
    {
        $this->last_updated_by_source_at = $last_updated_by_source_at;
    }

    public function toArray(): array
    {
        return get_object_vars($this);
    }
}