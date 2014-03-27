<?php
namespace Administration\AbstractClasses;

use Zend\Db\TableGateway\Exception;

class RelationsHandler
{
    protected $relationTypes = array('oneToMany', 'manyToOne', 'manyToMany');
    protected $typeOfRelation;
    protected $relatedToModel;
    protected $relatedSelectDisplayFields;

    /*
     * Input: Related Model ,
     * type of relation can be 'oneToMany', 'manyToOne', 'manyToMany',
     * fields to show from related model on auto-generated select can be field or array of fields
     */
    public function __construct($model, $typeOfRelation, $selectBoxDisplayFields = null)
    {
        $this->setIfValidRelationType($typeOfRelation);
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
}