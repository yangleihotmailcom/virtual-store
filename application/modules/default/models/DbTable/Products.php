<?php

class Default_Model_DbTable_Products extends Zend_Db_Table_Abstract
{
	protected $_name = 'products';
	protected $_primary = 'ASIN';
	
	public function getHomeBanner( $num = 10, $category = '' ) {
		$num = intval($num);
		$adapter = $this->getDefaultAdapter();
		$select = 'select asin,SmallImage,MediumImage,LargeImage,Title from products ';
		$where = "where LargeImage != :largeImage ";
		$bind = array( 'largeImage'=>'');
		if ( $category != '') 
		{
			$where .= 'and ProductGroup = :productGroup ';
			$bind['productGroup'] = $category;
		}
		$order = "order by rand() limit $num";
		$resultSet = $adapter->query($select.$where.$order, $bind);
		$results = $resultSet->fetchAll();
		return $results;
	}
	
	public function getItemsByCategory( $categoryName , $numOfItem )
	{
		$adapter = $this->getDefaultAdapter();
		
		$select = 'select asin,SmallImage,MediumImage,LargeImage,Title, Amount, Offers.`Condition`, Offers.Price ';
		$from = 'from products left join Offers on products.asin = Offers.key_asin ';
		$where = "where products.ProductGroup = :categoryName and MediumImage != '' and (Amount is not null or Offers.Price is not null) ";
		$orderby = "order by rand() limit ".intval($numOfItem);

		$bind = array("categoryName" => $categoryName );

		$resultSet = $adapter->query($select.$from.$where.$orderby, $bind);
		$results = $resultSet->fetchAll();
		return $results;
	}

	private function getColumnsListFromTable( $tableName )
	{
		$adapter = $this->getDefaultAdapter();
		$Columns = array();
	
		$resultSet = $adapter->query('show columns from '.$tableName);
		$results = $resultSet->fetchAll();
		foreach( $results as $fields) $Columns[] = $fields['Field'];
		return $Columns;
	}
	
	public function getItemsByAsins ( $asins )
	{
		
		$adapter = $this->getDefaultAdapter();       		
		
		$columns = array_merge(Model_Amazon::$itemQuickViewAttr, Model_Amazon::$imageNames );
		$columns = implode(',', array_keys($columns)).','
		           .'Offers.`Condition`, Offers.Price, Offers.OfferListingId,Offers.Availability, Offers.IsEligibleForSuperSaverShipping';
// 		$offersColumns = $this->getColumnsListFromTable('Offers');
// 		$productsColumns = $this->getColumnsListFromTable('Products');
// 		$offersColumns = array_diff($offersColumns, $productsColumns);
//         print_r(Model_Amazon::$itemQuickViewAttr);
// 		echo $columns; exit;

		$bind = array();
		for ( $i=1, $j = count($asins); $i <= $j; $i++)
		{
		   $bind['asin'.$i] = $asins[$i-1];
		}
		
		$placeholders = array_keys($bind);
		foreach($placeholders as &$value ) $value = ':'.$value;
		$placeholder = implode($placeholders,',');
		$select = 'select asin,'.$columns;
		$from = ' from products left join Offers on products.asin = Offers.key_asin';
		$where = " where asin in ( $placeholder )";
		
		$resultSet = $adapter->query($select.$from.$where, $bind);
		$results = $resultSet->fetchAll();
		return $results;
	}
	
	public function getProductCategoies()
	{
		$adapter = $this->getDefaultAdapter();
		
		$select = 'select distinct ProductGroup from products order by ProductGroup';
		$categoiesResultSet = $adapter->query($select);
		$categoiesResults = $categoiesResultSet->fetchAll();
		return $categoiesResults;
		
	}
	
	public function getCategoiesGroup() {
	
		$categoiesGroupResults = $this->getProductCategoies();
		$len = count($categoiesGroupResults);
		$result = array();
		$temp = '';
		$begin = 0 ;
		$superLong = 0;		
		for($i=0; $i<$len; $i++ )
		{   
		    $titleLen = strlen($categoiesGroupResults[$i]['ProductGroup']);  
			$temp .= $categoiesGroupResults[$i]['ProductGroup'].'  ';
			if ( $titleLen > 29 ) $superLong++; 
			if ( strlen( $temp) > 90
				 || ( $i-$begin >= 3 && $superLong >= 2 )	 
				 || ($i-$begin >= 4 && $titleLen > 25)					 
 				 || ($i-$begin >= 5 && $titleLen > 19) 
				 || $i-$begin >= 6 )
			{
			   $result[] = array_slice($categoiesGroupResults, $begin, $i-$begin );
			   $begin = $i;
			   $temp = '';
			   $superLong = $titleLen > 29 ? 1 : 0 ;
			}
			
			if ( $i == $len - 1 )
			{
				$result[] = array_slice($categoiesGroupResults, $begin, $i-$begin+1);
			}
		
		}
		return $result;
	}
	
}

