<?php

namespace AC\ListScreenRepository;

use AC\ListScreen;
use AC\ListScreenCollection;
use AC\ListScreenTypes;
use DateTime;
use LogicException;

class DataBase implements Write, ListScreenRepository, SourceAware {

	const TABLE = 'admin_columns';

	/** @var ListScreenTypes */
	private $list_screen_types;

	public function __construct( ListScreenTypes $list_screen_types ) {
		$this->list_screen_types = $list_screen_types;
	}

	/**
	 * @param array $args
	 *
	 * @return ListScreenCollection
	 */
	public function find_all( array $args = [] ) {
		global $wpdb;

		$table_name = $wpdb->prefix . self::TABLE;

		$sql = "SELECT * FROM {$table_name}";

		if ( isset( $args['key'] ) ) {
			$sql .= $wpdb->prepare( " WHERE list_key = %s", $args['key'] );
		}

		$sql .= ';';

		$list_screens = new ListScreenCollection();

		foreach ( $wpdb->get_results( $sql ) as $list_data ) {
			try {
				$list_screen = $this->create_list_screen( $list_data );
			} catch ( LogicException $e ) {
				continue;
			}

			$list_screens->push( $list_screen );
		}

		return $list_screens;
	}

	/**
	 * @param string $list_id
	 *
	 * @return ListScreen|null
	 */
	public function find( $list_id ) {
		global $wpdb;

		$table_name = $wpdb->prefix . self::TABLE;

		$sql = $wpdb->prepare( "SELECT * FROM {$table_name} WHERE list_id = %s LIMIT 1;", $list_id );

		$data = $wpdb->get_row( $sql );

		if ( null === $data ) {
			return null;
		}

		try {
			$list_screen = $this->create_list_screen( $data );
		} catch ( LogicException $e ) {
			return null;
		}

		return $list_screen;
	}

	/**
	 * @param ListScreen $data
	 *
	 * @return void
	 */
	public function save( ListScreen $list_screen ) {
		$id = $this->get_id( $list_screen->get_layout_id() );

		if ( $id ) {
			$this->update( $id, $list_screen );

			return;
		}

		$this->create( $list_screen );
	}

	/**
	 * @param string $list_id
	 *
	 * @return int
	 */
	private function get_id( $list_id ) {
		global $wpdb;

		$table_name = $wpdb->prefix . self::TABLE;

		$sql = $wpdb->prepare( "SELECT id FROM {$table_name} WHERE list_id = %s LIMIT 1;", $list_id );

		return (int) $wpdb->get_var( $sql );
	}

	private function create( ListScreen $listScreen ) {
		global $wpdb;

		if ( empty( $listScreen->get_layout_id() ) ) {
			throw new LogicException( 'Invalid listscreen Id.' );
		}

		$wpdb->insert(
			$wpdb->prefix . self::TABLE,
			[
				'title'         => $listScreen->get_title(),
				'list_id'       => $listScreen->get_layout_id(),
				'list_key'      => $listScreen->get_key(),
				'columns'       => serialize( $listScreen->get_settings() ),
				'settings'      => serialize( $listScreen->get_preferences() ),
				'date_modified' => $listScreen->get_updated()->format( 'Y-m-d H:i:s' ),
				'date_created'  => $listScreen->get_updated()->format( 'Y-m-d H:i:s' ),
			],
			[
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
			]
		);
	}

	private function update( $id, ListScreen $listScreen ) {
		global $wpdb;

		if ( empty( $listScreen->get_layout_id() ) ) {
			throw new LogicException( 'Invalid listscreen Id.' );
		}

		$wpdb->update(
			$wpdb->prefix . self::TABLE,
			[
				'list_id'       => $listScreen->get_layout_id(),
				'list_key'      => $listScreen->get_key(),
				'title'         => $listScreen->get_title(),
				'columns'       => serialize( $listScreen->get_settings() ),
				'settings'      => serialize( $listScreen->get_preferences() ),
				'date_modified' => $listScreen->get_updated()->format( 'Y-m-d H:i:s' ),
			],
			[
				'id' => $id,
			],
			[
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
			],
			[
				'%d',
			]
		);
	}

	public function delete( ListScreen $list_screen ) {
		global $wpdb;

		/**
		 * Fires before a column setup is removed from the database
		 * Primarily used when columns are deleted through the Admin Columns settings screen
		 *
		 * @param ListScreen $list_screen
		 *
		 * @deprecated NEWVERSION
		 * @since      3.0.8
		 */
		do_action( 'ac/columns_delete', $list_screen );

		$wpdb->delete(
			$wpdb->prefix . self::TABLE,
			[
				'list_id' => $list_screen->get_layout_id(),
			],
			[
				'%s',
			]
		);
	}

	private function create_list_screen( $data ) {
		$key = $data->list_key;

		// create a cloned reference
		$list_screen = $this->list_screen_types->get_list_screen_by_key( $key );

		if ( null === $list_screen ) {
			throw new LogicException( 'List screen not found.' );
		}

		$list_screen->set_title( $data->title )
		            ->set_layout_id( $data->list_id )
		            ->set_updated( DateTime::createFromFormat( 'Y-m-d H:i:s', $data->date_modified ) );

		if ( $data->settings ) {
			$list_screen->set_preferences( unserialize( $data->settings ) );
		}

		if ( $data->columns ) {
			$list_screen->set_settings( unserialize( $data->columns ) );
		}

		$list_screen->set_source( $data->id );

		return $list_screen;
	}

	public function exists( $list_id ) {
		return null !== $this->get_id( $list_id );
	}

	public function getSource( ListScreen $listScreen ) {
		return $this->get_id( $listScreen->get_layout_id() );
	}

}