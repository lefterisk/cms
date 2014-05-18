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
use Zend\View\Model\JsonModel;
use Administration\Model;
use Zend\Db\TableGateway\Exception;
use Zend\Escaper\Escaper;
use Administration\Helper\Model\GenericRelationFilter;
use Administration\Helper\Model\FormManager;


class GenericModelController extends AbstractActionController
{
    protected $controlPanel;
    protected $component;

    protected function initializeComponent()
    {
        $this->controlPanel = $this->getServiceLocator()->get('ControlPanel');
        $this->component    = $this->controlPanel->instantiateModelForUser($this->params()->fromRoute('model'));
        if ($this->component) {
            $this->component->finaliseTable();
        }
        $escaper            = new Escaper('utf-8');

        $this->layout()->setVariable('controlPanel' , $this->controlPanel);
        $this->layout()->setVariable('escaper'      , $escaper);
        $this->layout()->setVariable('cleanUrl'     , $this->cleanUrlFromLanguage());

        $this->translator = $this->getServiceLocator()->get('translator')->addTranslationFilePattern(
            'phpArray',
            __DIR__ . '/../../../language/partials',
            $this->params()->fromRoute('model').'_%s.php'
        );
    }

    public function indexAction()
    {
    	$this->initializeComponent();

        //If user not logged in redirect to Login Page
        if (!$this->controlPanel->getAuthService()->hasIdentity()) {
            return $this->redirect()->toRoute('adminLogin');
        }

        //If Model does not exist or is not available to userGroup throw 404
        if (!$this->component) {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        $relationFilters = new GenericRelationFilter($this->component->getModel(), $this->controlPanel);
        $viewModel       = new ViewModel(
            array(
                'listing' => $this->component->getListing(
                        ($this->params()->fromRoute('parent'))         ? $this->params()->fromRoute('parent'): 0,
                        ($this->params()->fromRoute('itemsperpage'))   ? $this->params()->fromRoute('itemsperpage'): 20,
                        ($this->params()->fromRoute('page'))           ? $this->params()->fromRoute('page'): 1,
                        ($this->params()->fromRoute('order'))          ? $this->params()->fromRoute('order'): null,
                        ($this->params()->fromRoute('direction'))      ? $this->params()->fromRoute('direction'): null,
                        ($this->params()->fromPost('relationFilters')) ? $this->params()->fromPost('relationFilters'): array()
                    ),
                'visibleListingFields' => $this->component->getModel()->getListingFields(),
                'listingSwitches'      => $this->component->getModel()->getListingSwitches(),
                'relationFilters'      => $relationFilters->getForm(),
                'modelPrefix'          => $this->component->getModel()->getPrefix(),
                'model'                => $this->params()->fromRoute('model'),
                'controlPanel'         => $this->controlPanel,
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

        //If Model does not exist or is not available to userGroup throw 404
        if (!$this->component) {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        $request = $this->getRequest();
        $form    = new FormManager($this->component, $this->controlPanel);

        if ($request->isPost()) {
            $form->getFormObject()->setInputFilter($this->component->getModel()->getInputFilter());
            $form->getFormObject()->setData($this->component->getModel()->preparePostData($request->getPost()));
            if ($form->getFormObject()->isValid()) {
                $this->component->save($form->getFormObject()->getData());
                //After Save redirect to listing
                return $this->redirect()->toRoute('genericModel', array(
                    'action' => 'index',
                    'model'  => $this->params()->fromRoute('model')
                ));
            }
        }

        return new ViewModel(
            array(
                'form'               => $form,
                'multilingualFields' => $this->component->getModel()->getAllMultilingualFields(),
                'modelPrefix'        => $this->component->getModel()->getPrefix(),
                'model'              => $this->params()->fromRoute('model'),
                'controlPanel'       => $this->controlPanel,
            )
        );
    }

    public function editAction()
    {
        $this->initializeComponent();

        //If user not logged in redirect to Login Page
        if (!$this->controlPanel->getAuthService()->hasIdentity()) {
            return $this->redirect()->toRoute('adminLogin');
        }

        //If Model does not exist or is not available to userGroup throw 404
        if (!$this->component) {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        $request = $this->getRequest();
        $form    = new FormManager($this->component, $this->controlPanel);

        if ($request->isPost()) {
            $form->getFormObject()->setInputFilter($this->component->getModel()->getInputFilter());
            $form->getFormObject()->setData($this->component->getModel()->preparePostData($request->getPost()));

            if ($form->getFormObject()->isValid()) {
                $this->component->save($form->getFormObject()->getData());
                //After Save redirect to listing
                return $this->redirect()->toRoute('genericModel', array(
                    'action' => 'index',
                    'model'  => $this->params()->fromRoute('model')
                ));
            }
        }

        try {
            $item = $this->component->getItemById($this->params()->fromRoute('item'));
        }
        catch (\Exception $ex) {
            return $this->redirect()->toRoute('genericModel', array(
                'action' => 'index',
                'model'  => $this->params()->fromRoute('model')
            ));
        }
        if (!$item) {
            return $this->redirect()->toRoute('genericModel', array(
                'action' => 'index',
                'model'  => $this->params()->fromRoute('model')
            ));
        }

        //Bind form to Item
        $form->getFormObject()->bind($item);
        $form = $this->component->getModel()->populatedFormHook($form);

        return new ViewModel(
            array(
                'form'               => $form,
                'modelPrefix'        => $this->component->getModel()->getPrefix(),
                'model'              => $this->params()->fromRoute('model'),
                'multilingualFields' => $this->component->getModel()->getAllMultilingualFields(),
                'controlPanel'       => $this->controlPanel,
            )
        );
    }

    public function deleteAction()
    {
        $this->initializeComponent();

        //If user not logged in redirect to Login Page
        if (!$this->controlPanel->getAuthService()->hasIdentity()) {
            return $this->redirect()->toRoute('adminLogin');
        }

        //If Model does not exist or is not available to userGroup throw 404
        if (!$this->component) {
            $this->getResponse()->setStatusCode(404);
            return;
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

        //If user not logged in redirect to Login Page
        if (!$this->controlPanel->getAuthService()->hasIdentity()) {
            return $this->redirect()->toRoute('adminLogin');
        }

        //If Model does not exist or is not available to userGroup throw 404
        if (!$this->component) {
            $this->getResponse()->setStatusCode(404);
            return;
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

    public function editSingleBooleanFieldAction()
    {
        $this->initializeComponent();

        //If user not logged in redirect to Login Page
        if (!$this->controlPanel->getAuthService()->hasIdentity()) {
            return new JsonModel(
                array(
                    'success'  => false,
                    'messages' => array(
                        'You ve been logged out!'
                    ),
                )
            );
        }

        //If Model does not exist or is not available to userGroup throw 404
        if (!$this->component) {
            return new JsonModel(
                array(
                    'success'  => false,
                    'messages' => array(
                        'You are not authorized to edit this!'
                    ),
                )
            );
        }

        //See if item to be edited exists
        echo $this->params()->fromRoute('id');
        try {
            $item = $this->component->getItemById($this->params()->fromRoute('item'));
        }
        catch (\Exception $ex) {
            return new JsonModel(
                array(
                    'success'  => false,
                    'messages' => array(
                        'Something went wrong with retrieving the Item you wish to edit!'
                    ),
                )
            );
        }
        if (!$item) {
            return new JsonModel(
                array(
                    'success'  => false,
                    'messages' => array(
                        'The Item you are trying to edit does not exist!'
                    ),
                )
            );
        } else {
            $id    = $this->params()->fromRoute('item');
            $field = $this->params()->fromPost('field');
            $value = $this->params()->fromPost('value');

            if (!empty($field) && isset($value)) {
                try{
                    $this->component->editSingleBooleanField($id, $field, $value);
                } catch (\Exception $ex) {
                    return new JsonModel(
                        array(
                            'success'  => false,
                            'messages' => array(
                                $ex->getMessage()
                            ),
                        )
                    );
                }
            } else {
                return new JsonModel(
                    array(
                        'success'  => false,
                        'messages' => array(
                            'You must supply both field name and value!'
                        ),
                    )
                );
            }

            return new JsonModel(
                array(
                    'success'  => true,
                    'messages' => array(
                        'field' => $this->params()->fromPost('field'),
                        'value' => $this->params()->fromPost('value')
                    )
                )
            );
        }
    }

    protected function jsonResponse($success, $messages)
    {
        return new JsonModel(
            array(
                'success'  => $success,
                'messages' => $messages,
            )
        );
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

        return $this->redirect()->toRoute('genericModel', array_merge(
            $listingParams,
            array(
                'action' => 'index',
                'model'  => $this->params()->fromRoute('model')
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
        return $this->url()->fromRoute('genericModel', $urlParamsArray);
    }
}
