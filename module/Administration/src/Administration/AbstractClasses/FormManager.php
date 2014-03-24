<?php
namespace Administration\AbstractClasses;

use Zend\Db\TableGateway\Exception;
use Zend\Form\Form;

class FormManager
{
    private $tabs = array();
    private $form;

    public function __construct(Form $form)
    {
        $this->form = $form;
    }

    public function addTab($name, array $containingFields = array())
    {
        if (empty($name)) {
            throw new Exception\InvalidArgumentException('Tab must have a name!');
        } else {
            $this->tabs[] = array('name' => $name , 'fields' => $containingFields);
        }
    }

    public function getTabs()
    {
        return $this->tabs;
    }

    public function getFormObject()
    {
        return $this->form;
    }
}