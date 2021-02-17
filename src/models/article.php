<?php
/**
 * PHP version 7.4
 *
 * @author      Zairon Jacobs <zaironjacobs@gmail.com>
 */

/**
 * Article class to store article data
 *
 * @author      Zairon Jacobs <zaironjacobs@gmail.com>
 */

use MongoDB\BSON\UTCDateTime;

class Article
{
    private string $title;
    private string $source_name;
    private string $author;
    private string $description;
    private string $url;
    private UTCDateTime $published_at;


    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getSourceName(): string
    {
        return $this->source_name;
    }

    /**
     * @param string $source_name
     */
    public function setSourceName(string $source_name): void
    {
        $this->source_name = $source_name;
    }

    /**
     * @return string
     */
    public function getAuthor(): string
    {
        return $this->author;
    }

    /**
     * @param string $author
     */
    public function setAuthor(string $author): void
    {
        $this->author = $author;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @param string $url
     */
    public function setUrl(string $url): void
    {
        $this->url = $url;
    }

    /**
     * @return UTCDateTime
     */
    public function getPublishedAt(): UTCDateTime
    {
        return $this->published_at;
    }

    /**
     * @param UTCDateTime $published_at
     */
    public function setPublishedAt(UTCDateTime $published_at): void
    {
        $this->published_at = $published_at;
    }

    public function toArray(): array
    {
        return get_object_vars($this);
    }
}