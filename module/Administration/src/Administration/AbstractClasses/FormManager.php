<?php
namespace Administration\AbstractClasses;

use Zend\Db\TableGateway\Exception;

class FormManager
{
    private $tabs = array();

    public function addTab($name, array $containingFields = array())
    {
        if (empty($name)) {
            throw new Exception\InvalidArgumentException('Tab must have a name!');
        } else {
            $this->tabs[] = array($name , $containingFields);
        }
    }

    public function getTabs()
    {
        return $this->tabs;
    }
}