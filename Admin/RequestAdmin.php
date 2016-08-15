<?php

namespace Daken\ReleaseProfilerBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Show\ShowMapper;

class RequestAdmin extends Admin
{
    protected $datagridValues = [
        '_page' => 1,
        '_sort_order' => 'DESC',
        '_sort_by' => 'id'
    ];

    /**
     * @param DatagridMapper $datagridMapper
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('id')
            ->add('created')
            ->add('scheme')
            ->add('host')
            ->add('path')
            ->add('query')
            ->add('matchedRoute')
            ->add('time')
            ->add('requestMethod')
            ->add('responseCode')
            ->add('clientIp')
        ;
    }

    /**
     * @param ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('id')
            ->add('created')
            ->add('scheme')
            ->add('host')
            ->add('path')
            ->add('matchedRoute')
            ->add('time')
            ->add('totalDatabaseQueryCount', null, array('label' => 'DB Queries'))
            ->add('requestMethod', null, array('label' => 'Method'))
            ->add('responseCode')
            ->add('clientIp')
        ;
    }

    /**
     * @param ShowMapper $showMapper
     */
    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->add('id')
            ->add('created')
            ->add('scheme')
            ->add('host')
            ->add('path')
            ->add('query')
            ->add('matchedRoute')
            ->add('time')
            ->add('totalDatabaseQueryCount')
            ->add('totalDatabaseQueryTime')
            ->add('requestMethod')
            ->add('request', null, ['safe' => true])
            ->add('responseCode')
            ->add('responseAsString', null, ['safe' => false, 'label' => 'Response body'])
            ->add('clientIp')
            ->add('username')
            ->end()

            ->with('Database Queries')
                ->add('databaseQueries', null, array(
                    'template' => 'DakenReleaseProfilerBundle:Admin:request_queries.html.twig',
                ))
            ->end()

            ->with('Errors')
                ->add('errors', null, array(
                    'template' => 'DakenReleaseProfilerBundle:Admin:request_errors.html.twig',
                ))
            ->end()
            ;
        ;
    }

    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->remove('edit');
        $collection->remove('create');
    }
}
