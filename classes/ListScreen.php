<?php
namespace AC;

use LogicException;
use WP_Screen;

abstract class ListScreen {

	/** @var string */
	private $type;

	/** @var string */
	private $group;

	/** @var string */
	private $label;

	/** @var string e.g. post_type or taxonomy */
	private $subtype = false;

	/** @var array */
	private $settings = [];

	public function __construct( $type, $group, $label, $settings = [], $subtype = false ) {
		$this->type = $type;
		$this->group = $group;
		$this->label = $label;
		$this->subtype = $subtype;
		$this->settings = $settings;
	}

	/**
	 * @return void
	 */
	abstract public function set_value_callback();

	/** @var string */
	abstract public function get_url();

	/**
	 * @param WP_Screen $wp_screen
	 *
	 * @return bool
	 */
	abstract public function is_valid( WP_Screen $wp_screen );

	/**
	 * @return string
	 */
	public function get_type() {
		return $this->type;
	}

	/**
	 * @return string
	 */
	public function get_group() {
		return $this->group;
	}

	/**
	 * @return string
	 */
	public function get_label() {
		return $this->label;
	}

	/**
	 * @return string
	 */
	public function get_subtype() {
		return $this->subtype;
	}

	/**
	 * @return array
	 */
	public function get_settings() {
		return $this->settings;
	}

	/**
	 * @return Column[]
	 */
	public function get_columns() {
		if ( ! $this->settings[ 'columns' ] ) {
			return [];
		}

		$columns = [];

		foreach ( $this->settings[ 'columns' ] as $data ) {
//			$factory = new Column\Factory( Column\Types::instance() );
//
//			if ( ! isset( $data['type'] ) ) {
//				throw new LogicException( 'Missing column data type.' );
//			}
//
//			if ( ! isset( $data['settings'] ) ) {
//				throw new LogicException( 'Missing column settings.' );
//			}
//
//			try {
//				$column = $factory->create( $this->type, $data['type'], new DataObject( $data['settings'] ) );
//			} catch ( LogicException $e ) {
//				continue;
//			}

			// todo
			$columns[] = new Column();
		}

		return $columns;
	}

}