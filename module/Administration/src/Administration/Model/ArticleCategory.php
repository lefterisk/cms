<?php 
namespace Administration\Model;

use Administration\Helper\Model\TableHandler;
use Administration\Helper\Model\RelationsHandler;

class ArticleCategory  extends TableHandler
{

    public function __construct($followRelations = true, $controlPanel)
    {
        parent::__construct('ArticleCategory');//<--Table name

        $this->setListingFields(array("title"));
        $this->setMaximumTreeDepth(4);
        $this->setListingSwitches(array());
        $this->setPrefix("article_category_");
        $this->setFollowRelations($followRelations);
        $this->setIsStandAlonePage(true);

        //Fields
		$this->setDates(array());
		$this->setEnums(array());
		$this->setVarchars(array());
		$this->setTexts(array());
		$this->setLongTexts(array());
		$this->setIntegers(array('sortOrder'));
		$this->setImages(array());
		$this->setFiles(array());
		$this->setMultilingualVarchars(array('title'));
		$this->setMultilingualTexts(array());
		$this->setMultilingualLongTexts(array());
		$this->setMultilingualFiles(array());
//		$this->setRequiredFields(array());
//		$this->setMultilingualRequiredFields(array());
		//$this->setRelations(array($userGroup = new RelationsHandler('UserGroup','manyToMany','name','UserToUserGroups')));
        $this->setRelations(array($article = new RelationsHandler('Article','manyToMany','title','ArticleToArticleCategory')));
        //$this->setRelations(array($userGroup = new RelationsHandler('UserGroup','manyToOne','name')));
        //$this->finaliseTable();
//		$this->setMetaTitle();
//		$this->setMetaDescription();
//		$this->setMetaKeywords();

    }
}