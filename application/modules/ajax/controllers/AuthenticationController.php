<?php

class Ajax_AuthenticationController extends Zend_Controller_Action
{
    public function init()
    {
    	$this->_helper->layout->disableLayout(); 
    	$contextSwitch = $this->_helper->getHelper('contextSwitch');
    	// Action 'refresh','logout' return data by json
    	$contextSwitch->addActionContext('refresh', 'json')
    	              ->addActionContext('logout', 'json')
                	  ->initContext();
    	return;
    }

    /**
     *  dummy action.
     */
    public function indexAction()
    {
    	$request = $this->getRequest();
//     	if ( ! $request->isXmlHttpRequest() ) exit();
    }

    /**
     *  Login
     */
    public function loginAction()
    {
    	$request = $this->getRequest();
    	if ( ! $request->isXmlHttpRequest() ) return;
    	 
    	$formSignin = new Ajax_Form_Signin();
    	$this->view->formSignin = $formSignin;
	   	if ( ! $request->isPost() ) return;
	  	if ( ! $formSignin->isValid($request->getPost())) return ;

//--------debug -------------------------------
//         $data = array('usernameSignin'=>'yang5@hotmail.com', 'passwordSignin'=>5);     	
//     	$formSignin->isValid($data);
//---------------------------------------    	
    	$username = $formSignin->getValue('usernameSignin');
    	$password = $formSignin->getValue('passwordSignin');
    	 
    	$result = $this->LoginRegisterDone($username, $password);
		if ( ! $result["result"] )
		{
			$formSignin->addError( 'Username/password are not correct.' );
			return;
		}
		
        echo $result["data"];  		
        die();  
    }

    /**
     * Client registers
     */
    public function registerAction()
    {
       	$request = $this->getRequest();
//     	if ( ! $request->isXmlHttpRequest() ) return;

    	$formRegister = new Ajax_Form_Register();
    	$this->view->formRegister = $formRegister; 
    	if ( ! $request->isPost() ) return;
    	if ( ! $formRegister->isValid($request->getPost())) return ;

    	$username = $formRegister->getValue('usernameRegister');
    	$originalPassword = $formRegister->getValue('passwordRegister');
    	$Sha1Password = sha1($originalPassword);
    	
    	$userTable = new Default_Model_DbTable_Users();
    	$account = array ( 'username' => $username,
    			            'password' => $Sha1Password,
    			            'lastLogin' => time(),
    			            'createTime' => time(), );
    	$userTable->insert($account);
    	
    	$result = $this->LoginRegisterDone($username, $originalPassword);
		if ( ! $result["result"] )
		{
			$formRegister->addError( 'Internal error, Please try again.' );
			return;
		}
		
        echo $result["data"];  		
        die();  
    }

    /**
     * Logout
     */
    public function logoutAction()
    {
        Zend_Auth::getInstance()->clearIdentity();
        $this->view->result = true ;    
    }

    /**
     *  Refresh the captcha.
     */
    public function refreshAction()
    {
    	$request = $this->getRequest();
//     	if ( ! $request->isXmlHttpRequest() ) return;

    	$form = new Ajax_Form_Register();
    	$captcha = $form->getElement('captcha')->getCaptcha();
    	
    	$data = array();
    	
    	$data['id']  = $captcha->generate();
    	$data['src'] = $captcha->getImgUrl().$captcha->getId().$captcha->getSuffix();

    	$this->view->result = $data;
    }
    
    /**
     * Process the login/Register.
     * @param string $username
     * @param string $password
     * @return multitype:boolean NULL |multitype:boolean NULL string
     */
    private function LoginRegisterDone( $username, $password )
    {
    	$returnValue = array( "result" => false , "data" => null );
    	$auth = Zend_Auth::getInstance();
    	$authAdapter = new Zend_Auth_Adapter_DbTable( Zend_Db_Table::getDefaultAdapter() );
    	$authAdapter->setTableName('users')
			    	->setIdentityColumn('username')
			    	->setCredentialColumn('password')
			    	->setCredentialTreatment('sha1(?)')
			    	->setIdentity($username)
			    	->setCredential($password);
    	$result = $auth->authenticate($authAdapter);
    	 
    	if ( ! $result->isValid() )
    	{
//     		$formSignin->addError( 'Username/password are not correct.' );
    		return $returnValue ;
    	}
    	 
    	$identity = $authAdapter->getResultRowObject( null, 'password');
    	$authStorage = $auth->getStorage()
    	                    ->write($identity);
    	$userId = $identity->id;
    	
//  shopping cart
    	$cartTable = new Default_Model_DbTable_Cart();
    	$browserTable = new Default_Model_DbTable_Browser();
    	$select = $browserTable->select()->where('userId = ?' , $userId );
    	$secureBrowser = $browserTable->fetchRow($select);  //print_r($secureBrowser->id); exit;

    	if ( is_null($secureBrowser) )
    	{  // browser does not exist for this user.
    		$request = $this->getRequest();
    		$browserId = $request->getCookie('browserID', '');
    		if ( is_int($browserId) ) $browser = $browserTable->find($browserId);
    		
    		if ( ! is_int($browserId) || count($browser) == 0 || $browser[0]['userId'] != 0 )
    		{
    			$ip = $request->getClientIp();
    			$data = array( 'ip'=> $request->getClientIp() ,
    					'createTime' => time(),
    					'userId' => $userId,
    			);
    			$secureBrowserId = $browserTable->insert($data);
    		}
    		else 
    		{
                $where = $browserTable->getAdapter()->quoteInto('id=?', $browserId);
                $data = array( 'userId'=> $userId );
                $browserTable->update($data, $where);

                $where = $cartTable->getAdapter()->quoteInto('browser_id', $browserId);
                $data = array( 'user_id'=> $userId);
                $cartTable->update($data, $where);
    		}
    	}
    	else 
    	{  // browser exists for this user.
    		$request = $this->getRequest();
    		$browserId = $request->getCookie('browserID', ''); // print_r($browserId); exit();

    		if ( is_numeric($browserId) ) { 
    			$browser = $browserTable->find($browserId)->toArray();  //print_r(count($browser)); exit();
    		}
    		
    		if ( ! is_numeric($browserId) || count($browser) == 0 || $browser[0]['userId'] != 0 )
    		{
    			// nothing for the moment.
    		}
    		else
    		{
    			// merge 
    			$adapter = $cartTable->getAdapter();
    			$adapter->query('update cart set user_id = :user_id, browser_id = :secureBrowser where browser_id = :browser_id',
    					        array('browser_id' => $browserId, 'user_id' => $userId, 'secureBrowser' => $secureBrowser->id ));

    			$resultSet = $adapter->query('select * from cart where user_id = :user_id and browser_id = :secureBrowser',
    					array('user_id' => $userId, 'secureBrowser' => $secureBrowser->id ));
    			 
    			$results = $resultSet->fetchAll();
    			$asinsHandled = array();
    			$cartIds = array();
    			for ( $i = 0 , $j = count($results) ; $i < $j ; $i++ )
    			{
    				$qty = 0 ;
    				$currentAsin = $results[$i]['asin'];
    				if ( in_array($currentAsin, $asinsHandled)) continue;
    				for( $k = $i+1; $k < $j; $k++ ) 
    					 if ( $results[$k]['asin'] == $currentAsin ) {
    					        $qty += $results[$k]['qty'];
    					        $cartIds[] = $results[$k]['id'];   
    					 }
    				if ( $qty > 0 ) {
    					$adapter->query('update cart set qty = qty + :qty where id = :id',
  							array('qty' => $qty, 'id' => $results[$i]['id']));
    					$asinsHandled[] = $currentAsin;
    				}	 
    			} 
    			if ( count($cartIds) > 0 )
    			{
    				$adapter->query('delete from cart where id in ('.implode(',', $cartIds).')' ); 
    			}
    			
    		}
    		
    	}
    	
	    setcookie('browserID','',time()+60*60*24*365*25,'/');
    	$returnValue["result"] = true;
    	$returnValue["data"] = json_encode($identity);
    	
    	return $returnValue;
    }


}









