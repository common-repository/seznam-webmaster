<?php

class Seznam_Webmaster_Reindex_All
{
    /**
     * Holds the values to be used in API call
     */
    private $options;
	
	/**
     * Holds the values to be used for reindex
     */
    private $reindex_data;
	
	/**
     * Holds instance of Seznam_Webmaster_Functions class
     */
    private $functions;

    /**
     * Start up
     */
    public function __construct()
    {
		add_action( 'wp_ajax_seznam_webmaster_reindex_all', array( $this, 'reindex_all' ) );
		$this->functions = new Seznam_Webmaster_Functions();
	}
		
	/**
     * Call Webmaster API to reindex url
     */
    public function reindex_all()
    {
		check_ajax_referer( 'seznam-webmaster-ajax', 'security' );
		
		// Get reindex data from DB
		$this->reindex_data = get_option( 'seznam_webmaster_reindex_data' );
		if ( ! is_array( $this->reindex_data ) ) {
			$this->reindex_data = [
				'done' => false,
				'processed_items' => 0,
				'total_items' => 0,
				'last_id' => 0,
				'post' => true,
			];
		}
		
		if ( $this->reindex_data['done'] === true ) {
			wp_die();
		}
		
		$urls = [];
		if ( $this->reindex_data['post'] === true ) {
			$urls = $this->get_posts_urls();
		} else {
			$urls = $this->get_terms_urls();
		}
		
		foreach ( $urls as $id => $url ) {
			$this->call_api_reindex($url);
		}
		
		$this->reindex_data['processed_items'] += count( $urls );
		
		$response = [];
		if ( $this->reindex_data['done'] !== true && $this->reindex_data['processed_items'] < 500 ) {
			update_option( 'seznam_webmaster_reindex_data', $this->reindex_data );
			$response['done'] = false;
		} else {
			delete_option( 'seznam_webmaster_reindex_data' );
			$response['done'] = true;
		}
		
		$response['processed_items'] = $this->reindex_data['processed_items'];
		$response['total_items'] = $this->reindex_data['total_items'];
		$json_response = json_encode($response);
		
		echo $json_response;
		wp_die();
    }
	
	/*
	 * Get URLs of published posts, pages and products
	 */
	private function get_posts_urls() {
		global $wpdb;
		$urls = [];
		
		// Reindex just public post types
		$args = array(
			'public'   => true,
		);
		$types = get_post_types( $args, 'names', 'and' );
		unset( $types[ 'attachment' ] );
		$types_join = join("','", $types);

		$posts = $wpdb->get_results( 
				"SELECT ID FROM {$wpdb->prefix}posts 
				WHERE post_type IN ('$types_join') 
					AND post_status = 'publish' 
				ORDER BY ID ASC", 
				OBJECT 
		);
		
		// If first run, count total items
		if ( $this->reindex_data['last_id'] === 0) {
			$this->reindex_data['total_items'] += count($posts);
		}
				
		foreach ( $posts as $post ) {
			// Skip reindexed posts
			if ( $this->reindex_data['last_id'] >= $post->ID ) {
				continue;
			}

			$permalink = get_permalink( $post->ID );
			// Error - skip it
			if ( ! is_string( $permalink ) ) continue;
			$urls[ $post->ID ] = $permalink;

			// Maximum URLs per run is 5
			if ( count( $urls ) >= 5 ) {
				$this->reindex_data['last_id'] = $post->ID;
				break;
			}
		}
		
		// If last items, set next run to terms
		if ( count ($urls) < 5 ) {
			$this->reindex_data['last_id'] = 0;
			$this->reindex_data['post'] = false;
		}
		
		return $urls;
	}

	/*
	 * Get URLs of selected terms
	 */
	private function get_terms_urls() {
		global $wpdb;
		$urls = [];
		
		// Reindex just public taxonomies
		$args = array(
			'public'   => true,
		);
		$public_taxonomies = get_taxonomies( $args, 'names', 'and' );
		unset( $public_taxonomies[ 'post_format' ] );
		$taxonomies_join = join("','", $public_taxonomies);
		
		$terms = $wpdb->get_results( 
				"SELECT t.term_id FROM {$wpdb->prefix}terms t 
				JOIN {$wpdb->prefix}term_taxonomy tt ON t.term_id = tt.term_id
				WHERE tt.taxonomy IN ('$taxonomies_join') 
				ORDER BY term_id ASC", 
				OBJECT 
		);
			
		// If first run, add total items
		if ( $this->reindex_data['last_id'] === 0) {
			$this->reindex_data['total_items'] += count($terms);
		}

		foreach ( $terms as $term ) {
			// Skip reindexed posts
			if ( $this->reindex_data['last_id'] >= $term->term_id ) {
				continue;
			}

			$term_link = get_term_link( (int) $term->term_id );
			// Error - skip it
			if ( ! is_string( $term_link ) ) continue;
			$urls[ $term->term_id ] = $term_link;
			
			// Maximum URLs per run is 5
			if ( count( $urls ) >= 5 ) {
				$this->reindex_data['last_id'] = $term->term_id;
				break;
			}
		}
		
		// If last items, set process done
		if ( count ($urls) < 5 ) {
			$this->reindex_data['last_id'] = $term->term_id;
			$this->reindex_data['done'] = true;
		}
		
		return $urls;
	}

	/**
     * Call Webmaster API to reindex URL
     */
    public function call_api_reindex($url)
    {
		$this->options = get_option( 'seznam_webmaster' );
		
		if ( isset ( $this->options['api_key'] ) && $this->options['api_key'] && $url ) {
			// Get API request URL
			$api_key = $this->options['api_key'];
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
			$this->functions->log_reindex('all', $data);
			
		}
    }
}