<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Administration\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Administration\Model;

class IndexController extends AbstractActionController
{
    public function indexAction()
    {
    	var_dump($this->params()->fromRoute('model'));
    	var_dump($this->params()->fromRoute('page'));
    	var_dump($this->params()->fromRoute('item'));
    	var_dump($this->params()->fromRoute('action'));

    	$model = 'Administration\\Model\\'.$this->params()->fromRoute('model');
        $component = new $model($this->getServiceLocator()->get('Zend\Db\Adapter\Adapter'));
        return new ViewModel(
            array(
                'listing' => $component->getListing()
            )
        );
    }

    public function addAction()
    {
    	var_dump($this->params()->fromRoute('model'));
    	var_dump($this->params()->fromRoute('collection'));
    	var_dump($this->params()->fromRoute('item'));
    	var_dump($this->params()->fromRoute('action'));
        return new ViewModel();
    }

    public function editAction()
    {
        var_dump($this->params()->fromRoute('model'));
        var_dump($this->params()->fromRoute('collection'));
        var_dump($this->params()->fromRoute('item'));
        var_dump($this->params()->fromRoute('action'));
        return new ViewModel();
    }
}
