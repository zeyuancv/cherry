<?php
namespace Zeyuan\Cherry\mvc;

class Model{
	protected $_db;
	
	public function __construct(){
		require_once (APP_PATH.D.'vendor'.D.'thingengineer'.D.'mysqli-database-class'. D .'MysqliDb.php');
		$this->_db = new \MysqliDb (DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
		$this->_db->setPrefix (TB_PREFIX);
	}
	
	
}