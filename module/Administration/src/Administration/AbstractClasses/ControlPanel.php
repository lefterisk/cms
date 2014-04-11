<?php
namespace Administration\AbstractClasses;

use Zend\Db\Adapter\Adapter;
use Zend\Db\TableGateway\Exception;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Sql;
use Zend\Crypt\Password\Bcrypt;
use Zend\Db\Sql\Expression;




class ControlPanel
{
    private $adapter;
    private $sql;
    private $sessionTable = 'userSession';
    private $userTable    = 'user';
    protected $siteLanguagesArray  = array();
    protected $adminLanguagesArray = array();
    protected $logged = false;

    public function __construct($adapter)
    {
        $this->adapter = $adapter;
        $this->sql     = new Sql($this->adapter);
        $this->clearSessionTable();
        $this->initialiseSiteLanguages();
        //$this->initialiseSiteLanguages();
        $this->initialiseAdminLoginCheck();
    }

    private function initialiseAdminLoginCheck()
    {
        if (isset($_SESSION['sessionHash'])) {
            $userId = $this->existsInSessionTable($_SESSION['sessionHash']);
            if ($userId) {
                $user = $this->existsAndIsActiveUser($userId);
                if ($user) {
                    $this->logged = true;
                    die('loged');
                }
            }
        }
    }

    private function existsAndIsActiveUser ($userId)
    {
        $statement    = $this->sql->select($this->userTable)->where(array('id' => $userId , 'status' => '1'));
        $selectString = $this->sql->getSqlStringForSqlObject($statement);
        $result       = $this->adapter->query($selectString, Adapter::QUERY_MODE_EXECUTE)->current();

        if ($result) {
            return $result;
        }
        return false;

    }

    private function existsInSessionTable($sessionHash)
    {
        $statement    = $this->sql->select($this->sessionTable)->where(array('session_hash' => $sessionHash));
        $selectString = $this->sql->getSqlStringForSqlObject($statement);
        $result       = $this->adapter->query($selectString, Adapter::QUERY_MODE_EXECUTE)->current();

        if ($result) {
            return $result->user_id;
        }
        return false;
    }

    private function clearSessionTable()
    {
        $statement = $this->sql->delete($this->sessionTable)->where(array(new \Zend\Db\Sql\Predicate\Expression("session_time_out < NOW() ")));
        $sqlString = $this->sql->getSqlStringForSqlObject($statement);
        $this->adapter->query($sqlString, Adapter::QUERY_MODE_EXECUTE);
    }

    private function  initialiseAdminLanguages()
    {
        $statement    = $this->sql->select('AdminLanguage')->where(array('status' => '1'));
        $selectString = $this->sql->getSqlStringForSqlObject($statement);
        $results      = $this->adapter->query($selectString, Adapter::QUERY_MODE_EXECUTE);
        if (count($results) > 0) {
            foreach ($results as $language) {
                $this->adminLanguagesArray[$language['id']] = array(
                    'name'    => $language['name'],
                    'code'    => $language['code'],
                    'image'   => $language['image'],
                    'default' => $language['default']
                );
            }
        } else {
            throw new Exception\InvalidArgumentException('Something is wrong with your site setup. No Site Languages are detected!');
        }
    }

    private function  initialiseSiteLanguages()
    {
        $statement    = $this->sql->select('SiteLanguage')->where(array('status' => '1'));
        $selectString = $this->sql->getSqlStringForSqlObject($statement);
        $results      = $this->adapter->query($selectString, Adapter::QUERY_MODE_EXECUTE);
        if (count($results) > 0) {
            foreach ($results as $language) {
                $this->siteLanguagesArray[$language['id']] = array(
                    'name'    => $language['name'],
                    'code'    => $language['code'],
                    'image'   => $language['image'],
                    'default' => $language['default']
                );
            }
        } else {
            throw new Exception\InvalidArgumentException('Something is wrong with your site setup. No Site Languages are detected!');
        }
    }

    public function isUserLogged()
    {
        return $this->logged;
    }

    public function getSiteLanguages()
    {
        return $this->siteLanguagesArray;
    }

    public function getDefaultSiteLanguageId()
    {
        foreach ($this->getSiteLanguages() as $key => $language) {
            if ($language['default'] == '1') {
                return $key;
            }
        }
        //if no default language is detected then throw exception
        throw new Exception\InvalidArgumentException('Something is wrong with your site setup. No Default Site Language was detected!');
    }

    public function getAdminLanguages()
    {
        return $this->adminLanguagesArray;
    }

    public function getDefaultAdminLanguageId()
    {
        foreach ($this->getAdminLanguages() as $key => $language) {
            if ($language['default'] == '1') {
                return $key;
            }
        }
        //if no default language is detected then throw exception
        throw new Exception\InvalidArgumentException('Something is wrong with your site setup. No Default Admin Language was detected!');
    }

    public function attemptAdminLogin($email, $password)
    {
        if (!empty($email) && !empty($password)) {
            $statement    = $this->sql->select('User')->where(array('email' => $email, 'status' => '1'));
            $selectString = $this->sql->getSqlStringForSqlObject($statement);
            $result       = $this->adapter->query($selectString, Adapter::QUERY_MODE_EXECUTE)->current();

            if ($result && $this->verifyPassword($password, $result->password)) {

                if (!isset($_SESSION['sessionHash'])) {

                    $_SESSION['sessionHash'] = md5($result->email);

                    $date = date("Y-m-d H:i:s",strtotime(date("Y-m-d H:i:s")." +10 minutes"));
                    $statement = $this->sql->insert($this->sessionTable);
                    $statement->values(array('session_hash' => md5($result->email), 'user_id' =>  $result->id, 'session_time_out' => $date));
                    $sqlString = $this->sql->getSqlStringForSqlObject($statement);
                    $this->adapter->query($sqlString, Adapter::QUERY_MODE_EXECUTE);
                }
            }
        }
    }

    private function verifyPassword($suppliedPassword, $storedPassword)
    {
        $bcrypt = new Bcrypt();
        return $bcrypt->verify($suppliedPassword, $storedPassword);
    }
}

