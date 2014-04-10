<?php 
namespace Administration\Model;

use Administration\AbstractClasses\TableHandler;
use Administration\AbstractClasses\RelationsHandler;

class User  extends TableHandler
{

    public function __construct($dbAdapter, $controlPanel, $followRelations = true)
    {
        parent::__construct('User', $dbAdapter, $controlPanel);//<--Table name

        $this->setListingFields(array("email"));
        $this->setListingSwitches(array("status"));
    	$this->setPrefix("user_");
        $this->setFollowRelations($followRelations);

        //Fields
		$this->setDates(array());
		$this->setEnums(array('status'));
		$this->setVarchars(array('email', 'first_name', 'last_name', 'password'));
		$this->setTexts(array());
		$this->setLongTexts(array());
		$this->setIntegers(array());
		$this->setImages(array());
		$this->setFiles(array());
		$this->setMultilingualVarchars(array('title'));
//		$this->setMultilingualTexts(array('description'));
		$this->setMultilingualLongTexts(array('longDescription'));
		$this->setMultilingualFiles(array('multiLangfile'));
//		$this->setRequiredFields(array());
//		$this->setMultilingualRequiredFields(array());
		//$this->setRelations(array($userGroup = new RelationsHandler('UserGroup','manyToMany','name','UserToUserGroups')));
        $this->setRelations(array($userGroup = new RelationsHandler('UserGroup','manyToOne','name')));
        $this->finaliseTable();
//		$this->setMetaTitle();
//		$this->setMetaDescription();
//		$this->setMetaKeywords();

    }
}