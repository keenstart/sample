<?php
return array(
  'controllers' => array(
    'invokables' => array(
      'Deploy\Controller\Deploy' => 'Deploy\Controller\DeployController',
    )
  ),
  'router' => array(
    'routes' => array(
      'deploy' => array(
        'type' => 'segment',
        'options' => array(
          'route' => '/deploy[/][:controller][/][:action]',
          'constraints' => array(
            'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
            'id' => '[0-9+]',
          ),
          'defaults' => array(
            '__NAMESPACE__' => 'Deploy\Controller',
          	'controller' => 'Deploy',
            'action' => 'deploy'
          )
        )
      )
    )
  ),
    
  'view_manager' => array(
    'template_path_stack' => array(
      'deploy' => __DIR__ . '/../view',
    ),
    'strategies' => array(
      'ViewJsonStrategy'
    ),
  )
);