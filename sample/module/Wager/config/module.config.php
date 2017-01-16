<?php
return array(
  'controllers' => array(
    'factories' => array(
      'Wager\Controller\Index' => 'Wager\Factory\IndexControllerFactory',
      'Wager\Controller\Profile' => 'Wager\Factory\ProfileControllerFactory',
      'Wager\Controller\Wallet' => 'Wager\Factory\WalletControllerFactory',
      'Wager\Controller\Message' => 'Wager\Factory\MessageControllerFactory', 
      'Wager\Controller\General' => 'Wager\Factory\GeneralControllerFactory', 
      'Wager\Controller\Terms' => 'Wager\Factory\TermsControllerFactory',   
    )
  ),
  'router' => array(
    'routes' => array(
      'wager' => array(
        'type' => 'segment',
        'options' => array(
          'route' => '/wager[/][:controller][/][:action][/][:id]',
          'constraints' => array(
            'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
            'id' => '[0-9+]',
          ),
          'defaults' => array(
            '__NAMESPACE__' => 'Wager\Controller',
          	'controller' => 'Index',
                'action' => 'index'
          )
        )
      )
    )
  ),
    
  'view_manager' => array(
    'template_path_stack' => array(
      'wager' => __DIR__ . '/../view',
    ),
    'strategies' => array(
      'ViewJsonStrategy'
    ),
  )
);