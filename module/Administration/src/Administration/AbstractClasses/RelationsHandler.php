<?php
namespace Administration\AbstractClasses;

use Zend\Db\TableGateway\Exception;

class RelationsHandler
{
    protected $relationTypes = array('oneToMany', 'manyToOne', 'manyToMany');
    protected $typeOfRelation;
    protected $relatedToModel;

    public function __construct($model, $typeOfRelation)
    {
        $this->setIfValidRelationType($typeOfRelation);
        $this->relatedToModel = $model;
    }

    protected function setIfValidRelationType($typeOfRelation)
    {
        if (in_array($typeOfRelation , $this->relationTypes)) {
            $this->typeOfRelation = $typeOfRelation;
        } else {
            throw new Exception\InvalidArgumentException('This is not a valid Relation Type!');
        }
    }

    public function hasLookUpTable()
    {
        if ($this->typeOfRelation = 'manyToOne' || $this->typeOfRelation = 'manyToMany') {
            return true;
        } else {
            return false;
        }
    }

    public function getRelatedModel()
    {
        return $this->relatedToModel;
    }
}