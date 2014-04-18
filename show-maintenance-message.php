<?php


if ( ! class_exists( 'ETUShowMaintenanceMessage' ) ) {
	class ETUShowMaintenanceMessage {
		function ETUShowMaintenanceMessage() {
			if ( false !== get_transient( 'etu-in-maintenance-mode' ) )
				add_action( 'template_redirect', array( $this, 'show_message' ) );
		}
		
		function show_message() {
			$file = dirname( __FILE__ ) . '/maintenance-page.html';
			if ( file_exists( dirname( __FILE__ ) . '/custom-maintenance-page.html' ) )
				$file = dirname( __FILE__ ) . '/custom-maintenance-page.html';
			
			echo file_get_contents( $file );
			
			exit;
		}
	}
	
	new ETUShowMaintenanceMessage();
}
