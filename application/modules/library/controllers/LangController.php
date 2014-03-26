<?php

class Library_LangController extends Zend_Controller_Action
{

    public function init()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
    }

    public function indexAction()
    {
        // action body
    }

    public function switchAction()
    {
        $session = new Zend_Session_Namespace("global");
        $session->language = $this->_getParam('lang');
        $this->_redirect($this->getRequest()->getServer('HTTP_REFERER'));
    }
    
}

