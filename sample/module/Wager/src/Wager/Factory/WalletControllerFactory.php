<?php

namespace Wager\Factory;

 use Wager\Controller\WalletController;
 use Zend\ServiceManager\FactoryInterface;
 use Zend\ServiceManager\ServiceLocatorInterface;

 class WalletControllerFactory implements FactoryInterface
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

         return new WalletController($adapterService);
     }
 }