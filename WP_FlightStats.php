<?php

// Main Plugin Class

class WP_FlightStats{

	// FLIGHTSTATS RSS QUERY URL
	const FS_RSS = 'http://www.flightstats.com/go/rss/flightStatusByRoute.do?';

	// FLIGHTSTATS GUIDs:
	private $FS_GUID_route;
	private $FS_GUID_flight;
	
	// USED TO EITHER SET OR UPDATE 'GUIDs' VARS 
	private function update_guids()
	{
		$this->FS_GUID_route = get_option( 'FS_GUID_route' );
		$this->FS_GUID_flight = get_option( 'FS_GUID_flight' );
	}

	// CONSTRUCTOR TO SET PLACEHOLDERS ON ACTIVATION AND CREATE AN INSTANCE VARIABLE FOR EACH ACCOUNT OPTION
	public function __construct()
	{	
		// Check if options has been set or alternitively set on activation
		if( !get_option('FS_Options_Set') )
		{
			// SET GUID PLACEHOLDERS IN WP_OPTIONS
			add_option( 'FS_GUID_route', '' );
			add_option( 'FS_GUID_flight', '' );
			// Set 'FS_Options_Set' so options are only created on activation.
			add_option( 'FS_Options_Set', 'true' );
		}
		
		// SET GUID IVARS
		$this->update_guids();
					
		// ---- REGISTER WORDPRESS HOOKS ----

		// ADMIN ACTION HOOK TO 'fs_admin()'
		add_action('admin_menu', array( &$this, 'fs_admin_auth' ) );

		// SHORTCODE HOOK TO 'fs_shortcode()'
		add_shortcode( 'flightstats', array( &$this, 'fs_shortcode' ) );
		

	} // ***  __construct END ***
	


	// REGISTER ADMIN PAGE AND CALL "create_admin_page()" TO CREATE PAGE
	public function fs_admin_auth()
	{
		add_menu_page( "FlightStats", "FlightStats", "manage_options", "flightstats", array( &$this, 'create_fs_admin' ) );
	}



	// OUTPUT ADMIN PAGE HTML FROM 'FS_Admin_Page.php'
	public function create_fs_admin()
	{
		// CHECK USER CAN MANAGE OPTIONS
		if (!current_user_can('manage_options'))
			{wp_die( __('You do not have sufficient permissions to access this page.') );}
		
		// CHECK IF SETTING ADMIN FORM HAS BEEN SUBMITTED
		if ( isset($_POST['flightstats_admin_submitted']) )
		{			
			// CHECK IF USER WANTS TO DELETE ACCOUNT SETTINGS
			if ( isset($_POST['FS_Delete']) )
			{
				update_option( 'FS_GUID_route', '' );
				update_option( 'FS_GUID_flight', '' );

			}
			// ELSE UPDATE SETTING WITH
			else
			{
				update_option( 'FS_GUID_route', $_POST['FS_GUID_route'] );
				update_option( 'FS_GUID_flight', $_POST['FS_GUID_flight'] );
			}
			
			// UPDATE GUID IVARS
			$this->update_guids();

		}
					
		// INCLUDE ADMIN PAGE HTML CONTENT
		require 'views/admin_page.php';
	}
	
	
	
	// INITILISE INSTANCE OF 'fs_shortcode'. - CALLED FROM SHORTCODE HOOK IN __constructor
	public function fs_shortcode($atts)
	{	
		/* ----------------------------------
		 *
		 *	INCLUDE ONE OF THE THEMES SELECTED FROM THE ADMIN MENU  --- NOT YET DONE 
		 *
		 * ----------------------------------
		 */
		// RETURN THE GENERIC FORM FOR QUERIENG FLIGHTSTATS RSS FEED
		// -- note * cannot include as file content needs to be 'returned' for the sake of the page structure
		$fs_query_form = file_get_contents('views/query_form.php', FILE_USE_INCLUDE_PATH);
		
		return $fs_query_form;
		
	}


} // --- WP_FlightStats --- end ---