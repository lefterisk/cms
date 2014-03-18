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
        var_dump($this->params()->fromRoute('order'));
        var_dump($this->params()->fromRoute('direction'));


    	$model = 'Administration\\Model\\'.$this->params()->fromRoute('model');
        $component = new $model($this->getServiceLocator()->get('Zend\Db\Adapter\Adapter'));
        return new ViewModel(
            array(
                'listing' => $component->getListing(
                    ($this->params()->fromRoute('itemsperpage'))? $this->params()->fromRoute('itemsperpage'): 20,
                    ($this->params()->fromRoute('page'))        ? $this->params()->fromRoute('page'): 1,
                    ($this->params()->fromRoute('order'))       ? $this->params()->fromRoute('order'): null,
                    ($this->params()->fromRoute('direction'))   ? $this->params()->fromRoute('direction'): null
                ),
                'visibleListingFields' => $component->getListingFields(),
                'userRights' => array('read','edit','add','delete')
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
