<?php

class Library_BooksController extends Zend_Controller_Action
{

    public function init()
    {
        $contextSwitch = $this->_helper->getHelper('contextSwitch');
        $contextSwitch->addActionContext('list','json')
                      ->initContext();
    }

    public function listAction()
    {
         $booksList = new Library_Model_ListBooks();
         $booksList = $booksList->listBooks();
         
         $paginator = new Zend_Paginator(new Zend_Paginator_Adapter_DbSelect($booksList));
         $paginator->setItemCountPerPage(3)
                   ->setCurrentPageNumber($this->_getParam('page',1));
         
         $books = array();
         foreach ( $paginator as $book) $books[]=$book;
         if ( ! $this->_request->isXmlHttpRequest()) $this->view->paginator = $paginator; 

         $num = 1234.67 ; 
         $this->view->number = Zend_Locale_Format::toNumber($num, 
         		 array('precision'=>2, 'locale'=> Zend_Registry::get('locale')));
    }


}

