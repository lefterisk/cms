<?php 
namespace Administration\Model;

use Administration\Helper\Model\CustomSelectionHandler;
use Administration\Helper\Model\TableHandler;
use Administration\Helper\Model\RelationsHandler;

class UserGroup  extends TableHandler
{
	public $manager;

    public function __construct($followRelations = true, $controlPanel)
    {
        parent::__construct('UserGroup');

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

        $optionsArray = array();
        if ($controlPanel) {
            foreach ($controlPanel->getExistingModelsArray() as $modelName) {
                $optionsArray[$modelName] = $modelName;
            }
        }

        $this->setCustomSelections(array($customSelect = new CustomSelectionHandler('group_view_permission', $optionsArray, true, 'UserGroupsPermission') ));
		$this->setRelations(array($user = new RelationsHandler('User','oneToMany','email')));
       //$this->finaliseTable();
//		$this->setMetaTitle();
//		$this->setMetaDescription();
//		$this->setMetaKeywords();
    	
    }
}