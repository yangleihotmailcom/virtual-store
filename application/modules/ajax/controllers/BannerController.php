<?php

class Ajax_BannerController extends Zend_Controller_Action
{

    public function init()
    {
       // $this->_helper->layout->disableLayout();
       // $this->_helper->viewRenderer->setNoRender();
    	$contextSwitch = $this->_helper->getHelper('contextSwitch');
    	$contextSwitch->addActionContext('home', 'json')
    	              ->addActionContext('category', 'json')    	
    	              ->addActionContext('category', 'json')
    	              ->addActionContext('getitem', 'json')
    	              ->initContext();
        return;
    }

    public function indexAction()
    {
        // action body
    }

    /**
     *  Banners for home page.
     */
    public function homeAction()
    {
    	$request = $this->getRequest(); 
        if ( ! $request->isXmlHttpRequest() ) return;
        $products = new Default_Model_DbTable_Products();
        
        $min = 5;
        $max = 20 ;
        
        $num = $request->getParam('num', $max);
        $num = max($min, min($max, $num));
        
        $category = $request->getParam('category', '');
        $banner = $products->getHomeBanner($num, $category);
        $this->view->results = $banner;
    }

    /**
     * Banners for categories pages
     */
    public function categoryAction()
    {

    	$request = $this->getRequest();
    	if ( ! $request->isXmlHttpRequest() ) return;
    	$products = new Default_Model_DbTable_Products();
    	
    	$categoryName = $request->getParam('categoryName', 'Book');
    	$num = $request->getParam('num', 5 );    	
    	
    	$items = $products->getItemsByCategory($categoryName, $num);
    	$this->view->results = $items;
    }

    
    public function cursorAction()
    {
    	$request = $this->getRequest();
        $direction = $request->getParam('direction','left');
        $width = $request->getParam('width',10);
        $height = $request->getParam('height',10);
        $color = $request->getParam('color','000000');
        
        if ( strlen($color) != 6 ) $color = '000000';
        $color = intval($color, 16);
        $blue = $color & 0xff ;
        $green = ($color & 0xff00) >> 8 ;
        $red = ($color & 0xff0000) >> 16 ;

        $img = imagecreatetruecolor($width, $height);
        $colorPoly = imagecolorallocate($img, $red, $green, $blue);
        $white = imagecolorallocate($img, 255, 255, 255);
        imagefill($img, 0, 0, $white);
        imagecolortransparent($img,$white);
        
        $points = array();
        switch ( $direction )
        {
        	case 'left' : $points[] = 0;
        	               $points[] = ($height >> 1)+2;
        	               
        	               $points[] = $width-1;
        	               $points[] = 4 ;
        	               
        	               $points[] = $width-1;
        	               $points[] = $height-1;
                           break;
        	case 'right': $points[] = $width;
                           $points[] = ($height >> 1)+2;
                           
                           $points[] = 0 ;
                           $points[] = 4 ;
                           
                           $points[] = 0;
                           $points[] = $height-1;
                           break;
        }
        
        header('Content-type: image/png');
        imagefilledpolygon($img, $points, 3, $colorPoly);                
        imagepng($img);
        imagedestroy($img);
        exit();
    }

    /**
     * Quickview for product details
     */
    public function quickviewAction()
    {
    	
    	$request = $this->getRequest();
//     	if ( ! $request->isXmlHttpRequest() ) return;
    	    	
    	$this->_helper->layout->disableLayout();

    	$asin = trim($request->getParam('asin' , ''));
    	if ( strlen($asin) == 0 ) return ;
    	
    	$productTable = new Default_Model_DbTable_Products();
    	$offerTable = new Default_Model_DbTable_Offers();
    	$cartTable = new Default_Model_DbTable_Cart();

    	$select = $productTable->select();
    	$select->where('asin= ?' , $asin);
    	$item = $productTable->fetchAll($select)->toArray();
    	
    	$select = $offerTable->select();
    	$select->where('key_asin= ?' , $asin);
    	$offer = $offerTable->fetchAll($select)->toArray();

    	$common = new Model_Common();
    	$common->initBrowser();
    	$userId = $common->getUserId();
    	$browserId = $common->getBrowserId();
         
    	$adapter = $cartTable->getAdapter();
    	$sql = <<<SQL
    	    select * 
    	    from cart
    	    where browser_id = :browser_id and user_id = :user_id and asin = :asin
SQL;
    	$resultset = $adapter->query($sql, array('browser_id'=> $browserId,
    			                                  'user_id' => $userId,
    			                                  'asin' => $asin));
    	$itemInCart = $resultset->fetchAll();

    	$this->view->itemInCart = $itemInCart;
    	$this->view->item = $item;
    	$this->view->offer= $offer;
    }

    /**
     *  Navigator for search.
     */
    public function navigatorAction()
    {
    	$request = $this->getRequest();
    	$this->_helper->layout->disableLayout();
    	
    	$searchform = new Default_Form_Search();
    	$this->view->searchForm = $searchform;
    	
    	$products = new Default_Model_DbTable_Products();
    	$this->view->categories = $products->getProductCategoies();
    }

    /**
     *  Get ASIN from amazon
     */
    public function getitemAction()
    {
    	$request = $this->getRequest();
    //	if ( ! $request->isXmlHttpRequest() ) return;
    	$asin = $request->getParam('asin', '');
    	if ( $asin == '' ) return ;
    	
    	$amazon = new Model_Amazon();
    	$result = $amazon->lookupItems($asin);
    	$this->view->result = $result;
    }

    /**
     *  Logo image, should be cached later.
     */
    public function logoAction()
    {
    	$im = imagecreatetruecolor(320, 100);
    	
    	// Create some colors
    	$white = imagecolorallocate($im, 255, 255, 255);
    	imagefill($im, 0, 0, $white);
    	imagecolortransparent($im,$white);
    	// The text to draw
    	$text = 'Lei\'s Virtual';
    	//$font = APPLICATION_PATH. '/../public/images/arial.ttf';
    	//$font = APPLICATION_PATH. '/../public/images/FRSCRIPT.TTF';    	
    	$font = APPLICATION_PATH. '/../public/images/FORTE.TTF';    	
    	
    	$size = 36;
    	$degree = 0 ;
    	$r = 128;
    	$g = 128;
    	$b = 128;

    	$br = 200;
    	$bg = 200;
    	$bb = 200;
    	
    	$step = 4 ;
    	$num = 10 ;
    	
    	$x = 21;
    	$y = 50;


    	$x -= $num;
    	$y -= $num;
    	
    	$br += $step*$num; $br = min(255, $br );
    	$bg += $step*$num; $bg = min(255, $bg );
    	$bb += $step*$num; $bb = min(255, $bb );
    	 
    	// Add shadow first
    	for ( $i = 0 ; $i < $num ; $i++)
    	{
	    	$x++;
	    	$y++;
    		$br -= $i*$step; $br = max(0, $br );
    		$bg -= $i*$step; $bg = max(0, $bg );
    		$bb -= $i*$step; $bb = max(0, $bb );
    	 
    		$color = imagecolorallocate($im, $br, $bg, $bb);
    		imagettftext($im, $size, $degree, $x, $y, $color, $font, $text);
    	}
    	$x++;
    	$y++;
    	$color = imagecolorallocate($im, $r, $g, $b);
    	imagettftext($im, $size, $degree, $x, $y, $color, $font, $text);

    	$text = 'STORE';
    	//$font = APPLICATION_PATH. '/../public/images/arial.ttf';
    	//$font = APPLICATION_PATH. '/../public/images/FRSCRIPT.TTF';
    	//$font = APPLICATION_PATH. '/../public/images/FORTE.TTF';
    	$font = APPLICATION_PATH. '/../public/images/GLECB.TTF';
    	 
    	$size = 20;
    	$degree = 0 ;
    	$r = 0;
    	$g = 0;
    	$b = 0;
    	
    	$x = 250;
    	$y = 60;
    	
    	$br = 200;
    	$bg = 200;
    	$bb = 200;
    	 
    	$step = 10 ;
    	$num = 3 ;
    	
    	$x += $num;
    	$y += $num;
    	 
    	$br += $step*$num; $br = min(255, $br );
    	$bg += $step*$num; $bg = min(255, $bg );
    	$bb += $step*$num; $bb = min(255, $bb );
    	
    	// Add shadow first
    	for ( $i = 0 ; $i < $num ; $i++)
    	{
    	    $x--;
    		$y--;
    		$br -= $i*$step; $br = max(0, $br );
    		$bg -= $i*$step; $bg = max(0, $bg );
    		$bb -= $i*$step; $bb = max(0, $bb );
    		$color = imagecolorallocate($im, $br, $bg, $bb);
    		imagettftext($im, $size, $degree, $x, $y, $color, $font, $text);
    	}
   		$x--;
   		$y--;
   		$color = imagecolorallocate($im, $r, $g, $b);
   		imagettftext($im, $size, $degree, $x, $y, $color, $font, $text);
   		
    	imagepng($im);
    	header('Content-Type: image/png');    	
    	imagedestroy($im);
    	exit();
    }

}









