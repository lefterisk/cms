<?php 
namespace Administration\Model;

use Administration\AbstractClasses\TableHandler;
use Administration\AbstractClasses\RelationsHandler;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

class SiteLanguage  extends TableHandler implements InputFilterAwareInterface
{
    protected $inputFilter;

    public function __construct($dbAdapter, $followRelations = true)
    {
        parent::__construct('SiteLanguage', $dbAdapter);

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

    public function setInputFilter(InputFilterInterface $inputFilter)
    {
        throw new \Exception("Not used");
    }

    public function getInputFilter()
    {
        if (!$this->inputFilter) {
            $inputFilter = new InputFilter();

            $inputFilter->add(array(
                'name'     => 'name',
                'required' => true,
                'filters'  => array(
                    array('name' => 'StripTags'),
                    array('name' => 'StringTrim'),
                ),
                'validators' => array(
                    array(
                        'name'    => 'StringLength',
                        'options' => array(
                            'encoding' => 'UTF-8',
                            'min'      => 1,
                            'max'      => 100,
                        ),
                    ),
                ),
            ));

            $inputFilter->add(array(
                'name'     => 'code',
                'required' => true,
                'filters'  => array(
                    array('name' => 'StripTags'),
                    array('name' => 'StringTrim'),
                ),
                'validators' => array(
                    array(
                        'name'    => 'StringLength',
                        'options' => array(
                            'encoding' => 'UTF-8',
                            'min'      => 2,
                            'max'      => 5,
                        ),
                    ),
                ),
            ));

            $this->inputFilter = $inputFilter;
        }
        return $this->inputFilter;
    }
}