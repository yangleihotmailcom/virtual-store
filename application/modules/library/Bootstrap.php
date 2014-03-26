<?php


class Library_Bootstrap extends Zend_Application_Module_Bootstrap {

	  protected function _initSetTranslations()
	  {
	  	 if ( PHP_SAPI == 'cli') return;
	  	 $bootstrap = $this->getApplication();
	  	 $layout = $bootstrap->getResource('layout');
	  	 $view = $layout->getView();
	  	 
	  	 $translate = new Zend_Translate('gettext',
	  	 		               APPLICATION_PATH.'/i18n',
	  	 		               null,
	  	 		               array('scan'=> Zend_Translate::LOCALE_FILENAME));
//	  	 $translate->addTranslation(APPLICATION_PATH.'/i18n/en.mo','en');
//	  	 $translate->setLocale('en');

	  	 $session = new Zend_Session_Namespace('global');
	  	 $locale = new Zend_Locale();
	  	 if ( isset( $session->language ) ) {
	  	 	  $requestLang = $session->language;
	  	 	  $locale->setLocale($requestLang);
	  	 } else {
	         $locale->setLocale(Zend_Locale::BROWSER);
	         $requestLang = key( $locale->getBrowser());
	  	 }
         if ( in_array($requestLang, $translate->getlist()))
         	 $lang = $requestLang;
         else  
         	 $lang = 'en';	 
         
         Zend_Registry::set('locale', $locale);
         $translate->setLocale($lang);         
	  	 $view->translate = $translate;
	  	 //print_r($translate); exit();
	  }
}