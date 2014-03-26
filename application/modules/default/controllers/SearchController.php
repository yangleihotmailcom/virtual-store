<?php
/**
 * Process search result
 * @author lei
 *
 */
class Default_SearchController extends Zend_Controller_Action
{

    public function init()
    {
    	
    }

    public function indexAction()
    {
         $request = $this->getRequest();
         
         $category = $request->getParam('category', '');
         $keyword  = $request->getParam('keyword','');
         $page     = $request->getParam('page','1');
         
         if ( $page > 10 ) $page = 10 ;

         if ( $category == '' ) $this->_redirect("/");
         $products = array();   // store asin 
         $results = array();    // set it to view
         
         $this->view->totalPages = 0;
         $this->view->totalResults = 0;
          
         if ( strlen($keyword) != 0 ) 
         {
         	$amazon = new Model_Amazon();
         	$amazonResults = $amazon->searchItem($category, $keyword, $page);
         	$amazon->updateMultipleItemsByAmazon($amazonResults);
         	foreach( $amazonResults as $product ) $products[]= $product->ASIN;

         	if ( count($products) == 0 )
         	{
         		$this->view->searchError = 'No item found, please try again.';
         	}
         	else 
         	{
	         	$productsObj = new Default_Model_DbTable_Products();
	         	$results = $productsObj->getItemsByAsins($products);
	         	$this->view->totalPages = $amazonResults->totalPages();
	         	$this->view->totalResults = $amazonResults->totalResults();
         	}
         } 
         else 
        {
         	$this->view->searchError = 'Please provide a valid keyword.';
         }
          
         $this->view->results = $results;
         $this->view->parameters = array("category"=>$category, 
         		                          "keyword"=> $keyword, 
         		                          "page" => $page);
//          $this->view->keyword = $keyword;
//          $this->view->category = $category;
//          $this->view->page = $page;
    }

}

