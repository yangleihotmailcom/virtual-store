<?php
/**
 * Search form
 * @author lei
 *
 */
class Default_Form_Search extends Zend_Form
{

    public function init()
    {
        $this->setMethod('get');
        $this->setAction("/default/search/index");
        $this->setName('searchform');
        
        $categories = $this->createElement("select", 'category')
                           ->setLabel('Category:');
        $options = array();
        
        foreach( Model_Amazon::$categories as $value ) $options[$value] = $value; 
        $categories->addMultiOptions( $options );
        
        $keyword = $this->createElement('text', 'keyword')
                        ->setLabel('Keyword:')  
                        ->setRequired(true);
        
        //$submit = $this->createElement('submit', 'Go')->setIgnore(true);
        
        $this->addElements(array($categories,$keyword));//,$submit));
    }


}

