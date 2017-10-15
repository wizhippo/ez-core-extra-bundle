<?php

/**
 * File containing the SearchHandlerAdapter class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Wizhippo\eZ\Publish\Core\Pagination\Pagerfanta;

use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\SPI\Search\Handler as SearchHandlerInterface;
use Pagerfanta\Adapter\AdapterInterface;

/**
 * Pagerfanta adapter for eZ Publish content search.
 * Will return results as SearchHit objects.
 */
class HandlerSearchHitAdapter implements AdapterInterface
{
    /**
     * @var \eZ\Publish\API\Repository\Values\Content\Query
     */
    private $query;

    /**
     * @var SearchHandlerInterface
     */
    private $searchHandler;

    /**
     * @var int
     */
    private $nbResults;

    public function __construct(Query $query, SearchHandlerInterface $searchHandler)
    {
        $this->query = $query;
        $this->searchHandler = $searchHandler;
    }

    /**
     * Returns the number of results.
     *
     * @return int The number of results.
     */
    public function getNbResults()
    {
        if (isset($this->nbResults)) {
            return $this->nbResults;
        }

        $countQuery = clone $this->query;
        $countQuery->limit = 0;

        return $this->nbResults = $this->searchHandler->findContent($countQuery)->totalCount;
    }

    /**
     * Returns a slice of the results, as SearchHit objects.
     *
     * @param int $offset The offset.
     * @param int $length The length.
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Search\SearchHit[]
     */
    public function getSlice($offset, $length)
    {
        $query = clone $this->query;
        $query->offset = $offset;
        $query->limit = $length;
        $query->performCount = false;

        $searchResult = $this->searchHandler->findContent($query);

        if (!isset($this->nbResults) && isset($searchResult->totalCount)) {
            $this->nbResults = $searchResult->totalCount;
        }

        return $searchResult->searchHits;
    }
}
