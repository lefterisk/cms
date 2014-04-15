<?php
namespace Administration\Helper\Model;

use Zend\Db\TableGateway\Exception;

class RelationsHandler
{
    protected $relationTypes = array('oneToMany', 'manyToOne', 'manyToMany');
    protected $typeOfRelation;
    protected $relatedToModel;
    protected $lookUpTableName;
    protected $relatedSelectDisplayFields;

    /*
     * Input: Related Model ,
     * type of relation can be 'oneToMany', 'manyToOne', 'manyToMany',
     * fields to show from related model on auto-generated select can be field or array of fields
     */
    public function __construct($model, $typeOfRelation, $selectBoxDisplayFields = null, $lookupTableName = null)
    {
        $this->setIfValidRelationType($typeOfRelation);
        if ($this->hasLookUpTable()) {
            $this->setLookUpTableName($lookupTableName);
        }
        $this->relatedToModel = $model;
        $this->relatedSelectDisplayFields = $selectBoxDisplayFields;
    }

    protected function setIfValidRelationType($typeOfRelation)
    {
        if (in_array($typeOfRelation , $this->relationTypes)) {
            $this->typeOfRelation = $typeOfRelation;
        } else {
            throw new Exception\InvalidArgumentException( '"' . $typeOfRelation . '" is not a valid Relation Type!');
        }
    }

    protected function setLookUpTableName($lookupTableName)
    {
        if (!empty($lookupTableName)) {
            $this->lookUpTableName = $lookupTableName;
        } else {
            throw new Exception\InvalidArgumentException( 'A Lookup Table Name is necessary for this Relation!');
        }
    }

    public function hasLookUpTable()
    {
        if ($this->typeOfRelation == 'manyToMany') {
            return true;
        } else {
            return false;
        }
    }

    public function hasLookupColumn()
    {
        if ($this->typeOfRelation == 'manyToOne') {
            return true;
        } else {
            return false;
        }
    }

    public function getRelationType()
    {
        return $this->typeOfRelation;
    }

    public function getRelatedSelectDisplayFields()
    {
        return $this->relatedSelectDisplayFields;
    }

    public function getRelatedModel()
    {
        return $this->relatedToModel;
    }

    public function getLookUpTableName()
    {
        return $this->lookUpTableName;
    }
}