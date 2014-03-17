<?php
namespace Administration\AbstractClasses;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Adapter\Adapter;
use Zend\Db\TableGateway\Exception;

class AbstractModelTable extends TableGateway
{
    public function finaliseTable()
    {
        $this->createTablesIfNotExist();
        $this->addFieldsIfDontExist('integers',                 $this->getIntegers());
        $this->addFieldsIfDontExist('enums',                    $this->getEnums());
        $this->addFieldsIfDontExist('dates',                    $this->getDates());
        $this->addFieldsIfDontExist('varchars',                 $this->getVarchars());
        $this->addFieldsIfDontExist('images',                   $this->getImages());
        $this->addFieldsIfDontExist('files',                    $this->getFiles());
        $this->addFieldsIfDontExist('customSelections',         $this->getCustomSelections());
        $this->addFieldsIfDontExist('texts',                    $this->getTexts());
        $this->addFieldsIfDontExist('longTexts',                $this->getLongTexts());
        $this->addFieldsIfDontExist('imageCaptions',            $this->getImageCaptions());
        $this->addFieldsIfDontExist('fileCaptions',             $this->getFileCaptions());
        if ($this->isMultiLingual()) {
            $this->addFieldsIfDontExist('multiLingualFileCaptions', $this->getMultilingualFilesCaptions());
            $this->addFieldsIfDontExist('multiLingualTexts',        $this->getMultilingualTexts());
            $this->addFieldsIfDontExist('multiLingualLongTexts',    $this->getMultilingualLongTexts());
            $this->addFieldsIfDontExist('multiLingualVarchars',     $this->getMultilingualVarchars());
            $this->addFieldsIfDontExist('multiLingualFiles',        $this->getMultilingualFiles());
        }
        if ($this->followRelations()) {
            $this->addJoinRelationsIfNotExist($this->getRelations());
        }
    }

    private function createTablesIfNotExist()
    {
        $this->adapter->query('CREATE TABLE IF NOT EXISTS `'.$this->getTableName().'` (`id` int(11) unsigned NOT NULL AUTO_INCREMENT, PRIMARY KEY (`id`))', Adapter::QUERY_MODE_EXECUTE);
        if ($this->isMultiLingual()) {
            $this->adapter->query('CREATE TABLE IF NOT EXISTS `'.$this->getTableDescriptionName().'` (`id` int(11) unsigned NOT NULL AUTO_INCREMENT, `'.$this->getLanguageID().'` int(11) DEFAULT NULL, `'.$this->getPrefix().'id` int(11) DEFAULT NULL, PRIMARY KEY (`id`))', Adapter::QUERY_MODE_EXECUTE);
        }
    }

    private function addFieldsIfDontExist($type, $fieldsArray)
    {

        $tableToAddTheColumn = '';
        $fieldType           = '';

        switch($type){
            case 'integers':
                $tableToAddTheColumn = $this->getTableName();
                $fieldType = " INT(11) ";
                break;
            case 'enums':
                $tableToAddTheColumn = $this->getTableName();
                $fieldType = " ENUM( '0', '1' ) ";
                break;
            case 'dates':
                $tableToAddTheColumn = $this->getTableName();
                $fieldType = " DATETIME ";
                break;
            case 'varchars':
            case 'images':
            case 'files':
            case 'customSelections':
                $tableToAddTheColumn = $this->getTableName();
                $fieldType = " VARCHAR( 255 ) ";
                break;
            case 'texts':
            case 'longTexts':
                $tableToAddTheColumn = $this->getTableName();
                $fieldType = " TEXT ";
                break;
            case 'multiLingualVarchars':
            case 'multiLingualFiles':
            case 'imageCaptions':
            case 'fileCaptions':
            case 'multiLingualFileCaptions':
                if ($this->isMultiLingual()) {
                    $tableToAddTheColumn = $this->getTableDescriptionName();
                } else {
                    $tableToAddTheColumn = $this->getTableName();
                }
                $fieldType = " VARCHAR( 255 ) ";
                break;
            case 'multiLingualTexts':
            case 'multiLingualLongTexts':
                $tableToAddTheColumn = $this->getTableDescriptionName();
                $fieldType = " TEXT ";
                break;
        }

        //if you have missing or wrong arguments throw exception
        if(is_null($type) || empty($tableToAddTheColumn)){
            throw new Exception\InvalidArgumentException('First Parameter must be a supported field type!');
        }
        if(!is_array($fieldsArray)){
            throw new Exception\InvalidArgumentException('Second Parameter must be an array of fields!');
        }

        for ($i=0; $i<count($fieldsArray); $i++)
        {
            $statement = $this->adapter->createStatement("SHOW COLUMNS FROM " . $tableToAddTheColumn . " LIKE '" . $fieldsArray[$i] . "'" );
            $result    = $statement->execute();
            if ($result->count()==0)
            {
                $this->adapter->query("ALTER TABLE " . $tableToAddTheColumn . " ADD " . $fieldsArray[$i] . $fieldType .";", Adapter::QUERY_MODE_EXECUTE);
            }
        }
    }

    private function addJoinRelationsIfNotExist($relations)
    {
        if (is_array($relations)) {
            foreach ($relations as $relation) {
                $relationModelPath = 'Administration\\Model\\' . $relation->getRelatedModel();
                $relationModel = new $relationModelPath($this->adapter, false);
                if ($relationModel->getPrefix() == '') {
                    throw new Exception\InvalidArgumentException('Please set the prefix in the ' . $relationModel->getTableName() . ' model!');
                }
                if ($this->getPrefix() == '') {
                    throw new Exception\InvalidArgumentException('Please set the prefix in the ' . $this->getTableName() . ' model!');
                }
                if ($relation->hasLookUpTable()) {
                    $this->adapter->query('CREATE TABLE IF NOT EXISTS `' . $this->getTableName() .'To'.$relationModel->getTableName(). '` ( `'.$this->getPrefix().'id` int(11) DEFAULT NULL, `'.$relationModel->getPrefix().'id` int(11) DEFAULT NULL)', Adapter::QUERY_MODE_EXECUTE);
                } elseif ($relation->hasLookupColumn()) {
                    $statement = $this->adapter->createStatement("SHOW COLUMNS FROM " . $this->getTableName() . " LIKE '" . $relationModel->getPrefix() . "id'" );
                    $result    = $statement->execute();
                    if ($result->count()==0)
                    {
                        $this->adapter->query("ALTER TABLE " . $this->getTableName() . " ADD `" . $relationModel->getPrefix() . "id` int(11) DEFAULT NULL;", Adapter::QUERY_MODE_EXECUTE);
                    }
                }
            }
        } else {
            throw new Exception\InvalidArgumentException('Relations must be an array!');
        }
    }

    private function addCustomSelectionFieldsIfNotExist()
    {

    }

    public function getListing()
    {
        if (is_array($this->getListingFields()) && count($this->getListingFields()) > 0) {

            if ($this->isMultiLingual()) {
                $statement = $this->sql->select()->join(array( 'dc' => $this->getTableDescriptionName()),'dc.' . $this->getPrefix() . 'id = '.$this->getTableName().'.id');
            } else {
                $statement = $this->sql->select();
            }
            $selectString = $this->sql->getSqlStringForSqlObject($statement);
            $results = $this->adapter->query($selectString, Adapter::QUERY_MODE_EXECUTE);
            return $results;

        } else {
            throw new Exception\InvalidArgumentException('List of fields for the listing must be an Array!');
        }
    }
}
