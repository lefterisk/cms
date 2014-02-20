<?php 
namespace Administration\Model;

use Zend\Mvc\Controller\AbstractActionController;
use Administration\AbstractClasses\AbstractModel;

class User extends AbstractModel 
{
	public function __construct()
    {
    	$this->setModelName('User');
    	$this->setTableName('Users');
    	$this->setPrimaryKeyField('userId');
    	$this->setLanguageForeignKeyField('languageId');
    	$this->setIntegers(array());
    	$this->setBooleans(array());
    	$this->setVarchars(array());
    	$this->setShortTexts(array());
    	$this->setLongTexts(array());
    	$this->setFiles(array());
    	$this->setMultilanguageVarchars(array());
    	$this->setMultilanguageShortTexts(array());
    	$this->setMultilanguageLongTexts(array());
    	$this->setMultilanguageFiles(array());

    	$this->checkDbTable();
    }
}