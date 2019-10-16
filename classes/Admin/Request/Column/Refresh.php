<?php

namespace AC\Admin\Request\Column;

use AC\Admin\Request\Column;
use AC;

class Refresh extends Column {

	public function __construct() {
		parent::__construct( 'refresh' );
	}

	public function get_column( AC\Request $request ) {
		parse_str( $request->get('data'), $formdata );
		$options = $formdata['columns'];
		$name = filter_input( INPUT_POST, 'column_name' );

		if ( empty( $options[ $name ] ) ) {
			wp_die();
		}

		$settings = $options[ $name ];

		if ( empty( $settings['type'] ) ) {
			wp_die();
		}

		$settings['name'] = $name;

		return ( new AC\ColumnFactory() )->create( $settings['type'], $settings, $this->list_screen );
	}

}