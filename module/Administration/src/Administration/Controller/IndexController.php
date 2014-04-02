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
use Administration\AbstractClasses\ControlPanel;

class IndexController extends AbstractActionController
{
    public function indexAction()
    {
    	$controlPanel = new ControlPanel($this->getServiceLocator()->get('Zend\Db\Adapter\Adapter'));
        $model = 'Administration\\Model\\'.$this->params()->fromRoute('model');
        $component = new $model($this->getServiceLocator()->get('Zend\Db\Adapter\Adapter'), $controlPanel);
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
                'model'                => $this->params()->fromRoute('model'),
                'controlPanel'         => $controlPanel,
            )
        );
    }

    public function addAction()
    {
        $controlPanel = new ControlPanel($this->getServiceLocator()->get('Zend\Db\Adapter\Adapter'));
        $model = 'Administration\\Model\\'.$this->params()->fromRoute('model');
        $component = new $model($this->getServiceLocator()->get('Zend\Db\Adapter\Adapter'), $controlPanel);

        $request = $this->getRequest();
        $form = $component->getForm();

        if ($request->isPost()) {
            $form->getFormObject()->setInputFilter($component->getInputFilter());
            $form->getFormObject()->setData($component->preparePostData($request->getPost()));
            if ($form->getFormObject()->isValid()) {
                $component->save($form->getFormObject()->getData());
                //After Save redirect to listing
                return $this->redirect()->toRoute('administration', array(
                    'action' => 'index',
                    'model'  => $this->params()->fromRoute('model')
                ));
            }
        }

        return new ViewModel(
            array(
                'form'               => $form,
                'multilingualFields' => $component->getAllMultilingualFields(),
                'controlPanel'       => $controlPanel,
            )
        );
    }

    public function editAction()
    {
        $controlPanel = new ControlPanel($this->getServiceLocator()->get('Zend\Db\Adapter\Adapter'));
        $model = 'Administration\\Model\\'.$this->params()->fromRoute('model');
        $component = new $model($this->getServiceLocator()->get('Zend\Db\Adapter\Adapter'), $controlPanel);

        $request = $this->getRequest();
        $form = $component->getForm();

        if ($request->isPost()) {
            $form->getFormObject()->setInputFilter($component->getInputFilter());
            //var_dump($request->getPost());
            $form->getFormObject()->setData($component->preparePostData($request->getPost()));

            if ($form->getFormObject()->isValid()) {
                $component->save($form->getFormObject()->getData());
                //After Save redirect to listing
//                return $this->redirect()->toRoute('administration', array(
//                    'action' => 'index',
//                    'model'  => $this->params()->fromRoute('model')
//                ));
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
        $form->getFormObject()->bind($item);

        return new ViewModel(
            array(
                'form'               => $form,
                'multilingualFields' => $component->getAllMultilingualFields(),
                'controlPanel'       => $controlPanel,
            )
        );
    }
}
