<?php

namespace Tpf\Database;

class PaginationQuery extends Query
{
    public const LIMIT_PER_PAGE = 20;

    public function __construct($className)
    {
        parent::__construct($className);
        
        $this->setOffset(($this->getCurrentPage() - 1) * static::LIMIT_PER_PAGE);
        $this->setLimit(static::LIMIT_PER_PAGE);
    }

    /**
     * @method int getCurrentPage()
     * returns number of page from request search query
     */
    public function getCurrentPage()
    {
        return isset($_GET["pagenum"])
            ? intval($_GET["pagenum"])
            : 1;
    }

    /**
     * @method int getNumberOfPages()
     */
    public function getNumberOfPages()
    {
        global $dbal;

        $response = $dbal->get_results($this->prepareSelect("count(1) as `count`"));
        $rows = isset($response[0])
            ? $response[0]->count
            : 0;

        return (int) ceil($rows / static::LIMIT_PER_PAGE);
    }

    public function getLinks()
    {
        $numberOfPages = $this->getNumberOfPages();

        $links = [];

        for ($i = 1; $i <= $numberOfPages; $i++) {
            $links[] = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . '?' . ($_SERVER['QUERY_STRING'] != '' ? $_SERVER['QUERY_STRING'] . '&' : '')  . 'page=' . $i;
        }

        return $links;
    }
}