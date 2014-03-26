<?php
/**
 * Set permission for roles
 * @author lei
 *
 */
class Model_LibraryAcl extends Zend_Acl {
	public function __construct(){

		$this->addRole(new Zend_Acl_Role('guest'));
		$this->addRole(new Zend_Acl_Role('user'),'guest');
		$this->addRole(new Zend_Acl_Role('admin'),'user');
		
		$this->add(new Zend_Acl_Resource('library'))
		     ->add(new Zend_Acl_Resource('library:books'),'library')
		     ->add(new Zend_Acl_Resource('library:Lang'), 'library');

		$this->add(new Zend_Acl_Resource('admin'))
		     ->add(new Zend_Acl_Resource('admin:book'),'admin');
		
		$this->add(new Zend_Acl_Resource('default'))
		     ->add(new Zend_Acl_Resource('default:authentication'),'default')
		     ->add(new Zend_Acl_Resource('default:index'),'default')
		     ->add(new Zend_Acl_Resource('default:error'),'default');
		
		$this->allow('guest','default:authentication','login');
		$this->allow('guest','default:index','index');		
		$this->allow('guest','default:error','error');
		$this->allow('guest', 'library:Lang', 'switch');

		$this->deny('user','default:authentication','login');
		
		$this->allow('user','default:authentication','logout');
		$this->allow('user','library:books',array('index','list'));
//		$this->allow('user','default:authentication','login');
		
		$this->allow('admin','admin:book', array('index','add','edit','delete'));
	}
}
