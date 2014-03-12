<?php
namespace Administration\AbstractClasses;

use Zend\Db\TableGateway\TableGateway;

class AbstractModelTable extends TableGateway
{
	protected $tableGateway;

    public function finaliseTable()
    {
        $statement = $this->adapter->query('SHOW TABLES LIKE "'.$this->getTable().'"');
        var_dump($statement->execute());

    }
}
