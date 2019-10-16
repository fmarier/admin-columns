<?php

namespace AC;

use AC\Storage;

class ListScreenFactory {

	/** @var DefaultColumns */
	private $default_columns;

	public function __construct() {
		$this->default_columns = new DefaultColumns();
	}

	public function create( $key, Storage\DataObject $data = null ) {
		$list_screen = $this->get_list_screen_by_key( $key );

		if ( ! $list_screen ) {
			return false;
		}

		// todo
		// create a new reference
		$list_screen = clone $list_screen;

		$column_types = ColumnTypes::instance();

		// Adds column that are registered to this list screen to the 'ColumnTypes' instance
		foreach ( $list_screen->get_column_types() as $column_type ) {
			$column_type->set_list_screen( $list_screen );

			if ( $column_type->is_valid() ) {
				$column_types->register_column( $list_screen, $column_type );
			}
		}

		// Add native columns to the 'ColumnTypes' instance
		foreach ( $this->default_columns->get( $list_screen->get_key() ) as $name => $label ) {
			if ( 'cb' === $name ) {
				continue;
			}

			$column = new Column();
			$column->set_label( $label )
			       ->set_type( $name )
			       ->set_original( true );

			$column_types->register_column( $list_screen, $column );
		}

		if ( $data && is_array( $data->columns ) ) {
			$list_screen->set_title( $data->title )
			            ->set_settings( $data->columns )
			            ->set_preferences( $data->settings ? $data->settings : [] )
			            ->set_layout_id( $data->list_id )
			            ->set_read_only( isset( $data->read_only ) ? $data->read_only : false )
			            ->set_updated( isset( $data->date_modified ) ? $data->date_modified : 0 );
		}


		// todo: add placeholders
		foreach ( $data->columns as $settings ) {
			$column = ( new ColumnFactory( $column_types ) )->create( $settings, $list_screen );

			if ( $column ) {
				$list_screen->register_column( $column );
			}
		}

		return $list_screen;
	}

	/**
	 * @param string $key
	 *
	 * @return ListScreen|false
	 */
	private function get_list_screen_by_key( $key ) {
		foreach ( ac_get_list_screen_types() as $list_screen ) {
			if ( $key === $list_screen->get_key() ) {
				return $list_screen;
			}
		}

		return false;
	}

}