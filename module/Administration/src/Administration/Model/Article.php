<?php 
namespace Administration\Model;

use Administration\Helper\Model\TableHandler;
use Administration\Helper\Model\RelationsHandler;

class Article  extends TableHandler
{

    public function __construct($followRelations = true, $controlPanel)
    {
        parent::__construct('Article');//<--Table name

        $this->setListingFields(array("title"));
        $this->setListingSwitches(array("status"));
        $this->setPrefix("article_");
        $this->setFollowRelations($followRelations);
        echo '111<br/><br/>';   
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
		$this->setRelations(array(
            $articleCategory = new RelationsHandler('ArticleCategory','manyToMany','title','ArticleToArticleCategory'),
            $articleAuthor   = new RelationsHandler('User','manyToOne','email')
        ));
        //$this->finaliseTable();
//		$this->setMetaTitle();
//		$this->setMetaDescription();
//		$this->setMetaKeywords();

    }
}