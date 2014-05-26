<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array(
    'router' => array(
        'routes' => array(
            'adminHome' => array(
                'type' => 'segment',
                'options' => array(
                    'route'    => '/administration[/home/:action][/language/:language]',
                    'defaults' => array(
                        'controller' => 'Administration\Controller\Home',
                        'action'     => 'index',
                        'defaults'  => 'en'
                    ),
                    'constraints' => array(
                        'language'   => '[a-z]{2}'
                    )
                ),
            ),
            'genericModel' => array(
                'type' => 'segment',
                'options' => array(
                    'route'    => '/administration/model[/:model][/parent/:parent][/itemsperpage/:itemsperpage][/page/:page][/order/:order][/direction/:direction][/:item][/:action][/language/:language]',
                    'constraints' => array(
                        'model'         => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'itemsperpage'  => '[0-9]+',
                        'page'          => '[0-9]+',
                        'order'         => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'direction'     => '(asc|desc)',
                        'parent'        => '[0-9]*',
                        'item'          => '[0-9_-]*',
                        'action'        => '[a-zA-Z][a-zA-Z0-9_-]*',

                    ),
                    'defaults' => array(
                        'controller' => 'Administration\Controller\GenericModel',
                        'action'     => 'index',
                        'language'   => '[a-z]{2}'
                    ),
                ),
            ),
            'sitemanager' => array(
                'type' => 'segment',
                'options' => array(
                    'route'    => '/administration/sitemanager[/page/:page][/:action][/model/:model][/item/:item][/language/:language]',
                    'constraints' => array(
                        'page'          => '[0-9]*',
                        'model'         => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'action'        => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'item'          => '[0-9]*',
                        'language'      => '[a-z]{2}'
                    ),
                    'defaults' => array(
                        'controller' => 'Administration\Controller\SiteManager',
                        'action'     => 'index',
                    ),
                ),
            ),
            'adminLogin' => array(
                'type' => 'segment',
                'options' => array(
                    'route'    => '/administration/login[/:action][/language/:language]',
                    'constraints' => array(
                        'action'        => '[a-zA-Z][a-zA-Z0-9_-]*',
                    ),
                    'defaults' => array(
                        'controller' => 'Administration\Controller\Login',
                        'action'     => 'index',
                        'model'      => 'login',
                        'language'   => '[a-z]{2}'
                    ),
                ),
            ),
        ),
    ),
    'service_manager' => array(
        'abstract_factories' => array(
            'Zend\Cache\Service\StorageCacheAbstractServiceFactory',
            'Zend\Log\LoggerAbstractServiceFactory',
        ),
        'aliases' => array(
            'translator' => 'MvcTranslator',
        ),
    ),
    'translator' => array(
        'locale' => 'en',
        'translation_file_patterns' => array(
            array(
                'type'     => 'phpArray',
                'base_dir' => __DIR__ . '/../language',
                'pattern'  => '%s.php',
            ),
        ),
    ),
    'controllers' => array(
        'invokables' => array(
            'Administration\Controller\GenericModel' => 'Administration\Controller\GenericModelController',
            'Administration\Controller\SiteManager'  => 'Administration\Controller\SiteManagerController',
            'Administration\Controller\Home'         => 'Administration\Controller\HomeController',
            'Administration\Controller\Login'        => 'Administration\Controller\LoginController'
        ),
    ),
    'view_manager' => array(
        'display_not_found_reason' => true,
        'display_exceptions'       => true,
        'doctype'                  => 'HTML5',
        'not_found_template'       => 'error/404',
        'exception_template'       => 'error/index',
        'template_map' => array(
            'administration/login'       => __DIR__ . '/../view/layout/login.phtml',
            'layout/layout'              => __DIR__ . '/../view/layout/layout.phtml',
            'administration/index/index' => __DIR__ . '/../view/administration/index/index.phtml',
            'error/404'                  => __DIR__ . '/../view/error/404.phtml',
            'error/index'                => __DIR__ . '/../view/error/index.phtml',
        ),
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
        'strategies' => array(
            'ViewJsonStrategy',
        ),
    ),
    // Placeholder for console routes
    'console' => array(
        'router' => array(
            'routes' => array(
            ),
        ),
    ),
    'session' => array(
        'config' => array(
            'class' => 'Zend\Session\Config\SessionConfig',
            'options' => array(
                'name' => 'cms',
            ),
        ),
        'save_handler' => 'SessionSaveHandler',
        'storage' => 'Zend\Session\Storage\SessionArrayStorage',
        'validators' => array(
            array(
                'Zend\Session\Validator\RemoteAddr',
                'Zend\Session\Validator\HttpUserAgent',
            ),
        ),
    ),
);
