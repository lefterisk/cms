<?php 
namespace Administration\Model;

use Administration\Helper\Model\CustomModelInterface;

class Home implements CustomModelInterface
{
    protected $viewVariables = array();

	public function __construct($controlPanel)
    {
        

    }

    public function getViewVariablesArray()
    {
        return $this->viewVariables;
    }
}