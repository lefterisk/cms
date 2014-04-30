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
use Administration\Helper\Authentication\Adapter\UserAuthAdapter;
use Administration\Helper\Authentication\Storage\SessionDBStorage;
use Zend\Authentication\AuthenticationService;
use Zend\Db\TableGateway\TableGateway;
use Zend\Session\SaveHandler\DbTableGateway;
use Zend\Session\SaveHandler\DbTableGatewayOptions;
use Zend\Session\SessionManager;
use Zend\Session\Container;
use Zend\Session\Config\SessionConfig;
use Administration\Helper\General\ControlPanel;


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

            }

            $controlPanel = $sm->get('ControlPanel');
            $session      = $sm->get('Session');

            //Locale setup
            if (!$session->locale || !$controlPanel->isValidAdminLocale($session->locale)) {
                $session->locale = $controlPanel->getDefaultAdminLocale();
            }

            if ($e->getRouteMatch()->getParam('language') && $controlPanel->isValidAdminLocale($e->getRouteMatch()->getParam('language'))) {
                $session->locale = $e->getRouteMatch()->getParam('language');
            }

            $e->getApplication()->getServiceManager()->get('translator')->setLocale($session->locale);
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
                'Session' => function ($sm) {
                    return new Container();
                },
                'SessionSaveHandler' => function ($sm) {
                    $dbAdapter     = $sm->get('DbAdapter');
                    $tableGateway  = new TableGateway('Session', $dbAdapter);
                    $saveHandler   = new DbTableGateway($tableGateway, new DbTableGatewayOptions());
                    return $saveHandler;
                 },
                'AuthStorage' => function($sm) {
                    $manager       = new SessionManager();
                    $sessionConfig = new SessionConfig();
                    $saveHandler   = $sm->get('SessionSaveHandler');
                    $saveHandler->open($sessionConfig->getOption('save_path'), 'user');
                    $manager->setSaveHandler($saveHandler);
                    $authStorage   = new SessionDBStorage('user', null, $manager);
                    return $authStorage;
                },
                'AuthService' => function ($sm) {
                    $dbAdapter           = $sm->get('DbAdapter');
                    $dbTableAuthAdapter  = new UserAuthAdapter($dbAdapter, 'user','email','password');
                    $storage             = $sm->get('AuthStorage');
                    $authService         = new AuthenticationService($storage, $dbTableAuthAdapter);
                    return $authService;
                },
                'ControlPanel' => function ($sm) {
                    $dbAdapter    = $sm->get('DbAdapter');
                    $authService  = $sm->get('AuthService');
                    $controlPanel = new ControlPanel($dbAdapter, $authService);
                    return $controlPanel;
                },
            ),
        );
    }
}
