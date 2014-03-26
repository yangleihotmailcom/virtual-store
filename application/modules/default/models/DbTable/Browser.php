<?php

class Default_Model_DbTable_Browser extends Zend_Db_Table_Abstract
{

    protected $_name = 'browser';

    public function getBrowserByUserId( $userId)
    {
    	$select = $this->select()->where('userId=?' , $userId);
    	$browser = $this->fetchAll($select)->toArray();
        return $browser;    	
    }
}

