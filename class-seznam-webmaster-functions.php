<?php

class Seznam_Webmaster_Functions
{

    /**
     * Start up
     */
    public function __construct()
    {
		
    }

    /**
     * Logging reindex URLs
     */
    public function log_reindex($type = 'single', $data)
    {
		if ($type === 'all') {
			$table_index = 'seznam_webmaster_log_all';
		} else {
			$table_index = 'seznam_webmaster_log_single';
		}
		
		// Get log data and check if exists
		$log = get_option( $table_index );
		if ($log === false) {
			$log = [];
		}
		
		// Limit count of log items
		while ( count( $log ) >= 100 ) {
			array_shift($log);
		}
		
		$log[] = $data;
		
		update_option( $table_index, $log );
    }
}