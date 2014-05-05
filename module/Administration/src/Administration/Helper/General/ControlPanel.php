<?php
namespace Administration\Helper\General;

use Zend\Db\Adapter\Adapter;
use Zend\Db\TableGateway\Exception;
use Zend\Db\Sql\Sql;
use Zend\Session\Container;
use Zend\Code\Scanner\DirectoryScanner;
use Administration\Helper\Model\GenericModelTableGateway;

class ControlPanel
{
    protected $adapter;
    protected $sql;
    protected $auth;
    protected $siteLanguagesArray  = array();
    protected $adminLanguagesArray = array();
    protected $modelPath           = 'Administration\\Model\\';
    //Models that should not appear in any list
    protected $hiddenModels        = array();
    //Developer ToolBox Models
    protected $devToolsModels      = array('AdminLanguage','SiteLanguage','User', 'UserGroup');
    //Support ToolBox Models
    protected $supportToolsModels  = array();

    public function __construct($adapter, $authentication)
    {
        $this->adapter = $adapter;
        $this->auth    = $authentication;
        $this->sql     = new Sql($this->adapter);
    }

    /*
     * Get Site Languages
     * returns Array or throws Exception
     */
    public function getSiteLanguages()
    {
        if (count($this->siteLanguagesArray) <= 0 ) {
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

        return $this->siteLanguagesArray;
    }

    /*
     * Get Default Site Language ID
     * returns ID (pk of SiteLanguage table)
     */
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

    /*
     * Get Default Site Language Locale
     * returns iso code
     */
    public function getDefaultSiteLocale()
    {
        foreach ($this->getSiteLanguages() as $language) {
            if ($language['default'] == '1') {
                return $language['code'];
            }
        }
        //if no default language is detected then throw exception
        throw new Exception\InvalidArgumentException('Something is wrong with your site setup. No Default Site Language was detected!');
    }

    /*
     * Get Admin Languages
     * returns Array or throws Exception
     */
    public function getAdminLanguages()
    {
        if (count($this->adminLanguagesArray) <= 0 ) {
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
                throw new Exception\InvalidArgumentException('Something is wrong with your site setup. No Admin Languages are detected!');
            }
        }

        return $this->adminLanguagesArray;
    }

    /*
     * Get Default Admin Language ID
     * returns ID (pk of AdminLanguage table)
     */
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

    /*
     * Get Default Admin Language Locale
     * returns iso code
     */
    public function getDefaultAdminLocale()
    {
        foreach ($this->getAdminLanguages() as $language) {
            if ($language['default'] == '1') {
                return $language['code'];
            }
        }
        //if no default language is detected then throw exception
        throw new Exception\InvalidArgumentException('Something is wrong with your site setup. No Default Admin Language was detected!');
    }

    public function isValidAdminLocale($locale) {
        foreach ($this->getAdminLanguages() as $language) {
            if ($language['code'] == $locale) {
                return true;
            }
        }
        return false;
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

    /*
     * Set Models to hide from all menus and lists
     */
    public function setHiddenModels(Array $models)
    {
        $this->hiddenModels = $models;
    }

    /*
     * Get Models to hide from all menus and lists
     */
    public function getHiddenModels()
    {
        return $this->hiddenModels;
    }

    /*
     * Set Models to appear in the left
     * Column Developer-tools box regardless of access
     */
    public function setDevToolsModels(Array $models)
    {
        $this->devToolsModels = $models;
    }

    /*
     * Get Models to appear in the left
     * Column Developer-tools box regardless of access
     */
    public function getDevToolsModels()
    {
        return $this->devToolsModels;
    }

    /*
     * Set Models to appear in the left
     * Column Support-tools box regardless of access
     */
    public function setSupportToolsModels(Array $models)
    {
        $this->supportToolsModels = $models;
    }

    /*
     * Get Models to appear in the left
     * Column Support-tools box regardless of access
     */
    public function getSupportToolsModels()
    {
        return $this->supportToolsModels;
    }

    /*
     * Get Models to appear in the left
     * Content Management box regardless of access
     */
    public function getContentModels()
    {
        return array_diff(
            $this->getExistingModelsArray(),
            array_merge(
                $this->getDevToolsModels(),
                $this->getSupportToolsModels(),
                $this->getHiddenModels()
            )
        );
    }

    /*
     * Get Models to appear in the left
     * Content Management box for current access level
     */
    public function getContentBoxModels()
    {
        return array_intersect(
            $this->getPermittedModelsForUser(),
            $this->getContentModels()
        );
    }

    /*
     * Get Models to appear in the left
     * Developer Tool box for current access level
     */
    public function getDeveloperBoxModels()
    {
        return array_intersect(
            $this->getPermittedModelsForUser(),
            $this->getDevToolsModels()
        );
    }

    /*
     * Get Models to appear in the left
     * Support & help box for current access level
     */
    public function getSupportBoxModels()
    {
        return array_intersect(
            $this->getPermittedModelsForUser(),
            $this->getSupportToolsModels()
        );
    }

    /*
     * Returns all available models except
     * the hidden ones (returned by getHiddenModels)
     */
    public function getExistingModelsArray()
    {
        if (!isset($this->existingModels)) {
            $scanner = new DirectoryScanner(__DIR__ . '/../../Model/');
            $models  = array();
            foreach ($scanner->getClassNames() as $fullName) {
                $explodedNameArray = explode('\\', $fullName);
                $modelName = array_pop($explodedNameArray);
                if (!in_array($modelName, $this->getHiddenModels())) {
                    $models[] = $modelName;
                }
            }
            $this->existingModels = $models;
        }
        return $this->existingModels;
    }

    /*
     * Returns all models that the current
     * user is permitted to access
     */
    public function getPermittedModelsForUser()
    {
        if (!isset($this->permittedModels)) {
            if ($this->auth->hasIdentity()) {
                $user = $this->auth->getIdentity();
                if ($user['user_group_id']) {
                    $userGroupModel        = $this->instantiateModel('UserGroup');
                    $group                 = $userGroupModel->getItemById($user['user_group_id']);
                    $this->permittedModels = array_merge($this->hiddenModels, $group['group_view_permission']);
                } else {
                    $this->permittedModels = array();
                }
            } else {
                $this->permittedModels = array();
            }
        }
        //Login is available to all users
        return $this->permittedModels;
    }

    /*
     * Instantiate an existing model
     */
    public function instantiateModel($model, $followRelations = true)
    {
        if (in_array(ucfirst($model), $this->getExistingModelsArray())) {
            $modelName    = $this->modelPath . ucfirst($model);
            $entityModel  = new $modelName($followRelations, $this);
            $tableGateway = new GenericModelTableGateway($entityModel, $this);
            return $tableGateway;
        } else {
            return false;
        }
    }

    /*
     * Instantiate an existing model only
     * if the user is permitted to access it
     */
    public function instantiateModelForUser($model, $followRelations = true)
    {
        if (in_array(ucfirst($model), $this->getPermittedModelsForUser())) {
            $modelName    = $this->modelPath . ucfirst($model);
            $entityModel  = new $modelName($followRelations, $this);
            $tableGateway = new GenericModelTableGateway($entityModel, $this);
            return $tableGateway;
        } else {
            return false;
        }
    }
}