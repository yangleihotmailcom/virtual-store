<?php

class Plugin_AccessCheck extends Zend_Controller_Plugin_Abstract {

	  private $_acl = null;
	  
	  public function __construct( Zend_Acl $acl ) 
	  {
	  	  $this->_acl = $acl;
	  }
	  
	  public function preDispatch(Zend_Controller_Request_Abstract $request)
	  {
	  	
// 	  	  $module = $request->getModuleName();
// 	  	  $resource = $request->getControllerName();
// 	  	  $action = $request->getActionName();
//           $role = Zend_Registry::get('role');
// 		   if ( ! $this->_acl->isAllowed($role, $module.':'.$resource, $action))
// 		   {
// 		   	  $request->setControllerName('authentication')->setActionName('login');
		   	//$request->setControllerName('index')->setActionName('index');
// 		   }
		   	
	  }
}
