<?php 
namespace Administration\Model;

use Administration\AbstractClasses\TableHandler;
use Administration\AbstractClasses\RelationsHandler;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

class User  extends TableHandler implements InputFilterAwareInterface
{
    protected $inputFilter;

    public function __construct($dbAdapter, $followRelations = true)
    {
        parent::__construct('User', $dbAdapter);

        $this->setIsMultilingual(false);
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
//		$this->setMultilingualVarchars(array('title'));
//		$this->setMultilingualTexts(array('description'));
//		$this->setMultilingualLongTexts(array('longDescription'));
//		$this->setMultilingualFiles(array('multiLangfile'));
//		$this->setRequiredFields(array());
//		$this->setMultilingualRequiredFields(array());
		$this->setRelations(array($userGroup = new RelationsHandler('UserGroup','manyToOne','name')));
        $this->finaliseTable();
//		$this->setMetaTitle();
//		$this->setMetaDescription();
//		$this->setMetaKeywords();

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
                'name'     => 'email',
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
                'name'     => 'first_name',
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

            $this->inputFilter = $inputFilter;
        }
        return $this->inputFilter;
    }
}