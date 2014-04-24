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


class LoginController extends AbstractActionController
{
    protected $controlPanel;

    protected function initializeComponent()
    {
        $this->controlPanel = $this->getServiceLocator()->get('ControlPanel');
        //$this->component    = $this->controlPanel->instantiateModelForUser($this->params()->fromRoute('model'));
        $this->layout()->setVariable('controlPanel' , $this->controlPanel);
    }

    public function indexAction()
    {
    	$this->initializeComponent();

        $viewModel = new ViewModel();

        return $viewModel;
    }

    public function loginAction()
    {
        $this->initializeComponent();

        $request = $this->getRequest();

        $email     = $this->params()->fromPost('email');
        $password  = $this->params()->fromPost('password');

        //if user-pass is set and login info validates
        if ($request->isPost() && $email && $password) {
            $this->controlPanel->getAuthService()->getAdapter()->setIdentity($email);
            $this->controlPanel->getAuthService()->getAdapter()->setCredential($password);
            $result = $this->controlPanel->getAuthService()->authenticate();

            foreach($result->getMessages() as $message)
            {
                //save message temporary into flashmessenger
                $this->flashmessenger()->addMessage($message);
            }
            //$this->controlPanel->attemptAdminLogin($email, $password);
        }

        if (!$this->controlPanel->getAuthService()->hasIdentity()) {
            return $this->redirect()->toRoute('adminLogin', array());
        } else {
            return $this->redirect()->toRoute('adminHome');
        }
    }

}
