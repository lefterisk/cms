<?php
namespace Administration\AbstractClasses;

class TableHandle 
{
	//Table properties
	private $tableName;
	private $tableDescriptionName;
	private $tableKey;
	private $languageID;
	private $nameField;
	
	//Generic fields which do not support multilanguage.
	private $enums = array();
	private $dates = array();
	private $dateTimes = array();
	private $images = array();
	private $files = array();

	//Fields which do not support multilanguage.
	private $varchars = array();
	private $texts = array();
	private $longTexts = array();
	private $integers = array();
	private $doubles = array();

	//Fields which support multilanguage.
	private $multilanguageVarchars = array();
	private $multilanguageTexts = array();
	private $multilanguageLongTexts = array();
	private $multilanguageFiles = array();

	//Fields which support custom meta tags.
	private $metaTitle;
	private $metaDescription;
	private $metaKeywords;
	
	//Captions
	private $useImagesCaptions = false;
	private $useFilesCaptions = false;
	private $useMultilanguageFilesCaptions = false;	

	//Required fields
	private $requiredFields = array();
	private $requiredMultilanguageFields = array();
	
	//Related Tables
	private $relations = array();

	//Joined Tables.
	private $joinedTables = array();

	//Custom Fields
	private $customFields = array();
	
	//Custom Selection.
	private $customSelections = array();
	
	//Action Managers
	private $actionManager = array();

	//Prefix used for file uploads.
	private $prefix;

	/**
	 * Instantiates a new TableHandle object
	 */
	public function __construct($tableName, $tableDescriptionName, $tableKey, $languageID, $nameField)
	{
		$this->tableName 			= $tableName;
		$this->tableDescriptionName = $tableDescriptionName;
		$this->tableKey 			= $tableKey;
		$this->languageID 			= $languageID;
		$this->nameField 			= $nameField;
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
	 * Returns table description name, which stores multilanguage data.
	 */
	public function getTableDescriptionName()
	{
		return $this->tableDescriptionName;
	}
	
	/**
	 * Returns table key.
	 */
	public function getTableKey()
	{
		return $this->tableKey;
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
	 * Returns datetimes.
	 */
	public function getDateTimes()
	{
		return $this->dateTimes;
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
	public function getImagesCaptions()
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
	public function getFilesCaptions()
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
	 * Returns doubles.
	 */
	public function getDoubles()
	{
		return $this->doubles;
	}

	/**
	 * Returns varchars, which support multilanguage.
	 */
	public function getMultilanguageVarchars()
	{
		return $this->multilanguageVarchars;
	}

	/**
	 * Returns texts, which support multilanguage.
	 */
	public function getMultilanguageTexts()
	{
		return $this->multilanguageTexts;
	}

	/**
	 * Returns longTexts, which support multilanguage.
	 */
	public function getMultilanguageLongTexts()
	{
		return $this->multilanguageLongTexts;
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
	 * Returns files, which support multilanguage.
	 */
	public function getMultilanguageFiles()
	{
		return $this->multilanguageFiles;
	}

	/**
	 * Returns files' captions.
	 */
	public function getMultilanguageFilesCaptions()
	{
		if ($this->useMultilanguageFilesCaptions==false)
		{
			return array();
		}
		else
		{
			$captions = array();
			for ($i=0; $i<count($this->multilanguageFiles); $i++)
			{
				$captions[$i] = $this->multilanguageFiles[$i] . "_caption";
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
	 * Returns required multilanguage fields.
	 */
	public function getMultilanguageRequiredFields()
	{
		return $this->requiredMultilanguageFields;
	}

	/**
	 * Returns Related Tables.
	 */
	public function getRelations()
	{
		return $this->relations;
	}

	/**
	 * Returns joined Tables.
	 */
	public function getJoinedTables()
	{
		return $this->joinedTables;
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
	 * Returns all non multilanguage fields.
	 */
	public function getAllNonMultilanguageFields()
	{
		return array_merge($this->enums, $this->dates, $this->dateTimes, $this->varchars, $this->texts, $this->longTexts, $this->integers, $this->doubles, $this->joinedTables, $this->customSelections);
	}
	
	/**
	 * Returns all multilanguage fields.
	 */
	public function getAllMultilanguageFields()
	{
		return array_merge($this->multilanguageVarchars, $this->multilanguageTexts, $this->multilanguageLongTexts, $this->getImagesCaptions(), $this->getFilesCaptions(), $this->getMultilanguageFilesCaptions());
	}	
	
	/**
	 * Returns all simple fields.
	 */
	public function getSimpleFields()
	{
		$simpleFields = array_merge($this->dates, $this->dateTimes, $this->varchars, $this->enums, $this->joinedTables, $this->customSelections, $this->customFields, $this->integers, $this->doubles, $this->texts, $this->multilanguageVarchars, $this->multilanguageTexts);
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
		return array_merge($this->longTexts, $this->multilanguageLongTexts);
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
		return array_merge($this->images, $this->files, $this->multilanguageFiles);
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
	 * Sets key.
	 */
	public function setTableKey($tableKey)
	{
		$this->tableKey = $tableKey;
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
	 * Sets datetimes.
	 */
	public function setDateTimes($dateTimes)
	{
		$this->dateTimes = $dateTimes;
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
	 * Sets Doubles.
	 */
	public function setDoubles($doubles)
	{
		$this->doubles = $doubles;
	}

	/**
	 * Sets varchars, which support multilanguage.
	 */
	public function setMultilanguageVarchars($multilanguageVarchars)
	{
		$this->multilanguageVarchars = $multilanguageVarchars;
		if (isset($this->metaTitle) && !in_array($this->metaTitle, $multilanguageVarchars))
		{
			array_push($this->multilanguageVarchars, $this->metaTitle);	
		}
	}

	/**
	 * Sets longTexts, which support multilanguage.
	 */
	public function setMultilanguageTexts($multilanguageTexts)
	{
		$this->multilanguageTexts = $multilanguageTexts;
		if (isset($this->metaDescription) && !in_array($this->metaDescription, $multilanguageTexts))
		{
			array_push($this->multilanguageTexts, $this->metaDescription);	
		}
		if (isset($this->metaKeywords) && !in_array($this->metaKeywords, $multilanguageTexts))
		{
			array_push($this->multilanguageTexts, $this->metaKeywords);	
		}
	}

	/**
	 * Sets longTexts, which support multilanguage.
	 */
	public function setMultilanguageLongTexts($multilanguageLongTexts)
	{
		$this->multilanguageLongTexts = $multilanguageLongTexts;
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
		array_push($this->multilanguageVarchars, $metaTitle);
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
		array_push($this->multilanguageTexts, $metaKeywords);
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
		array_push($this->multilanguageTexts, $metaDescription);
	}

	/**
	 * Sets files, which support multilanguage.
	 */
	public function setMultilanguageFiles($multilanguageFiles, $useCaption = false)
	{
		$this->multilanguageFiles = $multilanguageFiles;
		$this->useMultilanguageFilesCaptions = $useCaption;
	}

	/**
	 * Sets required fields.
	 */
	public function setRequiredFields($requiredFields)
	{
		$this->requiredFields = $requiredFields;
	}

	/**
	 * Sets required multilanguage fields.
	 */
	public function setMultilanguageRequiredFields($requiredMultilanguageFields)
	{
		$this->requiredMultilanguageFields = $requiredMultilanguageFields;
	}

	/**
	 * Sets joined Tables.
	 */
	public function setRelations($relations)
	{
		$this->relations = $relations;
	}

	/**
	 * Sets joined Tables.
	 */
	public function setJoinedTables($joinedTables)
	{
		$this->joinedTables = $joinedTables;
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
}