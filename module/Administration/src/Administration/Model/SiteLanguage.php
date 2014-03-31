<?php 
namespace Administration\Model;

use Administration\AbstractClasses\TableHandler;
use Administration\AbstractClasses\RelationsHandler;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

class SiteLanguage  extends TableHandler implements InputFilterAwareInterface
{

    public function __construct($dbAdapter, $controlPanel, $followRelations = true)
    {
        parent::__construct('SiteLanguage', $dbAdapter, $controlPanel);

        $this->setListingFields(array("name"));
        $this->setListingSwitches(array("status" , "default"));
    	$this->setPrefix("language_");
        $this->setFollowRelations($followRelations);

        //Fields
		$this->setEnums(array('status', 'default'));
		$this->setVarchars(array('name', 'code'));
		$this->setImages(array('image'));
        $this->finaliseTable();


    }
}