<?php

class Default_Model_DbTable_Cart extends Zend_Db_Table_Abstract
{
    protected $_name = 'cart';

    public function getShoppingCart( $request )
    {
    	$common = new Model_Common();
    	$common->initBrowser();
        $userId = $common->getUserId();
        $browserId = $common->getBrowserId();
    	    	
    	$adapter = $this->getAdapter();
    	$select =  <<<SQL
               select c.*, p.SmallImage,p.MediumImage,p.LargeImage
               from cart c left join products p on c.asin = p.ASIN
               where browser_id=:browser_id and user_id=:user_id
SQL;
    	$resultset = $adapter->query($select, array('browser_id' => $browserId,'user_id'=>$userId));
    	$result = $resultset->fetchAll();  // print_r($result);
    	return $result;
    }
}

