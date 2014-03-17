<?php 
namespace Administration\Model;

use Administration\AbstractClasses\TableHandler;
use Administration\AbstractClasses\RelationsHandler;

class User  
{
    public $manager;
    public $viewHelper;

    public function __construct($dbAdapter, $followRelations = true)
    {
    	$this->manager = new TableHandler('User', $dbAdapter, $followRelations);
        $this->manager->setIsMultilingual(false);
        $this->manager->setNameField("email");
    	$this->manager->setPrefix("user_");
        //Fields
		$this->manager->setDates(array());
		$this->manager->setEnums(array('status'));
		$this->manager->setVarchars(array('email', 'first_name', 'last_name', 'password'));
		$this->manager->setTexts(array());
		$this->manager->setLongTexts(array());
		$this->manager->setIntegers(array());
		$this->manager->setImages(array());
		$this->manager->setFiles(array());
//		$this->manager->setMultilingualVarchars(array('title'));
//		$this->manager->setMultilingualTexts(array('description'));
//		$this->manager->setMultilingualLongTexts(array('longDescription'));
//		$this->manager->setMultilingualFiles(array('multiLangfile'));
//		$this->manager->setRequiredFields(array());
//		$this->manager->setMultilingualRequiredFields(array());
		$this->manager->setRelations(array($userGroup = new RelationsHandler('UserGroup','manyToOne','name')));
        $this->manager->finaliseTable();
//		$this->manager->setMetaTitle();
//		$this->manager->setMetaDescription();
//		$this->manager->setMetaKeywords();

    }
}