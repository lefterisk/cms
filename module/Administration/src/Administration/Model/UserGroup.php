<?php 
namespace Administration\Model;

use Administration\AbstractClasses\TableHandler;
use Administration\AbstractClasses\RelationsHandler;

class UserGroup
{
	public $manager;

    public function __construct($dbAdapter, $followRelations = true)
    {
        $this->manager = new TableHandler('UserGroup', $dbAdapter, $followRelations);
        $this->manager->setIsMultilingual(false);
        $this->manager->setNameField("name");
        $this->manager->setPrefix("user_group_");
        //Fields
        $this->manager->setDates(array());
        $this->manager->setEnums(array('status'));
        $this->manager->setVarchars(array('name'));
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
		$this->manager->setRelations(array($user = new RelationsHandler('User','oneToMany','email')));
        $this->manager->finaliseTable();
//		$this->manager->setMetaTitle();
//		$this->manager->setMetaDescription();
//		$this->manager->setMetaKeywords();
    	
    }
}