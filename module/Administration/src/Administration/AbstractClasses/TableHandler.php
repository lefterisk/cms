<?php
namespace Administration\AbstractClasses;

use Zend\Db\TableGateway\Exception;
use Zend\Form\Element;
use Zend\Form\Form;
use Zend\Db\Sql\Sql;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

class TableHandler extends AbstractModelTable implements InputFilterAwareInterface
{
	//Table properties
	private $tableName;
	private $tableDescriptionName;
    private $languageID = 'languageId';
    private $followRelations = true;
    private $listingFields = array();
    private $listingSwitches = array();
    private $inputFilter;

    //Generic fields which do not support Multilingual.
	private $enums = array();
	private $dates = array();
	private $images = array();
	private $files = array();

	//Fields which do not support Multilingual.
	private $varchars = array();
	private $texts = array();
	private $longTexts = array();
	private $integers = array();

	//Fields which support Multilingual.
	private $MultilingualVarchars = array();
	private $MultilingualTexts = array();
	private $MultilingualLongTexts = array();
	private $MultilingualFiles = array();

	//Fields which support custom meta tags.
	private $metaTitle;
	private $metaDescription;
	private $metaKeywords;
	
	//Captions
	private $useImagesCaptions = false;
	private $useFileCaptions = false;
	private $useMultilingualFilesCaptions = false;	

	//Required fields
	private $requiredFields = array();
	private $requiredMultilingualFields = array();
	
	//Related Tables
	private $relations = array();

	//Custom Fields
	private $customFields = array();
	
	//Custom Selection.
	private $customSelections = array();
	
	//Action Managers
	private $actionManager = array();

	//Prefix used for file uploads.
	private $prefix = '';

    //Contains all related to the add-edit form (zend form, tab-manager)
    public  $tableFormManager;

    //Db adapter
    public  $adapter;

    //Sql interface
    public  $sql;

    //Control object contains languages admin-rights etc
    public  $controlPanel;

	/**
	 * Instantiates a new TableHandler object
	 */
	public function __construct($tableName, $dbAdapter, $controlPanel = null)
	{
        $this->adapter = $dbAdapter;
        $this->sql     = new Sql($this->adapter);
        $this->controlPanel = $controlPanel;
        $this->setTableName($tableName);
        $this->setTableDescriptionName($tableName.'Description');
	}

	////////////////////////////////////////////////////////////////
	// Getter Methods
	////////////////////////////////////////////////////////////////
	
	/**
	 * Returns table name.
	 */
	public function getTableName()
	{
		return $this->tableName;
	}
	
	/**
	 * Returns table description name, which stores Multilingual data.
	 */
	public function getTableDescriptionName()
	{
		return $this->tableDescriptionName;
	}
	
	/**
	 * Returns languageID.
	 */
	public function getLanguageID()
	{
		return $this->languageID;
	}	
	
	/**
	 * Returns name field.
	 */
	public function getListingFields()
	{
		return $this->listingFields;
	}

    /**
     * Returns boolean switches for the listing.
     */
    public function getListingSwitches()
    {
        return $this->listingSwitches;
    }

	/**
	 * Returns enums.
	 */
	public function getEnums()
	{
		return $this->enums;
	}

	/**
	 * Returns dates.
	 */
	public function getDates()
	{
		return $this->dates;
	}

	/**
	 * Returns images.
	 */
	public function getImages()
	{
		return $this->images;
	}

	/**
	 * Returns images' captions.
	 */
	public function getImageCaptions()
	{
		if ($this->useImagesCaptions==false)
		{
			return array();
		}
		else
		{
			$captions = array();
			for ($i=0; $i<count($this->images); $i++)
			{
				$captions[$i] = $this->images[$i] . "_caption";
			}
			return $captions;
		}
	}

	/**
	 * Returns files.
	 */
	public function getFiles()
	{
		return $this->files;
	}

	/**
	 * Returns files' captions.
	 */
	public function getFileCaptions()
	{
		if ($this->useFileCaptions==false)
		{
			return array();
		}
		else
		{
			$captions = array();
			for ($i=0; $i<count($this->files); $i++)
			{
				$captions[$i] = $this->files[$i] . "_caption";
			}
			return $captions;
		}
	}
	
	/**
	 * Returns varchars.
	 */
	public function getVarchars()
	{
		return $this->varchars;
	}

	/**
	 * Returns texts.
	 */
	public function getTexts()
	{
		return $this->texts;
	}

	/**
	 * Returns Long Texts.
	 */
	public function getLongTexts()
	{
		return $this->longTexts;
	}

	/**
	 * Returns integers.
	 */
	public function getIntegers()
	{
		return $this->integers;
	}

	/**
	 * Returns varchars, which support Multilingual.
	 */
	public function getMultilingualVarchars()
	{
		return $this->MultilingualVarchars;
	}

	/**
	 * Returns texts, which support Multilingual.
	 */
	public function getMultilingualTexts()
	{
		return $this->MultilingualTexts;
	}

	/**
	 * Returns longTexts, which support Multilingual.
	 */
	public function getMultilingualLongTexts()
	{
		return $this->MultilingualLongTexts;
	}
	
	/**
	 * Returns the meta title.
	 */
	public function getMetaTitle()
	{
		return $this->metaTitle;
	}
	
	/**
	 * Returns the meta keywords.
	 */
	public function getMetaKeywords()
	{
		return $this->metaKeywords;
	}
	
	/**
	 * Returns the meta description.
	 */
	public function getMetaDescription()
	{
		return $this->metaDescription;
	}

	/**
	 * Returns files, which support Multilingual.
	 */
	public function getMultilingualFiles()
	{
		return $this->MultilingualFiles;
	}

	/**
	 * Returns files' captions.
	 */
	public function getMultilingualFilesCaptions()
	{
		if ($this->useMultilingualFilesCaptions==false)
		{
			return array();
		}
		else
		{
			$captions = array();
			for ($i=0; $i<count($this->MultilingualFiles); $i++)
			{
				$captions[$i] = $this->MultilingualFiles[$i] . "_caption";
			}
			return $captions;
		}
	}

	/**
	 * Returns required fields.
	 */
	public function getRequiredFields()
	{
		return $this->requiredFields;
	}

	/**
	 * Returns required Multilingual fields.
	 */
	public function getMultilingualRequiredFields()
	{
		return $this->requiredMultilingualFields;
	}

	/**
	 * Returns the Relations objects.
	 */
	public function getRelations()
	{
		return $this->relations;
	}
	
	/**
	 * Returns Custom Selections.
	 */ 
	public function getCustomFields()
	{
		return $this->customFields;
	}	
	
	/**
	 * Returns Custom Selections.
	 */ 
	public function getCustomSelections()
	{
		return $this->customSelections;
	}	

	/**
	 * Returns prefix.
	 */
	public function getPrefix()
	{
		return $this->prefix;
	}

	/**
	 * Returns all non Multilingual fields.
	 */
	public function getAllNonMultilingualFields()
	{
		return array_merge($this->enums, $this->dates, $this->varchars, $this->texts, $this->longTexts, $this->integers, $this->customSelections);
	}
	
	/**
	 * Returns all Multilingual fields.
	 */
	public function getAllMultilingualFields()
	{
		return array_merge($this->MultilingualVarchars, $this->MultilingualTexts, $this->MultilingualLongTexts, $this->getImageCaptions(), $this->getFileCaptions(), $this->getMultilingualFilesCaptions());
	}	
	
	/**
	 * Returns all simple fields.
	 */
	public function getSimpleFields()
	{
		$simpleFields = array_merge($this->dates, $this->varchars, $this->enums, $this->customSelections, $this->customFields, $this->integers, $this->texts, $this->MultilingualVarchars, $this->MultilingualTexts);
		// Treat Meta Fields seperately
		for ($i=0; $i<count($simpleFields); $i++)
		{
			if (in_array($simpleFields[$i], $this->getMetaFields()))
			{
				unset($simpleFields[$i]);
			}
		}
		return $simpleFields; 
	}

    /**
     * Returns Relations Fields.
     */
    public function getRelationsFields()
    {
        $relationsFields = array();
        foreach ($this->getRelations() as $relation) {
            $relationsFields[] = $relation->inputFieldName;
        }
        return $relationsFields;
    }
	
	/**
	 * Returns all advanced fields.
	 */
	public function getAdvancedFields()
	{
		return array_merge($this->longTexts, $this->MultilingualLongTexts);
	}
	
	/**
	 * Returns all meta fields.
	 */
	public function getMetaFields()
	{
		$metaFields = array();
		
		if (isset($this->metaTitle)) 
		{
			array_push($metaFields, $this->metaTitle);
		}
		if (isset($this->metaDescription)) 
		{
			array_push($metaFields, $this->metaDescription);
		}
		if (isset($this->metaKeywords)) 
		{
			array_push($metaFields, $this->metaKeywords);
		}

		return $metaFields;
	}
	
	/**
	 * Returns all different types of files.
	 */ 
	public function getAllFileFields()
	{
		return array_merge($this->images, $this->files, $this->MultilingualFiles);
	}

    /**
     * Returns all fields.
     */
    public function getAllFields()
    {
        return array_merge($this->getSimpleFields(), $this->getAdvancedFields(), $this->getAllFileFields(), $this->getRelations());
    }

    /**
     * Returns all action managers associated with this table.
     */
    public function getActionManagers()
    {
        return $this->actionManager;
    }

    public function getInputFilter()
    {
        if (!$this->inputFilter) {
            $inputFilter = new InputFilter();

            /*THis is because ZF2 has select input as required by default*/
            foreach ($this->getRelations() as $relation) {
                $inputFilter->add(array(
                    'name' => $relation->inputFieldName,
                    'required' => false,
                ));
                echo $relation->inputFieldName;
            }

            $this->inputFilter = $inputFilter;
        }
        return $this->inputFilter;
    }

    /**
     * Returns the form object.
     */
    public function getForm()
    {
        if (empty($this->tableFormManager)) {
            $this->tableFormManager = $this->getDefaultForm();
        }
        return $this->tableFormManager;
    }

    /**
     * Instantiates the default formmanager.
     */
    public function getDefaultForm()
    {
        $this->tableFormManager = new FormManager($this->getFormObject());
        if (count($this->getSimpleFields()) > 0 ) {
            $this->tableFormManager->addTab('Tab1', array_merge($this->getSimpleFields(), $this->getRelationsFields(), array('id')));
        }
        if (count($this->getAdvancedFields()) > 0 ) {
            $this->tableFormManager->addTab('Tab2', $this->getAdvancedFields());
        }
        if (count($this->getAllFileFields()) > 0 ) {
            $this->tableFormManager->addTab('Tab3', $this->getAllFileFields());
        }
        return $this->tableFormManager;
    }

    /**
     * Instantiates the Zend Form Object.
     */
    public function getFormObject()
    {
        $form = new Form($this->getTableName());
        $form->add(array(
            'type' => 'hidden',
            'name' => 'id',
        ));

        foreach ($this->getAllFields() as $field) {

            $type          = 'Zend\Form\Element\Text';
            $attributes    = array();
            $value_options = array();
            $name          = '';
            $label         = '';

            if (in_array($field, array_merge($this->getIntegers(), $this->getVarchars(), $this->getMultilingualVarchars(), $this->getImageCaptions(), $this->getFileCaptions(), $this->getMultilingualFilesCaptions())) ) {

                $type       = 'Zend\Form\Element\Text';
                $attributes = array('class' => 'form-control');
                $name       = $field;
                $label      = $field;

            } elseif (in_array($field, array_merge($this->getTexts(), $this->getMultilingualTexts()))) {

                $type       = 'Zend\Form\Element\Textarea';
                $attributes = array('class' => 'form-control');
                $name       = $field;
                $label      = $field;

            } elseif (in_array($field, array_merge($this->getLongTexts(), $this->getMultilingualLongTexts()))) {

                $type       = 'Zend\Form\Element\Textarea';
                $attributes = array('class' => 'tinyMce');
                $name       = $field;
                $label      = $field;

            } elseif (in_array($field, $this->getEnums())) {

                $type       = 'Zend\Form\Element\Radio';
                $attributes = array('class' => 'switch','value' => '0');
                $value_options = array('0' => 'No', '1' => 'Yes');
                $name       = $field;
                $label      = $field;

            } elseif (in_array($field, array_merge($this->getImages(), $this->getFiles(), $this->getMultilingualFiles()))) {

                $type       = 'Zend\Form\Element\File';
                $attributes = array('class' => 'form-control');
                $name       = $field;
                $label      = $field;

            } elseif (in_array($field , $this->getRelations())) {
                $type       = 'Zend\Form\Element\Select';
                $attributes = array('class' => 'form-control');

                if ($field->getRelationType() == 'oneToMany') {
                    $attributes['multiple'] = 'multiple';
                }

                $name       = $field->inputFieldName;
                $label      = $field->activeModel->getTableName();

                foreach ($field->activeModel->getListing() as $listingItem) {
                    $value_options[$listingItem->id] = $listingItem->{$field->getRelatedSelectDisplayFields()};
                }
            }

            if (in_array($field, $this->getAllMultilingualFields())) {
                foreach ($this->controlPanel->getSiteLanguages() as $languageId => $language) {
                    $form->add(array(
                        'type' => $type,
                        'name' => $name . '[' . $languageId . ']',
                        'options' => array(
                            'label' => $label,
                            'value_options' => $value_options,
                            'empty_option' => 'Please choose '.$label
                        ),
                        'attributes' => array_merge($attributes,array('placeholder' => $name)),
                    ));
                }
            } else {
                $form->add(array(
                    'type' => $type,
                    'name' => $name,
                    'options' => array(
                        'label' => $label,
                        'value_options' => $value_options,
                        'empty_option' => 'Please choose '.$label
                    ),
                    'attributes' => array_merge($attributes,array('placeholder' => $name)),
                ));
            }
        }
        return $form;
    }

    /**
     * Returns whether the model is multilingual (has Description table).
     */
    public function isMultiLingual()
    {
        if (count($this->getAllMultilingualFields()) > 0 ) {
            return true;
        }
        return false;
    }

    /**
     * Returns whether to follow relations.
     */
    public function followRelations()
    {
        return $this->followRelations;
    }
	

	////////////////////////////////////////////////////////////////
	// Setter Methods
	////////////////////////////////////////////////////////////////
	
	/**
	 * Sets table name.
	 */
	public function setTableName($tableName)
	{
		$this->tableName = $tableName;
	}
	
	/**
	 * Sets table description name.
	 */
	public function setTableDescriptionName($tableDescriptionName)
	{
		$this->tableDescriptionName = $tableDescriptionName;
	}

    /**
     * Sets whether to follow the models relations
     */
    public function setFollowRelations($followRelations)
    {
        $this->followRelations = $followRelations;
    }

	/**
	 * Sets LanguageID.
	 */
	public function setLanguageID($languageID)
	{
		$this->languageID = $languageID;
	}
	
	/**
	 * Sets name field.
	 */
	public function setListingFields($listingFields)
	{
		$this->listingFields = $listingFields;
	}

	/**
	 * Sets enums.
	 */
	public function setEnums($enums)
	{
		$this->enums = $enums;
	}

	/**
	 * Sets dates.
	 */
	public function setDates($dates)
	{
		$this->dates = $dates;
	}

	/**
	 * Sets images.
	 */
	public function setImages($images, $useCaption = false)
	{
		$this->images = $images;
		$this->useImagesCaptions = $useCaption;
	}

	/**
	 * Sets files.
	 */
	public function setFiles($files, $useCaption = false)
	{
		$this->files = $files;
		$this->useFileCaptions = $useCaption;
	}
	
	/**
	 * Sets Varchars.
	 */
	public function setVarchars($varchars)
	{
		$this->varchars = $varchars;
	}

	/**
	 * Sets Texts.
	 */
	public function setTexts($texts)
	{
		$this->texts = $texts;
	}

	/**
	 * Sets Long Texts.
	 */
	public function setLongTexts($longTexts)
	{
		$this->longTexts = $longTexts;
	}

	/**
	 * Sets Integers.
	 */
	public function setIntegers($integers)
	{
		$this->integers = $integers;
	}

	/**
	 * Sets varchars, which support Multilingual.
	 */
	public function setMultilingualVarchars($MultilingualVarchars)
	{
        $this->MultilingualVarchars = $MultilingualVarchars;
		if (isset($this->metaTitle) && !in_array($this->metaTitle, $MultilingualVarchars))
		{
			array_push($this->MultilingualVarchars, $this->metaTitle);	
		}
	}

	/**
	 * Sets longTexts, which support Multilingual.
	 */
	public function setMultilingualTexts($MultilingualTexts)
	{
        $this->MultilingualTexts = $MultilingualTexts;
		if (isset($this->metaDescription) && !in_array($this->metaDescription, $MultilingualTexts))
		{
			array_push($this->MultilingualTexts, $this->metaDescription);	
		}
		if (isset($this->metaKeywords) && !in_array($this->metaKeywords, $MultilingualTexts))
		{
			array_push($this->MultilingualTexts, $this->metaKeywords);	
		}
	}

	/**
	 * Sets longTexts, which support Multilingual.
	 */
	public function setMultilingualLongTexts($MultilingualLongTexts)
	{
        $this->MultilingualLongTexts = $MultilingualLongTexts;
	}
	
	/**
	 * Sets the meta title, which is treated as an immutable field, i.e., 
	 * once initialized it cannot be altered.
	 */
	public function setMetaTitle($metaTitle)
	{
		if (isset($this->metaTitle))
		{
			return;
		}
		$this->metaTitle = $metaTitle;
		array_push($this->MultilingualVarchars, $metaTitle);
	}
	
	/**
	 * Sets the meta keywords, which is treated as an immutable field, i.e., 
	 * once initialized it cannot be altered.
	 */
	public function setMetaKeywords($metaKeywords)
	{
		if (isset($this->metaKeywords))
		{
			return;
		}
		$this->metaKeywords = $metaKeywords;
		array_push($this->MultilingualTexts, $metaKeywords);
	}
	
	/**
	 * Sets the meta description, which is treated as an immutable field, i.e., 
	 * once initialized it cannot be altered.
	 */
	public function setMetaDescription($metaDescription)
	{
		if (isset($this->metaDescription))
		{
			return;
		}
		$this->metaDescription = $metaDescription;
		array_push($this->MultilingualTexts, $metaDescription);
	}

	/**
	 * Sets files, which support Multilingual.
	 */
	public function setMultilingualFiles($MultilingualFiles, $useCaption = false)
	{
        $this->MultilingualFiles = $MultilingualFiles;
		$this->useMultilingualFilesCaptions = $useCaption;
	}

	/**
	 * Sets required fields.
	 */
	public function setRequiredFields($requiredFields)
	{
		$this->requiredFields = $requiredFields;
	}

	/**
	 * Sets required Multilingual fields.
	 */
	public function setMultilingualRequiredFields($requiredMultilingualFields)
	{
        $this->requiredMultilingualFields = $requiredMultilingualFields;
	}

	/**
	 * Sets Relation.
	 */
	public function setRelations(Array $relations)
	{

        foreach ($relations as $relation) {
            if ($this->followRelations()) {
                $relationModelPath = 'Administration\\Model\\' . $relation->getRelatedModel();
                $relationModel = new $relationModelPath($this->adapter, $this->controlPanel, false);
                if ($relationModel->getPrefix() == '') {
                    throw new Exception\InvalidArgumentException('Please set the prefix in the ' . $relationModel->getTableName() . ' model!');
                }

                $relation->inputFieldName = $relationModel->getPrefix().'id';
                $relation->activeModel    = $relationModel;
            }
            $this->relations[]        = $relation;
        }
	}
	
	/**
	 * Sets Custom Fields.
	 */
	public function setCustomFields($customFields)
	{
		$this->customFields = $customFields;
	}
	
	/**
	 * Sets Custom Selections.
	 */
	public function setCustomSelections($customSelections)
	{
		$this->customSelections = $customSelections;
	}

	/**
	 * Sets prefix.
	 */
	public function setPrefix($prefix)
	{
		$this->prefix = $prefix;
	}

    /**
     * Sets listing switches.
     */
    public function setListingSwitches($listingSwitches)
    {
        $this->listingSwitches = $listingSwitches;
    }

    /**
     * For Compatibility.
     */
    public function setInputFilter(InputFilterInterface $inputFilter)
    {
        throw new \Exception("Not used");
    }
	
	/**
	 * Adds an action manager associated with this table.
	 */
	public function addActionManager($actionManager)
	{
		array_push($this->actionManager, $actionManager);
	}
}