<?php
/**
 * User Authentication 
 * @author lei
 *
 */
class Default_AuthenticationController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {
        // action body
    }

    public function loginAction()
    {
    	$auth = Zend_Auth::getInstance();
    	
//     	if ( $auth->hasIdentity() ){
//     		$this->_redirect('index/index');
//     	}
    	
    	$request = $this->getRequest();
    	$form = new Form_LoginForm();
    	
    	if ( $request->isPost()) {
    		if ( $form->isValid($request->getPost())) {
    			$authAdapter = $this->getAuthAdapter();
    			
    			$username = $form->getValue('username');
    			$password = $form->getValue('password');
    			
    			$authAdapter->setIdentity($username);
    			$authAdapter->setCredential($password);
    			
    			 
    			$result = $auth->authenticate($authAdapter);
    			
    			if ( $result->isValid() ) {
    				//         	echo 'valid';
    				$identity = $authAdapter->getResultRowObject();
    				$authStorage = $auth->getStorage();
    				$authStorage->write($identity);
    				$this->_redirect('/index/index');
    			}
    			else{
    				echo 'invaild';
    			}
    		}
    	}

    	$this->view->form = $form;
    }

    public function logoutAction()
    {
        $auth = Zend_Auth::getInstance();
        $auth->clearIdentity();
        
        $id = $auth->getStorage()->read();
        //echo $id; exit();
                
        $this->_redirect('/index/index');
    }
    
    private function getAuthAdapter()
    {
    	$authAdapter = new Zend_Auth_Adapter_DbTable( Zend_Db_Table::getDefaultAdapter());
    	$authAdapter->setTableName("users")
    	            ->setIdentityColumn("id")
    	            ->setCredentialColumn("pwd")
    	            ->setCredentialTreatment("sha1(?)");
    	return $authAdapter;
    }


}





