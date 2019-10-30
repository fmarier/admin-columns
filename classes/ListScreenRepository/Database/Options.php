<?php

namespace AC\ListScreenRepository\Database;

use AC\ListScreen;
use AC\ListScreenCollection;
use AC\ListScreenFactory;
use AC\ListScreenRepository;
use AC\Storage\DataObject;

class Options implements ListScreenRepository\Write, ListScreenRepository\ListScreenRepository {

	const SETTINGS_KEY = 'cpac_layouts';
	const COLUMNS_KEY = 'cpac_options';

	/** @var ListScreenFactory */
	private $list_screen_factory;

	public function __construct( $list_screen_factory ) {
		$this->list_screen_factory = $list_screen_factory;
	}

	public function find_all( array $args = [] ) {
		global $wpdb;
		$storage_key = self::COLUMNS_KEY . '_' . $args['type'];
		$list_screens = [];

		// Load from DB
		if ( $results = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->options} WHERE option_name LIKE %s ORDER BY option_id DESC", $wpdb->esc_like( $storage_key ) . '%' ) ) ) {

			foreach ( $results as $result ) {

				// Removes incorrect layouts.
				// For example when a list screen is called "Car" and one called "Carrot", then
				// both layouts from each model are in the DB results.
				if ( strlen( $result->option_name ) !== strlen( $storage_key ) + 13 && $result->option_name != $storage_key ) {
					continue;
				}

				$list_id = str_replace( $storage_key, '', $result->option_name );
				$list_screens[] = $this->create_list_screen_from_columns_settings( $list_id, $result );
			}
		}

		return new ListScreenCollection( $list_screens );
	}

	private function create_list_screen_from_columns_settings( $list_id, $settings ) {
		$type = str_replace( [ self::COLUMNS_KEY . '_', $list_id ], '', $settings->option_name );

		$data = [
			'type'     => $type,
			'columns'  => unserialize( $settings->option_value ),
			'list_id'  => $list_id,
			'settings' => $this->find_layout_settings( $list_id, $type ),
		];

		$list_screen_settings = $this->find_layout_settings( $list_id, $type );
		if ( $list_screen_settings ) {
			$data['title'] = $list_screen_settings['name'];
			$data['roles'] = $list_screen_settings['roles'];
			$data['users'] = $list_screen_settings['users'];
		}

		return $this->list_screen_factory->create( $type, new DataObject( $data ) );
	}

	/**
	 *
	 */
	private function find_layout_settings( $id, $type ) {
		global $wpdb;

		$key = self::SETTINGS_KEY . $type . $id;
		$sql = $wpdb->prepare( "
			SELECT *
			FROM  {$wpdb->options}
			WHERE {$wpdb->options}.option_name = %s
			", $key );

		$result = $wpdb->get_row( $sql );

		if ( empty( $result ) ) {
			return [];
		}

		return (array) unserialize( $result->option_value );
	}

	public function find( $id ) {
		global $wpdb;

		$results = $wpdb->get_row( $wpdb->prepare( "
			SELECT {$wpdb->options}.option_value, {$wpdb->options}.option_name 
			FROM {$wpdb->options} 
			WHERE option_name LIKE %s 
			LIMIT 1", 'cpac_options_%' . $wpdb->esc_like( $id ) ) );

		if ( ! $results ) {
			return null;
		}

		return $this->create_list_screen_from_columns_settings( $id, $results );
	}

	public function find_columns_by_id() {

	}

	public function save( ListScreen $list_screen ) {
		// TODO: Implement save() method.

		$list_screen->get_settings();
	}

	public function exists( $id ) {
		// TODO: Implement exists() method.
	}

	public function delete( ListScreen $list_screen ) {
		// TODO: Implement delete() method.
	}

}