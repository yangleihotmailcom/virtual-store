<?php

class Library_YoutubeController extends Zend_Controller_Action
{

	private $developerKey = null ; 
    public function init()
    {
       $this->_helper->layout->disableLayout();
       $this->developerKey = 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx';
    }

    public function indexAction()
    {
    	
//     	$username = 'Lei Yang';
//     	$httpClient = new Zend_Http_Client();
    	
//     	$youtube = new Zend_Gdata_YouTube( $httpClient,'virtual Store', 'MyVideos', $this->developerKey );
//        	try 
//     	{
//     	   $feed = $youtube->getUserUploads( $username );	
//     	} catch (Exception $e) {
//     		echo $e->getMessage();
//     		exit();
//     	}
//     	$this->view->feed = $feed;
    	
//     	print_r( $feed->count() );
    }


}

