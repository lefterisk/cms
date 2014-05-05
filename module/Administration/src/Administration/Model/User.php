<?php 
namespace Administration\Model;

use Administration\Helper\Model\TableHandler;
use Administration\Helper\Model\RelationsHandler;
use Zend\Crypt\Password\Bcrypt;

class User  extends TableHandler
{

    public function __construct($followRelations = true, $controlPanel)
    {
        parent::__construct('User');//<--Table name

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
		$this->setMultilingualVarchars(array());
//		$this->setMultilingualTexts(array('description'));
		$this->setMultilingualLongTexts(array());
		$this->setMultilingualFiles(array());
//		$this->setRequiredFields(array());
//		$this->setMultilingualRequiredFields(array());
		//$this->setRelations(array($userGroup = new RelationsHandler('UserGroup','manyToMany','name','UserToUserGroups')));
        $this->setRelations(array($userGroup = new RelationsHandler('UserGroup','manyToOne','name')));
        //$this->finaliseTable();
//		$this->setMetaTitle();
//		$this->setMetaDescription();
//		$this->setMetaKeywords();

    }

    public function preSaveHook(Array $data)
    {
        if (array_key_exists('password', $data) && empty($data['password'])) {
            unset($data['password']);
        } elseif (array_key_exists('password', $data) && !empty($data['password'])) {
            $bcrypt           = new Bcrypt();
            $data['password'] = $bcrypt->create($data['password']);
        }
        return $data;
    }

    public function populatedFormHook($form)
    {
        $formObject = $form->getFormObject();
        $formObject->get('password')->setValue('');
        return $form;
    }
}