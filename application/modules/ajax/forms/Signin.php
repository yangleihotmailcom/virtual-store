<?php
/**
 * User signup form
 * @author lei
 *
 */
class Ajax_Form_Signin extends Zend_Form
{

    public function init()
    {
    	$this->setName("Signin");

    	$notEmpty = new Zend_Validate_NotEmpty();
    	$notEmpty->setMessage('Please provide an Email.');
    	$emailValidator = new Zend_Validate_EmailAddress();
    	$emailValidator->setMessage("Please provide a valid Email." );

    	$username = new Zend_Form_Element_Text("usernameSignin");
    	$username->setLabel('Email')
    	         ->setRequired(true)
    	         ->addValidators( array($emailValidator, $notEmpty) )
    	         ->addFilter('StringTrim');

    	$notEmpty = new Zend_Validate_NotEmpty();
    	$notEmpty->setMessage('Please provide a password.');
    	$password = new Zend_Form_Element_Password('passwordSignin');
    	$password->setLabel('Password')
    	         ->setRequired(true)
    	         ->addValidator($notEmpty)
    	         ->addFilter('StringTrim');;
    	
    	$submit = new Zend_Form_Element_Submit('Login');
    	$submit->setLabel('Submit');
    	
    	$this->addElements(array($username,$password,$submit));
    	foreach( $this->getElements() as $element ) $element->removeDecorator("Errors");
    	$this->setMethod('post');
    	
    	// 		$currentAction = Zend_Controller_Front::getInstance()->getBaseUrl();
    	// 		echo $currentAction; exit();

    	$this->setAction('/ajax/authentication/login');
    }

}

