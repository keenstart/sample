<?php

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array(
    'router' => array(
        'routes' => array(
            'application' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/application[/][:controller][/][:action]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[0-9+]',
                    ),
                    'defaults' => array(
                        '__NAMESPACE__' => 'Application\Controller',
                        'controller' => 'Index',
                        'action' => 'index'
                    )
                )
            )
        )
    ),
    'console' => array(
      'router' => array(
        'routes' => array(
          'update-feed' => array(
            'options' => array(
              'route'    => 'update feed [--start=] [--slice=] [--loadfroms3=] [--startDate=] [--endDate=] [--specificFile=] [--env=]',
              'defaults' => array(
                'controller' => 'Application\Controller\Console',
                'action' => 'updatefeed'
              )
            )
          ),
          'remove-untaken-wagers' => array(
            'options' => array(
              'route' => 'remove untaken wagers [--env=]',
              'defaults' => array(
                'controller' => 'Application\Controller\Console',
                'action' => 'updateuntakenwagers'
              )
            )
          ),
          'retrieve-feed' => array(
            'options' => array(
              'route' => 'retrieve feed from cron [--env=]',
              'defaults' => array(
                'controller' => 'Application\Controller\Console',
                'action' => 'retrievefromcron'
              )
            )
          ),
          'validate-transfers' => array(
            'options' => array(
              'route' => 'validate transfers',
              'defaults' => array(
                'controller' => 'Application\Controller\Console',
                'action' => 'checkbitcointransfers'
              )
            )
          )
        )
      )
    ),
    'service_manager' => array(
        'factories' => array(
            'translator' => 'Zend\I18n\Translator\TranslatorServiceFactory',
            //'Application\Models\DbalConnectorInterface' => 'Application\Factory\DbalConnectorFactory',
            //'Zend\Db\Adapter\Adapter' => 'Zend\Db\Adapter\AdapterServiceFactory',
        ),
    ),
    'translator' => array(
        'locale' => 'en_US',
        'translation_file_patterns' => array(
            array(
                'type'     => 'gettext',
                'base_dir' => __DIR__ . '/../language',
                'pattern'  => '%s.mo',
            ),
        ),
    ),
    'controllers' => array(
        'factories' => array(
            'Application\Controller\Console' => 'Application\Controller\ConsoleController'
        ),
    ),
);
