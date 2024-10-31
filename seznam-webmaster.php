<?php
/**
 * @package Seznam_Webmaster
 * @version 1.4.7
 */
/*
Plugin Name: Seznam Webmaster
Plugin URI: https://lukashartmann.cz/wordpress-plugin-pro-seznam-webmaster/
Description: Integruje kód služby Seznam Webmaster a komunikuje se službou pomocí API. Po propojení se službou Seznam Webmaster odesílá ihned po uložení publikované stránky, příspěvky a další.
Author: Lukáš Hartmann
Version: 1.4.7
Author URI: https://lukashartmann.cz/
Text Domain: seznam-webmaster
 */

include_once 'class-seznam-webmaster-functions.php';
$seznam_webmaster_functions = new Seznam_Webmaster_Functions();

// Initialize admin options
include_once 'class-seznam-webmaster-options.php';
if( is_admin() ) {
    $seznam_webmaster_options = new Seznam_Webmaster_Options();
}

// Insert meta tag into head on front page
include_once 'class-seznam-webmaster-meta-tag.php';
$seznam_webmaster_meta_tag = new Seznam_Webmaster_Meta_Tag();

// Call reindex API after save post/taxonomy
include_once 'class-seznam-webmaster-reindex-single.php';
if( is_admin() ) {
    $seznam_webmaster_reindex_single = new Seznam_Webmaster_Reindex_Single();
}

// Call reindex API for multiple pages
include_once 'class-seznam-webmaster-reindex-all.php';
if( is_admin() ) {
    $seznam_webmaster_reindex_all = new Seznam_Webmaster_Reindex_All();
}


// Add settings link
function seznam_webmaster_add_plugin_page_settings_link( $links ) {
	$links[] = '<a href="' .
		admin_url( 'admin.php?page=seznam-webmaster-api' ) .
		'">' . __('Settings') . '</a>';
	return $links;
}
add_filter('plugin_action_links_'.plugin_basename(__FILE__), 'seznam_webmaster_add_plugin_page_settings_link');

// Add notice if API key empty
function seznam_webmaster_empty_key_notice() {
	$options = get_option( 'seznam_webmaster' );
	if ( ! isset ( $options['api_key'] ) || ! $options['api_key'] ) {
		?>
		<div class="notice notice-warning is-dismissible">
			<p>
				Není nastaven API klíč. Plugin Seznam Webmaster nebude fungovat správně. 
				Nastavte ho prosím v 
				<a href="<?php echo admin_url('admin.php?page=seznam-webmaster-api') ?>">
					nastavení API klíčů
				</a>.
			</p>
		</div>
		<?php
	}
}
add_action( 'admin_notices', 'seznam_webmaster_empty_key_notice' );