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
                'listingSwitches'      => $component->getListingSwitches(),
                'userRights'           => array('read','edit','add','delete'),
                'model'                => $this->params()->fromRoute('model'),
            )
        );
    }

    public function addAction()
    {
        $model = 'Administration\\Model\\'.$this->params()->fromRoute('model');
        $component = new $model($this->getServiceLocator()->get('Zend\Db\Adapter\Adapter'));

        $request = $this->getRequest();
        if ($request->isPost()) {

            $form = $component->getFormObject();
            $form->setInputFilter($component->getInputFilter());
            $form->setData($request->getPost());
            if ($form->isValid()) {
                $component->save($form->getData());
                //After Save redirect to listing
                return $this->redirect()->toRoute('administration', array(
                    'action' => 'index',
                    'model'  => $this->params()->fromRoute('model')
                ));
            } else {

            }
        }

        $form = $component->getForm();
        return new ViewModel(
            array(
                'form'               => $form,
                'multilingualFields' => $component->getAllMultilingualFields(),
            )
        );
    }

    public function editAction()
    {
//        var_dump($this->params()->fromRoute('model'));
//        var_dump($this->params()->fromRoute('collection'));
//        var_dump($this->params()->fromRoute('item'));
//        var_dump($this->params()->fromRoute('action'));
        $model = 'Administration\\Model\\'.$this->params()->fromRoute('model');
        $component = new $model($this->getServiceLocator()->get('Zend\Db\Adapter\Adapter'));

        $request = $this->getRequest();
        if ($request->isPost()) {

            $form = $component->getFormObject();
            $form->setInputFilter($component->getInputFilter());
            $form->setData($request->getPost());
            if ($form->isValid()) {
                $component->save($form->getData());
            } else {

            }
        }

        try {
            $item = $component->getItemById($this->params()->fromRoute('item'));
        }
        catch (\Exception $ex) {
            return $this->redirect()->toRoute('administration', array(
                'action' => 'index',
                'model'  => $this->params()->fromRoute('model')
            ));
        }
        if (!$item) {
            return $this->redirect()->toRoute('administration', array(
                'action' => 'index',
                'model'  => $this->params()->fromRoute('model')
            ));
        }

        //Bind form to Item
        $form = $component->getForm();
        $form->getFormObject()->bind($item);

        return new ViewModel(
            array(
                'form'               => $form,
                'multilingualFields' => $component->getAllMultilingualFields()
            )
        );
    }
}
