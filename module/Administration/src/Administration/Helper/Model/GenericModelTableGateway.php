<?php
namespace Administration\Helper\Model;

use Zend\Db\Adapter\Adapter;
use Zend\Db\TableGateway\Exception;
use Zend\Db\Sql\Select;


class GenericModelTableGateway
{
    protected $lastInsertValue;
    //Entity model
    protected $model;
    //Db adapter
    public  $adapter;
    //Sql interface
    public  $sql;
    //Control object contains languages admin-rights etc
    public  $controlPanel;

    public $sitemapTable = 'Sitemap';
    public $routesTable  = 'Routes';

    public function __construct($model, $controlPanel)
    {
        $this->controlPanel = $controlPanel;
        $this->adapter      = $this->controlPanel->getDbAdapter();
        $this->sql          = $this->controlPanel->getSQL();
        $this->model        = $model;
    }

    public function getModel()
    {
       return $this->model;
    }

    public function finaliseTable()
    {
        $this->createTablesIfNotExist();
        $this->addFieldsColumnsIfDontExist('integers',      $this->model->getIntegers());
        $this->addFieldsColumnsIfDontExist('enums',         $this->model->getEnums());
        $this->addFieldsColumnsIfDontExist('dates',         $this->model->getDates());
        $this->addFieldsColumnsIfDontExist('varchars',      $this->model->getVarchars());
        $this->addFieldsColumnsIfDontExist('images',        $this->model->getImages());
        $this->addFieldsColumnsIfDontExist('files',         $this->model->getFiles());
        $this->addFieldsColumnsIfDontExist('texts',         $this->model->getTexts());
        $this->addFieldsColumnsIfDontExist('longTexts',     $this->model->getLongTexts());
        $this->addFieldsColumnsIfDontExist('imageCaptions', $this->model->getImageCaptions());
        $this->addFieldsColumnsIfDontExist('fileCaptions',  $this->model->getFileCaptions());
        if ($this->model->isMultiLingual()) {
            $this->addFieldsColumnsIfDontExist('multiLingualFileCaptions', $this->model->getMultilingualFilesCaptions());
            $this->addFieldsColumnsIfDontExist('multiLingualTexts',        $this->model->getMultilingualTexts());
            $this->addFieldsColumnsIfDontExist('multiLingualLongTexts',    $this->model->getMultilingualLongTexts());
            $this->addFieldsColumnsIfDontExist('multiLingualVarchars',     $this->model->getMultilingualVarchars());
            $this->addFieldsColumnsIfDontExist('multiLingualFiles',        $this->model->getMultilingualFiles());
        }
        if ($this->model->followRelations()) {
            $this->addJoinRelationsIfNotExist($this->model->getRelations());
        }
        $this->addCustomSelectionFieldsIfNotExist($this->model->getCustomSelections());
    }

    private function createTablesIfNotExist()
    {
        $this->adapter->query('CREATE TABLE IF NOT EXISTS `' . $this->model->getTableName() . '` (`id` int(11) unsigned NOT NULL AUTO_INCREMENT, `' . $this->model->getPublishedField() . '`  ENUM( "0", "1" ) NOT NULL DEFAULT "0", PRIMARY KEY (`id`))', Adapter::QUERY_MODE_EXECUTE);
        if ($this->model->isMultiLingual()) {
            $results = $this->adapter->query("SHOW TABLES LIKE '" . $this->model->getTableDescriptionName() . "'" , Adapter::QUERY_MODE_EXECUTE);
            if ($results->count() <= 0) {

                $this->adapter->query('CREATE TABLE IF NOT EXISTS `'.$this->model->getTableDescriptionName().'` (`'.$this->model->getLanguageID().'` int(11) DEFAULT NULL, `'.$this->model->getPrefix().'id` int(11) DEFAULT NULL, PRIMARY KEY (`'.$this->model->getLanguageID().'`,`'.$this->model->getPrefix().'id`))', Adapter::QUERY_MODE_EXECUTE);

                //Make sure every entry on main table has a corresponding one in description
                $statement    = $this->sql->select($this->model->getTableName());
                $selectString = $this->sql->getSqlStringForSqlObject($statement);
                $results      = $this->adapter->query($selectString, Adapter::QUERY_MODE_EXECUTE);
                if ($results->count() > 0) {
                    foreach ($results as $entry) {
                        foreach ($this->controlPanel->getSiteLanguages() as $languageId => $language) {
                            $insertStatement = $this->sql->insert($this->model->getTableDescriptionName());
                            $insertStatement->values(array($this->model->getLanguageID() => $languageId, $this->model->getPrefix().'id' => $entry['id'] ));
                            $insertStatementString = $this->sql->getSqlStringForSqlObject($insertStatement);
                            $this->adapter->query($insertStatementString, Adapter::QUERY_MODE_EXECUTE);
                        }
                    }
                }
            }
        }
        if ($this->model->getMaximumTreeDepth() > 0) {
            $this->adapter->query('CREATE TABLE IF NOT EXISTS `' . $this->model->getParentLookupTableName() . '` (`' . $this->model->getPrefix() . 'id' . '` int(11) unsigned NOT NULL, `parent_id` int(11) unsigned NOT NULL, PRIMARY KEY (`' . $this->model->getPrefix() . 'id' . '`, `parent_id`))', Adapter::QUERY_MODE_EXECUTE);
        }
    }

    private function addFieldsColumnsIfDontExist($type, $fieldsArray)
    {

        $tableToAddTheColumn = '';
        $fieldType           = '';

        switch($type){
            case 'integers':
                $tableToAddTheColumn = $this->model->getTableName();
                $fieldType = " INT(11) ";
                break;
            case 'enums':
                $tableToAddTheColumn = $this->model->getTableName();
                $fieldType = " ENUM( '0', '1' ) NOT NULL DEFAULT '0' ";
                break;
            case 'dates':
                $tableToAddTheColumn = $this->model->getTableName();
                $fieldType = " DATETIME ";
                break;
            case 'varchars':
            case 'images':
            case 'files':
                $tableToAddTheColumn = $this->model->getTableName();
                $fieldType = " VARCHAR( 255 ) ";
                break;
            case 'texts':
            case 'longTexts':
                $tableToAddTheColumn = $this->model->getTableName();
                $fieldType = " TEXT ";
                break;
            case 'multiLingualVarchars':
            case 'multiLingualFiles':
            case 'imageCaptions':
            case 'fileCaptions':
            case 'multiLingualFileCaptions':
                if ($this->model->isMultiLingual()) {
                    $tableToAddTheColumn = $this->model->getTableDescriptionName();
                } else {
                    $tableToAddTheColumn = $this->model->getTableName();
                }
                $fieldType = " VARCHAR( 255 ) ";
                break;
            case 'multiLingualTexts':
            case 'multiLingualLongTexts':
                $tableToAddTheColumn = $this->model->getTableDescriptionName();
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

    private function addCustomSelectionFieldsIfNotExist(Array $selects)
    {
        foreach ($selects as $select) {

            if ($select->isMultiple()) {
                $this->adapter->query('CREATE TABLE IF NOT EXISTS `' . $select->getLookUpTableName() . '` ( `'.$this->model->getPrefix().'id` int(11) DEFAULT NULL, `' . $select->getFieldName() . '` VARCHAR( 255 ) DEFAULT NULL, PRIMARY KEY (`' . $this->model->getPrefix() . 'id`, `' . $select->getFieldName() . '`))', Adapter::QUERY_MODE_EXECUTE);
            } else {
                $statement = $this->adapter->createStatement("SHOW COLUMNS FROM " . $this->model->getTableName() . " LIKE '" .  $select->getFieldName() . "'" );
                $result    = $statement->execute();
                if ($result->count()==0)
                {
                    $this->adapter->query("ALTER TABLE " . $this->model->getTableName() . " ADD `" . $select->getFieldName() . "` VARCHAR( 255 ) DEFAULT '0';", Adapter::QUERY_MODE_EXECUTE);
                }
            }
        }
    }

    private function addJoinRelationsIfNotExist(Array $relations)
    {
        foreach ($relations as $relation) {

            if ($relation->activeModel->getPrefix() == '') {
                throw new Exception\InvalidArgumentException('Please set the prefix in the ' . $relation->activeModel->getTableName() . ' model!');
            }
            if ($this->model->getPrefix() == '') {
                throw new Exception\InvalidArgumentException('Please set the prefix in the ' . $this->model->getTableName() . ' model!');
            }
            if ($relation->hasLookUpTable()) {
                $this->adapter->query('CREATE TABLE IF NOT EXISTS `' . $relation->getLookUpTableName() . '` ( `'.$this->model->getPrefix().'id` int(11) DEFAULT NULL, `'.$relation->activeModel->getPrefix().'id` int(11) DEFAULT NULL)', Adapter::QUERY_MODE_EXECUTE);
            } elseif ($relation->hasLookupColumn()) {
                $statement = $this->adapter->createStatement("SHOW COLUMNS FROM " . $this->model->getTableName() . " LIKE '" . $relation->activeModel->getPrefix() . "id'" );
                $result    = $statement->execute();
                if ($result->count()==0)
                {
                    $this->adapter->query("ALTER TABLE " . $this->model->getTableName() . " ADD `" . $relation->activeModel->getPrefix() . "id` int(11) DEFAULT 0;", Adapter::QUERY_MODE_EXECUTE);
                }
            }
        }
    }

    public function getListing( $parent = 0, $itemsPerPage = 'all', $page = null, $order = null, $orderDirection = null, Array $filters = array(), Array $notInArray = array())
    {
        if (is_array($this->model->getListingFields()) && count($this->model->getListingFields()) > 0) {

            if ($this->model->isMultiLingual()) {
                $statement = $this->sql->select($this->model->getTableName())->join(array( 'dc' => $this->model->getTableDescriptionName()),'dc.' . $this->model->getPrefix() . 'id = ' . $this->model->getTableName() . '.id' , Select::SQL_STAR , Select::JOIN_LEFT)->where(array($this->model->getLanguageID() => $this->controlPanel->getDefaultSiteLanguageId()));
            } else {
                $statement = $this->sql->select($this->model->getTableName());
            }

            if ($this->model->getMaximumTreeDepth() > 0) {
                $subQry =  $this->sql->select()->from(array('dpc' => $this->model->getParentLookupTableName()))->columns(array('childCount' => new \Zend\Db\Sql\Expression('COUNT(dpc.parent_id)')))->where(array('dpc.parent_id' => new \Zend\Db\Sql\Expression( $this->model->getTableName() . '.' . 'id')));
                $statement->join(array( 'dp' => $this->model->getParentLookupTableName()),'dp.' . $this->model->getPrefix() . 'id = ' . $this->model->getTableName() . '.id' , array(Select::SQL_STAR, 'childCount' => new \Zend\Db\Sql\Expression('?', array($subQry))) , Select::JOIN_LEFT)->where(array('dp.parent_id' => $parent));
            }

            if (count($filters) > 0) {
                foreach ($filters as $relationField => $value) {
                    if ($value != 'all') {
                        foreach ($this->model->getRelations() as $relation) {
                            if ($relationField == $relation->inputFieldName) {
                                if ($relation->hasLookupColumn()) {
                                    $statement->where(array($relation->inputFieldName => $value));
                                } elseif ($relation->hasLookUpTable()) {
                                    $statement->join(array( 'dr' => $relation->getLookUpTableName()),'dr.' . $this->model->getPrefix() . 'id = ' . $this->model->getTableName() . '.id' , Select::SQL_STAR , Select::JOIN_LEFT)->where(array('dr.' . $relation->inputFieldName => $value));
                                }
                            }
                        }
                    }
                }
            }

            if (count($notInArray) > 0) {
                $statement->where->addPredicate(new \Zend\Db\Sql\Predicate\Expression($this->model->getTableName() . '.id NOT IN (?)', $notInArray));
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

    public function getListingForSelect($parentCat = 0, $treeLevel = 0)
    {
        $toReturn = array();
        $results  = $this->getListing($parentCat);

        foreach ($results as $result) {
            $depthString    = '';
            for ($i=1; $i <= $treeLevel; $i++ ) {
                if ($i == $treeLevel) {
                    $depthString .= '**|--';
                } else {
                    $depthString .= '**';
                }
            }
            $listingFields = $this->model->getListingFields();
            $result[$listingFields[0]] = $depthString . $result[$listingFields[0]];
            $toReturn[] = $result;

            if ($this->model->getMaximumTreeDepth() > 0 && $treeLevel < ($this->model->getMaximumTreeDepth()-1) && isset($result['childCount']) && $result['childCount'] > 0) {
                $toReturn = array_merge($toReturn, $this->getListingForSelect($result['id'],$treeLevel+1));
            }
        }
        return $toReturn;
    }

    public function getItemById($id)
    {
        //Data from Entity Table
        $statement    = $this->sql->select($this->model->getTableName())->where(array($this->model->getTableName().'.id' => $id));
        $selectString = $this->sql->getSqlStringForSqlObject($statement);
        $results      = $this->adapter->query($selectString, Adapter::QUERY_MODE_EXECUTE)->current();

        //Attach Data from Description Table
        if ($this->model->isMultiLingual()) {

            $descriptionStatement    = $this->sql->select($this->model->getTableDescriptionName())->where(array($this->model->getPrefix().'id' => $id));
            $descriptionSelectString = $this->sql->getSqlStringForSqlObject($descriptionStatement);
            $descriptionResults      = $this->adapter->query($descriptionSelectString, Adapter::QUERY_MODE_EXECUTE);

            if ($descriptionResults->count() > 0) {
                foreach ($descriptionResults as $descriptionEntry) {
                    foreach ($this->model->getAllMultilingualFields() as $multilingualField) {
                        $results[$multilingualField . '[' . $descriptionEntry[$this->model->getLanguageID()] . ']'] = $descriptionEntry[$multilingualField];
                    }
                }
            }
        }

        //Attach Data from Relations Look Up Tables
        if (count($this->model->getRelations()) > 0) {
            foreach ($this->model->getRelations() as $relation) {
                if ($relation->hasLookUpTable()) {
                    //If relation is manyToMany
                    $lookUpTableStatement    = $this->sql->select($relation->getLookUpTableName())->where(array($this->model->getPrefix().'id' => $id));
                    $lookUpTableSelectString = $this->sql->getSqlStringForSqlObject($lookUpTableStatement);
                    $lookUpTableResults      = $this->adapter->query($lookUpTableSelectString, Adapter::QUERY_MODE_EXECUTE);

                    if ($lookUpTableResults->count() > 0) {
                        foreach ($lookUpTableResults as $lookupTableResult) {
                            $results[$relation->inputFieldName][] =  $lookupTableResult[$relation->activeModel->getPrefix() . 'id'];
                        }
                    }
                } elseif ($relation->getRelationType() == 'oneToMany') {
                    //If relation is oneToMany
                    $relationStatement    = $this->sql->select($relation->activeModel->getTableName())->where(array($this->model->getPrefix().'id' => $id));
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
        //Attach Data from Custom (Multiple) Selections
        if (count($this->model->getCustomSelections()) > 0) {
            foreach ($this->model->getCustomSelections() as $selection) {
                if ($selection->isMultiple()) {
                    $statement        = $this->sql->select($selection->getLookUpTableName())->where(array($this->model->getPrefix().'id' => $id));
                    $selectString     = $this->sql->getSqlStringForSqlObject($statement);
                    $selectionResults = $this->adapter->query($selectString, Adapter::QUERY_MODE_EXECUTE);

                    if ($selectionResults->count() > 0) {
                        foreach ($selectionResults as $result) {
                            $results[$selection->getFieldName()][] =  $result[$selection->getFieldName()];
                        }
                    }
                }
            }
        }
        //Attach Data from Parent Lookup Table
        if ($this->model->getMaximumTreeDepth() > 0) {
            $statement        = $this->sql->select($this->model->getParentLookupTableName())->where(array($this->model->getPrefix().'id' => $id));
            $selectString     = $this->sql->getSqlStringForSqlObject($statement);
            $parentResults    = $this->adapter->query($selectString, Adapter::QUERY_MODE_EXECUTE);

            if ($parentResults->count() > 0) {
                foreach ($parentResults as $result) {
                    $results['parent_' . $this->model->getPrefix() . 'id'][] =  $result['parent_id'];
                }
            }
        }
        return $results;
    }

    public function save($data)
    {

        $data = $this->model->preSaveHook($data);
        if (!$data) {
            throw new Exception\InvalidArgumentException('Pre-save method for model ' . $this->model->getTableName() . 'Failed!');
        }

        $queryTableData            = array();
        $queryTableDescriptionData = array();
        $queryM2MRelationsData     = array();  // Many to Many Relations (with lookup Table)
        $queryO2MRelationsData     = array();  // One to Many Relations (with Column In Main Table)
        $queryCSMultiData          = array();  // Custom Selections Multiple (with Lookup Table)
        $queryParentData           = array();  // Parent lookup (manyToMany)

        foreach ($data as $fieldName => $fieldValue) {
            if (in_array( $fieldName, $this->model->getAllNonMultilingualFields())) {
                //Setup the data array for the main table
                $queryTableData[$fieldName] = $fieldValue;
            } elseif ($this->model->isMultiLingual() && in_array( $fieldName, $this->getAllMultilingualFieldNames())) {
                //Setup data arrays for the description queries
                foreach ($this->model->getAllMultilingualFields() as $multilingualField) {
                    foreach ($this->controlPanel->getSiteLanguages() as $languageId => $language) {
                        if ($multilingualField . '[' . $languageId . ']' == $fieldName) {
                            $queryTableDescriptionData[ $languageId ][ $multilingualField ] = $fieldValue;
                        }
                    }
                }
            } elseif (in_array( $fieldName, $this->model->getRelationsFields())) {
                foreach ($this->model->getRelations() as $relation) {
                    if ( $relation->inputFieldName ==  $fieldName && $relation->hasLookupColumn()) {
                        //If relation is manyToOne use the main data array
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
            } elseif (in_array( $fieldName, $this->model->getCustomSelectionFields())) {
                foreach ($this->model->getCustomSelections() as $selection) {
                    if ($selection->isMultiple()) {
                        if (is_array($fieldValue)) {
                            foreach ($fieldValue as $value) {
                                //Setup data array for Custom Selection Lookup Table Queries
                                $queryCSMultiData[$selection->getLookUpTableName()][] = array( $selection->getFieldName() => $value);
                            }
                        } else {
                            $queryCSMultiData[$selection->getLookUpTableName()] = array();
                        }
                    } else {
                        //If Custom Selection is not multiple then use main Table
                        $queryTableData[$fieldName] = $fieldValue;
                    }
                }
            } elseif ($this->model->getMaximumTreeDepth() > 0 && $fieldName == 'parent_' . $this->model->getPrefix() . 'id') {
                if (is_array($fieldValue)) {
                    foreach ($fieldValue as $value) {
                        //Setup data array for Parent Lookup Table Queries
                        $queryParentData[] = $value;
                    }
                }
            }
        }

        if (isset($data['id']) && !empty($data['id'])) {

            //Main Table Query
            $this->updateEntityTable($data['id'], $queryTableData);

            //Description Table Queries (1 per language)
            if ($this->model->isMultiLingual()) {
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

            //Custom multiple selections Queries
            if (count($queryCSMultiData) > 0) {
                $this->insertUpdateMSelections($data['id'], $queryCSMultiData);
            }

            //Parent Table Queries
            if ($this->model->getMaximumTreeDepth() > 0) {
                $this->insertUpdateParentLookup($data['id'], $queryParentData);
            }

        } else {

            //Main Table Query
            $this->insertToEntityTable($queryTableData);

            //Description Table Queries (1 per language)
            if ($this->model->isMultiLingual()) {
                $this->insertToEntityDescriptionTable($queryTableDescriptionData);
            }

            //ManyTOMany Relations Queries
            if (count($queryM2MRelationsData) > 0) {
                $this->insertUpdateM2MRelations($this->lastInsertValue, $queryM2MRelationsData);
            }

            //OneToMany Relations Queries
            if (count($queryO2MRelationsData) > 0) {
                $this->insertUpdateO2MRelations($this->lastInsertValue, $queryO2MRelationsData);
            }

            //Custom multiple selections Queries
            if (count($queryCSMultiData) > 0) {
                $this->insertUpdateMSelections($this->lastInsertValue, $queryCSMultiData);
            }

            //Parent Table Queries
            if ($this->model->getMaximumTreeDepth() > 0) {
                $this->insertUpdateParentLookup($this->lastInsertValue, $queryParentData);
            }
        }

        if (!$this->model->postSaveHook($data)) {
            throw new Exception\InvalidArgumentException('Post-save method for model ' . $this->model->getTableName() . 'Failed!');
        }
    }

    private function insertToEntityTable ($dataArray)
    {
        $statement = $this->sql->insert($this->model->getTableName());
        $statement->values($dataArray);
        $sqlString = $this->sql->getSqlStringForSqlObject($statement);
        $this->adapter->query($sqlString, Adapter::QUERY_MODE_EXECUTE);
        $this->lastInsertValue = $this->adapter->getDriver()->getConnection()->getLastGeneratedValue();
    }

    private function updateEntityTable ($id, $dataArray)
    {
        $statement = $this->sql->update($this->model->getTableName());
        $statement->set($dataArray);
        $statement->where(array('id' => $id));

        $sqlString = $this->sql->getSqlStringForSqlObject($statement);
        $this->adapter->query($sqlString, Adapter::QUERY_MODE_EXECUTE);
    }

    private function insertToEntityDescriptionTable ($dataArray)
    {
        foreach ($this->controlPanel->getSiteLanguages() as $languageId => $language) {
            $statement = $this->sql->insert($this->model->getTableDescriptionName());
            $statement->values(
                array_merge(
                    $dataArray[$languageId],
                    array(
                        $this->model->getPrefix().'id' => $this->lastInsertValue,
                        $this->model->getLanguageID() => $languageId
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

            $statement = $this->sql->update($this->model->getTableDescriptionName());
            $statement->set($dataArray[$languageId]);
            $statement->where(
                array(
                    $this->model->getPrefix().'id' => $id,
                    $this->model->getLanguageID() => $languageId
                )
            );
            $sqlString = $this->sql->getSqlStringForSqlObject($statement);
            $this->adapter->query($sqlString, Adapter::QUERY_MODE_EXECUTE);
        }
    }

    private function insertUpdateM2MRelations ($id, $dataArray)
    {
        foreach ($dataArray as $lookUpTable => $relationEntries) {
            $deleteRelationStatement = $this->sql->delete($lookUpTable)->where(array($this->model->getPrefix() . 'id' => $id));
            $deleteRelationSqlString = $this->sql->getSqlStringForSqlObject($deleteRelationStatement);
            $this->adapter->query($deleteRelationSqlString, Adapter::QUERY_MODE_EXECUTE);

            if (count($relationEntries) > 0) {
                foreach ($relationEntries as $relationEntry) {
                    $relationStatement = $this->sql->insert($lookUpTable);
                    $relationStatement->values(array_merge($relationEntry, array($this->model->getPrefix() . 'id' => $id)));
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
            $statement->set(array($this->model->getPrefix() . 'id' => '0'));
            $statement->where(
                array(
                    $this->model->getPrefix().'id' => $id,
                )
            );

            $sqlString = $this->sql->getSqlStringForSqlObject($statement);
            $this->adapter->query($sqlString, Adapter::QUERY_MODE_EXECUTE);

            //Update all currently related entries (if any exist)
            if (count($entityIds) > 0) {
                $statement = $this->sql->update($entityTable);
                $statement->set(array($this->model->getPrefix() . 'id' => $id));
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

    private function insertUpdateMSelections ($id, $dataArray)
    {
        foreach ($dataArray as $lookUpTable => $selectionEntries) {
            $deleteStatement = $this->sql->delete($lookUpTable)->where(array($this->model->getPrefix() . 'id' => $id));
            $deleteSqlString = $this->sql->getSqlStringForSqlObject($deleteStatement);
            $this->adapter->query($deleteSqlString, Adapter::QUERY_MODE_EXECUTE);

            if (count($selectionEntries) > 0) {
                foreach ($selectionEntries as $selectionEntry) {
                    $statement = $this->sql->insert($lookUpTable);
                    $statement->values(array_merge($selectionEntry, array($this->model->getPrefix() . 'id' => $id)));
                    $sqlString = $this->sql->getSqlStringForSqlObject($statement);
                    $this->adapter->query($sqlString, Adapter::QUERY_MODE_EXECUTE);
                }
            }
        }
    }

    private function insertUpdateParentLookup ($id, $dataArray)
    {
        $deleteStatement = $this->sql->delete($this->model->getParentLookupTableName())->where(array($this->model->getPrefix() . 'id' => $id));
        $deleteSqlString = $this->sql->getSqlStringForSqlObject($deleteStatement);
        $this->adapter->query($deleteSqlString, Adapter::QUERY_MODE_EXECUTE);

        foreach ($dataArray as $parent) {
            $statement = $this->sql->insert($this->model->getParentLookupTableName());
            $statement->values(array($this->model->getPrefix() . 'id' => $id, 'parent_id' => $parent));
            $sqlString = $this->sql->getSqlStringForSqlObject($statement);
            $this->adapter->query($sqlString, Adapter::QUERY_MODE_EXECUTE);
        }
    }

    public function deleteSingle($itemId)
    {
        if (!$this->model->preDeleteHook($itemId)) {
            throw new Exception\InvalidArgumentException('Pre-delete method for model ' . $this->model->getTableName() . 'Failed!');
        }

        //Main Entity Table
        $this->deleteFromEntityTable($itemId);
        //Description Table IF it exists
        if ($this->model->isMultilingual()) {
            $this->deleteFromEntityDescriptionTable($itemId);
        }
        //Relations If they exist
        if (count($this->model->getRelations()) > 0 ) {
            foreach ($this->model->getRelations() as $relation) {
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
        //Multiple Custom Selection If they exit
        if (count($this->model->getCustomSelections())) {
            foreach ($this->model->getCustomSelections() as $selection) {
                if ($selection->isMultiple()) {
                    $this->deleteFromLookUpTable($selection->getLookUpTableName(), $itemId);
                }
            }
        }

        //Parent Table Queries
        if ($this->model->getMaximumTreeDepth() > 0) {
            $this->deleteFromLookUpTable($this->model->getParentLookupTableName(), $itemId);
            $this->deleteParentFromLookupTable($itemId);
        }

        if (!$this->model->postDeleteHook($itemId)) {
            throw new Exception\InvalidArgumentException('Pre-delete method for model ' . $this->model->getTableName() . 'Failed!');
        }
        return true;
    }

    private function deleteFromEntityTable($itemId)
    {
        $statement = $this->sql->delete($this->model->getTableName())->where(array('id' => $itemId));
        $sqlString = $this->sql->getSqlStringForSqlObject($statement);
        $this->adapter->query($sqlString, Adapter::QUERY_MODE_EXECUTE);
    }

    private function deleteFromEntityDescriptionTable($itemId)
    {
        $statement = $this->sql->delete($this->model->getTableDescriptionName())->where(array($this->model->getPrefix().'id' => $itemId));
        $sqlString = $this->sql->getSqlStringForSqlObject($statement);
        $this->adapter->query($sqlString, Adapter::QUERY_MODE_EXECUTE);
    }

    private function deleteFromLookUpTable($lookUpTable, $itemId)
    {
        $statement = $this->sql->delete($lookUpTable)->where(array($this->model->getPrefix() . 'id' => $itemId));
        $sqlString = $this->sql->getSqlStringForSqlObject($statement);
        $this->adapter->query($sqlString, Adapter::QUERY_MODE_EXECUTE);
    }

    private function deleteFromRelatedEntityTable($relatedEntityTable, $itemId)
    {
        //mark all previously related entries as '0' (un-categorized)
        $statement = $this->sql->update($relatedEntityTable);
        $statement->set(array($this->model->getPrefix() . 'id' => '0'));
        $statement->where(
            array(
                $this->model->getPrefix().'id' => $itemId,
            )
        );
        $sqlString = $this->sql->getSqlStringForSqlObject($statement);
        $this->adapter->query($sqlString, Adapter::QUERY_MODE_EXECUTE);
    }

    private function deleteParentFromLookupTable($itemId)
    {
        //mark all previously related entries as '0' (root item)
        $statement = $this->sql->update($this->model->getParentLookupTableName());
        $statement->set(array('parent_id' => '0'));
        $statement->where(
            array(
                'parent_id' => $itemId,
            )
        );
        $sqlString = $this->sql->getSqlStringForSqlObject($statement);
        $this->adapter->query($sqlString, Adapter::QUERY_MODE_EXECUTE);
    }

    /**
     * Returns all Multilingual field names (language-specific).
     */
    public function getAllMultilingualFieldNames()
    {
        $fieldsArray = array();
        foreach ($this->model->getAllMultilingualFields() as $field) {
            foreach ($this->controlPanel->getSiteLanguages() as $languageId => $language) {
                $fieldsArray[] = $field . '[' . $languageId . ']';
            }
        }
        return $fieldsArray;
    }

    /**
     * Edits single boolean field in main table
     * @param $id int
     * @param $field string
     * @param $value boolean
     * @throws \Zend\Db\TableGateway\Exception\InvalidArgumentException
     */
    public function editSingleBooleanField($id, $field, $value)
    {
        $statement = $this->adapter->createStatement("SHOW COLUMNS FROM " . $this->model->getTableName() . " LIKE '" . $field . "'" );
        $result    = $statement->execute();
        if ($result->count() == 0){
            throw new Exception\InvalidArgumentException('This model does not contain a property '.$field );
        } else {
            $statement = $this->sql->update($this->model->getTableName());
            $statement->set(array($field => $value));
            $statement->where(
                array(
                    'id' => $id,
                )
            );
            $sqlString = $this->sql->getSqlStringForSqlObject($statement);
            $this->adapter->query($sqlString, Adapter::QUERY_MODE_EXECUTE);
        }
    }

    public function addToSiteMap($parent = 0)
    {
        if (!$this->model->isStandAlonePage()) {
            throw new Exception\InvalidArgumentException('This model can not be added to the sitemap');
        }
        $this->adapter->query('CREATE TABLE IF NOT EXISTS `' . $this->sitemapTable . '` (`id` int(11) unsigned NOT NULL AUTO_INCREMENT, `model`  VARCHAR( 255 ) NOT NULL, `parent` int(11) NOT NULL DEFAULT "0", PRIMARY KEY (`id`))', Adapter::QUERY_MODE_EXECUTE);
        $this->adapter->query('CREATE TABLE IF NOT EXISTS `' . $this->routesTable . '` (`route` varchar(255) NOT NULL, `sitemap_id` int(11) NOT NULL, `item_id` int(11) NOT NULL, PRIMARY KEY (`route`))', Adapter::QUERY_MODE_EXECUTE);

        $dataArray = array(
            'model'  => $this->model->getTableName(),
            'parent' => $parent
        );

        $statement = $this->sql->insert($this->sitemapTable);
        $statement->values($dataArray);
        $sqlString = $this->sql->getSqlStringForSqlObject($statement);
        $this->adapter->query($sqlString, Adapter::QUERY_MODE_EXECUTE);
        $this->lastInsertValue = $this->adapter->getDriver()->getConnection()->getLastGeneratedValue();
    }
}