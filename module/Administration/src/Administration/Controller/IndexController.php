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
    protected $controlPanel;
    protected $component;

    protected function initializeComponent()
    {
        $this->controlPanel = new ControlPanel($this->getServiceLocator()->get('Zend\Db\Adapter\Adapter'));
        $model              = 'Administration\\Model\\'.$this->params()->fromRoute('model');
        $this->component    = new $model($this->getServiceLocator()->get('Zend\Db\Adapter\Adapter'), $this->controlPanel);
    }

    public function indexAction()
    {
    	$this->initializeComponent();

        return new ViewModel(
            array(
                'listing' => $this->component->getListing(
                    ($this->params()->fromRoute('itemsperpage'))? $this->params()->fromRoute('itemsperpage'): 20,
                    ($this->params()->fromRoute('page'))        ? $this->params()->fromRoute('page'): 1,
                    ($this->params()->fromRoute('order'))       ? $this->params()->fromRoute('order'): null,
                    ($this->params()->fromRoute('direction'))   ? $this->params()->fromRoute('direction'): null
                ),
                'visibleListingFields' => $this->component->getListingFields(),
                'listingSwitches'      => $this->component->getListingSwitches(),
                'model'                => $this->params()->fromRoute('model'),
                'controlPanel'         => $this->controlPanel,
            )
        );
    }

    public function addAction()
    {
        $this->initializeComponent();

        $request = $this->getRequest();
        $form    = $this->component->getForm();

        if ($request->isPost()) {
            $form->getFormObject()->setInputFilter($this->component->getInputFilter());
            $form->getFormObject()->setData($this->component->preparePostData($request->getPost()));
            if ($form->getFormObject()->isValid()) {
                $this->component->save($form->getFormObject()->getData());
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
                'multilingualFields' => $this->component->getAllMultilingualFields(),
                'controlPanel'       => $this->controlPanel,
            )
        );
    }

    public function editAction()
    {
        $this->initializeComponent();

        $request = $this->getRequest();
        $form    = $this->component->getForm();

        if ($request->isPost()) {
            $form->getFormObject()->setInputFilter($this->component->getInputFilter());
            //var_dump($request->getPost());
            $form->getFormObject()->setData($this->component->preparePostData($request->getPost()));

            if ($form->getFormObject()->isValid()) {
                $this->component->save($form->getFormObject()->getData());
                //After Save redirect to listing
                return $this->redirect()->toRoute('administration', array(
                    'action' => 'index',
                    'model'  => $this->params()->fromRoute('model')
                ));
            }
        }

        try {
            $item = $this->component->getItemById($this->params()->fromRoute('item'));
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
                'multilingualFields' => $this->component->getAllMultilingualFields(),
                'controlPanel'       => $this->controlPanel,
            )
        );
    }

    public function deleteAction()
    {
        $this->initializeComponent();
        $itemId = $this->params()->fromRoute('item');
        if ( !empty( $itemId )) {
            try {
                $item = $this->component->getItemById($itemId);
            }
            catch (\Exception $ex) {
                $this->redirectToComponentListing($itemId);
            }
            if ($item) {
                $this->component->deleteSingle($itemId);
            } else {
                $this->redirectToComponentListing();
            }
        } else {
            $this->redirectToComponentListing();
        }
    }

    protected function redirectToComponentListing() {
        return $this->redirect()->toRoute('administration', array(
            'action' => 'index',
            'model'  => $this->params()->fromRoute('model')
        ));
    }
}
