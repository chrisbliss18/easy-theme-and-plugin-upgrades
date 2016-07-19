<?php

class CAJ_ETPU_Theme_Upgrader extends Theme_Upgrader {
	public function run( $options ) {
		$options['clear_destination'] = true;

		return parent::run( $options );
	}

	public function clear_destination( $remote_destination ) {
		error_log( 'Remote Destination: ' . $remote_destination );
	}
}
