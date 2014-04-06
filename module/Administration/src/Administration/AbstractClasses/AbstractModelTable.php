<?php
namespace Administration\AbstractClasses;

use Zend\Db\Adapter\Adapter;
use Zend\Db\TableGateway\Exception;
use Zend\Db\Sql\Select;


class AbstractModelTable
{
    protected $lastInsertValue;

    public function finaliseTable()
    {
        $this->createTablesIfNotExist();
        $this->addFieldsCollumnsIfDontExist('integers',                 $this->getIntegers());
        $this->addFieldsCollumnsIfDontExist('enums',                    $this->getEnums());
        $this->addFieldsCollumnsIfDontExist('dates',                    $this->getDates());
        $this->addFieldsCollumnsIfDontExist('varchars',                 $this->getVarchars());
        $this->addFieldsCollumnsIfDontExist('images',                   $this->getImages());
        $this->addFieldsCollumnsIfDontExist('files',                    $this->getFiles());
        $this->addFieldsCollumnsIfDontExist('customSelections',         $this->getCustomSelections());
        $this->addFieldsCollumnsIfDontExist('texts',                    $this->getTexts());
        $this->addFieldsCollumnsIfDontExist('longTexts',                $this->getLongTexts());
        $this->addFieldsCollumnsIfDontExist('imageCaptions',            $this->getImageCaptions());
        $this->addFieldsCollumnsIfDontExist('fileCaptions',             $this->getFileCaptions());
        if ($this->isMultiLingual()) {
            $this->addFieldsCollumnsIfDontExist('multiLingualFileCaptions', $this->getMultilingualFilesCaptions());
            $this->addFieldsCollumnsIfDontExist('multiLingualTexts',        $this->getMultilingualTexts());
            $this->addFieldsCollumnsIfDontExist('multiLingualLongTexts',    $this->getMultilingualLongTexts());
            $this->addFieldsCollumnsIfDontExist('multiLingualVarchars',     $this->getMultilingualVarchars());
            $this->addFieldsCollumnsIfDontExist('multiLingualFiles',        $this->getMultilingualFiles());
        }
        if ($this->followRelations()) {
            $this->addJoinRelationsIfNotExist($this->getRelations());
        }
    }

    private function createTablesIfNotExist()
    {
        $this->adapter->query('CREATE TABLE IF NOT EXISTS `' . $this->getTableName() . '` (`id` int(11) unsigned NOT NULL AUTO_INCREMENT, PRIMARY KEY (`id`))', Adapter::QUERY_MODE_EXECUTE);
        if ($this->isMultiLingual()) {
            $results = $this->adapter->query("SHOW TABLES LIKE '" . $this->getTableDescriptionName() . "'" , Adapter::QUERY_MODE_EXECUTE);
            if ($results->count() <= 0) {

                $this->adapter->query('CREATE TABLE IF NOT EXISTS `'.$this->getTableDescriptionName().'` (`'.$this->getLanguageID().'` int(11) DEFAULT NULL, `'.$this->getPrefix().'id` int(11) DEFAULT NULL, PRIMARY KEY (`'.$this->getLanguageID().'`,`'.$this->getPrefix().'id`))', Adapter::QUERY_MODE_EXECUTE);

                //Make sure every entry on main table has a corresponding one in description
                $statement    = $this->sql->select($this->getTableName());
                $selectString = $this->sql->getSqlStringForSqlObject($statement);
                $results      = $this->adapter->query($selectString, Adapter::QUERY_MODE_EXECUTE);
                if ($results->count() > 0) {
                    foreach ($results as $entry) {
                        foreach ($this->controlPanel->getSiteLanguages() as $languageId => $language) {
                            $insertStatement = $this->sql->insert($this->getTableDescriptionName());
                            $insertStatement->values(array($this->getLanguageID() => $languageId, $this->getPrefix().'id' => $entry['id'] ));
                            $insertStatementString = $this->sql->getSqlStringForSqlObject($insertStatement);
                            $this->adapter->query($insertStatementString, Adapter::QUERY_MODE_EXECUTE);
                        }
                    }
                }
            }
        }
    }

    private function addFieldsCollumnsIfDontExist($type, $fieldsArray)
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
                $fieldType = " ENUM( '0', '1' ) NOT NULL ";
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

                if ($relation->activeModel->getPrefix() == '') {
                    throw new Exception\InvalidArgumentException('Please set the prefix in the ' . $relation->activeModel->getTableName() . ' model!');
                }
                if ($this->getPrefix() == '') {
                    throw new Exception\InvalidArgumentException('Please set the prefix in the ' . $this->getTableName() . ' model!');
                }
                if ($relation->hasLookUpTable()) {
                    $this->adapter->query('CREATE TABLE IF NOT EXISTS `' . $relation->getLookUpTableName() . '` ( `'.$this->getPrefix().'id` int(11) DEFAULT NULL, `'.$relation->activeModel->getPrefix().'id` int(11) DEFAULT NULL)', Adapter::QUERY_MODE_EXECUTE);
                } elseif ($relation->hasLookupColumn()) {
                    $statement = $this->adapter->createStatement("SHOW COLUMNS FROM " . $this->getTableName() . " LIKE '" . $relation->activeModel->getPrefix() . "id'" );
                    $result    = $statement->execute();
                    if ($result->count()==0)
                    {
                        $this->adapter->query("ALTER TABLE " . $this->getTableName() . " ADD `" . $relation->activeModel->getPrefix() . "id` int(11) DEFAULT 0;", Adapter::QUERY_MODE_EXECUTE);
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
                $statement = $this->sql->select($this->getTableName())->join(array( 'dc' => $this->getTableDescriptionName()),'dc.' . $this->getPrefix() . 'id = ' . $this->getTableName() . '.id' , Select::SQL_STAR , Select::JOIN_LEFT)->where(array($this->getLanguageID() => $this->controlPanel->getDefaultSiteLanguageId()));
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
        //Data from Entity Table
        $statement    = $this->sql->select($this->getTableName())->where(array($this->getTableName().'.id' => $id));
        $selectString = $this->sql->getSqlStringForSqlObject($statement);
        $results      = $this->adapter->query($selectString, Adapter::QUERY_MODE_EXECUTE)->current();

        //Attach Data from Description Table
        if ($this->isMultiLingual()) {

            $descriptionStatement    = $this->sql->select($this->getTableDescriptionName())->where(array($this->getPrefix().'id' => $id));
            $descriptionSelectString = $this->sql->getSqlStringForSqlObject($descriptionStatement);
            $descriptionResults      = $this->adapter->query($descriptionSelectString, Adapter::QUERY_MODE_EXECUTE);

            if ($descriptionResults->count() > 0) {
                foreach ($descriptionResults as $descriptionEntry) {
                    foreach ($this->getAllMultilingualFields() as $multilingualField) {
                        $results[$multilingualField . '[' . $descriptionEntry[$this->getLanguageID()] . ']'] = $descriptionEntry[$multilingualField];
                    }
                }
            }
        }

        //Attach Data from Relations Look Up Tables
        if (count($this->getRelations()) > 0) {
            foreach ($this->getRelations() as $relation) {
                if ($relation->hasLookUpTable()) {
                    //If relation is manyToMany
                    $lookUpTableStatement    = $this->sql->select($relation->getLookUpTableName())->where(array($this->getPrefix().'id' => $id));
                    $lookUpTableSelectString = $this->sql->getSqlStringForSqlObject($lookUpTableStatement);
                    $lookUpTableResults      = $this->adapter->query($lookUpTableSelectString, Adapter::QUERY_MODE_EXECUTE);

                    if ($lookUpTableResults->count() > 0) {
                        foreach ($lookUpTableResults as $lookupTableResult) {
                            $results[$relation->inputFieldName][] =  $lookupTableResult[$relation->activeModel->getPrefix() . 'id'];
                        }
                    }
                } elseif ($relation->getRelationType() == 'oneToMany') {
                    //If relation is oneToMany
                    $relationStatement    = $this->sql->select($relation->activeModel->getTableName())->where(array($this->getPrefix().'id' => $id));
                    $relationSelectString = $this->sql->getSqlStringForSqlObject($relationStatement);
                    $relationResults      = $this->adapter->query($relationSelectString, Adapter::QUERY_MODE_EXECUTE);

                    if ($relationResults->count() > 0) {
                        foreach ($relationResults as $relationResult) {
                            $results[$relation->inputFieldName][] =  $relationResult['id'];
                        }
                    }
                }
            }
        }
        return $results;
    }

    public function save($data)
    {
        $queryTableData            = array();
        $queryTableDescriptionData = array();
        $queryM2MRelationsData     = array();
        $queryO2MRelationsData     = array();

        foreach ($data as $fieldName => $fieldValue) {
            if (in_array( $fieldName, $this->getAllNonMultilingualFields())) {
                //Setup the data array for the main table
                $queryTableData[$fieldName] = $fieldValue;
            } elseif ($this->isMultiLingual() && in_array( $fieldName, $this->getAllMultilingualFieldNames())) {
                //Setup data arrays for the description queries
                foreach ($this->getAllMultilingualFields() as $multilingualField) {
                    foreach ($this->controlPanel->getSiteLanguages() as $languageId => $language) {
                        if ($multilingualField . '[' . $languageId . ']' == $fieldName) {
                            $queryTableDescriptionData[ $languageId ][ $multilingualField ] = $fieldValue;
                        }
                    }
                }
            } else {
                foreach ($this->getRelations() as $relation) {
                    if ( $relation->inputFieldName ==  $fieldName && $relation->hasLookupColumn()) {
                        //If relation is oneToMany use the main data array
                        $queryTableData[$fieldName] = $fieldValue;
                    } elseif ($relation->inputFieldName ==  $fieldName && $relation->hasLookUpTable() ) {
                        //Setup data arrays for manyToMany relations queries
                        if (is_array($fieldValue)) {
                            foreach ($fieldValue as $value) {
                                $queryM2MRelationsData[$relation->getLookUpTableName()][] = array( $relation->activeModel->getPrefix() . 'id' => $value);
                            }
                        } else {
                            $queryM2MRelationsData[$relation->getLookUpTableName()] = array();
                        }
                    } elseif ($relation->inputFieldName ==  $fieldName && $relation->getRelationType() == 'oneToMany') {
                        //Setup data array for oneToMany relations queries
                        $queryO2MRelationsData[$relation->activeModel->getTableName()] = array();
                        if (is_array($fieldValue)) {
                            foreach ($fieldValue as $value) {
                                $queryO2MRelationsData[$relation->activeModel->getTableName()][] =  $value;
                            }
                        }
                    }
                }
            }
        }

        if (isset($data['id']) && !empty($data['id'])) {

            //Main Table Query
            $this->updateEntityTable($data['id'], $queryTableData);

            //Description Table Queries (1 per language)
            if ($this->isMultiLingual()) {
                $this->updateEntityDescriptionTable($data['id'], $queryTableDescriptionData);
            }

            //ManyTOMany Relations Queries
            if (count($queryM2MRelationsData) > 0) {
                $this->insertUpdateM2MRelations($data['id'], $queryM2MRelationsData);
            }

            //OneToMany Relations Queries
            if (count($queryO2MRelationsData) > 0) {
                $this->insertUpdateO2MRelations($data['id'], $queryO2MRelationsData);
            }
        } else {

            //Main Table Query
            $this->insertToEntityTable($queryTableData);

            //Description Table Queries (1 per language)
            if ($this->isMultiLingual()) {
                $this->insertToEntityDescriptionTable($queryTableDescriptionData);
            }

            //ManyTOMany Relations Queries
            if (count($queryM2MRelationsData) > 0) {
                $this->insertUpdateToEntityRelationsTables($this->lastInsertValue, $queryM2MRelationsData);
            }

            //OneToMany Relations Queries
            if (count($queryO2MRelationsData) > 0) {
                $this->insertUpdateO2MRelations($this->lastInsertValue, $queryO2MRelationsData);
            }
        }
    }

    private function insertToEntityTable ($dataArray)
    {
        $statement = $this->sql->insert($this->getTableName());
        $statement->values($dataArray);
        $sqlString = $this->sql->getSqlStringForSqlObject($statement);
        $this->adapter->query($sqlString, Adapter::QUERY_MODE_EXECUTE);
        $this->lastInsertValue = $this->adapter->getDriver()->getConnection()->getLastGeneratedValue();
    }

    private function updateEntityTable ($id, $dataArray)
    {
        $statement = $this->sql->update($this->getTableName());
        $statement->set($dataArray);
        $statement->where(array('id' => $id));

        $sqlString = $this->sql->getSqlStringForSqlObject($statement);
        $this->adapter->query($sqlString, Adapter::QUERY_MODE_EXECUTE);
    }

    private function insertToEntityDescriptionTable ($dataArray)
    {
        foreach ($this->controlPanel->getSiteLanguages() as $languageId => $language) {
            $statement = $this->sql->insert($this->getTableDescriptionName());
            $statement->values(
                array_merge(
                    $dataArray[$languageId],
                    array(
                        $this->getPrefix().'id' => $this->lastInsertValue,
                        $this->getLanguageID() => $languageId
                    )
                )
            );
            $sqlString = $this->sql->getSqlStringForSqlObject($statement);
            $this->adapter->query($sqlString, Adapter::QUERY_MODE_EXECUTE);
        }
    }

    private function updateEntityDescriptionTable ($id, $dataArray)
    {
        foreach ($this->controlPanel->getSiteLanguages() as $languageId => $language) {

            $statement = $this->sql->update($this->getTableDescriptionName());
            $statement->set($dataArray[$languageId]);
            $statement->where(
                array(
                    $this->getPrefix().'id' => $id,
                    $this->getLanguageID() => $languageId
                )
            );
            $sqlString = $this->sql->getSqlStringForSqlObject($statement);
            $this->adapter->query($sqlString, Adapter::QUERY_MODE_EXECUTE);
        }
    }

    private function insertUpdateM2MRelations ($id, $dataArray)
    {
        foreach ($dataArray as $lookUpTable => $relationEntries) {
            $deleteRelationStatement = $this->sql->delete($lookUpTable)->where(array($this->getPrefix() . 'id' => $id));
            $deleteRelationSqlString = $this->sql->getSqlStringForSqlObject($deleteRelationStatement);
            $this->adapter->query($deleteRelationSqlString, Adapter::QUERY_MODE_EXECUTE);

            if (count($relationEntries) > 0) {
                foreach ($relationEntries as $relationEntry) {
                    $relationStatement = $this->sql->insert($lookUpTable);
                    $relationStatement->values(array_merge($relationEntry, array($this->getPrefix() . 'id' => $id)));
                    $relationSqlString = $this->sql->getSqlStringForSqlObject($relationStatement);
                    $this->adapter->query($relationSqlString, Adapter::QUERY_MODE_EXECUTE);
                }
            }
        }
    }

    private function insertUpdateO2MRelations ($id, $dataArray)
    {
        foreach ($dataArray as $entityTable => $entityIds) {
            //mark all previously related entries as '0' (un-categorized)
            $statement = $this->sql->update($entityTable);
            $statement->set(array($this->getPrefix() . 'id' => '0'));
            $statement->where(
                array(
                    $this->getPrefix().'id' => $id,
                )
            );

            $sqlString = $this->sql->getSqlStringForSqlObject($statement);
            $this->adapter->query($sqlString, Adapter::QUERY_MODE_EXECUTE);

            //Update all currently related entries (if any exist)
            if (count($entityIds) > 0) {
                $statement = $this->sql->update($entityTable);
                $statement->set(array($this->getPrefix() . 'id' => $id));
                $statement->where(
                    array(
                        'id' => $entityIds,
                    )
                );
                $sqlString = $this->sql->getSqlStringForSqlObject($statement);
                $this->adapter->query($sqlString, Adapter::QUERY_MODE_EXECUTE);
            }
        }
    }

    public function deleteSingle($itemId)
    {
        //Main Entity Table
        $this->deleteFromEntityTable($itemId);
        //Description Table IF it exists
        if ($this->isMultilingual()) {
            $this->deleteFromEntityDescriptionTable($itemId);
        }
        //Relations If they exist
        if (count($this->getRelations()) > 0 ) {
            foreach ($this->getRelations() as $relation) {
                if ($relation->hasLookupColumn()) {
                    //If relation is oneToMany the main delete query will take care of it
                } elseif ($relation->hasLookUpTable() ) {
                    //If relation is manyToMany
                    $this->deleteFromLookUpTable($relation->getLookUpTableName(), $itemId);
                } elseif ($relation->getRelationType() == 'oneToMany') {
                    //If relation is oneToMany
                    $this->deleteFromRelatedEntityTable($relation->activeModel->getTableName(), $itemId);
                }
            }
        }
        return true;
    }

    private function deleteFromEntityTable($itemId)
    {
        $statement = $this->sql->delete($this->getTableName())->where(array('id' => $itemId));
        $sqlString = $this->sql->getSqlStringForSqlObject($statement);
        $this->adapter->query($sqlString, Adapter::QUERY_MODE_EXECUTE);
    }

    private function deleteFromEntityDescriptionTable($itemId)
    {
        $statement = $this->sql->delete($this->getTableDescriptionName())->where(array($this->getPrefix().'id' => $itemId));
        $sqlString = $this->sql->getSqlStringForSqlObject($statement);
        $this->adapter->query($sqlString, Adapter::QUERY_MODE_EXECUTE);
    }

    private function deleteFromLookUpTable($lookUpTable, $itemId)
    {
        $statement = $this->sql->delete($lookUpTable)->where(array($this->getPrefix() . 'id' => $itemId));
        $sqlString = $this->sql->getSqlStringForSqlObject($statement);
        $this->adapter->query($sqlString, Adapter::QUERY_MODE_EXECUTE);
    }

    private function deleteFromRelatedEntityTable($relatedEntityTable, $itemId)
    {
        //mark all previously related entries as '0' (un-categorized)
        $statement = $this->sql->update($relatedEntityTable);
        $statement->set(array($this->getPrefix() . 'id' => '0'));
        $statement->where(
            array(
                $this->getPrefix().'id' => $itemId,
            )
        );
        $sqlString = $this->sql->getSqlStringForSqlObject($statement);
        $this->adapter->query($sqlString, Adapter::QUERY_MODE_EXECUTE);
    }
}
