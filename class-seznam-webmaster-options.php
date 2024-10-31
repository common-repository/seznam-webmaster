<?php

class Seznam_Webmaster_Options
{
    /**
     * Holds the values to be used in the fields callbacks
     */
    private $options;

    /**
     * Start up
     */
    public function __construct()
    {
		add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
        add_action( 'admin_init', array( $this, 'page_init' ) );
		add_action( 'admin_footer', array( $this, 'reindex_all_javascript' ) );
		
		add_action( 'admin_enqueue_scripts', array( $this, 'seznam_webmaster_scripts' ), 10, 1 );
    }

    /**
     * Add options page
     */
    public function add_plugin_page()
    {
        // Main menu page
        add_menu_page(
            'Seznam Webmaster - Nastavení', 
            'Seznam Webmaster', 
            'manage_options', 
            'seznam-webmaster', 
            array( $this, 'admin_dashboard' ),
			'dashicons-megaphone'
        );
		
		// Submenus
		add_submenu_page(
			'seznam-webmaster',
            'Aktuální stav webu', 
            'Stav webu', 
            'manage_options', 
            'seznam-webmaster', 
            array( $this, 'admin_dashboard' )
        );
		
		add_submenu_page(
			'seznam-webmaster', 
            'API klíče', 
            'API klíče', 
            'manage_options', 
            'seznam-webmaster-api', 
            array( $this, 'admin_api_keys' )
        );
		
		add_submenu_page(
			'seznam-webmaster', 
            'Reindexace celého webu', 
            'Reindexace webu', 
            'manage_options', 
            'seznam-webmaster-reindex-all', 
            array( $this, 'admin_reindex_all' )
        );
		
		add_submenu_page(
			'seznam-webmaster', 
            'Logy', 
            'Logy', 
            'manage_options', 
            'seznam-webmaster-logs', 
            array( $this, 'admin_logs' )
        );
    }

    /**
     * Admin dashboard page callback
     */
    public function admin_dashboard()
    {
        // Set class property
        $this->options = get_option( 'seznam_webmaster' );
        ?>
        <div class="wrap">
            <h1>Seznam Webmaster</h1>
        <?php
		if ( ! isset ( $this->options['meta_tag'] ) || ! $this->options['meta_tag'] ) {
			?>
			<div class="wrap">
				<h2>Chybí meta tag</h2>
				<p>Meta tag není zadaný. Pro ověření webu je potřeba zadat meta tag,
					nebo web ověřit pomocí souboru.
					<a href="<?php echo admin_url('admin.php?page=seznam-webmaster-api') ?>">
						Zadat meta tag
					</a>
				</p>
			</div>
			<?php
		}
		
		if ( isset ( $this->options['api_key'] ) && $this->options['api_key'] ) {
			$api_data = $this->get_api_data( $this->options['api_key'] );
			if (is_object($api_data) && is_object($api_data->documents)) {
				
				$this->show_web_stats($api_data->documents);
				
				$this->show_history_graph($api_data->history);
				
			} else {
				?>
				<div class="wrap">
					<h2>Nefunkční API klíč</h2>
					<p>API klíč je zadaný, ale nevrací správná data. <u>U nově vložených webů do služby Seznam Webmaster může trvat několik hodin až den, než začne správně fungovat.</u><br />
						<strong>API hlásí chybu: <?php echo $api_data; ?></strong>
					</p>
					<p>
						Zkontrolujte prosím, zda  API klíč správný:
						<a href="<?php echo admin_url('admin.php?page=seznam-webmaster-api') ?>">
							Zadat API klíč
						</a>
					</p>
				</div>
				<?php
			}
		} else {
			?>
			<div class="wrap">
				<h2>Chybí API klíč</h2>
				<p>API klíč není zadaný. Bez API klíče nebude služba fungovat.
					<a href="<?php echo admin_url('admin.php?page=seznam-webmaster-api') ?>">
						Zadat API klíč
					</a>
				</p>
			</div>
			<?php
		}
    }
	
	/*
	 * Show web stats for admin dashboard
	 * @param object $api_data Contains documents data from API
	 */
	private function show_web_stats($api_data) {
		?>
			
			<div class="wrap">
				<h2>Aktuální stav webu</h2>
				<p>Pozor, služba Seznam Webmaster poskytuje data s určitým 
					intervalem aktualizace. To znamená, že nemusí plně odpovídat 
					realitě. Nově indexované stránky by se ve statistikách měly 
					objevit do 24 hodin.</p>
				<table class="widefat fixed" cellspacing="0">
					<thead>
						<tr>
							<th>Typ stránek</th>
							<th>Počet stránek</th>
							<th>Zobrazit</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td>Uložené v databázi robota</td>
							<td><?php echo $api_data->content->count ?></td>
							<td><a href="#seznam_webmaster_content_urls">Zobrazit seznam URL</a></td>
						</tr>
						<tr>
							<td>V indexu</td>
							<td><?php echo $api_data->index->count ?></td>
							<td><a href="#seznam_webmaster_index_urls">Zobrazit seznam URL</a></td>
						</tr>
						<tr>
							<td>S přesměrováním</td>
							<td><?php echo $api_data->redirect->count ?></td>
							<td><a href="#seznam_webmaster_redirect_urls">Zobrazit seznam URL</a></td>
						</tr>
						<tr>
							<td>S chybou</td>
							<td><?php echo $api_data->error->count ?></td>
							<td><a href="#seznam_webmaster_error_urls">Zobrazit seznam URL</a></td>
						</tr>
					</tbody>
				</table>
				
				<h1 style="margin-top: 3em;">Příklady URL v kategoriích</h2>
				<p>Náhodný vzorek URL (max. 1000 URL) ze všech kategorií.</p>
				
				<h3 id="seznam_webmaster_content_urls" style="margin-top: -20px; padding-top: 40px;">Uložené v databázi robota</h3>
				<?php $this->show_url_table($api_data->content->urls) ?>
				
				<h3 id="seznam_webmaster_index_urls" style="margin-top: -20px; padding-top: 40px;">V indexu</h3>
				<?php $this->show_url_table($api_data->index->urls) ?>
				
				<h3 id="seznam_webmaster_redirect_urls" style="margin-top: -20px; padding-top: 40px;">S přesměrováním</h3>
				<?php $this->show_url_table($api_data->redirect->urls) ?>
				
				<h3 id="seznam_webmaster_error_urls" style="margin-top: -20px; padding-top: 40px;">S chybou</h3>
				<?php $this->show_url_table($api_data->error->urls) ?>
			</div>
			
		<?php
	}
	
	/*
	 * Show table of URLs
	 * @param array $data Array of URLs
	 */
	private function show_url_table( $data ) {
		if ( $data ) {
			?>
			<a href="#" name="seznam-webmaster-show-url-table" class="button button-primary">
				Zobrazit/skrýt
			</a>
			<table class="widefat fixed seznam-webmaster-url-table" style="display: none; margin-top: 15px;" cellspacing="0">
				<thead>
					<tr>
						<th style="width: 30px;">ID</th>
						<th>URL</th>
						<th style="width: 55px;">Odkaz</th>
					</tr>
				</thead>
				<tbody>
					<?php
					foreach ( $data as $key => $url ) {
					?>
					<tr>
						<td><?php echo ($key+1); ?></td>
						<td><?php echo $url ?></td>
						<td><a href="<?php echo $url ?>" target="blank">Zobrazit</a></td>
					</tr>
					<?php
					}
					?>
				</tbody>
			</table>

			<?php
		} else {
			echo 'V této kategorii nejsou žádné URL.';
		}
	}
	
	/*
	 * Show web stats for admin dashboard
	 * @param object $api_data Contains history data from API
	 */
	private function show_history_graph($data) {
		?>
			
			<div class="wrap">
				<h1 style="margin-top: 3em;">Historie počtu webových stránek</h1>
				<p>Posloupnost počtů webových stránek v čase za jednotlivé dny a kategorie.</p>
				
				<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
				<div id="chart_div"></div>
				
				<script>
					google.charts.load('current', {packages: ['corechart', 'line'], language: 'cs'});
					google.charts.setOnLoadCallback(drawCurveTypes);

					function drawCurveTypes() {
						var data = new google.visualization.DataTable();
						data.addColumn('date', 'X');
						data.addColumn('number', 'V databázi');
						data.addColumn('number', 'V indexu');
						data.addColumn('number', 'S přesměrováním');
						data.addColumn('number', 'S chybou');

						data.addRows([
							<?php
							foreach ( $data as $key => $row ) {
								$date = strtotime( $row->date );
								printf(
									'[new Date(%d, %d, %d), %d, %d, %d, %d],',
									date('Y', $date),
									date('n', $date) - 1,
									date('j', $date),
									$row->counts->downloaded,
									$row->counts->indexed,
									$row->counts->redirected,
									$row->counts->error
								);
							}
							?>
						]);

						var graphHeight = 300;
						if (window.innerWidth > window.innerHeight) {
							graphHeight = window.innerHeight * 0.8;
						} else {
							graphHeight = window.innerWidth;
						}

						var options = {
							hAxis: {
								title: 'Datum',
								format: 'MMM yyyy',
							},
							vAxis: {
								title: 'Počet stránek',
								minValue: 0
							},
							height: graphHeight
						};

						var chart = new google.visualization.LineChart(document.getElementById('chart_div'));
						chart.draw(data, options);
					}
				</script>
			</div>
			
		<?php
	}

	/**
     * API keys page callback
     */
    public function admin_api_keys()
    {
        // Set class property
        $this->options = get_option( 'seznam_webmaster' );
        ?>
        <div class="wrap">
            <h1>API klíče</h1>
			
			<h2>URL webu</h2>
			Zkontrolujte si, zda URL webu odpovídá URL ve službě Seznam Webmaster.
			Dejte pozor na protokol HTTP/HTTPS. Pokud se liší, nebude správně fungovat reindexace.
			<br /><br />
			<strong>URL:</strong> <?php echo get_home_url(); ?>
			
            <form method="post" action="options.php">
            <?php
                // This prints out all hidden setting fields
                settings_fields( 'seznam_webmaster_option_group' );
                do_settings_sections( 'seznam-webmaster-setting-admin' );
                submit_button();
            ?>
            </form>
        </div>
		<?php
    }
	
	/**
     * Reindex all page callback
     */
    public function admin_reindex_all()
    {
        // Set class property
        $this->options = get_option( 'seznam_webmaster' );
        ?>
        <div class="wrap">
			<h1>Kompletní reindexace webu</h1>
			Pokud máte nový web, nebo jste udělali velkou hromadnou změnu, můžete
			nechat web nechat kompletně přeindexovat.
			<br /><br />
			Kvůli limitům API se reindexace po 500 záznamech zastaví.
			<br /><br />
			<strong>Aktuální limity nastavené službou Seznam Webmaster:</strong>
			<ol>
				<li>nejvýše 5 stránek za vteřinu</li>
				<li>nejvýše 100 stránek za minutu</li>
				<li>nejvýše 500 krát za den</li>
			</ol>
			<br />
			<div id="seznam-webmaster-reindex-status" style="display: none;">
				<span class="stop" style="display: none; color: green;">
					Dokončeno, odesláno: 
					<span class="finished">0</span>
				</span>
				<span class="run">
					<img src="<?php echo admin_url( 'images/spinner.gif' ) ?>" alt="Načítání" />
					Probíhá reindexace, odesláno: 
					<span class="processed">0</span>/<span class="total">0</span>
				</span>
				URL.
			</div>
			<br />
			<a href="#" id="seznam-webmaster-reindex-all" class="button button-primary">
				Reindexovat vše
			</a>
        </div>
		<?php
    }
	
	/**
     * Logs page callback
     */
    public function admin_logs()
    {
		if ( filter_input( INPUT_GET, 'delete', FILTER_SANITIZE_STRING ) ) {
			$this->delete_logs( filter_input( INPUT_GET, 'delete', FILTER_SANITIZE_STRING ) );
		}
		
        // Set class property
        $this->options = get_option( 'seznam_webmaster' );
        ?>
        <div class="wrap">
			<h1>Logy</h1>
			Zde můžete vidět poslední reindexované URL s datem a stavem.
			<h2>Jednotlivé</h2>
			<?php $this->get_logs('single') ?>
			<a href="#" id="seznam-webmaster-single-log" class="button button-primary">
				Zobrazit
			</a>
			<h2>Hromadné</h2>
			<?php $this->get_logs('all') ?>
			<a href="#" id="seznam-webmaster-all-log" class="button button-primary">
				Zobrazit
			</a>
        </div>
		<?php
    }

    /**
     * Register and add settings
     */
    public function page_init()
    {        
        register_setting(
            'seznam_webmaster_option_group', // Option group
            'seznam_webmaster', // Option name
            array( $this, 'sanitize' ) // Sanitize
        );

        add_settings_section(
            'meta_tag_section_id', // ID
            'Meta tag', // Title
            array( $this, 'print_meta_tag_info' ), // Callback
            'seznam-webmaster-setting-admin' // Page
        );  

        add_settings_field(
            'meta_tag', // ID
            'Kód meta tagu', // Title 
            array( $this, 'meta_tag_callback' ), // Callback
            'seznam-webmaster-setting-admin', // Page
            'meta_tag_section_id' // Section           
        );  
		
		add_settings_section(
            'api_key_section_id', // ID
            'Kód API', // Title
            array( $this, 'print_api_key_info' ), // Callback
            'seznam-webmaster-setting-admin' // Page
        );  

        add_settings_field(
            'api_key', 
            'Kód API', 
            array( $this, 'api_key_callback' ), 
            'seznam-webmaster-setting-admin', 
            'api_key_section_id'
        );      
    }

    /**
     * Sanitize each setting field as needed
     *
     * @param array $input Contains all settings fields as array keys
     */
    public function sanitize( $input )
    {
        $new_input = array();
        if( isset( $input['meta_tag'] ) )
            $new_input['meta_tag'] = sanitize_text_field( $input['meta_tag'] );

        if( isset( $input['api_key'] ) )
            $new_input['api_key'] = sanitize_text_field( $input['api_key'] );

        return $new_input;
    }

    /** 
     * Print the Meta Tag text
     */
    public function print_meta_tag_info()
    {
        print 'Přidejte web do služby Seznam Webmaster a zadejte kód metatagu do políčka níže. Po přidání se začne zobrazovat na stránkách a web bude možné ověřit ve službě Seznam Webmaster. Pozor na cachovací pluginy. Pokud takové máte, bude nutné před ověřením smazat cache úvodní stránky.';
    }
	
	/** 
     * Print the API key text
     */
    public function print_api_key_info()
    {
        print 'Vygenerujte si API klíč a zadejte jeho hodnotu. Po přidání začně plugin komunikovat s API, odesílat stránky na reindexaci a zobrazovat základní info o webu.<br /><br /><strong>U nově vložených webů do služby Seznam Webmaster může trvat několik hodin až den, než začne správně fungovat.</strong>';
    }

    /** 
     * Get the settings option array and print one of its values
     */
    public function meta_tag_callback()
    {
        printf(
            '<input type="text" id="meta_tag" name="seznam_webmaster[meta_tag]" value="%s" size="60" />
			<br />
			<small>
				Přidejte jen řetězec mezi uvozovkami atributu content="...".
				<a href="#" class="seznam-webmaster-metatag-image-toggle">Více</a>
			</small>
			<br>
			<img class="seznam-webmaster-metatag-image" style="display: none;" src="' . plugins_url( 'assets/images/metatag.png', __FILE__ ) . '">
			',
            isset( $this->options['meta_tag'] ) ? esc_attr( $this->options['meta_tag']) : ''
        );
    }

    /** 
     * Get the settings option array and print one of its values
     */
    public function api_key_callback()
    {
        printf(
            '<input type="text" id="api_key" name="seznam_webmaster[api_key]" value="%s" size="60" />',
            isset( $this->options['api_key'] ) ? esc_attr( $this->options['api_key']) : ''
        );
    }
	
	/** 
     * Get data from Seznam Webmaster API
	 * 
	 * @param string $api_key
	 * @return array
     */
    public function get_api_data($api_key = "")
    {
        $url = "https://reporter.seznam.cz/wm-api/web?key=$api_key";
		
		// Get data from API
		$response = wp_remote_get( $url,
			array(
				'timeout' => 5,
			)
		);
		$body = wp_remote_retrieve_body( $response );
		$response_code = wp_remote_retrieve_response_code( $response );
		
		// Check if result is valid
		if ($response_code === 204) {
			return 'Web není plně aktivován, zkuste to prosím později.';
		} elseif ( ! $body ) {
			return false;
		}
		
		// Decode data from JSON and return
		$data = json_decode($response['body']);
		if ( isset ( $data->documents->content ) ) {
			return $data;
		} else {
			return $data->title." ".$data->detail;
		}
    }
	
	/** 
     * Delete logs
	 * 
	 * @param string $type
	 * @return void
     */
    public function delete_logs($type = '')
    {
        if ($type === 'all') {
			$table_index = 'seznam_webmaster_log_all';
		} elseif ($type === 'single') {
			$table_index = 'seznam_webmaster_log_single';
		} else {
			return;
		}
		
		delete_option( $table_index );
		
		show_message( '<div class="notice notice-success">
							<p>Smazáno</p>
						</div>'
				);
    }
	
	/** 
     * Get logs
     */
    public function get_logs($type = 'single')
    {
		if ($type === 'all') {
			$table_index = 'seznam_webmaster_log_all';
		} else {
			$table_index = 'seznam_webmaster_log_single';
		}
        $logs = get_option( $table_index );
		
		if ($logs !== false && is_array( $logs ) ) {
			?>
			<div id="seznam-webmaster-log-table-<?php echo $type; ?>" style="display: none;">
				<a href="#" id="seznam-webmaster-<?php echo $type; ?>-log-hide" class="button button-primary" style="padding-left: 19px; padding-right: 20px;">
					Skrýt
				</a>
				<a href="<?php echo admin_url( "admin.php?page=seznam-webmaster-logs&delete=$type" ) ?>" 
				   id="seznam-webmaster-delete-<?php echo $type; ?>-log" class="button button-primary">
					Smazat log
				</a>
				<br /><br />
				<table class="widefat fixed" cellspacing="0">
					<thead>
						<tr>
							<th>Datum</th>
							<th>URL</th>
							<th>Stav</th>
							<th>Popis</th>
						</tr>
					</thead>
					<tbody>
						<?php
						$logs = array_reverse( $logs );
						foreach ($logs as $log) {
						?>
							<tr>
								<td><?php echo date('d.m.Y H:i:s', strtotime( $log['date'] )) ?></td>
								<td><?php echo $log['url'] ?></td>
								<td><?php echo $log['success'] ? 'Úspěch' : 'Chyba' ?></td>
								<td><?php echo $log['message'] ?></td>
							</tr>
						<?php
						}
						?>
					</tbody>
				</table>
			</div>
			<?php
		}
    }
	
	/** 
     * JS for multi reindex
     */
	function reindex_all_javascript() {
		
		$ajax_nonce = wp_create_nonce( "seznam-webmaster-ajax" );
		
		?>
		<script type="text/javascript" >
		jQuery(document).ready(function($) {
			$('#seznam-webmaster-reindex-all').on('click', function(e){
				$('#seznam-webmaster-reindex-status').show();
				$('#seznam-webmaster-reindex-status .stop').hide();
				$('#seznam-webmaster-reindex-status .run').show();
				
				$('#seznam-webmaster-reindex-status .processed').text(0);
				$('#seznam-webmaster-reindex-status .finished').text(0);
				$('#seznam-webmaster-reindex-status .total').text(0);
				
				call_ajax();

				function call_ajax () {
					
					var data = {
						action: 'seznam_webmaster_reindex_all',
						security: '<?php echo $ajax_nonce; ?>',
					};
					
					jQuery.post(ajaxurl, data, function(response) {
						var data = JSON.parse(response);
						
						$('#seznam-webmaster-reindex-status .processed').text(data.processed_items);
						$('#seznam-webmaster-reindex-status .finished').text(data.processed_items);
						$('#seznam-webmaster-reindex-status .total').text(data.total_items);
						
						if (data.done === true) {
							$('#seznam-webmaster-reindex-status .run').hide();
							$('#seznam-webmaster-reindex-status .stop').show();
						} else {
							var now = new Date().getTime();
							var millisecondsToWait = 5000;
							while ( new Date().getTime() < now + millisecondsToWait )
							{
								// Sleep
							}
							call_ajax();
						}
					});
				}
				
				e.preventDefault();
			});
			
			$('#seznam-webmaster-single-log').on('click', function(e){
				$('#seznam-webmaster-log-table-single').show();
				$(this).hide();
				e.preventDefault();
			});
			
			$('#seznam-webmaster-single-log-hide').on('click', function(e){
				$('#seznam-webmaster-log-table-single').hide();
				$('#seznam-webmaster-single-log').show();
				e.preventDefault();
			});
			
			$('#seznam-webmaster-all-log').on('click', function(e){
				$('#seznam-webmaster-log-table-all').show();
				$(this).hide();
				e.preventDefault();
			});
			
			$('#seznam-webmaster-all-log-hide').on('click', function(e){
				$('#seznam-webmaster-log-table-all').hide();
				$('#seznam-webmaster-all-log').show();
				e.preventDefault();
			});
			
			$('[name=seznam-webmaster-show-url-table]').on('click', function(e){
				$(this).next('.seznam-webmaster-url-table').toggle();
				e.preventDefault();
			});
		});
		</script> <?php
	}
	
	/** 
     * Add scripts
     */
	function seznam_webmaster_scripts() {
		wp_register_script( 'seznam-webmaster-js', plugins_url( 'assets/js/seznam-webmaster.js?v='.time(), __FILE__ ), array( 'jquery' ) );
		wp_enqueue_script( 'seznam-webmaster-js' );
	}
}