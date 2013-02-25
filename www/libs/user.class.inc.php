<?php
class user {

	////////////////Private Variables//////////

	private $db; //mysql database object
	private $id;
	private $user_name;
	private $full_name;
	private $supervisor_name;
	private $supervisor_id;
	private $enabled;
	private $group_id;
	private $group_name;
	private $time_created;
	private $ldap;
	private $default_project; //default project object
	private $default_data_dir; //default data_dir object

	////////////////Public Functions///////////

	public function __construct($db,$ldap,$id = 0,$username = "") {
		$this->db = $db;
		$this->ldap = $ldap;
		if ($id != 0) {
			$this->load_by_id($id);
		}
		elseif (($id == 0) && ($username != "")) {
			$this->load_by_username($username);

		}
	}
	public function __destruct() {
	}
	public function create($username,$supervisor_id,$group_id,$bill_project,$cfop = "",$activity = "") {
		$username = trim(rtrim($username));
		

		$error = false;
		//Verify Username
		if ($username == "") {
			$error = true;
			$message = "<div class='alert'>Please enter a username.</div>";
		}
		elseif ($this->get_user_exist($username)) {
			$error = true;
			$message .= "<div class='alert'>User already exists in database.</div>";
		}
		elseif (!$this->ldap->is_ldap_user($username)) {
			$error = true;
			$message = "<div class='alert'>User does not exist in LDAP database.</div>";
		}


		//Verify CFOP/Activty Code if Member of Users group and bill project is enabled
		$project = new project($this->db);
		if (($group_id == get_users_group_id($this->db)) && !$project->verify_cfop($cfop) && $bill_project) {
			$error = true;
			$message .= "<div class='alert'>Invalid CFOP.</div>";
		}

		if (($group_id == get_users_group_id($this->db)) && !$project->verify_activity_code($activity) && $bill_project) {
			$error = true;
			$message .= "<div class='alert'>Invalid Activity Code.</div>";
		}

		//If Errors, return with error messages
		if ($error) {
			return array('RESULT'=>false,
					'MESSAGE'=>$message);
		}

		//Everything looks good, add user and default user project
		else {
			$full_name = $this->ldap->get_ldap_full_name($username);
			$home_dir = $this->ldap->get_home_dir($username);
			$user_array = array('user_name'=>$username,
					'user_full_name'=>$full_name,
					'user_group_id'=>$group_id,
					'user_enabled'=>1
			);
			$user_id = $this->db->build_insert("users",$user_array);
			$this->load_by_id($user_id);
			//If member of Users group, add default project
			if ($this->get_group() == "Users") {
				$description = "default";
				$default = 1;
				$project->create($username,$username,$description,$default,$bill_project,$user_id,$cfop,$activity);
				$data_dir = new data_dir($this->db);
				$data_dir->create($project->get_project_id(),$home_dir);
			}
			//Sets supervisor id to user id if the user is a supervisor or  admin user
			if ($supervisor_id == 0) {
				$supervisor_id = $user_id;
			}
			$this->set_supervisor($supervisor_id);
			return array('RESULT'=>true,
					'MESSAGE'=>'User succesfully added.',
					'user_id'=>$user_id);
		}

	}
	public function get_user_id() {
		return $this->id;
	}
	public function get_user_name() {
		return $this->user_name;
	}
	public function get_full_name() {
		return $this->full_name;
	}
	public function get_group() {
		return $this->group_name;
	}
	public function get_group_id() {
		return $this->group_id;
	}
	public function get_supervisor_name() {
		return $this->supervisor_name;
	}
	public function get_supervisor_id() {
		return $this->supervisor_id;
	}
	public function get_enabled() {
		return $this->enabled;
	}
	public function get_time_created() {
		return $this->time_created;
	}
	public function get_jobs_summary($start_date,$end_date) {

		$sql = "SELECT ROUND(SUM(jobs.job_total_cost),2) as total_cost, ";
		$sql .= "ROUND(SUM(jobs.job_billed_cost),2) as billed_cost, ";
		$sql .= "COUNT(1) as num_jobs, ";
		$sql .= "queues.queue_name as queue, ";
		$sql .= "projects.project_name as project, ";
		$sql .= "cfops.cfop_value as cfop, cfops.cfop_activity as activity ";
		$sql .= "FROM jobs ";
		$sql .= "LEFT JOIN queues ON queues.queue_id=jobs.job_queue_id ";
		$sql .= "LEFT JOIN projects ON projects.project_id=jobs.job_project_id ";
		$sql .= "LEFT JOIN cfops ON cfops.cfop_id=jobs.job_cfop_id ";
		$sql .= "WHERE DATE(jobs.job_end_time) BETWEEN '" . $start_date ."' AND '" . $end_date . "' ";
		$sql .= "AND jobs.job_user_id='". $this->get_user_id() . "' ";
		$sql .= "GROUP BY jobs.job_queue_id, jobs.job_project_id, jobs.job_user_id";

		$result = $this->db->query($sql);
		foreach($result as $key=>$value) {
			if ($value['total_cost'] == 0.00) {
				$result[$key]['total_cost'] = 0.01;
			}
		}
		return $result;
	}
	public function get_jobs_report($start_date,$end_date) {
		$sql = "SELECT jobs.job_number as 'Job Number', ";
		$sql .= "jobs.job_name as 'Job Name', ";
		$sql .= "jobs.job_total_cost as 'Cost', ";
		$sql .= "queues.queue_name as 'Queue', ";
		$sql .= "projects.project_name as 'Project', ";
		$sql .= "jobs.job_submission_time as 'Submission Time', ";
		$sql .= "jobs.job_start_time as 'Start Time', jobs.job_end_time as 'End Time', ";
		$sql .= "jobs.job_ru_wallclock as 'Elapsed Time (Secs)', jobs.job_cpu_time as 'CPU Time (Secs)', ";
		$sql .= "round(jobs.job_reserved_mem / 1073741824,2) as 'Reserved Memory (GB)', ";
		$sql .= "round(jobs.job_used_mem /1073741824,2) as 'Used Memory (GB)', ";
		$sql .= "round(jobs.job_maxvmem / 1073741824,2) as 'Virtual Memory (GB)', ";
		$sql .= "jobs.job_slots as 'CPUs' ";
		$sql .= "FROM jobs ";
		$sql .= "LEFT JOIN queues ON queues.queue_id=jobs.job_queue_id ";
		$sql .= "LEFT JOIN projects ON projects.project_id=jobs.job_project_id ";
		$sql .= "WHERE DATE(jobs.job_end_time) BETWEEN '" . $start_date ."' AND '" . $end_date . "' ";
		$sql .= "AND jobs.job_user_id='". $this->get_user_id() . "' ";
		$result = $this->db->query($sql);
		return $result;



	}
	public function get_data_summary($month,$year) {
		$sql = "SELECT data_dir.data_dir_path as directory, ";
		$sql .= "data_cost.data_cost_dir as data_cost_dir, ";
		$sql .= "projects.project_name as project, ";
		$sql .= "ROUND((data_usage.data_usage_bytes / 1099511627776),4) as terabytes, ";
		$sql .= "data_usage.data_usage_files as files, ";
		$sql .= "ROUND(data_usage.data_usage_total_cost,2) as total_cost, ";
		$sql .= "ROUND(data_usage.data_usage_billed_cost,2) as billed_cost, ";
		$sql .= "cfops.cfop_value as cfop, ";
		$sql .= "cfops.cfop_activity as activity_code ";
		$sql .= "FROM data_usage ";
		$sql .= "LEFT JOIN projects ON projects.project_id=data_usage.data_usage_project_id ";
		$sql .= "LEFT JOIN data_dir ON data_dir.data_dir_id=data_usage.data_usage_data_dir_id ";
		$sql .= "LEFT JOIN cfops ON cfops.cfop_id=data_usage.data_usage_cfop_id ";
		$sql .= "LEFT JOIN data_cost ON data_cost.data_cost_id=data_usage.data_usage_data_cost_id ";
		$sql .= "WHERE projects.project_owner='" . $this->get_user_id() . "' ";
		$sql .= "AND YEAR(data_usage.data_usage_time)='" . $year . "' ";
        $sql .= "AND MONTH(data_usage.data_usage_time)='" . $month . "' ";
		return $this->db->query($sql);
		
	}
	public function default_project() {
		$this->project = new project($this->db,0,date("Y-m-d H:i:s"),$this->get_user_name());
		return $this->project;

	}
	public function default_data_dir() {
		//$this->dafault_data_dir = new data_dir($this->db)
		//return $this->default_data_dir;
	}
	public function get_projects() {
		$sql = "SELECT * FROM projects WHERE project_enabled='1'";
		$all_projects = $this->db->query($sql);
		$ldap_groups = $this->ldap->get_user_groups($this->get_user_name());
		$user_projects = array();
		foreach ($ldap_groups as $group) {
			foreach ($all_projects as $project) {
				if ($group == $project['project_ldap_group']) {
					array_push($user_projects,$project);
				}

			}

		}
		return $user_projects;

	}
	public function is_project_member($project) {
		$user_projects = $this->get_projects();
		foreach ($user_projects as $user_project) {
			if ($user_project == $project) {
				return true;
			}
		}
		return false;
	}
	public function get_queues() {
		$sql = "SELECT queue_name,queue_ldap_group FROM queues WHERE queue_enabled='1'";
		$all_queues = $this->db->query($sql);
		$ldap_groups = $this->ldap->get_user_groups($this->get_user_name());
		$user_queues = array();
		foreach ($all_queues as $queue) {
			if ($queue['queue_ldap_group'] === "") {
				array_push($user_queues,$queue['queue_name']);
			}
			else {
				foreach ($ldap_groups as $group) {
					if ($group == $queue['queue_ldap_group']) {
						array_push($user_queues,$queue['queue_name']);
					}
				}
			}

		}
		return $user_queues;




	}
	public function is_supervisor() {
		if (($this->get_user_id() == $this->get_supervisor_id()) && ($this->get_group()=="Users")) {
			return true;
		}
		else {
			return false;
		}

	}
	public function enable() {
		$sql = "UPDATE users SET user_enabled='1' WHERE user_id='" . $this->get_user_id() . "' LIMIT 1";
		$this->db->non_select_query($sql);
		$this->enabled = true;
		return true;
	}
	public function disable() {
		$sql = "UPDATE users SET user_enabled='0' WHERE user_id='" . $this->get_user_id() . "' LIMIT 1";
		$this->enabled = false;
		$this->db->non_select_query($sql);
		return true;

	}
	public function set_group($group_id) {
		$sql = "UPDATE users SET user_group_id='" . $group_id . "' WHERE user_id='" . $this->get_user_id() . "' LIMIT 1";
		$this->db->non_select_query($sql);
		$this->group_id = $group_id;

	}
	public function set_supervisor($supervisor_id) {
		$sql = "UPDATE users SET user_supervisor='" . $supervisor_id . "' WHERE user_id='" . $this->get_user_id() . "'";
		$this->db->non_select_query($sql);
		//gets supervisors username
		$supervisor_sql = "SELECT user_name FROM users WHERE user_id='" . $supervisor_id . "' LIMIT 1";
		$result = $this->db->query($supervisor_sql);

		$this->supervisor_id = $supervisor_id;
		$this->supervisor_name = $result[0]['user_name'];;
		return $this->db->non_select_query($sql);
	}
	public function get_supervising_users() {

		if ($this->is_supervisor()) {
			$sql = "SELECT user_id as id, user_name as username, user_full_name as fullname ";
			$sql .= "FROM users LEFT JOIN groups ON groups.group_id=users.user_group_id ";
			$sql .= "WHERE user_supervisor='" . $this->get_user_id() . "' AND user_enabled='1' ";
			$sql .= "AND groups.group_name='Users' ORDER BY username ASC";
			return $this->db->query($sql);
		}
		elseif ($this->is_admin()) {
			$sql = "SELECT user_id as id, user_name as username, user_full_name as fullname ";
			$sql .= "FROM users LEFT JOIN groups ON groups.group_id=users.user_group_id ";
			$sql .= "WHERE user_enabled='1' ";
			$sql .= "AND groups.group_name='Users' ORDER BY username ASC";
			return $this->db->query($sql);

		}
		return false;
	}
	public function is_admin() {
		if ($this->get_group() == "Administrators") {
			return true;
		}
		else { return false;
		}
	}
	//is_supervising_user()
	//$user_id - user id to test if you are the supervisor
	//returns true if user is supervising the respected user, false otherwise
	public function is_supervising_user($user_id) {
		if ($this->is_supervisor()) {
			$sql = "SELECT user_supervisor as supervisor_id ";
			$sql .= "FROM users LEFT JOIN groups ON groups.group_id=users.user_group_id ";
			$sql .= "WHERE user_id='" . $user_id . "' AND user_enabled='1' ";
			$sql .= "AND groups.group_name='Users' LIMIT 1";
			$result = $this->db->query($sql);
			if ($result[0]['supervisor_id'] == $this->get_user_id()) {
				return TRUE;
			}
			else {
				return FALSE;
			}
		}
		return FALSE;

	}
	public function email_bill($admin_email,$start_date = 0,$end_date = 0) {
		if (($start_date == 0) && ($end_date == 0)) {
			$end_date = date('Ymd',strtotime('-1 second', strtotime(date('Ym') . "01")));
			$start_date = substr($end_date,0,4) . substr($end_date,4,2) . "01";
		}
		$jobs_summary = $this->get_jobs_summary($start_date,$end_date);
		$user_stats = new user_stats($this->db,$this->get_user_id(),$start_date,$end_date);

		$subject = "Biocluster Accounting Bill - " . get_pretty_date($start_date) . "-" . get_pretty_date($end_date);
		$to = $this->get_user_name() . "@illinois.edu";
		$message = "<p>Biocluster Accounting Bill - " . get_pretty_date($start_date) . "-" . get_pretty_date($end_date) . "</p>";
		$message .= "<br>Name: " . $this->get_full_name();
		$message .= "<br>Username: " . $this->get_user_name();
		$message .= "<br>Start Date: " . get_pretty_date($start_date);
		$message .= "<br>End Date: " . get_pretty_date($end_date);
		$message .= "<br>Number of Jobs: " . $user_stats->get_num_jobs();
		$message .= "<p>Below is your bill.  You can go to https://biocluster.igb.illinois.edu/accounting/ ";
		$message .= "to view a detail listing of your jobs.";
		$message .= "<p>Cluster Usage</p>";
		if (count($jobs_summary)) {
			$message .= "<p><table border>";
			$message .= "<tr><td>Queue</td><td>Project</td>";
			$message .= "<td>Cost</td><td>Billed Amount</td><td>CFOP</td><td>Activity Code</td></tr>";
			foreach ($jobs_summary as $summary) {
				$message .= "<tr>";
				$message .= "<td>" . $summary['queue'] . "</td>";
				$message .= "<td>" . $summary['project'] . "</td>";
				$message .= "<td>$" . number_format($summary['total_cost'],2) . "</td>";
				$message .= "<td>$" . number_format($summary['billed_cost'],2) . "</td>";
				$message .= "<td>" . $summary['cfop'] . "</td>";
				$message .= "<td>" . $summary['activity'] . "</td>";
				$message .= "</tr>";
			}
			$message .= "</table>";
		}
		else {
			$message .= "<p>No Jobs";

		}
		$month = date('m',strtotime($start_date));
		$year = date('Y',strtotime($start_date));
		$data_summary = $this->get_data_summary($month,$year);
		$message .= "<p>Data Usage</p>";	
		if (count($data_summary)) {
			$message .= "<p><table border>";
			$message .= "<tr><td>Directory</td>";
			$message .= "<td>Type</td>";
			$message .= "<td>Project</td>";
			$message .= "<td>Terabytes</td>";
			$message .= "<td>Cost</td>";
			$message .= "<td>Billed Amount</td>";
			$message .= "<td>CFOP</td>";
			$message .= "<td>Activity Code</td>";
			$message .= "</tr>";
			foreach ($data_summary as $data) {
				$message .= "<tr>";
				$message .= "<td>" . $data['directory'] . "</td>";
			        $message .= "<td>" . $data['data_cost_dir'] . "</td>";
			        $message .= "<td>" . $data['project'] . "</td>";
			        $message .= "<td>" . $data['terabytes'] . "</td>";
			        $message .= "<td>$" . number_format($data['total_cost'],2) . "</td>";
			        $message .= "<td>$" . number_format($data['billed_cost'],2) . "</td>";
			        $message .= "<td>".  $data['cfop'] . "</td>";
			        $message .= "<td>" . $data['activity_code'] . "</td>";
			        $message .= "</tr>";


			}
			$message .= "</table>";
		}
		else {
			$message .= "No Data Usage.";
		}

		$headers = "From: " . $admin_email . "\r\n";
		$headers .= "Content-Type: text/html; charset=iso-8859-1" . "\r\n";
		mail($to,$subject,$message,$headers," -f " . $admin_email);



	}
	//////////////////Private Functions//////////
	private function load_by_id($id) {
		$this->id = $id;
		$this->get_user();
	}
	private function load_by_username($username) {
		$username = mysql_real_escape_string($username,$this->db->get_link());
		$sql = "SELECT user_id FROM users WHERE user_name = '" . $username . "' LIMIT 1";
		$result = $this->db->query($sql);
		if (isset($result[0]['user_id'])) {
			$this->id = $result[0]['user_id'];
			$this->get_user();
		}
	}
	private function get_user() {

		$sql = "SELECT users.user_id, users.user_group_id, users.user_name, ";
		$sql .= "users.user_full_name, users.user_enabled, users.user_time_created, ";
		$sql .= "groups.group_name, ";
		$sql .= "supervisor.user_id as supervisor_id, supervisor.user_name as supervisor_name ";
		$sql .= "FROM users ";
		$sql .= "LEFT JOIN groups ON groups.group_id=users.user_group_id ";
		$sql .= "LEFT JOIN users AS supervisor ON supervisor.user_id=users.user_supervisor ";
		$sql .= "WHERE users.user_id='" . $this->id . "' LIMIT 1";
		$result = $this->db->query($sql);
		$this->user_name = $result[0]['user_name'];
		$this->full_name = $result[0]['user_full_name'];
		$this->time_created = $result[0]['user_time_created'];
		$this->supervisor_name = $result[0]['supervisor_name'];
		$this->supervisor_id = $result[0]['supervisor_id'];
		$this->enabled = $result[0]['user_enabled'];
		$this->group_name = $result[0]['group_name'];
		$this->group_id = $result[0]['user_group_id'];


	}
	private function get_user_exist($username) {

		$username = mysql_real_escape_string($username,$this->db->get_link());
		$sql = "SELECT COUNT(1) as count FROM users WHERE user_name='" . $username . "' AND user_enabled='1'";
		$result = $this->db->query($sql);
		return $result[0]['count'];

	}
	private function get_disable_user_id($username) {

		$username = mysql_real_escape_string($username,$this->db->get_link());
		$sql = "SELECT user_id FROM users WHERE user_name='" . $username . "' AND user_enabled='0'";
		$result = $this->db->query($sql);
		if (count($result)) {
			return $result[0]['user_id'];
		}
		else {
			return false;
		}
	}


}


?>
