<?php
namespace Administration\Helper\Model;

use Zend\Db\TableGateway\Exception;

class CustomSelectionHandler
{
    protected $isMultiple;
    protected $options;
    protected $lookUpTable;
    protected $fieldName;

    public function __construct($fieldName, $options = array(), $isMultiple = false, $lookUpTable = null)
    {
        $this->fieldName  = $fieldName;
        $this->isMultiple = $isMultiple;
        if (is_array($options)) {
            $this->setOptions($options);
        } else {
            throw new Exception\InvalidArgumentException( 'Options must be an Array');
        }
        if ($this->isMultiple) {
            if (!empty($lookUpTable)) {
                $this->lookUpTable = $lookUpTable;
            } else {
                throw new Exception\InvalidArgumentException( 'For multiple Custom Select you need to specify a lookup table!');
            }
        }
    }

    public function setOptions(Array $options)
    {
        $this->options = $options;
    }

    public function isMultiple()
    {
        return $this->isMultiple;
    }

    public function getSelectOptions()
    {
        return $this->options;
    }

    public function getLookUpTableName()
    {
        if ($this->isMultiple) {
            return $this->lookUpTable;
        } else {
            throw new Exception\InvalidArgumentException( 'This is not a multiple custom select it doesnt have a lookuptable!');
        }
    }

    public function getFieldName()
    {
        return $this->fieldName;
    }
}