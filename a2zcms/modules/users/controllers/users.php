<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
Author: Stojan Kukrika
Version: 1.0
*/

// ------------------------------------------------------------------------
class Users extends Website_Controller{
	
	private $page;
	private $pagecontent;
	function __construct(){
		parent::__construct();		
		$this->load->model(array('Model_user', "Model_message"));	
	
	}
	function login(){
		$this->page = $this->db->limit(1)->get('pages')->first_row();
		$this->pagecontent = Website_Controller::createSiderContent($this->page->id);
		if($this->_is_logged_in()){
			redirect('');
		}
		
		if($_POST){
			  if ($u = $this->Model_user->login($this->input->post('username'),$this->input->post('password')))
		        {		        													
		        	$user = $this->Model_user->selectuser($this->input->post('username'));
		        	$data = array(
		        	'user_id' => $user->id,
					'username' => $user->username,
					'name' => $user->name,
					'surname' => $user->surname,
					'logged_in' => true,
					'admin_logged_in' => $this->_is_admin($user->id),
					);
					$this->session->set_userdata($data);
					$result = $this->db->from('assigned_roles')
										->join('permission_role','assigned_roles.role_id=permission_role.role_id')
										->join('permissions','permissions.id=permission_role.permission_id')
										->where('assigned_roles.user_id',$this->session->userdata("user_id"))
										->select('name')
										->get()->result();
					foreach ($result as $row)
					{
						$this->session->set_userdata($row->name,$row->name);
					}					
					redirect('');
		        }
		        else
		        {
		        	echo '<br><p class="text-danger">Wrong username or password!</p>';
		        }
		}		
		$this->_member_area();		
		$data['content'] = array(
            'right_content' => $this->pagecontent['sidebar_right'],
            'left_content' => $this->pagecontent['sidebar_left'],
        );
		$this->load->view('login',$data);
	}
	function login_partial(){			
		
		$this->load->view('login_partial');
	}
	
	function logout(){
		$this->session->sess_destroy();
		redirect('');
	}
	
	function register(){
		$this->page = $this->db->limit(1)->get('pages')->first_row();
		$this->pagecontent = Website_Controller::createSiderContent($this->page->id);
		$this->_member_area();
		
		$data['content'] = array(
            'right_content' => $this->pagecontent['sidebar_right'],
            'left_content' => $this->pagecontent['sidebar_left'],
        );
		
		$this->load->view('register', $data);
		
		$this->load->library('form_validation');
		$this->form_validation->set_rules('password', "Password", 'required');
		$this->form_validation->set_rules('confirm_password', "Confirm password", 'required');
		$this->form_validation->set_rules('username', "Username", 'required|is_unique[users.username]');
		$this->form_validation->set_rules('email', "Email", 'required|valid_email|is_unique[users.email]');
		if ($this->form_validation->run() == TRUE)
        {
			$this->load->library('hash');
			
			if($this->input->post('password')!="" && $this->input->post('password')==$this->input->post('confirm_password'))
			{
				$code = md5(microtime() . $this->input->post('password'));
				$this->Model_user->insert(array('name'=>$this->input->post('name'),
											'surname'=>$this->input->post('surname'),
											'username'=>$this->input->post('username'),
											'password'=>$this->hash->make($this->input->post('password')),
											'email'=>$this->input->post('email'),
											'confirmation_code'=> $code,
											'confirmed'=>0,
											'active'=>1,
											'created_at' => date("Y-m-d H:i:s"),
											'updated_at' => date("Y-m-d H:i:s")));
	        	echo '<div class="container"><div class="col-xs-12 col-sm-6 col-lg-8"><br>
						<div class="row">You have successfully registered</p></div></div></div>';
					
					//Send validation mail 
					
					$this->load->library('email');

					$this->email->from($this->Model_user->getsiteemail()->value, $this->Model_user->getsitetitle()->value);
					$this->email->to($this->input->post('email')); 
					
					$this->email->subject('Confirmation');
					$this->email->message("Confirm your subscription <a href=''>Confirmar</a>".$code);	
					$this->email->send();
	        }
	        else
	        {
	            echo '<br><p class="text-danger">Password not equal</p>';
	        }
		}		
		
		
	}
	

	function _member_area(){
		if($this->_is_logged_in()){
			redirect('');
		}
	}
	
	function _is_logged_in(){
		if($this->session->userdata('logged_in')){
			return true;
		}else{
			return false;
		}
	}
	
	function userdata(){
		if($this->_is_logged_in()){
			return $this->Model_user->select($this->session->userdata('user_id'));
		}else{
			return false;
		}
	}
	
	function _is_admin($user_id=0)
	{
		$roles = $this->Model_user->isadmin($user_id);
		$is_admin = false;
		if(!empty($roles)){
			foreach($roles as $item)
			{
				if($item->is_admin=='1')
				{
					$is_admin = true;
				}
			}	
		}	 
		return $is_admin;
	}

	function account ()
	{
		$this->page = $this->db->limit(1)->get('pages')->first_row();
		$this->pagecontent = Website_Controller::createSiderContent($this->page->id);
		
		$data['content'] = array(
            'right_content' => $this->pagecontent['sidebar_right'],
            'left_content' => $this->pagecontent['sidebar_left'],
        );
		
		$this->load->view('changepassword', $data);
		
		$this->load->library('hash');					
		if($this->input->post('old_password')!="" && $this->input->post('password')!="" && $this->input->post('password')==$this->input->post('confirm_password'))
		{
			$user = $this->Model_user->selectuser($this->session->userdata('username'));
			if($user->password==$this->hash->make($this->input->post('old_password')))
			{
				$data = array('password'=>$this->hash->make($this->input->post('password')));
				$this->Model_user->update($data,$this->session->userdata('user_id'));
				$this->logout();
			}
			else {
				echo '<br><p class="text-danger">Old password is not valid!</p>';
			}
		}
	}
	
	function messages ()
	{
		$this->page = $this->db->limit(1)->get('pages')->first_row();
		$this->pagecontent = Website_Controller::createSiderContent($this->page->id);
		
		$sent = $this->Model_message->selectSend($this->session->userdata('user_id'));
		foreach($sent as $item){
			if($this->session->userdata('timeago')=='Yes'){
				$item->created_at =timespan(strtotime($item->created_at), time() ) . ' ago' ;
			}
			else{				
				$item->created_at = date($this->session->userdata("datetimeformat"),strtotime($item->created_at));
			}
		}
		$received = $this->Model_message->selectReceived($this->session->userdata('user_id'));
		foreach($received as $item){
			if($this->session->userdata('timeago')=='Yes'){
				$item->created_at =timespan(strtotime($item->created_at), time() ) . ' ago' ;
			}
			else{				
				$item->created_at = date($this->session->userdata("datetimeformat"),strtotime($item->created_at));
			}
		}
		$data['send'] = $sent;
		$data['received'] = $received;
		$data['allUsers'] = $this->Model_user->selectAll($this->session->userdata('user_id'));
		
		$data['content'] = array(
            'right_content' => $this->pagecontent['sidebar_right'],
            'left_content' => $this->pagecontent['sidebar_left'],
        );
		
		$this->load->view('messages', $data);
	}
	
	function readmessage($id_message)
	{
		$data = array('read'=>1, 'updated_at'=>date("Y-m-d H:i:s"));
		$this->Model_message->update($data,$id_message);
	}
	
	function sendmessage()
	{
		if($this->input->post('subject')!="" && $this->input->post('recipients')!="")
		{
			foreach($this->input->post('recipients') as $to){
				$this->Model_message->insert(array('subject'=>$this->input->post('subject'),
											'user_id_from'=>$this->session->userdata('user_id'),
											'user_id_to'=>$to,
											'content'=>$this->input->post('message'),
											'read'=>'0',
											'created_at' => date("Y-m-d H:i:s"),
											'updated_at' => date("Y-m-d H:i:s")));
			}
		}
		redirect(base_url('users/messages'));
	}
	function deletereceiver($id_message)
	{
		$this->Model_message->deletereceiver($id_message);
	}
	function deletesender($id_message)
	{
		$this->Model_message->deletesender($id_message);
	}
	
	function forgot()
	{
		$this->page = $this->db->limit(1)->get('pages')->first_row();
		$this->pagecontent = Website_Controller::createSiderContent($this->page->id);
		
		$data['content'] = array(
            'right_content' => $this->pagecontent['sidebar_right'],
            'left_content' => $this->pagecontent['sidebar_left'],
        );
		
		$this->load->view('forgot', $data);		
		
		if($_POST){
			  if ($u = $this->Model_user->checkuser($this->input->post('username'),$this->input->post('email')))
		        {
		        	$characters = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
				    $randstring = '';
				    for ($i = 0; $i < 8; $i++) {
				        $randstring = $characters[rand(0, strlen($characters))];
				    }
					$this->load->library('hash');	
					$data = array('password'=>$this->hash->make($randstring));
					$this->Model_user->update($data,$u->id);
				
					//Send validation mail 
					
					$this->load->library('email');

					$this->email->from($this->Model_user->getsiteemail()->value, $this->Model_user->getsitetitle()->value);
					$this->email->to($this->input->post('email')); 
					
					$this->email->subject('Change password');
					$this->email->message("Your password is changed to: ".$randstring);	
					$this->email->send();
					
				}
			  else {
			  	echo '<br><p class="text-danger">Username and email is not match in users of this site!</p>';
			  }
		}
	
	}	
	
}

?>