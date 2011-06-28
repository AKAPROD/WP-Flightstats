<?php

class FS_Query
{

	// FLIGHTSTATS RSS QUERY URL
	const FS_RSS = 'http://www.flightstats.com/go/rss/flightStatusByRoute.do?';

	const FS_ERROR_MISSING = '<p class="fs_error">There was a problem with your request.<br />Please check the information you entered and try again. <a href="" >Try Again</a></p>';

	// HANDLE RSS QUERY PARAMS, EXECUTION CALLS AND RESPONSES
	static function query()
	{	

		// SANITIZE POST INPUT
		$fs_rss_params = filter_var_array( $_POST, FILTER_SANITIZE_STRING );


		// --------------------------- CREATE DATE ------
		// SET TIME TO TODAY
		$fs_time = time();
		// THEN..
		// CHECK IF SELECTED DAY YESTERDAY OR TOMORROW AND ADD OR SUBTRACT 60*60*24 = 86400 - ONE DAY
		if ( $fs_rss_params['fs_sched_date'] === 'yesterday' ){ $fs_time -= 86400; }
		elseif ( $fs_rss_params['fs_sched_date'] === 'tomorrow' ){ $fs_time += 86400; }

		// CREATE FORMATTED DATE FOR QUERY
		$fs_date = date( 'Y-m-d',  $fs_time );
		// ----------------------------------------------

		// CREATE REMAINING QUERY VARS

		// FLIGHT NO = $fs_flight
		if ( isset($fs_rss_params['fs_flight_no']) && $fs_rss_params['fs_flight_no'] != '' )
			{ $fs_flight = trim($fs_rss_params['fs_flight_no']); }

		// DEPARTURE AIRPORT =  $fs_dep
		if ( isset($fs_rss_params['fs_dep_airport']) && $fs_rss_params['fs_dep_airport'] != '' )
			{ $fs_dep = trim($fs_rss_params['fs_dep_airport']); }

		// ARRIVAL AIRPORT = $fs_arr
		if ( isset($fs_rss_params['fs_arr_airport']) && $fs_rss_params['fs_arr_airport'] != '' )
			{ $fs_arr = trim($fs_rss_params['fs_arr_airport']); }


		// IF FLIGHT NO IS SUPPLIED QUERY IT ALONE.
		if ( isset($fs_flight) ){

			$fs_result = self::execute_query( $fs_date, $fs_flight );

			// ! IMPORTANT ! // CHECK RETURNED submit_rss VALUE, IF THAT VALUE IS FALSE CONTINUE ON TO TRY QUERY BY ROUTE - ( INCORRECT FLIGHT NO. )
			if ( $fs_result ){
				return $fs_result; // ------------ POSSIBBLE RETURN ----
			}
		}

		// FROM HERE THE FLIGHT NO QUERY EITHER RETURNED FALSE OR WASN'T SET, SO TRY QUERY WITH AIRPORT LOCATIONS
		if ( isset( $fs_dep ) && isset( $fs_arr ) ){
			
			// CREATE ARRAY TO PASS TO submit_rss. THE ARRAY WILL DISTINGUISH BETWEEN FLIGHT NO QUERY AND ROUTE QUERY
			$fs_route[] = $fs_dep;
			$fs_route[] = $fs_arr;

			$fs_result = self::execute_query( $fs_date, $fs_route );

			// ! IMPORTANT ! // CHECK RETURNED submit_rss VALUE, IF THAT VALUE IS ALSO FALSE CONTINUE TO RETURN ERROR MESSAGE
			if ( $fs_result ){
				return $fs_result; // ------------ POSSIBBLE RETURN ----
			}

		}

		// IF ALL POSSIBBLE QUERY PARAMS FAILED OR WASN'T AVAILABLE,
		// I.E. NONE OF THE CONDITIONALS RETURNED A VALUE EXITING THE METHOD, SIMPLY RETURN THE ERROR MESSAGE

		return self::FS_ERROR_MISSING; // ------------ POSSIBBLE RETURN ----

	} // ---------


	//HANDLES ACTUALL QUERY AND PARSES RESULTS - RETURNS EITHER PARSED RESULTS READY FOR OUTPUT OR FALSE.
	private static function execute_query( $date, $param )
	{	
	
		// GET GUIDs FROM WP_OPTIONS
		$flight_guid = get_option( 'FS_GUID_flight' );
		$route_guid = get_option( 'FS_GUID_route' );
		
		// CHECK IF $param IS ARRAY MEANING AIRPORTS WHERE PASSED AND QUERY NEEDS TO BE DONE BY ROUTE 
		if ( is_array($param) )
		{
			// BUILD QUERY STRING
			$fs_query_string  = self::FS_RSS;
			$fs_query_string .= 'guid=';
			$fs_query_string .= $route_guid;
			$fs_query_string .= '&departureCode=';
			$fs_query_string .= $param[1];
			$fs_query_string .= '&arrivalCode=';
			$fs_query_string .= $param[2];
			$fs_query_string .= '&departureDate=';
			$fs_query_string .= $fs_date;
			
			// SUBMIT THE QUERY.
			$flightstats_xml_result = file_get_contents($fs_query_string);
		
		}
		// ELSE IF $param IS NOT ARRAY FLIGHT NO WAS PASSED AND QUERY NEEDS TO BE DONE BY FLIGHT NO.
		else
		{	
			
			// BREAK $param ( fs_flight ) INTO $fs_airlineCode & $fs_flightNumber
			$fs_airlineCode  = strtoupper( preg_replace( '/[^A-z]/i', '', $param ) );
			$fs_flightNumber = preg_replace( '/\D/i', '', $param );
			
		
			// BUILD QUERY STRING
			$fs_query_string  = self::FS_RSS;
			$fs_query_string .= 'guid=';
			$fs_query_string .= $flight_guid;
			$fs_query_string .= '&airlineCode=';
			$fs_query_string .= $fs_airlineCode;
			$fs_query_string .= '&flightNumber=';
			$fs_query_string .= $fs_flightNumber;
			$fs_query_string .= '&departureDate=';
			$fs_query_string .= $fs_date;
			
			// SUBMIT THE QUERY.
			$flightstats_xml_result = file_get_contents($fs_query_string);
		
		}
		
		
		
		return 'RESULTS FROM FLIGHTSTATS';
	}


}
