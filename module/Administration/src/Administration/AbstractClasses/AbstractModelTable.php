<?php
namespace Administration\AbstractClasses;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Adapter\Adapter;
use Zend\Db\TableGateway\Exception;
use Zend\Db\Sql\Select;


class AbstractModelTable
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
            $this->adapter->query('CREATE TABLE IF NOT EXISTS `'.$this->getTableDescriptionName().'` (`'.$this->getLanguageID().'` int(11) DEFAULT NULL, `'.$this->getPrefix().'id` int(11) DEFAULT NULL, PRIMARY KEY (`'.$this->getLanguageID().'`,`'.$this->getPrefix().'id`))', Adapter::QUERY_MODE_EXECUTE);
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
                $this->adapter->query("ALTER TABLE " . $tableToAddTheColumn . " ADD " . '`' . $fieldsArray[$i] . '`' . $fieldType .";", Adapter::QUERY_MODE_EXECUTE);
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

    public function getListing($itemsPerPage = 'all', $page = null, $order = null, $orderDirection = null, $filters = null)
    {
        if (is_array($this->getListingFields()) && count($this->getListingFields()) > 0) {

            if ($this->isMultiLingual()) {
                $statement = $this->sql->select($this->getTableName())->join(array( 'dc' => $this->getTableDescriptionName()),'dc.' . $this->getPrefix() . 'id = '.$this->getTableName().'.id', Select::SQL_STAR , Select::JOIN_LEFT);
            } else {
                $statement = $this->sql->select($this->getTableName());
            }

            $offset = 0;
            if ($page != null ) {
                if (is_int((int)$page) && (int)$page > 0) {
                    $offset = $itemsPerPage * ($page - 1);
                } else {
                    throw new Exception\InvalidArgumentException('Page must be an integer > 0!');
                }
            }

            if ($order != null) {
                $orderBy = $order;
                if ($orderDirection != null) {
                    $orderBy .= ' '.$orderDirection;
                }
                $statement->order($orderBy);
            }
            if ($itemsPerPage != 'all') {
                $statement->limit($itemsPerPage)->offset($offset);
            }
            $selectString = $this->sql->getSqlStringForSqlObject($statement);
            $results = $this->adapter->query($selectString, Adapter::QUERY_MODE_EXECUTE);
            return $results;
        } else {
            throw new Exception\InvalidArgumentException('List of fields for the listing must be an Array!');
        }
    }

    public function getItemById($id)
    {
        if ($this->isMultiLingual()) {
            $statement = $this->sql->select($this->getTableName())->join(array( 'dc' => $this->getTableDescriptionName()),'dc.' . $this->getPrefix() . 'id = '.$this->getTableName().'.id', array('*') , Select::JOIN_LEFT);
        } else {
            $statement = $this->sql->select($this->getTableName());
        }
        $statement->where(array($this->getTableName().'.id' => $id));
        $selectString = $this->sql->getSqlStringForSqlObject($statement);
        echo $selectString;
        exit;
        $results = $this->adapter->query($selectString, Adapter::QUERY_MODE_EXECUTE)->current();
        return $results;
    }

    public function save($data)
    {
        $queryTableData            = array();
        $queryTableDescriptionData = array();
        $queryRelationsData        = array();
        foreach ($data as $fieldName => $fieldValue) {
            if (in_array( $fieldName, $this->getAllNonMultilingualFields())) {
                $queryTableData[$fieldName] = $fieldValue;
            } elseif ($this->isMultiLingual() && in_array( $fieldName, $this->getAllMultilingualFields())) {
//                $languages = array(array('id'=>'1'),array('id'=>'2'));
//                foreach ($this->getAllMultilingualFields() as $multilingualField) {
//                    foreach ($languages as $language) {
//                        if ($multilingualField . '[' . $language['id'] . ']' == $fieldName) {
//                            $queryTableDescriptionData[$language['id']][$multilingualField] = $fieldValue;
//                        }
//                    }
//                }
            } else {
                foreach ($this->getRelations() as $relation) {
                    if ( $relation->inputFieldName ==  $fieldName && $relation->hasLookupColumn()) {
                        $queryTableData[$fieldName] = $fieldValue;
                    }
                }
            }
        }
        //Prepare Main Table Query
        var_dump($data);
        exit;
        if (isset($data['id']) && !empty($data['id'])) {
            $tableStatement = $this->sql->update($this->getTableName());
            $tableStatement->set($queryTableData);
            $tableStatement->where(array('id' => $data['id']));
        } else {
            $tableStatement = $this->sql->insert($this->getTableName());
            $tableStatement->values($queryTableData);
        }

        //Prepare Description Table Query
//        if ($this->isMultiLingual() && isset($data['id']) && !empty($data['id']) ) {
//            foreach ($languages as $language) {
//                $statement = $this->sql->select($this->getTableDescriptionName())->columns(array('num' => new \Zend\Db\Sql\Expression('COUNT(*)')))->where(array($this->getPrefix().'_id' => $data['id'], $this->getLanguageID() => $language['id']));
//                $sqlString = $this->sql->getSqlStringForSqlObject($statement);
//                $result    = $this->adapter->query($sqlString, Adapter::QUERY_MODE_EXECUTE)->current();
//                var_dump($result);
//            }
//            exit;
//
//        }
        $sqlString = $this->sql->getSqlStringForSqlObject($tableStatement);
        $result    = $this->adapter->query($sqlString, Adapter::QUERY_MODE_EXECUTE);

    }
}
