<?php
namespace Administration\Helper\General;

use Zend\Db\Adapter\Adapter;
use Zend\Db\TableGateway\Exception;
use Zend\Db\Sql\Sql;
use Zend\Session\Container;
use Zend\Code\Scanner\DirectoryScanner;

class ControlPanel
{
    protected $adapter;
    protected $sql;
    protected $auth;
    protected $siteLanguagesArray  = array();
    protected $adminLanguagesArray = array();
    protected $modelRootPath;


    public function __construct($adapter, $authentication)
    {
        $this->adapter = $adapter;
        $this->auth    = $authentication;
        $this->sql     = new Sql($this->adapter);
        $this->initialiseSiteLanguages();


        //$this->initialiseSiteLanguages();
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

    public function getAuthService()
    {
        return $this->auth;
    }

    public function getDbAdapter()
    {
        return $this->adapter;
    }

    public function getSQL()
    {
        return $this->sql;
    }

    public function getExistingModelsArray()
    {
        $scanner = new DirectoryScanner(__DIR__ . '/../../Model/');
        $models = array();
        foreach ($scanner->getClassNames() as $fullName) {
            $explodedNameArray = explode('\\', $fullName);
            $models[] = array_pop($explodedNameArray);
        }
        return $models;
    }
}