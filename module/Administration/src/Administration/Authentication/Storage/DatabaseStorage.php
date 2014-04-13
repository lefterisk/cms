<?php
namespace Administration\Authentication\Storage;

use Zend\Authentication\Storage\StorageInterface;
use Zend\Db\Adapter\Adapter as DbAdapter;
use Zend\Db\Sql\Sql;


class DatabaseStorage implements StorageInterface
{
    protected $dbAdapter;
    protected $sql;
    protected $tableName;
    protected $identityColumn;
    protected $currentIdentity = null;

    /**
     * Sets db storage options
     *
     * @param DbAdapter $dbAdapter
     * @param  string $tableName
     */
    public function __construct(DbAdapter $dbAdapter, $tableName, $identityColumn)
    {
        $this->dbAdapter = $dbAdapter;
        $this->sql       = new Sql($this->dbAdapter);

        if (null !== $tableName) {
            $this->tableName = $tableName;
        }

        if (null !== $identityColumn) {
            $this->identityColumn = $identityColumn;
        }

        $this->maintainSessionTable();
    }

    private function maintainSessionTable()
    {
        $this->dbAdapter->query('CREATE TABLE IF NOT EXISTS `' . $this->tableName . '` (`' . $this->identityColumn . '` varchar(255) NOT NULL, `session_time_out` DATETIME)', DbAdapter::QUERY_MODE_EXECUTE);
        $statement = $this->sql->delete($this->tableName)->where(array(new \Zend\Db\Sql\Predicate\Expression("session_time_out < NOW() ")));
        $sqlString = $this->sql->getSqlStringForSqlObject($statement);
        $this->dbAdapter->query($sqlString, DbAdapter::QUERY_MODE_EXECUTE);
    }

    /**
     * Returns true if and only if storage is empty
     *
     * @return bool
     */
    public function isEmpty()
    {

    }

    /**
     * Returns the contents of storage
     * Behavior is undefined when storage is empty.
     *
     * @return mixed
     */
    public function read()
    {

    }

    /**
     * Writes $contents to storage
     *
     * @param  mixed $contents
     * @return void
     */
    public function write($contents)
    {
        $date = date("Y-m-d H:i:s",strtotime(date("Y-m-d H:i:s")." +10 minutes"));

        if ($this->existsInStorage($contents)) {
            $statement = $this->sql->update($this->tableName);
            $statement->set(array('session_time_out' => $date));
            $statement->where(array($this->identityColumn => $contents));
        } else {
            $statement = $this->sql->insert($this->tableName);
            $statement->values(array($this->identityColumn => $contents, 'session_time_out' => $date));
        }

        $sqlString = $this->sql->getSqlStringForSqlObject($statement);
        $this->dbAdapter->query($sqlString, DbAdapter::QUERY_MODE_EXECUTE);
    }

    public function existsInStorage($identity)
    {
        $statement    = $this->sql->select($this->tableName)->where(array($this->identityColumn => $identity));
        $selectString = $this->sql->getSqlStringForSqlObject($statement);
        $result       = $this->dbAdapter->query($selectString, DbAdapter::QUERY_MODE_EXECUTE)->current();
        return $result;
    }

    public function setCurrentIdentity($identity)
    {
        $this->currentIdentity = $identity;
    }

    /**
     * Clears contents from storage
     *
     * @return void
     */
    public function clear()
    {
        if ($this->currentIdentity) {
            $statement = $this->sql->delete($this->tableName)->where(array($this->identityColumn => $this->currentIdentity));
            $sqlString = $this->sql->getSqlStringForSqlObject($statement);
            $this->dbAdapter->query($sqlString, DbAdapter::QUERY_MODE_EXECUTE);
        }
    }
}
