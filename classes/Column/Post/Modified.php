<?php
defined( 'ABSPATH' ) or die();

/**
 * @since 2.0
 */
class AC_Column_Post_Modified extends AC_Column {

	public function __construct() {
		$this->set_type( 'column-modified' );
		$this->set_label( __( 'Last modified', 'codepress-admin-columns' ) );
	}

	public function get_value( $post_id ) {
		$modified = $this->get_raw_value( $post_id );
		$date_format = $this->get_option( 'date_format' );

		if ( ! $date_format ) {
			$value = ac_helper()->date->date( $modified ) . ' ' . ac_helper()->date->time( $modified );
		}
		else {
			$value = date_i18n( $date_format, strtotime( $modified ) );
		}

		return $value;
	}

	public function get_raw_value( $post_id ) {
		return get_post_field( 'post_modified', $post_id );
	}

	function display_settings() {
		$this->field_settings->date();
	}

}