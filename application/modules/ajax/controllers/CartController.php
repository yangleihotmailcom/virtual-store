<?php

/**
 * Add/Delete/Update shopping cart
 * @author lei
 *
 */
class Ajax_CartController extends Zend_Controller_Action
{

    public function init()
    {
    	$this->_helper->layout->disableLayout(); 
    	$contextSwitch = $this->_helper->getHelper('contextSwitch');
    	$contextSwitch->addActionContext('additem', 'json')
    	              ->addActionContext('removeitem', 'json')
    	              ->addActionContext('updateitem', 'json')    	              
    	              ->addActionContext('emptycart', 'json')    	              
    	              ->addActionContext('checkout', 'json')    	              
                	  ->initContext();
    	
    }

    public function indexAction()
    {
        // action body
    }

    
    public function additemAction()
    {
    	$request = $this->getRequest();
    	$this->view->result = false;
    	
    //	if ( ! $request->isPost() ) return;
    	$asin = $request->getParam('asin', '');
    	$qty  = $request->getParam('qty' , 1);
    	if ( $asin == '' ) return ;

    	$common = new Model_Common();
    	$common->initBrowser();
        $userId = $common->getUserId();
        $browserId = $common->getBrowserId();
             	
    	$cartTable = new Default_Model_DbTable_Cart();
    	$select = $cartTable->select();
    	$select->where('browser_id = :browser_id and user_id = :user_id and asin = :asin' )
    	       ->bind(array( 'browser_id'=>$browserId, 'user_id' => $userId , 'asin' => $asin));
        $item = $cartTable->fetchAll($select)->toArray();

//        print_r($item);
        if ( count( $item ) > 0 )
        {
        	$adapter = $cartTable->getAdapter();
        	$sql = <<<SQL
        	   update cart
        	   set qty = :qty
        	   where browser_id = :browser_id and user_id = :user_id and asin = :asin
SQL;
        	$qty += $item[0]['qty'];
        	$adapter->query($sql, array("qty"=>$qty,"browser_id" => $browserId, "user_id" => $userId, "asin"=>$asin ));
        }
        else 
       {
        	$producTable = new Default_Model_DbTable_Products();
        	$item = $producTable->find($asin)->toArray();  //print_r($item);
        	
        	$offerTable = new Default_Model_DbTable_Offers();
        	$select = $offerTable->select()->where('key_asin=?' , $asin );
        	$offer = $offerTable->fetchAll($select)->toArray(); //print_r($offer); 
        	
        	if ( count($item) == 0 || ( $item[0]['Amount'] == '' && count($offer) == 0 ) ) return ;
        	
        	$listPrice = $item[0]['Amount'];
        	$amazonPrice = '';
        	$offerListingId = '';
        	if ( count($offer) > 0 ) 
        	{
        		$offer[0]['OfferListingId'];
        		$amazonPrice = $offer[0]['Price'];
        	}
        	
        	if ( $listPrice == '' && $amazonPrice == '' ) return;
        	if ( $listPrice == '' ) $listPrice = PHP_INT_MAX;
        	if ( $amazonPrice == '' ) $amazonPrice = PHP_INT_MAX;        	
        	$price = min($listPrice, $amazonPrice);
        	
        	$data = array('browser_id' => $browserId,
        			       'user_id' => $userId,
        			       'asin' => $asin,
        			       'offerListingId' => $offerListingId ,
        			       'title' => $item[0]['Title'],
        			       'qty' => $qty,
        			       'price' => $price,
        			      );
   			$cartTable->insert($data);
        }
        $this->view->result = true ;
        $this->view->qty = $qty;
    }

    public function removeitemAction()
    {
    	$request = $this->getRequest();
    	$this->view->result = false;
    	 
    	//	if ( ! $request->isPost() ) return;
    	$cartID = $request->getParam('cartID', '');    	
    	$asin = $request->getParam('asin', '');
    	if ( $cartID == '' || $asin == '' ) return ;
    	 
    	$common = new Model_Common();
    	$common->initBrowser();
        $userId = $common->getUserId();
        $browserId = $common->getBrowserId();
    	
        $cartTable = new Default_Model_DbTable_Cart();
        $adapter = $cartTable->getAdapter();
        $sql = <<<SQL
        	   delete from cart
        	   where id = :id and browser_id = :browser_id and user_id = :user_id and asin = :asin
SQL;
        $adapter->query($sql, array("id" => $cartID, "browser_id" => $browserId, "user_id" => $userId, "asin"=>$asin ));
        $this->view->result = true ;
    }

    public function updateitemAction()
    {
    	$request = $this->getRequest();
    	$this->view->result = false;
    	 
    	//	if ( ! $request->isPost() ) return;
    	$cartID = $request->getParam('cartID', '');    	
    	$asin = $request->getParam('asin', '');
    	$qty  = $request->getParam('qty' , 1);
    	if ( $cartID == '' || $asin == '' ) return ;
    	 
    	$common = new Model_Common();
    	$common->initBrowser();
        $userId = $common->getUserId();
        $browserId = $common->getBrowserId();
    	    	    	 
    	$cartTable = new Default_Model_DbTable_Cart();
    	$adapter = $cartTable->getAdapter();
    	$sql = <<<SQL
        	   update cart
        	   set qty = :qty
        	   where id = :id and browser_id = :browser_id and user_id = :user_id and asin = :asin
SQL;
    	$adapter->query($sql, array("qty"=>$qty, "id" => $cartID, "browser_id" => $browserId, "user_id" => $userId, "asin"=>$asin ));
    	$this->view->result = true ;
    }

    public function popupAction()
    {
    	$cartTable = new Default_Model_DbTable_Cart();
        $this->view->results = $cartTable->getShoppingCart($this->getRequest());
    }

    public function emptycartAction()
    {
    	$request = $this->getRequest();
    	$this->view->result = false;
    	
    	//	if ( ! $request->isPost() ) return;
    	$common = new Model_Common();
    	$common->initBrowser();
        $userId = $common->getUserId();
        $browserId = $common->getBrowserId();
    	    	
    	$cartTable = new Default_Model_DbTable_Cart();
    	$adapter = $cartTable->getAdapter();
    	$sql = <<<SQL
        	   delete from cart
        	   where browser_id = :browser_id and user_id = :user_id
SQL;
    	$adapter->query($sql, array("browser_id" => $browserId, "user_id" => $userId));
    	$this->view->result = true ;
    	 
    }

    public function checkoutAction()
    {
    	$request = $this->getRequest();
    	$this->view->valid = false;
    	$this->view->needLogin = true ;
    	$this->view->cart = null;
 //   	if ( ! $request->isXmlHttpRequest() ) return;
    	$this->view->valid = true;
    	$auth = Zend_Auth::getInstance();
    	if ( ! $auth->hasIdentity() ) return;
    	$this->view->needLogin = false ;    	
    	$amazon = new Model_Amazon();
    	
    	$cart = $amazon->createCart($request);
//    	$this->view->cart = $amazon->createCart();

    	$purchaseURL = '';
    	if ( ! is_null($cart) )
    	{	
    	   $purchaseURL = $cart->purchaseURL();
    	   $errors = $cart->errors();
    	}   
    	$result = array('purchaseURL' => $purchaseURL,
    			         'errors' => $errors );

    	$this->view->cart = $result;
    }


}













