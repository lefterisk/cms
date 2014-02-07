<?php
namespace Administration\AbstractClasses;

use Zend\Mvc\Controller\AbstractActionController;

class AbstractModel 
{
	protected $modelName;

	public function __construct()
    {
        echo $this->modelName;
    }
}