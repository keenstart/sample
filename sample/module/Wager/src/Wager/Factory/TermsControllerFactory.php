<?php

namespace Wager\Factory;

 use Wager\Controller\TermsController;
 use Zend\ServiceManager\FactoryInterface;
 use Zend\ServiceManager\ServiceLocatorInterface;

 class TermsControllerFactory implements FactoryInterface
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

         return new TermsController($adapterService);
     }
 }