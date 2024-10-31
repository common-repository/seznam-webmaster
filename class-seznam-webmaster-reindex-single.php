<?php

class Seznam_Webmaster_Reindex_Single
{
    /**
     * Holds the values to be used in API call
     */
    private $options;
	
	/**
     * Holds instance of Seznam_Webmaster_Functions class
     */
    private $functions;

    /**
     * Start up
     */
    public function __construct()
    {
//		add_action( 'publish_page', array( $this, 'call_api_reindex_post' ), 10, 1 );
//		add_action( 'publish_post', array( $this, 'call_api_reindex_post' ), 10, 1 );
		add_action( 'post_updated', array( $this, 'call_api_reindex_post' ), 10, 1 );
		add_action( 'created_term', array( $this, 'call_api_reindex_term' ), 10, 1 );
		add_action( 'edited_term', array( $this, 'call_api_reindex_term' ), 10, 1 );
		$this->functions = new Seznam_Webmaster_Functions();
    }

    /**
     * Call Webmaster API to reindex post
     */
    public function call_api_reindex_post( $post_ID )
    {
		$this->options = get_option( 'seznam_webmaster' );
		
		// Reindex just public post types
		$args = array(
			'public'   => true,
		);
		$post_types = get_post_types( $args, 'names', 'and' );
		unset( $post_types[ 'attachment' ] );
		if ( !in_array( get_post_type(), $post_types ) ) return;
		
		if ( isset ( $this->options['api_key'] ) && $this->options['api_key'] && get_post_status($post_ID) === 'publish' ) {
			// Get API request URL
			$api_key = $this->options['api_key'];
			$url = get_the_permalink($post_ID);
			$url_encoded = urlencode( $url );
			$api_url = "https://reporter.seznam.cz/wm-api/web/document/reindex?key=$api_key&url=$url_encoded";
			
			// Call API to reindex
			$response = wp_remote_post( $api_url,
				array(
					'timeout' => 5,
				)
			);
			$body = wp_remote_retrieve_body( $response );
			$result = json_decode($body);
			
			// Process the result
			if ($result->status === 200) {
				$success = true;
				$message = '';
			} elseif ($result->status === 204) {
				$success = false;
				$message = 'Web není plně aktivován, zkuste to prosím později.';
			} else {
				$success = false;
				$message = $result->title . ": " . $result->detail;
			}
			
			// Prepare log data and store it
			$data = [
				'date' => date('Y-m-d H:i:s'),
				'url' => $url,
				'success' => $success,
				'message' => $message,
			];
			$this->functions->log_reindex('single', $data);
		}
    }
	
	/**
     * Call Webmaster API to reindex term
     */
    public function call_api_reindex_term( $term_id )
    {
		global $taxonomy;
		$this->options = get_option( 'seznam_webmaster' );
		
		// Reindex just public taxonomies
		$args = array(
			'public'   => true,
		);
		$public_taxonomies = get_taxonomies( $args, 'names', 'and' );
		unset( $public_taxonomies[ 'post_format' ] );
		if ( !in_array( $taxonomy, $public_taxonomies ) ) return;
		
		if ( isset ( $this->options['api_key'] ) && $this->options['api_key'] ) {
			// Get API request URL
			$api_key = $this->options['api_key'];
			$url = get_term_link( $term_id );
			$url_encoded = urlencode( $url );
			$api_url = "https://reporter.seznam.cz/wm-api/web/document/reindex?key=$api_key&url=$url_encoded";
			
			// Call API to reindex
			$response = wp_remote_post( $api_url,
				array(
					'timeout' => 5,
				)
			);
			$body = wp_remote_retrieve_body( $response );
			$result = json_decode($body);
			$response_code = wp_remote_retrieve_response_code( $response );
			
			// Process the result
			if ($result->status === 200) {
				$success = true;
				$message = '';
			} elseif ($response_code === 204) {
				$success = false;
				$message = 'Web není plně aktivován, zkuste to prosím později.';
			} else {
				$success = false;
				$message = $result->title . ": " . $result->detail;
			}
			
			// Prepare log data and store it
			$data = [
				'date' => date('Y-m-d H:i:s'),
				'url' => $url,
				'success' => $success,
				'message' => $message,
			];
			$this->functions->log_reindex('single', $data);
		}
    }
}