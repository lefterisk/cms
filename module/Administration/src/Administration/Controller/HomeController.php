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


class HomeController extends AbstractActionController
{
    protected $controlPanel;

    protected function initializeComponent()
    {
        $this->controlPanel = $this->getServiceLocator()->get('ControlPanel');
        $this->component    = $this->controlPanel->instantiateModelForUser($this->params()->fromRoute('model'));
        $this->layout()->setVariable('controlPanel' , $this->controlPanel);
    }

    public function indexAction()
    {
    	$this->initializeComponent();

        //If user not logged in redirect to Login Page
        if (!$this->controlPanel->getAuthService()->hasIdentity()) {
            return $this->redirect()->toRoute('adminLogin');
        }

        $viewModel = new ViewModel();

        return $viewModel;
    }

}
