<?php

class  Model_Amazon {
	
	public static $categories = array(
			 "Apparel"
			,"Appliances"
			,"ArtsAndCrafts"
			,"Automotive"
			,"Baby"
			,"Beauty"
			,"Books"
			,"Classical"
			,"DigitalMusic"
			,"DVD"
			,"Electronics"
			,"Grocery"
			,"HealthPersonalCare"
			,"HomeImprovement"
			,"Industrial"
			,"Jewelry"
			,"KindleStore"
			,"Kitchen"
			,"LawnAndGarden"
			,"Magazines"
			,"Marketplace"
			,"Merchants"
			,"Miscellaneous"
			,"MobileApps"
			,"MP3Downloads"
			,"Music"
			,"MusicalInstruments"
			,"MusicTracks"
			,"OfficeProducts"
			,"OutdoorLiving"
			,"PCHardware"
			,"PetSupplies"
			,"Photo"
			,"Shoes"
			,"Software"
			,"SportingGoods"
			,"Tools"
			,"Toys"
			,"UnboxVideo"
			,"VHS"
			,"Video"
			,"VideoGames"
			,"Watches"
			,"Wireless"
			,"WirelessAccessories"
	);
	
	public static $itemQuickViewAttr = array(
			'Title' => null ,
			'Actor' => null ,			
			'Creator' => null ,
			'Author' => null,
			'Artist' => null ,			
			'Director' => null ,
			'Studio' => null ,			
			'DetailPageURL' => null ,
			'SalesRank' => null ,
			'Department' => null ,
			'Label' => null ,
			'Manufacturer' => null ,
			'ProductTypeName' => null ,
			'Amount' => null ,
			'FormattedPrice' => null ,
			'NumberOfItems' => null ,
			'NumberOfPages' => null,
			'NumberOfDiscs' => null ,
			'Format' => null ,
			'Genre' => null ,						
			);
	
	private static $itemAttrs = array(
			'ASIN' => null ,
			'Binding' => null ,
			'Brand' => null ,
			'Color' => null ,
			'ProductGroup' => null ,
			'Publisher' => null ,
			'SKU' => null ,
			'CurrencyCode' => null ,
			'EAN' => null ,
			'Model' => null ,
			'PackageQuantity' => null ,
			'UPC' => null ,
			'Warranty' => null ,
			'MPN' => null ,
			'PartNumber' => null ,
			'ItemPartNumber' => null ,
			'IsAdultProduct' => null ,
			'PublicationDate' => null ,
			'ISBN' => null ,
			'IsEligibleForTradeIn' => null ,
			'ReleaseDate' => null ,
			'RunningTime' => null ,
			'AspectRatio' => null ,
			'AudienceRating' => null ,
			'HardwarePlatform' => null ,
			'IsAutographed' => null ,
			'IsMemorabilia' => null ,
			'OperatingSystem' => null ,
			'Edition' => null ,
			'EISBN' => null ,
			'LegalDisclaimer' => null ,
			'Size' => null ,
			'ESRBAgeRating' => null ,
			'Platform' => null ,
				);

	public static $imageNames = array(
			'SmallImage' => null ,
			'MediumImage' => null ,
			'LargeImage' => null ,
	) ;
	
	private static $details = array (
			'SimilarProducts' => null,
			'Accessories'=> null,
			'Tracks'=> null,
			'Feature'=> null,
			'EditorialReviews'=> null,
			);
	
	private static $offers = array (
			'MerchantId' => null ,
			'MerchantName' => null ,
			'Condition' => null ,
			'OfferListingId' => null ,
			'Price' => null ,
			'CurrencyCode' => null ,
			'Availability' => null ,
			'IsEligibleForSuperSaverShipping' => null ,
			);
	
	
	private static $accessKeyID = 'xxxxxxxxxxxxxxxxxxxxx';
	private static $secretAccessKey = 'xxxxxxxxxxxxxxxxxxxxxxx';
	private static $associateTag = 'xxxxxxxxx';	
    private static $amazonService = null;
  
	public function __construct()
	{
// 		parent::__construct();
        if ( ! is_null(self::$amazonService) ) return ;
        self::$itemAttrs = array_merge(self::$itemAttrs, self::$imageNames, self::$itemQuickViewAttr);       
      	self::$amazonService = new Zend_Service_Amazon_Query(self::$accessKeyID,'US',self::$secretAccessKey);
	}
	
	
	public function searchItem($category, $keywords, $page=1, $ResponseGroup='Large')
	{
		self::$amazonService->Category($category)
		                    ->AssociateTag(self::$associateTag)
		                    ->Keywords($keywords)
		                    ->ResponseGroup($ResponseGroup)
		                    ->ItemPage($page);
		$result = self::$amazonService->search();
		return $result;
	}
	
	private function _lookupItemFromAmazon($Asin , $ResponseGroup='Large')
	{
		self::$amazonService->ASIN($Asin)
		                    ->ResponseGroup($ResponseGroup)
		                    ->AssociateTag(self::$associateTag);
		
		$result = self::$amazonService->search();
		return $result;
	}
	
	/**
	 * Update data in DB
	 * @param Zend_Service_Amazon_Item $item
	 * @param Default_Model_DbTable_Products $productsObj
	 * @param string $insertFlag
	 * @return multitype:
	 */
	private function handlerItem(Zend_Service_Amazon_Item $item, Default_Model_DbTable_Products $productsObj, $insertFlag = true)
	{
		
	// Products	
		$names = get_object_vars($item);
		$validNames = array_intersect_key($names, self::$itemAttrs);
        $otherNames = array_diff_key(self::$itemAttrs, $names);
        		
		$invalidNames = array_diff_key($names, $validNames, self::$details);
		$invalidKeys = implode(',' , array_keys($invalidNames));
		trigger_error($invalidKeys. ' are not in Fields.');
		
        foreach ( self::$imageNames as $imageName => $value ) {
        	$imageObj = $validNames[$imageName];
        	$validNames[$imageName] = '';
        	if ( is_object($imageObj) )
        	{
        		$imageURL = $imageObj->Url;
        		if ( is_object($imageURL)) $validNames[$imageName] = $imageURL->getUri();
        	}	
        }
        
        foreach ( $validNames as $key => &$value )
        { // the Actor,Format,... may be a array
        	if ( is_array( $value )) $value = implode('/', $value);
        }

        $data = array_merge($validNames, $otherNames);
        $data['createTime'] = time();
        
        $asin = $data['ASIN'];
        $where = "ASIN='$asin'";
        
        if ( $insertFlag) $productsObj->insert($data);
        else $productsObj->update($data, $where);

      // SimilarProducts
        $where = "key_asin='$asin'";
        $detailName = 'SimilarProducts';
        $similarProductsTable = new Default_Model_DbTable_SimilarProducts();
        $similarProductsTable->delete($where);
        if ( isset($names[$detailName]) && is_array($names[$detailName]) && count($names[$detailName]) >0 )
        {
        	 foreach ( $names[$detailName] as $detailObj )
        	 {
        	 	$detail = get_object_vars( $detailObj );
        	 	$detail['key_asin'] = $asin;
        	 	$similarProductsTable->insert($detail);
        	 }
        }

        // Accessories
        $where = "key_asin='$asin'";
        $detailName = 'Accessories';
        if ( isset($names[$detailName]) && is_array($names[$detailName]) && count($names[$detailName]) >0 )
        {
        	$accessoriesTable = new Default_Model_DbTable_Accessories();
        	$accessoriesTable->delete($where);
        	foreach ( $names[$detailName] as $detailObj )
        	{
        		$detail = get_object_vars( $detailObj );
        		$detail['key_asin'] = $asin;
        		$accessoriesTable->insert($detail);
        	}
        }
        
        // Tracks
        $where = "key_asin='$asin'";
        $detailName = 'Tracks';
        if ( isset($names[$detailName]) && is_array($names[$detailName]) && count($names[$detailName]) >0 )
        {
        	$TracksTable = new Default_Model_DbTable_Tracks();
        	$TracksTable->delete($where);
        	$detail = array();
        	$detail['key_asin'] = $asin;
        	foreach ( $names[$detailName] as $title )
        	{
        		$detail['title'] = $title;
        		$TracksTable->insert($detail);
        	}
        }

        // Feature
        $where = "key_asin='$asin'";
        $detailName = 'Feature';
        if ( isset($names[$detailName]) && is_array($names[$detailName]) && count($names[$detailName]) >0 )
        {
        	$FeaturesTable = new Default_Model_DbTable_Feature();
        	$FeaturesTable->delete($where);
        	$detail = array();
        	$detail['key_asin'] = $asin;        	
        	foreach ( $names[$detailName] as $feature )
        	{
        		$detail['Feature'] = $feature;
        		$FeaturesTable->insert($detail);
        	}
        }
        
        // EditorialReviews
        $where = "key_asin='$asin'";
        $detailName = 'EditorialReviews';
        $EditorialReviewsTable = new Default_Model_DbTable_EditorialReviews();
        $EditorialReviewsTable->delete($where);
        if ( isset($names[$detailName]) && is_array($names[$detailName]) && count($names[$detailName]) >0 )
        {
            foreach ( $names[$detailName] as $detailObj )
        	{
        		$detail = get_object_vars( $detailObj );
        		$detail['key_asin'] = $asin;
        		$EditorialReviewsTable->insert($detail);
        	}
        }

        // Offers
        $where = "key_asin='$asin'";
        $detailName = 'Offers';
        $OffersTable = new Default_Model_DbTable_Offers();        
        $OffersTable->delete($where);       
        if ( isset($names[$detailName]) ) 
  		{
            $Offers = $names[$detailName]->Offers;			
  		    if ( is_array($Offers) && count($Offers) > 0 )
            {
	        	foreach ( $Offers as $detailObj )
	        	{
	        		$detail = get_object_vars( $detailObj );
	        		$detail = array_intersect_key($detail, self::$offers);
	        		$detail['key_asin'] = $asin;
	        		$OffersTable->insert($detail);
	        	}
            }	
        }
        
        return $productsObj->find($asin)->toArray();
	}
	
	public function lookupItem($Asin, $ResponseGroup='Large')
	{
		$vAsin = trim($Asin);
		if( strlen($vAsin) == 0 ) return null;
		
		$productsObj = new Default_Model_DbTable_Products();
		$product = $productsObj->find($Asin)->toArray(); //print_r($product); exit;
		if ( count($product) > 0 && time() - $product[0]['createTime'] < 24*60*60 ) return $product;
		$result = $this->_lookupItemFromAmazon($Asin, $ResponseGroup);

		if ( get_class($result) == 'Zend_Service_Amazon_Item' ) {
			return $this->handlerItem( $result, $productsObj, count($product) == 0);
		}
		return array();
	}
	
	public function lookupItems( $asins, $ResponseGroup='Large')
	{
		$vAsins = trim($asins);
		if( strlen($vAsins) == 0 ) return null;
		
		$vAsins = explode(',', $asins);    //print_r($vAsins); exit();
		$needUpdatedItems = array();
		$results = array();
		$productsObj = new Default_Model_DbTable_Products();

		foreach ( $vAsins as $asin )
		{
			$product = $productsObj->find($asin)->toArray();
			if ( count($product) > 0 && time() - $product[0]['createTime'] < 24*60*60 ) 
			{
				$results[] = $product;
				continue;
			}
			$needUpdatedItems[$asin] = (count($product) == 0);
		}
		
        // print_r($needUpdatedItems); exit();
		if ( count($needUpdatedItems) > 0 )
		{
			$inputAsins = implode(',',  array_keys($needUpdatedItems)); // print_r($inputAsins); exit();
			$amazonResults = $this->_lookupItemFromAmazon( $inputAsins , $ResponseGroup); //print_r($amazonResults); exit();
			if ( get_class($amazonResults) == 'Zend_Service_Amazon_Item' ) 
			{
				$results[] = $this->handlerItem( $amazonResults, $productsObj, $needUpdatedItems[$amazonResults->ASIN]);
			}
			else
			foreach ( $amazonResults as $amazonResult )
			{
				//print_r($amazonResult); exit();
				$results[] = $this->handlerItem( $amazonResult, $productsObj, $needUpdatedItems[$amazonResult->ASIN]);
			}
		}
		//print_r($results); exit();
		return $results;
	}
	
	// $results :  the return value from amazon::searchItem
	public function updateMultipleItemsByAmazon( $results )
	{
		$productsObj = new Default_Model_DbTable_Products();
		foreach ( $results as $result )
		{
            $asin = trim($result->ASIN); 
			if ( strlen($asin) == 0 ) continue ;
			$product = $productsObj->find($asin)->toArray();
			if ( count($product) > 0 && time() - $product[0]['createTime'] < 24*60*60 ) continue;
			$this->handlerItem( $result, $productsObj, count($product) == 0);
		}
	}
	
	public function createCart( $request )
	{
		$cartTable = new Default_Model_DbTable_Cart();
		$result = $cartTable->getShoppingCart($request);
     	$options = array('AssociateTag' => self::$associateTag);
		for( $i = 0 , $j = count($result); $i < $j ; $i++)
		{  
			$options['Item.'.($i+1).'.ASIN'] = $result[$i]['asin'];
			$options['Item.'.($i+1).'.Quantity'] = $result[$i]['qty'];
		}
		return self::$amazonService->createCart($options);
	}
}

