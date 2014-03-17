<?php
namespace Administration\AbstractClasses;

use Zend\Db\TableGateway\Exception;


class TableHandler extends AbstractModelTable
{
	//Table properties
	private $tableName;
	private $tableDescriptionName;
    private $languageID = 'languageId';
	private $nameField;
    private $hasMultilingualContent = true;
    private $followRelations;

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
	private $useFilesCaptions = false;
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

    public  $tableGateWay;

	/**
	 * Instantiates a new TableHandle object
	 */
	public function __construct($tableName, $dbAdapter, $followRelations = true)
	{
        parent::__construct($tableName, $dbAdapter);
		$this->setTableName($tableName);
        $this->setTableDescriptionName($tableName.'Description');
        $this->setFollowRelations($followRelations);
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
	public function getNameField()
	{
		return $this->nameField;
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
		if ($this->useFilesCaptions==false)
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
	 * Returns Related Tables.
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
		return array_merge($this->enums, $this->dates, $this->dateTimes, $this->varchars, $this->texts, $this->longTexts, $this->integers, $this->doubles, $this->joinedTables, $this->customSelections);
	}
	
	/**
	 * Returns all Multilingual fields.
	 */
	public function getAllMultilingualFields()
	{
		return array_merge($this->MultilingualVarchars, $this->MultilingualTexts, $this->MultilingualLongTexts, $this->getImagesCaptions(), $this->getFilesCaptions(), $this->getMultilingualFilesCaptions());
	}	
	
	/**
	 * Returns all simple fields.
	 */
	public function getSimpleFields()
	{
		$simpleFields = array_merge($this->dates, $this->dateTimes, $this->varchars, $this->enums, $this->joinedTables, $this->customSelections, $this->customFields, $this->integers, $this->doubles, $this->texts, $this->MultilingualVarchars, $this->MultilingualTexts);
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
     * Returns whether the model is multilingual (has Description table).
     */
    public function isMultiLingual()
    {
        return $this->hasMultilingualContent;
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
     * Sets whether we want a description table.
     */
    public function setIsMultilingual($boolean)
    {
        if (is_bool($boolean)) {
            $this->hasMultilingualContent = $boolean;
        } else {
            throw new Exception\InvalidArgumentException('Parameter must be boolean!');
        }
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
	public function setNameField($nameField)
	{
		$this->nameField = $nameField;
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
		$this->useFilesCaptions = $useCaption;
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
		if (!$this->isMultiLingual()) {
            $this->throwMultilingualException();
        }
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
        if (!$this->isMultiLingual()) {
            $this->throwMultilingualException();
        }
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
        if (!$this->isMultiLingual()) {
            $this->throwMultilingualException();
        }
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
        if (!$this->isMultiLingual()) {
            $this->throwMultilingualException();
        }
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
        if (!$this->isMultiLingual()) {
            $this->throwMultilingualException();
        }
        $this->requiredMultilingualFields = $requiredMultilingualFields;
	}

	/**
	 * Sets joined Tables.
	 */
	public function setRelations($relations)
	{
		$this->relations = $relations;
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
	 * Adds an action manager associated with this table.
	 */
	public function addActionManager($actionManager)
	{
		array_push($this->actionManager, $actionManager);
	}
	
	/**
	 * Returns all action managers associated with this table.
	 */
	public function getActionManagers()
	{
		return $this->actionManager;
	}

    /**
     * Non Multilingual Exception
     */
    protected function throwMultilingualException()
    {
        throw new Exception\InvalidArgumentException('You are trying to setup multilingual variables in a non-multilingual model!');
    }
}