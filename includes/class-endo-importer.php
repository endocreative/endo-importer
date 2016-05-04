<?php 

class Endo_Importer {

	public function run() {

		add_action('admin_enqueue_scripts', array( $this, 'load_importer_scripts') );
		add_action('admin_menu', array( $this, 'create_admin_menu') );
		add_action('admin_init', array( $this, 'create_importer_settings' ) );

		// uncomment to delete a group of posts
		// add_action( 'admin_init', array( $this, 'delete_extra_posts' ) );

	}


	
	public function load_importer_scripts() {

		wp_enqueue_media();
		wp_enqueue_script( 'endo-importer', plugin_dir_url( __FILE__ ) . 'js/importer.js', array('jquery'), '10', true );
		
		$protocol = isset( $_SERVER['HTTPS'] ) ? 'https://' : 'http://';
		$params = array(
			'ajaxurl' => admin_url( 'admin-ajax.php', $protocol )
		);
		wp_localize_script( 'calculator-importer', 'importer_data', $params );

	}

	public function create_admin_menu() {

		add_menu_page( 'Endo Importer', 'Endo Importer', 'manage_options', 'endo-importer-page', array( $this, 'endo_importer_page') );

	}


	public function endo_importer_page() {

		$options = get_option('endo_ui_options');
		$file = $options['csv_file'];

		if( isset($_GET['settings-updated']) && $_GET['settings-updated'] ) {

		    if ( $file  ) {

				$imported = array();
				$items = $this->csv_to_array( $file, ',' );

				$i = 0;

				foreach ( $items as $item ) {

					// $name = sanitize_text_field( $item['Complex Name'] );
					$unit = sanitize_text_field( $item['Unit'] );
					
					if ( empty( $unit ) ) {
						continue;
					}
					
					$args = array(
					  'post_title'    => $unit,
					  'post_type'	  => 'floorplan', // set the correct custom post type
					  'post_status'   => 'publish',
					  'post_author'   => 1, // optional: set author by id
					);
					
					// create the new post 
					$post_id = wp_insert_post( $args );

					// add any metadata to the new post
					

					// for apartments
					// $meta = array(
					// 	'AIMid'				=> sanitize_text_field( $item['AIMid'] ),
					// 	'address' 			=> sanitize_text_field( $item['Address'] ),
					// 	'city'		 		=> sanitize_text_field( $item['City'] ), 
					// 	'state'		 		=> sanitize_text_field( $item['State'] ), 
					// 	'zip'		 		=> sanitize_text_field( $item['Zip Code'] ), 
					// 	'year_built'		=> sanitize_text_field( $item['Year Built'] ), 
					// 	'units'	 			=> sanitize_text_field( $item['Units'] ), 
					// 	'floorplans'		=> sanitize_text_field( $item['Floor Plans'] ), 
					// 	'rent_min'			=> sanitize_text_field( $item['Rent Minimum'] ), 
					// 	'rent_max'			=> sanitize_text_field( $item['Rent Maximum'] ), 
					// 	'pets_welcome'		=> sanitize_text_field( $item['Pets Welcome'] ), 
					// 	'pet_deposit'		=> sanitize_text_field( $item['Pet Deposit'] ), 
					// 	'pet_rent'			=> sanitize_text_field( $item['Pet Rent'] ),
					// 	'nr_pet_deposit'	=> sanitize_text_field( $item['NR Pet Deposit'] ),  
					// 	'w_d_unit'			=> sanitize_text_field( $item['Washer Dryer Unit'] ), 
					// 	'w_d_connections'	=> sanitize_text_field( $item['Wahser Dryer Connections'] ), 
					// 	'deposit'			=> sanitize_text_field( $item['Deposit'] ),   
					// 	'latitude'			=> sanitize_text_field( $item['Latitude'] ),  
					// 	'longitude'			=> sanitize_text_field( $item['Longitude'] ),
					// 	'mgmt_co'			=> sanitize_text_field( $item['Management Company'] ),   
					// 	'entry_gate'		=> sanitize_text_field( $item['Entry Gate'] ), 
					// 	'lease_terms'		=> sanitize_text_field( $item['Lease Terms'] ), 
					// 	'parking_options'	=> sanitize_text_field( $item['Parking Options'] ),    
					// 	'admin_fee'			=> sanitize_text_field( $item['Admin Fee'] ), 
					// 	'app_fee'			=> sanitize_text_field( $item['App Fee'] ),   
					// 	'images' 			=> sanitize_text_field( $item['Photo Images'] ),   
					// );
					
					// for floorplans
					$meta = array(
						'AIMid'				=> sanitize_text_field( $item['aimID'] ),
						'complex_name' 		=> sanitize_text_field( $item['Complex Name'] ),
						'beds' 				=> sanitize_text_field( $item['Bedrooms'] ),
						'baths' 			=> sanitize_text_field( $item['Bathrooms'] ),
						'sq_ft' 			=> sanitize_text_field( $item['Square Feet'] ),
						'rent'		 		=> sanitize_text_field( $item['Rent'] ),
						'layout'	 		=> sanitize_text_field( $item['Layout'] ),
						'nickname'	 		=> sanitize_text_field( $item['Nickname'] ),
						'deposit'	 		=> sanitize_text_field( $item['Deposit'] ),
						'washer_dryer' 		=> sanitize_text_field( $item['Washer/Dryer'] )
					);

					foreach( $meta as $key => $value ) {
						update_post_meta( $post_id, '_floorplan_' . $key, $value );
					}


					/**
					 * Create any connections for the new post
					 * https://github.com/scribu/wp-posts-to-posts/wiki/Creating-connections-programmatically
					 */
					
					$neighborhood = sanitize_text_field( $item['Area'] );
					p2p_type( 'floorplans_to_neighborhoods' )->connect( $post_id, get_page_by_title( $neighborhood, OBJECT, 'neighborhood' ), array(
					    'date' => current_time('mysql')
					) );

					$style = sanitize_text_field( $item['Style'] );
					p2p_type( 'floorplans_to_apt_styles' )->connect( $post_id, get_page_by_title( $style, OBJECT, 'apt_style' ), array(
					    'date' => current_time('mysql')
					) );

					$complex = sanitize_text_field( $item['Complex Name'] );
					p2p_type( 'floorplans_to_apartments' )->connect( $post_id, get_page_by_title( $complex, OBJECT, 'apartment' ), array(
					    'date' => current_time('mysql')
					) );

					$i++;
				}

			}

			else {
				echo '<div class="notice notice-warning"><p><strong>No file selected.</strong> Please select a file to upload.</p></div>';
			}

		}

		?>

		<div class="wrap">

			<?php 
				if ( isset( $i ) ) {
					echo '<div class="updated"><p>Number of items imported successfully: <strong>' . $i . '</strong></p></div>';
				}
			?>

	        <h2>Endo Importer</h2>
	        <p>Choose a csv file below to import.</p>

	      	<form method="post" action="options.php">
	            <?php settings_fields( 'endo_ui_options' ); ?>
            	<?php do_settings_sections( 'endo_ui_opt' ); ?>      
            	<?php submit_button('Import'); ?>
	        </form>

	    </div>

    	<?php 

	}


	public function create_importer_settings() {

		register_setting(
	        'endo_ui_options',
	        'endo_ui_options',
	        array( $this, 'endo_importer_validate_options' )
	    );

		add_settings_section(
	        'endo_ui_general',        
	        '',                 
	        '',
	        'endo_ui_opt'                           
	    );

	    add_settings_field(
	        'endo_ui_file',                  
	        'File Name',                         
	        array( $this, 'endo_ui_text_input' ),  
	        'endo_ui_opt',                         
	        'endo_ui_general'       
	    );

	    

	}

	public function endo_ui_text_input() {

		$options = wp_parse_args( get_option( 'endo_ui_options' ), array('csv_file' => ''));
		$file = $options['csv_file'];
	
		?>
		<input type="text" id="endo_importer_csv_file" name="endo_ui_options[csv_file]" value="<?php echo esc_url( $file ); ?>" />
	    <input id="upload_endo_importer_csv_button" type="button" class="button" value="<?php _e( 'Upload CSV', 'endo-importer' ); ?>" />
	        <span class="description"><?php _e('Upload a csv file.', 'endo-importer' ); ?></span>
	    <?php 
	}

	
	public function endo_importer_validate_options( $input ) {
	    return $input;
	}


	public function csv_to_array( $filename = '', $delimiter = ',' ) {

		if (!file_exists($filename) || !is_readable($filename)) {
			
			// return FALSE;
		}

		$header = NULL;
		$data = array();

		if (($handle = fopen($filename, 'r')) !== FALSE) {
			while (($row = fgetcsv($handle, 1000, $delimiter)) !== FALSE) {

				if (!$header) {
					$header = $row;
				} else {
					$data[] = array_combine($header, $row);
				}

			}
			fclose($handle);
		}
		return $data;

	}

	public function delete_extra_posts() {

		$post_type = 'apartment';

		$extra_posts = get_posts( array(
			'post_type'	=> $post_type,
			'posts_per_page'	=> 99999
		) );

		foreach( $extra_posts as $extra_post ) {

			wp_delete_post( $extra_post->ID, true );

		}
	}


}