<?php

class Form_LoginForm extends Zend_Form {
	public function __construct($option=null){
		parent::__construct();
		
		$this->setName("Login");
		
		$username = new Zend_Form_Element_Text("username");
		$username->setLabel('User name:')
		         ->setRequired();
		$password = new Zend_Form_Element_Password('password');
		$password->setLabel('Password')->setRequired();
		
		$submit = new Zend_Form_Element_Submit('login');
		$submit->setLabel('Submit');
		
		$this->addElements(array($username,$password,$submit));
		$this->setMethod('post');
// 		$currentAction = Zend_Controller_Front::getInstance()->getBaseUrl();
// 		echo $currentAction; exit();
		$this->setAction('/authentication/login');
	}

}
