<?php
namespace Administration\Helper\Model;

use Administration\Helper\General\ControlPanel;
use Zend\Db\Adapter\Adapter;
use Zend\Db\TableGateway\Exception;
use Zend\Db\Sql\Select;

class SiteManagerTableGateway
{
    //Db adapter
    public  $adapter;
    //Sql interface
    public  $sql;
    //Control object contains languages admin-rights etc
    public  $controlPanel;

    public $sitemapTable = 'Sitemap';
    public $routesTable  = 'Routes';

    public function __construct(ControlPanel $controlPanel)
    {
        $this->controlPanel = $controlPanel;
        $this->adapter      = $this->controlPanel->getDbAdapter();
        $this->sql          = $this->controlPanel->getSQL();
        $this->buildTablesIfNotExist();
    }

    private function buildTablesIfNotExist()
    {
        $this->adapter->query('CREATE TABLE IF NOT EXISTS `' . $this->sitemapTable . '` (`id` int(11) unsigned NOT NULL AUTO_INCREMENT, `model`  VARCHAR( 255 ) NOT NULL, `parent` int(11) NOT NULL DEFAULT "0", PRIMARY KEY (`id`))', Adapter::QUERY_MODE_EXECUTE);
        $this->adapter->query('CREATE TABLE IF NOT EXISTS `' . $this->routesTable . '` (`route` varchar(255) NOT NULL, `sitemap_id` int(11) NOT NULL, `item_id` int(11) NOT NULL, PRIMARY KEY (`route`))', Adapter::QUERY_MODE_EXECUTE);
    }

    public function getSiteMap($parent = 0, $depth = 0)
    {
        $statement    = $this->sql->select($this->sitemapTable);
        $statement->where(array('parent'=> $parent));
        $selectString = $this->sql->getSqlStringForSqlObject($statement);
        $results      = $this->adapter->query($selectString, Adapter::QUERY_MODE_EXECUTE);
        $siteMapArray = array();
        foreach ($results as $result) {
            $result['depth'] = $depth;
            $result['related'] = array();
            $entity = $this->controlPanel->instantiateModel($result['model'],true);
            if ($entity) {
                foreach ($entity->getModel()->getRelations() as $relation) {
                    if ($relation->activeModel->isStandAlonePage()) {
                        $result['related'][] = $relation->getRelatedModel();
                    }
                }
            }
            $siteMapArray[]  = $result;
            $siteMapArray    = array_merge($siteMapArray, $this->getSiteMap($result['id'], $depth+1));
        }
        return $siteMapArray;
    }
}