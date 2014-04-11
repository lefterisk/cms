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
use Zend\Db\TableGateway\Exception;


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

        if (!$this->controlPanel->isUserLogged() && $this->params()->fromRoute('model') != 'login') {
            return $this->redirect()->toRoute('administration', array('model'  => 'login'));
        }

        if ($this->isGenericComponent()) {
            $viewModel = new ViewModel(
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
        } else {
            if (method_exists($this->component ,'getViewVariablesArray')) {
                $viewModel = new ViewModel(array_merge($this->component->getViewVariablesArray(),array()));
            } else {
                throw new Exception\InvalidArgumentException('All non generic components must extend the CustomComponent Interface (you are missing the ViewVariablesArray method)');
            }
            $viewModel->setTemplate('administration/index/'.$this->params()->fromRoute('model').'.phtml');
        }
        return $viewModel;
    }

    public function addAction()
    {
        $this->initializeComponent();

        if (!$this->controlPanel->isUserLogged()) {
            return $this->redirect()->toRoute('administration', array('model'  => 'login'));
        }

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

        if (!$this->controlPanel->isUserLogged()) {
            return $this->redirect()->toRoute('administration', array('model'  => 'login'));
        }

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

        if (!$this->controlPanel->isUserLogged()) {
            return $this->redirect()->toRoute('administration', array('model'  => 'login'));
        }

        $itemId = $this->params()->fromRoute('item');
        if ( !empty( $itemId )) {
            try {
                $item = $this->component->getItemById($itemId);
            }
            catch (\Exception $ex) {
                $this->redirectToComponentListing();
            }
            if ($item) {
                $this->component->deleteSingle($itemId);
            } else {
                $this->redirectToComponentListing();
            }
        } else {
            $this->redirectToComponentListing();
        }
        $this->redirectToComponentListing();
    }

    public function deleteMultipleAction()
    {
        $this->initializeComponent();

        if (!$this->controlPanel->isUserLogged()) {
            return $this->redirect()->toRoute('administration', array('model'  => 'login'));
        }

        $request = $this->getRequest();

        if ($request->isPost() && is_array($this->params()->fromPost('multipleDeleteCheck')) && count($this->params()->fromPost('multipleDeleteCheck')) > 0 ) {
            foreach ($this->params()->fromPost('multipleDeleteCheck') as $idToDelete) {
                try {
                    $item = $this->component->getItemById($idToDelete);
                }
                catch (\Exception $ex) {

                }
                if ($item) {
                    $this->component->deleteSingle($idToDelete);
                } else {

                }
            }
        }
        $this->redirectToComponentListing();
    }

    public function loginAction()
    {
        $this->initializeComponent();

        $request = $this->getRequest();

        $email     = $this->params()->fromPost('email');
        $password  = $this->params()->fromPost('password');

        //if user-pass is set and login info validates
        if ($request->isPost() && $email && $password) {
            $this->controlPanel->attemptAdminLogin($email, $password);
        }

        if (!$this->controlPanel->isUserLogged()) {
            return $this->redirect()->toRoute('administration', array('model'  => 'login'));
        } else {
            return $this->redirect()->toRoute('administration', array('model'  => 'home'));
        }
    }

    protected function redirectToComponentListing()
    {
        $listingParams = array();
        if ($this->params()->fromRoute('itemsperpage')) {
            $listingParams['itemsperpage'] = $this->params()->fromRoute('itemsperpage');
        }
        if ($this->params()->fromRoute('page')) {
            $listingParams['page'] = $this->params()->fromRoute('page');
        }
        if ($this->params()->fromRoute('order')) {
            $listingParams['order'] = $this->params()->fromRoute('order');
        }
        if ($this->params()->fromRoute('direction')) {
            $listingParams['direction'] = $this->params()->fromRoute('direction');
        }

        return $this->redirect()->toRoute('administration', array_merge(
            $listingParams,
            array(
                'action' => 'index',
                'model'  => $this->params()->fromRoute('model')
            )
        ));
    }

    protected function isGenericComponent()
    {
        if (method_exists($this->component ,'genericComponent')) {
            return true;
        } else {
            return false;
        }
    }
}
