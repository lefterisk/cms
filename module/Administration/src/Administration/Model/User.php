<?php 
namespace Administration\Model;

use Administration\AbstractClasses\TableHandle;

class User  
{
	public function __construct($dbAdapter)
    {
    	$itemManager = new TableHandle('User', $dbAdapter);
        $itemManager->setLanguageID("languageId");
        $itemManager->setNameField("email");
    	$itemManager->setPrefix("user_");
        //Fields
		$itemManager->setDates(array('day'));
		$itemManager->setEnums(array('status','sticky'));
		$itemManager->setVarchars(array('email', 'address'));
		$itemManager->setTexts(array('simpleText_1','simpleText_2'));
		$itemManager->setLongTexts(array('simpleLongText_1','simpleLongText_2'));
		$itemManager->setIntegers(array('id','number'));
		$itemManager->setImages(array('image_1', 'image_2'));
		$itemManager->setFiles(array('file_1','file_2'));
		$itemManager->setMultilingualVarchars(array('title'));
		$itemManager->setMultilingualTexts(array('description'));
		$itemManager->setMultilingualLongTexts(array('longDescription'));
		$itemManager->setMultilingualFiles(array('multiLangfile'));
		$itemManager->setRequiredFields(array());
		$itemManager->setMultilingualRequiredFields(array());
		$itemManager->setRelations(array());
        $itemManager->finaliseTable();
//		$itemManager->setMetaTitle();
//		$itemManager->setMetaDescription();
//		$itemManager->setMetaKeywords();
    	
    }
}