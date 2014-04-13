<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Administration;

use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Administration\Authentication\Adapter\UserAuthAdapter;
use Administration\Authentication\Storage\DatabaseStorage;
use Zend\Authentication\AuthenticationService;
use Zend\Authentication\Storage\Session;
use Zend\Authentication\Storage\Chain;

class Module
{
    public function onBootstrap(MvcEvent $e)
    {
        $eventManager        = $e->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);
        $sm                  = $e->getApplication()->getServiceManager();
        $e->getApplication()->getEventManager()->getSharedManager()->attach('Zend\Mvc\Controller\AbstractController', 'dispatch', function($e) use ($sm) {
            if ($e->getRouteMatch()->getParam('model')) {
                switch ($e->getRouteMatch()->getParam('model')) {
                    case 'login':
                        $e->getTarget()->layout('administration/login');
                        break;
                }
            }
        }, 100);
    }

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }

    public function getServiceConfig()
     {
        return array(
            'factories' => array(
                'DbAdapter' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    return $dbAdapter;
                },
                'DbAuthStorage' => function ($sm) {
                    $dbAdapter       = $sm->get('Zend\Db\Adapter\Adapter');
                    $databaseStorage = new DatabaseStorage($dbAdapter, 'userSession', 'email');
                    return $databaseStorage;
                },
                'AuthService' => function ($sm) {
                    $dbAdapter           = $sm->get('Zend\Db\Adapter\Adapter');
                    $dbTableAuthAdapter  = new UserAuthAdapter($dbAdapter, 'user','email','password');
                    $sessionStorage      = new Session();

                    $authService = new AuthenticationService();
                    $authService->setAdapter($dbTableAuthAdapter);
                    $storage = new Chain;
                    $storage->add($sessionStorage);
                    $storage->add($sm->get('DbAuthStorage'));
                    $authService->setStorage($storage);

                    return $authService;
                }
            ),
        );
    }
}
