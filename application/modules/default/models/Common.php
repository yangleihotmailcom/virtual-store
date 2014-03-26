<?php

class  Model_Common {

	private $_browser_id ;
	private $_user_id;
	
	public function __construct()
	{
		$this->_browser_id = 0 ;
		$this->_user_id = 0 ;
	}
	
	public function getBrowserId()
	{
		return $this->_browser_id;
	} 
	
	public function getUserId()
	{
		return $this->_user_id;
	}
	
	public function initBrowser(  )
	{
		$auth = Zend_Auth::getInstance();
		$browserTable = new Default_Model_DbTable_Browser();
		 
		if( $auth->hasIdentity())
		{
			$this->_user_id = $auth->getIdentity()->id;  // print_r($userId); exit();
			$browser = $browserTable->getBrowserByUserId($this->_user_id); // print_r($browser); exit();
			$this->_browser_id = $browser[0]["id"];
		}
		else
		{
			if ( ! isset($_COOKIE['browserID']) || ! is_numeric($_COOKIE['browserID']) )
			{
				$ip = '127.0.0.1' ; // $request->getClientIp();
				$data = array( 'ip'=> $ip, 'createTime'=>time(), 'userId'=> $this->_user_id);
				$this->_browser_id = $browserTable->insert($data);
				$expired = time()+60*60*24*365*25; // 25 years
				setcookie('browserID',$this->_browser_id, $expired ,'/');
			}
			else 
				$this->_browser_id = trim( $_COOKIE['browserID']);
		}
	}
	
}

