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
            
            'administration' => array(
                'type' => 'segment',
                'options' => array(
                    'route'    => '/administration[/:model][/parent/:parent][/itemsperpage/:itemsperpage][/page/:page][/order/:order][/direction/:direction][/:item][/:action]',
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
                        'controller' => 'Administration\Controller\Index',
                        'action'     => 'index',
                        'model'      => 'home',
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
        'invokables' => array(
            'Administration\Controller\Index' => 'Administration\Controller\IndexController'
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
