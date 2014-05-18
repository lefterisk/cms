<?php
namespace Administration\Helper\Model;

use Zend\Db\TableGateway\Exception;
use Zend\Form\Element;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

class TableHandler implements InputFilterAwareInterface
{
	//Table properties
    private $modelPath = 'Administration\\Model\\';
    private $tableName;
	private $tableDescriptionName;
    private $languageID = 'languageId';
    private $followRelations = true;
    private $isStandAlonePage = false;
    private $maximumTreeDepth = 0;
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

	/**
	 * Instantiates a new TableHandler object
	 */
	public function __construct($tableName)
	{
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
     * Returns parent lookup table name, which stores manyToMany relation to the model itself.
     */
    public function getParentLookupTableName()
    {
        return $this->getTableName() . 'ToParent';
    }
	
	/**
	 * Returns languageID.
	 */
	public function getLanguageID()
	{
		return $this->languageID;
	}

    /**
     * Returns published state field name.
     */
    public function getPublishedField()
    {
        return $this->getPrefix() . 'status';
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
        if (!in_array($this->getPublishedField(), $this->listingSwitches)) {
            $this->listingSwitches[] = $this->getPublishedField();
        }
        return $this->listingSwitches;
    }

	/**
	 * Returns enums.
	 */
	public function getEnums()
	{
        if (!in_array($this->getPublishedField(), $this->enums)) {
            $this->enums[] = $this->getPublishedField();
        }
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
        if ($this->isStandAlonePage && !in_array($this->getPrefix() . 'meta_title', $this->MultilingualVarchars))
        {
            array_push($this->MultilingualVarchars, $this->getPrefix() . 'meta_title');
        }
        if ($this->isStandAlonePage && !in_array($this->getPrefix() . 'meta_slug', $this->MultilingualVarchars))
        {
            array_push($this->MultilingualVarchars, $this->getPrefix() . 'meta_slug');
        }
        return $this->MultilingualVarchars;
	}

	/**
	 * Returns texts, which support Multilingual.
	 */
	public function getMultilingualTexts()
	{
        if ($this->isStandAlonePage && !in_array($this->getPrefix() . 'meta_description', $this->MultilingualTexts))
        {
            array_push($this->MultilingualTexts, $this->getPrefix() . 'meta_description');
        }
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
		return array_merge(
            $this->enums,
            $this->dates,
            $this->varchars,
            $this->texts,
            $this->longTexts,
            $this->integers,
            $this->customSelections,
            $this->images,
            $this->files
        );
	}
	
	/**
	 * Returns all Multilingual fields.
	 */
	public function getAllMultilingualFields()
	{
		return array_merge(
            $this->MultilingualVarchars,
            $this->MultilingualTexts,
            $this->MultilingualLongTexts,
            $this->MultilingualFiles,
            $this->getImageCaptions(),
            $this->getFileCaptions(),
            $this->getMultilingualFilesCaptions()
        );
	}
	
	/**
	 * Returns all simple fields.
	 */
	public function getSimpleFields()
	{
		$simpleFields = array_merge(
            $this->dates,
            $this->varchars,
            $this->enums,
            $this->customFields,
            $this->integers,
            $this->texts,
            $this->MultilingualVarchars,
            $this->MultilingualTexts
        );
		return $simpleFields;
	}

    /**
     * Returns Relations Field names
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
     * Returns Custom Selections Field names.
     */
    public function getCustomSelectionFields()
    {
        $customSelectionFields = array();
        foreach ($this->getCustomSelections() as $select) {
            $customSelectionFields[] = $select->getFieldName();
        }
        return $customSelectionFields;
    }
	
	/**
	 * Returns all advanced fields.
	 */
	public function getAdvancedFields()
	{
		return array_merge($this->longTexts, $this->MultilingualLongTexts);
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
        return array_merge(
            $this->getSimpleFields(),
            $this->getAdvancedFields(),
            $this->getAllFileFields(),
            $this->getRelations(),
            $this->getCustomSelections()
        );
    }

    public function getMaximumTreeDepth()
    {
        return $this->maximumTreeDepth;
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
            }
            foreach ($this->getCustomSelections() as $selection) {
                $inputFilter->add(array(
                    'name' => $selection->getFieldName(),
                    'required' => false,
                ));
            }
            foreach ($this->getDates() as $date) {
                $inputFilter->add(array(
                    'name' => $date,
                    'required' => true,
                    'validators' => array(
                        array(
                            'name'  => 'Zend\Validator\Date',
                            'options'  => array(
                                'format' => 'Y-m-d H:i:s'
                            )
                        ),
                    )
                ));
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

    public function getDefaultFieldNames()
    {
        $defaultFields = array('id');
        if ($this->getMaximumTreeDepth() > 0 ) {
            $defaultFields[] = 'parent_' . $this->getPrefix() . 'id';
        }
        return $defaultFields;
    }

    /**
     * Instantiates the default form structure.
     * Tab name => array of fields (names) under that tab
     */
    public function getDefaultForm()
    {
        $formStructure = array();
        $generalTabFields = array_diff(
            array_merge(
                $this->getSimpleFields(),
                $this->getRelationsFields(),
                $this->getCustomSelectionFields(),
                $this->getDefaultFieldNames()
            ),
            $this->getMetaFields()
        );
        if (count($generalTabFields) > 0) {
            $formStructure['Tab1'] = $generalTabFields;
        }
        if (count($this->getAdvancedFields()) > 0) {
            $formStructure['Tab2'] = $this->getAdvancedFields();
        }
        if (count($this->getAllFileFields()) > 0) {
            $formStructure['Tab3'] = $this->getAllFileFields();
        }
        return $formStructure;
    }

    /**
     * Returns the auto-generated meta related fields
     * returns array;
     */
    public function getMetaFields()
    {
        return array(
            $this->getPrefix() . 'meta_title',
            $this->getPrefix() . 'meta_slug',
            $this->getPrefix() . 'meta_description'
        );
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
     * Returns if model instance is stand alone Page
     * Returns Bool
     */
    public function isStandAlonePage()
    {
        return $this->isStandAlonePage;
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
	}

	/**
	 * Sets longTexts, which support Multilingual.
	 */
	public function setMultilingualTexts($MultilingualTexts)
	{
        $this->MultilingualTexts = $MultilingualTexts;
	}

	/**
	 * Sets longTexts, which support Multilingual.
	 */
	public function setMultilingualLongTexts($MultilingualLongTexts)
	{
        $this->MultilingualLongTexts = $MultilingualLongTexts;
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
                $modelName     = $this->modelPath . ucfirst($relation->getRelatedModel());
                $relationModel = new $modelName(false,false);

                if ($relationModel->getPrefix() == '') {
                    throw new Exception\InvalidArgumentException('Please set the prefix in the ' . $relationModel->getTableName() . ' model!');
                }

                $relation->inputFieldName = $relationModel->getPrefix().'id';
                $relation->activeModel    = $relationModel;
            }
            $this->relations[] = $relation;
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

    public function setMaximumTreeDepth($maximumTreeDepth)
    {
        if (is_int($maximumTreeDepth)) {
            $this->maximumTreeDepth = $maximumTreeDepth;
        } else {
            throw new Exception\InvalidArgumentException('Maximum tree depth must be an Integer in the ' . $this->getTableName() . ' model!');
        }
    }

	/**
	 * Sets prefix.
	 */
	public function setPrefix($prefix)
	{
		$this->prefix = $prefix;
	}

    /**
     * Sets if the model instance is a standalone page (has Metadata)
     */
    public function setIsStandAlonePage($isStandAlonePage)
    {
        $this->isStandAlonePage = $isStandAlonePage;
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

    /**
     * Prepares the POST array for save (for multilingual array-fields).
     */
    public function preparePostData($post)
    {
        $returnArray = array();
        foreach ($post as $field => $values) {
            if (in_array($field, $this->getAllMultilingualFields()) && is_array($values)) {
                foreach ($values as $languageId => $fieldValue) {
                    $returnArray[$field . '[' . $languageId . ']'] = $fieldValue;
                }
            } else {
                $returnArray[$field] = $values;
            }
        }
        return $returnArray;
    }

    /*
     * Manipulate populated form (Should be called
     * after form has been bound to an object)
     */
    public function populatedFormHook ($form)
    {
        return $form;
    }

    public function preSaveHook(Array $data)
    {
        return $data;
    }

    public function postSaveHook(Array $data)
    {
        return true;
    }

    public function preDeleteHook($id)
    {
        return true;
    }

    public function postDeleteHook($id)
    {
        return true;
    }
}