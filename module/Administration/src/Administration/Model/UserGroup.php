<?php 
namespace Administration\Model;

use Administration\AbstractClasses\TableHandler;
use Administration\AbstractClasses\RelationsHandler;

class UserGroup  extends TableHandler
{
	public $manager;

    public function __construct($dbAdapter, $followRelations = true)
    {
        parent::__construct('UserGroup', $dbAdapter);

        $this->setListingFields(array("name"));
        $this->setPrefix("user_group_");
        $this->setFollowRelations($followRelations);

        //Fields
        $this->setDates(array());
        $this->setEnums(array('status'));
        $this->setVarchars(array('name'));
        $this->setTexts(array());
        $this->setLongTexts(array());
        $this->setIntegers(array());
        $this->setImages(array());
        $this->setFiles(array());
//		$this->setMultilingualVarchars(array('title'));
//		$this->setMultilingualTexts(array('description'));
//		$this->setMultilingualLongTexts(array('longDescription'));
//		$this->setMultilingualFiles(array('multiLangfile'));
//		$this->setRequiredFields(array());
//		$this->setMultilingualRequiredFields(array());
		$this->setRelations(array($user = new RelationsHandler('User','oneToMany','email')));
        $this->finaliseTable();
//		$this->setMetaTitle();
//		$this->setMetaDescription();
//		$this->setMetaKeywords();
    	
    }
}