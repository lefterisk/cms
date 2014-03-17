<?php 
namespace Administration\Model;

use Administration\AbstractClasses\TableHandler;
use Administration\AbstractClasses\RelationsHandler;

class User  
{
	public $manager;
    
    public function __construct($dbAdapter, $followRelations = true)
    {
    	$this->manager = new TableHandler('User', $dbAdapter, $followRelations);
        $this->manager->setIsMultilingual(false);
        $this->manager->setNameField("email");
    	$this->manager->setPrefix("user_");
        //Fields
		$this->manager->setDates(array('day'));
		$this->manager->setEnums(array('status','sticky'));
		$this->manager->setVarchars(array('email', 'address'));
		$this->manager->setTexts(array('simpleText_1','simpleText_2'));
		$this->manager->setLongTexts(array('simpleLongText_1','simpleLongText_2'));
		$this->manager->setIntegers(array('id','number'));
		$this->manager->setImages(array('image_1', 'image_2'),true);
		$this->manager->setFiles(array('file_1','file_2'),true);
//		$this->manager->setMultilingualVarchars(array('title'));
//		$this->manager->setMultilingualTexts(array('description'));
//		$this->manager->setMultilingualLongTexts(array('longDescription'));
//		$this->manager->setMultilingualFiles(array('multiLangfile'));
//		$this->manager->setRequiredFields(array());
//		$this->manager->setMultilingualRequiredFields(array());
		$this->manager->setRelations(array($userGroup = new RelationsHandler('UserGroup','manyToMany')));
        $this->manager->finaliseTable();
//		$this->manager->setMetaTitle();
//		$this->manager->setMetaDescription();
//		$this->manager->setMetaKeywords();
    	
    }
}