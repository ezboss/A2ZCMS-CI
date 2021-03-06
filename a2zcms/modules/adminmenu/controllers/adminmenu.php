<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
Author: Stojan Kukrika
Version: 1.0
*/

// ------------------------------------------------------------------------

class AdminMenu extends Website_Controller{
	
	function __construct(){
		parent::__construct();
		$this->load->model("Model_adminmenu");
		$this->load->module("users");
	}
	
	function head_navigation(){
		$data['current'] = $this->uri->segment(1);
		$data['currentuser'] = @$this->users->userdata();
		$data['langs'] = $this->config->item('ui_langs');
		$this->load->view("adminmenu",$data);
	}
	function left_navigation(){
		$data['current'] = $this->uri->segment(1);
		//Admin links
		if($this->users->_is_admin($this->session->userdata('user_id'))){
			$data['items'] = $this->Model_adminmenu->menu_admin();
		}
		
		$data['currentuser'] = @$this->users->userdata();
		$this->load->view("adminleftmenu", $data);
	}
		
}

?>