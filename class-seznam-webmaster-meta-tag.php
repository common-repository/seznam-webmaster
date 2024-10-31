<?php

class Seznam_Webmaster_Meta_Tag
{
    /**
     * Holds the values to be used in the template
     */
    private $options;

    /**
     * Start up
     */
    public function __construct()
    {
        add_action( 'wp_head', array( $this, 'add_meta_tag' ) );
    }

    /**
     * Add meta tag
     */
    public function add_meta_tag()
    {
		if ( ! is_front_page() ) {
			return false;
		}
		
		$this->options = get_option( 'seznam_webmaster' );
		
        if ( isset($this->options) && $this->options['meta_tag'] ) {
			
			printf(
				'<meta name="seznam-wmt" content="%s" />',
				esc_attr( $this->options['meta_tag'])
			);
		}
    }
}