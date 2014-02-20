<?php
namespace Administration\AbstractClasses;

use Zend\Mvc\Controller\AbstractActionController;

class AbstractModel 
{
	protected $dbAdapter;
	protected $modelName;
	protected $tableName;
	protected $primaryKeyField;
	protected $languageForeignKeyField;
	protected $integers;
	protected $booleans;
	protected $varchars;
	protected $shortTexts;
	protected $longTexts;
	protected $files;
	protected $multilanguageVarchars;
	protected $multilanguageShortTexts;
	protected $multilanguageLongTexts;
	protected $multilanguageFiles;

    public function getDbAdapter()
    {
    	return $this->dbAdapter;
    }

    public function setDbAdapter($dbAdapter)
    {
    	$this->dbAdapter = $dbAdapter;
    }

    public function getModelName()
    {
    	return $this->modelName;
    }

    public function setModelName($modelName)
    {
    	$this->modelName = $modelName;
    }

    public function getTableName()
    {
    	return $this->tableName;
    }

    public function setTableName($tableName)
    {
    	$this->tableName = $tableName;
    }

    public function getPrimaryKeyField()
    {
    	return $this->primaryKeyField;
    }

    public function setPrimaryKeyField($primaryKeyField)
    {
    	$this->primaryKeyField = $primaryKeyField;
    }

    public function getLanguageForeignKeyField()
    {
    	return $this->languageForeignKeyField;
    }

    public function setLanguageForeignKeyField($languageForeignKeyField)
    {
    	$this->languageForeignKeyField = $languageForeignKeyField;
    }

    public function getIntegers()
    {
    	return $this->integers;
    }

    public function setIntegers($integers)
    {
    	$this->integers = $integers;
    }

    public function getBooleans()
    {
    	return $this->booleans;
    }

    public function setBooleans($booleans)
    {
    	$this->booleans = $booleans;
    }

    public function getVarchars()
    {
    	return $this->varchars;
    }

    public function setVarchars($varchars)
    {
    	$this->varchars = $varchars;
    }

    public function getShortTexts()
    {
    	return $this->shortTexts;
    }

    public function setShortTexts($shortTexts)
    {
    	$this->shortTexts = $shortTexts;
    }

    public function getLongTexts()
    {
    	return $this->longTexts;
    }

    public function setLongTexts($longTexts)
    {
    	$this->longTexts = $longTexts;
    }

    public function getFiles()
    {
    	return $this->files;
    }

    public function setFiles($files)
    {
    	$this->files = $files;
    }

    public function getMultilanguageVarchars()
    {
    	return $this->multilanguageVarchars;
    }

    public function setMultilanguageVarchars($multilanguageVarchars)
    {
    	$this->multilanguageVarchars = $multilanguageVarchars;
    }

    public function getMultilanguageShortTexts()
    {
    	return $this->multilanguageShortTexts;
    }

    public function setMultilanguageShortTexts($multilanguageShortTexts)
    {
    	$this->multilanguageShortTexts = $multilanguageShortTexts;
    }

    public function getMultilanguageLongTexts()
    {
    	return $this->multilanguageLongTexts;
    }

    public function setMultilanguageLongTexts($multilanguageLongTexts)
    {
    	$this->multilanguageLongTexts = $multilanguageLongTexts;
    }

    public function getMultilanguageFiles()
    {
    	return $this->multilanguageFiles;
    }

    public function setMultilanguageFiles($multilanguageFiles)
    {
    	$this->multilanguageFiles = $multilanguageFiles;
    }

    protected function checkDbTable()
    {
    	//var_dump($this);
    }
}