<?php 
namespace Administration\Model;

use Administration\Helper\Model\TableHandler;
use Administration\Helper\Model\RelationsHandler;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

class AdminLanguage  extends TableHandler implements InputFilterAwareInterface
{

    public function __construct($controlPanel, $followRelations = true)
    {
        parent::__construct('AdminLanguage', $controlPanel);

        $this->setListingFields(array("name"));
        $this->setListingSwitches(array("status" , "default"));
    	$this->setPrefix("admin_language_");
        $this->setFollowRelations($followRelations);

        //Fields
		$this->setEnums(array('status', 'default'));
		$this->setVarchars(array('name', 'code'));
		$this->setImages(array('image'));
        $this->finaliseTable();


    }
}