<?php 
namespace Administration\Model;

use Administration\ModelInterface\CustomComponentInterface;

class Home implements CustomComponentInterface
{
    protected $viewVariables = array();

	public function __construct($dbAdapter, $controlPanel)
    {
        

    }

    public function getViewVariablesArray()
    {
        return $this->viewVariables;
    }
}