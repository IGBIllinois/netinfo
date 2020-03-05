<?php

class log {

	const MAX_FILE_LENGTH = 1000;

	public static function send_log($message) {
                $current_time = date('Y-m-d H:i:s');
                $full_msg = $current_time . ": " . $message . "\n";

                if (settings::log_enabled()) {
                        file_put_contents(settings::get_log_file(),$full_msg,FILE_APPEND | LOCK_EX);
                }
                if (php_sapi_name() == "cli") {
                        echo $full_msg;
                }
        }

	public static function get_log() {
		//$contents = file_get_contents(settings::get_log_file(),FALSE,NULL,self::MAX_FILE_LENGTH);
		$contents = file_get_contents(settings::get_log_file());
		return $contents;


	}


}

?>
