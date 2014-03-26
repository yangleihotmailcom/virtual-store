<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
	private $acl=null;
//	private $auth = null;
	
	protected function _initRouter()
	{
	   if ( PHP_SAPI == 'cli') 
	   {
	   	  $this->bootstrap('frontController');
	   	  $fc = $this->getResource('frontController');
	   	  $fc->setRouter( new Cli_Router() );
	   }
	}
	
 	protected function _initAutoload(){
 		$modelLoader = new Zend_Application_Module_Autoloader(array(
 				            'namespace' => '',
 				            'basePath' => APPLICATION_PATH.'/modules/default',
 				       ));
 		return $modelLoader;
 	}
 	
 	protected function _initViewHelpers() 
 	{
 		if ( PHP_SAPI == 'cli') return ;
 		$this->bootstrap('layout');
 		$layout = $this->getResource('layout');
 		$view = $layout->getView();
 		
//   		ZendX_JQuery::enableView($view);
 		
 		$view->doctype('HTML4_STRICT');
 		
 		$view->headMeta()->appendHttpEquiv('Content-type','text/html;charset=utf-8')
 		                 ->appendName('description','test,job,php')  ;
 		$view->headTitle()->setSeparator(' - ');
 		$view->headTitle('PHP page');

 		$auth = Zend_Auth::getInstance();
 		$userInfo = null;
 		if ( $auth->hasIdentity() ) $userInfo = $auth->getIdentity(); 
 		$view->userInfo = $userInfo; 

 		//  	$this->acl = new Model_LibraryAcl();
 		//		$this->auth = Zend_Auth::getInstance();
//  		$auth = Zend_Auth::getInstance();
 			
//  		if ( $auth->hasIdentity() ) {
//  			Zend_Registry::set('role', $auth->getStorage()->read()->role);
//  		} else {
//  			Zend_Registry::set('role', 'guest');
//  		}
 			
 		//  		$fc = Zend_Controller_Front::getInstance();
 		//  		$fc->registerPlugin(new Plugin_AccessCheck($this->acl));
 		 	
 		
//	$navContainerConfig = new Zend_Config_Xml( 	APPLICATION_PATH.'/configs/navigation.xml','nav');
//	$navContainer = new Zend_Navigation($navContainerConfig);
//	$view->navigation($navContainer)
//	     ->setAcl($this->acl)
//	     ->setRole(Zend_Registry::get('role'));
//	$view->lang = 'en'; 
 	}

}

