<?php

namespace Login\Factory;

 use Login\Controller\IndexController;
 use Zend\ServiceManager\FactoryInterface;
 use Zend\ServiceManager\ServiceLocatorInterface;

 class IndexControllerFactory implements FactoryInterface
 {
     /**
      * Create service
      *
      * @param ServiceLocatorInterface $serviceLocator
      *
      * @return mixed
      */
     public function createService(ServiceLocatorInterface $serviceLocator)
     {
         $realServiceLocator = $serviceLocator->getServiceLocator();
         $adapterService     = $realServiceLocator->get('Zend\Db\Adapter\Adapter');

         return new IndexController($adapterService);
     }
 }