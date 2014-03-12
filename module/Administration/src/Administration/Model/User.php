<?php 
namespace Administration\Model;

use Administration\AbstractClasses\TableHandle;

class User  
{
	public function __construct($dbAdapter)
    {
    	$itemManager = new TableHandle('User', 'UserDescription', 'id', 'languageId', 'email', $dbAdapter);
    	$itemManager->setPrefix("user_");
		$itemManager->setDates(array());
		$itemManager->setDateTimes(array());
		$itemManager->setEnums(array());
		$itemManager->setVarchars(array());	
		$itemManager->setTexts(array());
		$itemManager->setLongTexts(array());
		$itemManager->setIntegers(array());
		$itemManager->setImages(array());
		$itemManager->setFiles(array());
		$itemManager->setMultilanguageVarchars(array());
		$itemManager->setMultilanguageTexts(array());
		$itemManager->setMultilanguageLongTexts(array());
		$itemManager->setMultilanguageFiles(array());
		$itemManager->setRequiredFields(array());
		$itemManager->setMultilanguageRequiredFields(array());
		$itemManager->setJoinedTables(array());
		$itemManager->setRelations(array());
        $itemManager->tableGateWay->finaliseTable();
//		$itemManager->setMetaTitle();
//		$itemManager->setMetaDescription();
//		$itemManager->setMetaKeywords();
    	
    }
}