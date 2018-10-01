<?php
require_once 'includes/main.inc.php';
require_once 'includes/session.inc.php';

if (isset($_POST['create_report_full'])) {

	$network = $_POST['network'];
	$search = $_POST['search'];
	$exact = $_POST['exact'];
	$start_date = $_POST['start_date'];
	$end_date = $_POST['end_date'];
	$type = $_POST['report_type'];
	$data = functions::get_devices($db,$network,$search,$exact,$start_date,$end_date);
	$filename = "report." . $type; 


	switch ($type) {
		case 'csv':
			report::create_csv_report($data,$filename);
			break;
		case 'xls':
        		report::create_excel_2003_report($data,$filename);
	                break;
		case 'xlsx':
			report::create_excel_2007_report($data,$filename);
			break;
	}

}

elseif (isset($_POST['create_report_no_spares'])) {
        $network = $_POST['network'];
        $search = $_POST['search'];
        $exact = $_POST['exact'];
        $start_date = $_POST['start_date'];
        $end_date = $_POST['end_date'];
        $type = $_POST['report_type'];
	$include_spares = false;
        $data = functions::get_devices($db,$network,$search,$exact,$start_date,$end_date,$include_spares);
        $filename = "report." . $type;


        switch ($type) {
                case 'csv':
                        report::create_csv_report($data,$filename);
                        break;
                case 'xls':
                        report::create_excel_2003_report($data,$filename);
                        break;
                case 'xlsx':
                        report::create_excel_2007_report($data,$filename);
                        break;
        }




}
elseif (isset($_POST['create_host_file'])) {
        $network = $_POST['network'];
        $search = $_POST['search'];
        $exact = $_POST['exact'];
        $start_date = $_POST['start_date'];
        $end_date = $_POST['end_date'];
        $type = $_POST['report_type'];
	$include_spares = false;
	$data = functions::get_devices($db,$network,$search,$exact,$start_date,$end_date,$include_spares);
	$filename = "hosts";
	functions::create_host_file($data,$filename);
	



}
?>
