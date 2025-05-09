<?php

namespace UniqueLibs\ApiBundle\Services;

use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use FOS\RestBundle\View\View;
use Hateoas\Configuration\Route;
use Hateoas\Representation\Factory\PagerfantaFactory;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Component\HttpFoundation\Request;

class ApiManager
{
    /**
     * @param Request      $request
     * @param QueryBuilder $queryBuilder
     * @param string       $route
     * @param array        $routeParameters
     *
     * @return View
     */
    public function formatQueryBuilder(Request $request, QueryBuilder $queryBuilder, $route, array $routeParameters)
    {
        return $this->formatQuery($request, $queryBuilder->getQuery(), $route, $routeParameters);
    }

    /**
     * @param Request $request
     * @param Query   $query
     * @param string  $route
     * @param array   $routeParameters
     *
     * @return View
     */
    public function formatQuery(Request $request, Query $query, $route, array $routeParameters)
    {
        $limit = $request->query->getInt('limit', 10);

        if ($limit > 100) {
            $limit = 100;
        }

        $page = $request->query->getInt('page', 1);

        $pagerAdapter = new DoctrineORMAdapter($query, false);
        $pager = new Pagerfanta($pagerAdapter);
        $pager->setCurrentPage($page);
        $pager->setMaxPerPage($limit);

        $pagerFactory = new PagerfantaFactory();

        return View::create($pagerFactory->createRepresentation(
            $pager,
            new Route($route, array_merge($routeParameters, array('limit' => $limit, 'page' => $page)))
        ));
    }

    /**
     * @param Request      $request
     * @param QueryBuilder $queryBuilder
     * @param string       $route
     * @param array        $routeParameters
     *
     * @deprecated Use formatQueryBuilder() instead.
     *
     * @return View
     */
    public function formatCollectionGet(Request $request, QueryBuilder $queryBuilder, $route, array $routeParameters)
    {
        return $this->formatQueryBuilder($request, $queryBuilder, $route, $routeParameters);
    }
}