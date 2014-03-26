<?php
/**
 * Home page
 * @author lei
 *
 */
class Default_IndexController extends Zend_Controller_Action
{

    public function init()
    {
    	if ( PHP_SAPI == 'cli' )
    	{
           $this->_helper->layout->disableLayout();
           $this->_helper->viewRenderer->setNoRender();
           return;
    	}
    }

    public function indexAction()
    {
    	$this->view->headTitle('Home Page', 'PREPEND');
    	$products = new Default_Model_DbTable_Products();

    	$categories = $products->getCategoiesGroup();
   	    $this->view->categories = $categories;
    }
    
    public function cartAction() 
    {
    	$this->view->headTitle('Shopping cart', 'PREPEND');    	
     	$cartTable = new Default_Model_DbTable_Cart();
    	$this->view->results = $cartTable->getShoppingCart($this->getRequest());
    	if ( count($this->view->results) == 0 ) $this->render('cartnoitem');
    }
    
    public function detailsAction() 
    {
    	$request = $this->getRequest();
    	$asin = $request->getParam('asin', '');
    	if ( strlen($asin) == 0 ) $this->_redirect('/');
    	$amazon = new Model_Amazon();
    	$productsTable = new Default_Model_DbTable_Products();
    	$adapter = $productsTable->getAdapter();
    	try {

    	   $product = $amazon->lookupItem($asin);

    	   if ( count( $product) == 0 )
    	   {
    	   	   $this->render('nodetails');
    	   	   return ; 
    	   }
    	   $sql = 'select * from Accessories where key_asin = :asin';
    	   $accessories = $adapter->query($sql, array('asin'=> $asin))->fetchAll();

//     	   $accessoryItems = array ();
//     	   foreach ( $accessories as $accessory )
//     	   {
//     	   	$temp = $amazon->lookupItem($accessory['asin']);
//     	   	if ( count($temp) > 0 ) $accessoryItems[] = $temp[0];
//     	   }
    	   
    	   
    	   $sql = 'select * from EditorialReviews where key_asin = :asin';
    	   $editorialReviews = $adapter->query($sql, array('asin'=> $asin))->fetchAll();
    	   
    	   $sql = 'select * from Feature where key_asin = :asin';
    	   $feature = $adapter->query($sql, array('asin'=> $asin))->fetchAll();
    	   
    	   $sql = 'select * from Offers where key_asin = :asin';
    	   $offers = $adapter->query($sql, array('asin'=> $asin))->fetchAll();
    	   
    	   $sql = 'select * from SimilarProducts where key_asin = :asin';
    	   $similars = $adapter->query($sql, array('asin'=> $asin))->fetchAll();
//     	   $similarItems = array ();
//     	   foreach ( $similars as $similar)
//     	   { 
//     	   	   $temp = $amazon->lookupItem($similar['asin']);
//     	   	   if ( count($temp) > 0 ) $similarItems[] = $temp[0];
//     	   }
    	   
    	   //print_r($similarItems); exit();
    	   
    	   $sql = 'select * from Tracks where key_asin = :asin';
    	   $tracks = $adapter->query($sql, array('asin'=> $asin))->fetchAll();
    		
    	   $common = new Model_Common();
    	   $common->initBrowser();
    	   $userId = $common->getUserId();
    	   $browserId = $common->getBrowserId();
        	   
    	   $sql = <<<SQL
    	    select *
    	    from cart
    	    where browser_id = :browser_id and user_id = :user_id and asin = :asin
SQL;
    	   $resultset = $adapter->query($sql, array('browser_id'=> $browserId,
    	   		'user_id' => $userId,
    	   		'asin' => $asin));
    	   $itemInCart = $resultset->fetchAll();
    	   
    	} catch (Exception $e) {
    		  throw $e;
    	}
    	
    	$this->view->product = $product;
    	$this->view->offer = $offers;
    	$this->view->accessories = $accessories;
    	$this->view->editorialReviews = $editorialReviews;
    	$this->view->feature = $feature;
    	$this->view->similars = $similars;
    	$this->view->tracks = $tracks;
    	
    	$this->view->itemInCart = $itemInCart;    	
    	
    }
    
    public function categoryAction()
    {
    	$request = $this->getRequest();
    	$category = $request->getParam('name', '');
    	if ( $category == '' ) $this->_redirect('/');
    	
    	$productTable = new Default_Model_DbTable_Products();
    	// New release
        $newRelease = $productTable->getItemsByCategory($category, 5);
        // Best Buy
        $bestBuy = $productTable->getItemsByCategory($category, 5);
        // special
        $special = $productTable->getItemsByCategory($category, 5);
        
        $recommend = $productTable->getItemsByCategory($category, 5);
        
        $results = array( 'New Release' => $newRelease,
                           'Best Buy' => $bestBuy,
                           'Special' => $special,
                           'Recommend' => $recommend,  
                         );
        $num = 0 ;
        foreach ( $results as $result ) {	
        	$num += count( $result);
        }   
        if ( $num == 0 ) $this->_redirect('/');

        $this->view->results = $results; 
        $this->view->categoryName = $category;
    }
    
    public function cartitemlistAction()
    {
       $this->_helper->layout->disableLayout();    	
       $request = $this->getRequest();
   	   // if ( ! $request->isXmlHttpRequest() ) exit();
       $cartTable = new Default_Model_DbTable_Cart();
       $this->view->results = $cartTable->getShoppingCart($this->getRequest());
    } 

    public function checkoutAction()
    {
    	$this->view->valid = false;
    	$this->view->cart = null;
    	$auth = new Zend_Auth();
    	if ( ! $auth->hasIdentity() ) return;
    	$this->view->valid = true ;    	
    	$amazon = new Model_Amazon();
    	$this->view->cart = $amazon->createCart();
//     	$purchaseURL = $cart->purchaseURL();
//     	if ( ! is_null($purchaseURL)) $this->_redirect($purchaseURL);
    }
    
    public function testAction()
    {
    	$amazon = new Model_Amazon();
    	$cart = $amazon->createCart();
    	$purchaseURL = $cart->purchaseURL();
    	if ( ! is_null($purchaseURL)) $this->_redirect($purchaseURL);
    }

}

