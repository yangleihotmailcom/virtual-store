<?php
/**
 * User register form
 * @author lei
 *
 */
class Ajax_Form_Register extends Zend_Form
{

    public function init()
    {
    	$this->setName("Register");
    	 
    	$notEmpty = new Zend_Validate_NotEmpty();
    	$notEmpty->setMessage('Please provide an Email.');    	    	
    	$emailValidator = new Zend_Validate_EmailAddress();
    	$emailValidator->setMessage("Please provide a valid Email." );
    	$userNoExists = new Zend_Validate_Db_NoRecordExists(array('table'=>'users' , 'field' => 'username'));
    	 
    	$username = new Zend_Form_Element_Text("usernameRegister");
    	$username->setLabel('Email')
               	 ->setRequired(true)
	             ->addValidators( array( $notEmpty, $emailValidator, $userNoExists) )
//	             ->addValidators( array( $emailValidator, $userNoExists) )	             
      	         ->addFilter('StringTrim');
//    	         ->addErrorMessage('Please provide a valid Email.');


    	$notEmpty = new Zend_Validate_NotEmpty();
    	$notEmpty->setMessage('Please provide a password.');
    	$password = new Zend_Form_Element_Password('passwordRegister');
    	$password->setLabel('Password')
    	         ->setRequired(true)
    	         ->addValidator( $notEmpty )    	         
       	         ->addFilter('StringTrim');

    	
    	$notEmpty = new Zend_Validate_NotEmpty();
    	$notEmpty->setMessage('Please confirm your password.');
    	$equalToPassword = new Zend_Validate_Identical( 'passwordRegister' );
    	$equalToPassword->setMessage('Passwords are not matched.');    	
    	$password2 = new Zend_Form_Element_Password('password2Register');
    	$password2->setLabel('Confirm')
    	          ->setRequired(true)
    	          ->addFilter('StringTrim')
    	          ->addValidators( array($equalToPassword, $notEmpty));

    	$captcha = new Zend_Form_Element_Captcha('captcha' , 
	    			array(
	    			'label' => "Verification",
	    			'captcha' => array(
	    					'captcha' => 'Image',
	    					'wordLen' => 6,
	    					'timeout' => 300,
	    					'font' => APPLICATION_PATH.'/../public/images/arial.ttf',
	    					'imgDir' => APPLICATION_PATH.'/../public/images/captcha',
	    					'imgUrl' => '/images/captcha/',
	    					'messages' => array('badCaptcha' => 'The verification code is wrong.'),
                 			),
    	            ));
    //	$captcha->addErrorMessage('Verification code is wrong.');
    	
    	$submit = new Zend_Form_Element_Submit('Register');
    	$submit->setLabel('Submit');
    	 
    	$this->addElements(array($username,$password,$password2,$captcha,$submit));
    	foreach( $this->getElements() as $element ) $element->removeDecorator("Errors");
    	
    	$this->setMethod('post');
    	// 		$currentAction = Zend_Controller_Front::getInstance()->getBaseUrl();
    	// 		echo $currentAction; exit();
    	$this->setAction('/ajax/authentication/register');
    	 
    }


}

