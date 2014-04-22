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
use Zend\Db\TableGateway\Exception;


class SiteManagerController extends AbstractActionController
{
    protected $controlPanel;
    protected $component;

    protected function initializeComponent()
    {
        $this->controlPanel = $this->getServiceLocator()->get('ControlPanel');
        //$this->component    = $this->controlPanel->instantiateModelForUser($this->params()->fromRoute('model'));
        $this->layout()->setVariable('controlPanel' , $this->controlPanel);
    }

    public function indexAction()
    {
    	$this->initializeComponent();

        //If user not logged in redirect to Login Page
        if (!$this->controlPanel->getAuthService()->hasIdentity() && $this->params()->fromRoute('model') != 'login') {
            return $this->redirect()->toRoute('adminLogin');
        }

        $viewModel = new ViewModel(
            array(
                'controlPanel' => $this->controlPanel
            )
        );

        return $viewModel;
    }

    public function addAction()
    {
        $this->initializeComponent();

        //If user not logged in redirect to Login Page
        if (!$this->controlPanel->getAuthService()->hasIdentity()) {
            return $this->redirect()->toRoute('adminLogin');
        }

        $viewModel = new ViewModel();

        return $viewModel;
    }

    public function editAction()
    {
        $this->initializeComponent();

        //If user not logged in redirect to Login Page
        if (!$this->controlPanel->getAuthService()->hasIdentity()) {
            return $this->redirect()->toRoute('adminLogin');
        }

        $viewModel = new ViewModel();

        return $viewModel;
    }

    public function deleteAction()
    {
        $this->initializeComponent();

        //If user not logged in redirect to Login Page
        if (!$this->controlPanel->getAuthService()->hasIdentity()) {
            return $this->redirect()->toRoute('adminLogin');
        }


        $this->redirectToSiteManagerListing();
    }

    public function deleteMultipleAction()
    {
        $this->initializeComponent();

        //If user not logged in redirect to Login Page
        if (!$this->controlPanel->getAuthService()->hasIdentity()) {
            return $this->redirect()->toRoute('adminLogin');
        }


        $this->redirectToSiteManagerListing();
    }


    protected function redirectToSiteManagerListing()
    {
        $listingParams = array();
        if ($this->params()->fromRoute('page')) {
            $listingParams['page'] = $this->params()->fromRoute('page');
        }

        return $this->redirect()->toRoute('sitemanager', array_merge(
            $listingParams,
            array(
                'action' => 'index',
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
