<?php 
namespace Administration\Model;

use Administration\Helper\Model\TableHandler;
use Administration\Helper\Model\RelationsHandler;

class Article  extends TableHandler
{

    public function __construct($controlPanel, $followRelations = true)
    {
        parent::__construct('Article', $controlPanel);//<--Table name

        $this->setListingFields(array("title"));
        $this->setListingSwitches(array("status"));
        $this->setPrefix("article_");
        $this->setFollowRelations($followRelations);

        //Fields
		$this->setDates(array());
		$this->setEnums(array('status'));
		$this->setVarchars(array());
		$this->setTexts(array());
		$this->setLongTexts(array());
		$this->setIntegers(array('sortOrder'));
		$this->setImages(array());
		$this->setFiles(array());
		$this->setMultilingualVarchars(array('title'));
		$this->setMultilingualTexts(array('shortDescription'));
		$this->setMultilingualLongTexts(array('longDescription'));
		$this->setMultilingualFiles(array());
//		$this->setRequiredFields(array());
//		$this->setMultilingualRequiredFields(array());
		$this->setRelations(array($articleCategory = new RelationsHandler('ArticleCategory','manyToMany','title','ArticleToArticleCategory')));
        //$this->setRelations(array($userGroup = new RelationsHandler('UserGroup','manyToOne','name')));
        $this->finaliseTable();
//		$this->setMetaTitle();
//		$this->setMetaDescription();
//		$this->setMetaKeywords();

    }
}