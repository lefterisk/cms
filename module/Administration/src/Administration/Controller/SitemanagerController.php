<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Administration\Controller;

use Administration\Helper\Model\SiteManagerTableGateway;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Administration\Model;
use Zend\Db\TableGateway\Exception;
use Zend\Escaper\Escaper;


class SiteManagerController extends AbstractActionController
{
    protected $controlPanel;
    protected $component;

    protected function initializeComponent()
    {
        $this->controlPanel = $this->getServiceLocator()->get('ControlPanel');
        $escaper            = new Escaper('utf-8');

        $this->layout()->setVariable('controlPanel' , $this->controlPanel);
        $this->layout()->setVariable('escaper'      , $escaper);
        $this->layout()->setVariable('cleanUrl'     , $this->cleanUrlFromLanguage());
    }

    public function indexAction()
    {
    	$this->initializeComponent();

        //If user not logged in redirect to Login Page
        if (!$this->controlPanel->getAuthService()->hasIdentity() && $this->params()->fromRoute('model') != 'login') {
            return $this->redirect()->toRoute('adminLogin');
        }

        $siteManagerGateway = new SiteManagerTableGateway($this->controlPanel);

        $viewModel = new ViewModel(
            array(
                'controlPanel' => $this->controlPanel,
                'siteMap'      => $siteManagerGateway->getSiteMap()
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

        $this->component    = $this->controlPanel->instantiateModelForUser($this->params()->fromRoute('model'));
        //if component doesnt exist or is not standalone component
        if (!$this->component || !$this->component->getModel()->isStandAlonePage()) {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        try {
            $this->component->addToSiteMap();
        }
        catch (\Exception $ex) {
            $this->redirectToSiteManagerListing();
        }
        $this->redirectToSiteManagerListing();
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

    private function cleanUrlFromLanguage()
    {
        $urlParamsArray = array();
        foreach (array_merge($this->params()->fromRoute(), $this->params()->fromPost()) as $parameter => $value ) {
            if (!in_array($parameter, array('language'))) {
                $urlParamsArray[$parameter] = $value;
            }
        }
        return $this->url()->fromRoute('sitemanager', $urlParamsArray);
    }
}
