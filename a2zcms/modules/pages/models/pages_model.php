<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
Author: Stojan Kukrika
Version: 1.0
*/

// ------------------------------------------------------------------------

class Pages_model extends DataMapper {

	
	function __construct()
	{
		parent::__construct();
		
		$this->table = 'pages';
	}
	
}