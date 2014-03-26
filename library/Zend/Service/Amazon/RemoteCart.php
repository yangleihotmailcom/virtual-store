<?php

class Zend_Service_Amazon_RemoteCart
{
    protected $_dom;
    protected $_xpath;
    protected $_purchaseURL;
    protected $_items;  //  array : valid items in cart
    protected $_subTotal;  // array
    protected $_errors;    // array
    
    public function __construct(DOMDocument $dom)
    {
    	//echo "hele;"; exit;
        $this->_dom = $dom; // echo $this->_dom->saveXML(); exit;
        $this->_xpath = new DOMXPath($dom);
        $this->_xpath->registerNamespace('az', 'http://webservices.amazon.com/AWSECommerceService/2011-08-01');
        
        $this->_purchaseURL = null ;
        $results = $this->_xpath->query('/az:CartCreateResponse/az:Cart/az:PurchaseURL');
        $this->_purchaseURL = $results->item(0)->nodeValue;
        
        $results = $this->_xpath->query('/az:CartCreateResponse/az:Cart/az:SubTotal');
        $this->_subTotal = array();
        if ( $results->length > 0 )
        {
            $children = $results->item(0)->childNodes;
            foreach ( $children as $node )
            {
            	$this->_subTotal[$node->nodeName] = $node->nodeValue;
            }
        }
        
        $results = $this->_xpath->query('/az:CartCreateResponse/az:Cart/az:Request/az:Errors/az:Error');          
        $this->_errors = array();
        for ( $i = 0 , $j = $results->length ; $i < $j ; $i++ )
        {
        	$children = $results->item($i)->childNodes;
        	$temp = array();
        	foreach ( $children as $node )
        	{
        		$temp[$node->nodeName] = $node->nodeValue ;
        	}
        	$this->_errors[] = $temp;
        }

        $results = $this->_xpath->query('/az:CartCreateResponse/az:Cart/az:CartItems/az:CartItem');
        $this->_items = array();
        for ( $i = 0 , $j = $results->length ; $i < $j ; $i++ )
        {
        	$children = $results->item($i)->childNodes;
        	$temp = array();
        	foreach ( $children as $node )
        	{
        		if ( $node->nodeName == 'Price' || $node->nodeName == 'ItemTotal')
        		{
        			$arrPrice = array();
        			foreach ( $node->childNodes as $price )
        			{
        				$arrPrice[$price->nodeName] = $price->nodeValue;
        			}
        			$temp[$node->nodeName] = $arrPrice;
        		}
        		else
        		   $temp[$node->nodeName] = $node->nodeValue ;
        	}
        	$this->_items[] = $temp;
        }
//         echo '<pre>';
//         print_r( $this->_subTotal);
//         print_r( $this->_errors);        
//         print_r( $this->_items);
//         echo '</pre>';                
        
    }
    
    public function items()
    {
        return $this->_items;         	
    }

    public function subTotal()
    {
    	return $this->_subTotal;
    }

    public function errors()
    {
        return $this->_errors;
    }

    public function purchaseURL()
    {
    	return $this->_purchaseURL ;
    }
}
