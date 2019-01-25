<?php

namespace Wizhippo\Bundle\EzCoreExtraBundle\Controller;

use eZ\Publish\API\Repository\SearchService;
use eZ\Publish\API\Repository\Values\Content\LocationQuery;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;
use eZ\Publish\Core\MVC\Symfony\View\ContentView;
use eZ\Publish\Core\QueryType\ContentViewQueryTypeMapper;
use Pagerfanta\Pagerfanta;
use Symfony\Component\HttpFoundation\Request;
use Wizhippo\eZ\Publish\Core\Pagination\Pagerfanta\SearchAdapter;

class PagerFantaQueryController
{
    /** @var \eZ\Publish\API\Repository\SearchService */
    private $searchService;

    /** @var \eZ\Publish\Core\QueryType\ContentViewQueryTypeMapper */
    private $contentViewQueryTypeMapper;

    /**
     * @param \eZ\Publish\Core\QueryType\ContentViewQueryTypeMapper $contentViewQueryTypeMapper
     * @param \eZ\Publish\API\Repository\SearchService $searchService
     */
    public function __construct(
        ContentViewQueryTypeMapper $contentViewQueryTypeMapper,
        SearchService $searchService
    ) {
        $this->contentViewQueryTypeMapper = $contentViewQueryTypeMapper;
        $this->searchService = $searchService;
    }

    /**
     * Runs a search with pagination support.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \eZ\Publish\Core\MVC\Symfony\View\ContentView $view
     * @return ContentView
     * @throws InvalidArgumentException
     */
    public function queryPaginationAction(Request $request, ContentView $view)
    {
        $query = $this->contentViewQueryTypeMapper->map($view);
        $adapter = new SearchAdapter($query, $this->searchService);
        $searchResults = new Pagerfanta($adapter);
        $searchResults->setMaxPerPage($view->getParameter('page_limit'));
        $searchResults->setCurrentPage($request->get('page', 1));
        $view->addParameters([
            $view->getParameter('query')['assign_results_to'] => $searchResults,
            'search_result' => $adapter->getSearchResult(),
        ]);

        return $view;
    }
}
