<?php

/**
 * Router for console application
 * @author lei
 *
 */
class Cli_Router extends Zend_Controller_Router_Abstract {
	
	/**
	 * Get items which belong to category 'u2'
	 * @see Zend_Controller_Router_Interface::route()
	 */
	public function route(Zend_Controller_Request_Abstract $dispatcher)
	{
		$amazon = new Model_Amazon();
		foreach ( Model_Amazon::$categories as $category )
		{
			$results = $amazon->searchItem($category, 'u2' );
			$amazon->updateMultipleItemsByAmazon($results);	
			flush();		
		}
		exit();
	}
	
	public function assemble($userParams, $name = null, $reset = false, $encode = true)
	{
		
	}
}