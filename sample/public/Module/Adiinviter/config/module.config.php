<?php

return array(
	'controllers' => array(
		'invokables' => array(
			'Adiinviter\Controller\Adiinviter' => 'Adiinviter\Controller\AdiinviterController',
		),
	),

	// The following section is new and should be added to your file
	'router' => array(
		'routes' => array(
			'find-friends' => array(
				'type' => 'segment',
				'options' => array(
					'route' => '/find-friends[/][:action][/][:id]',
					'constraints' => array(
						'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
						'id' => '[a-zA-Z0-9]+',
					),
					'defaults' => array(
						'controller' => 'Adiinviter\Controller\Adiinviter',
						'action' => 'index',
					),
				),
			),
		),
	),

	'view_manager' => array(
		'template_path_stack' => array(
			'adiinviter' => __DIR__ . '/../view',
		),
	),
);

?>