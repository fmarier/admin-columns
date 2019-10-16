<?php
namespace AC;

class ColumnTypes {

	/** @var ColumnTypes */
	private static $instance = null;

	/** @var array */
	private $columns;

	/**
	 * @return ColumnTypes
	 */
	static public function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * @param ListScreen $list_screen
	 * @param Column     $column
	 */
	public function register_column( ListScreen $list_screen, Column $column ) {
		$this->columns[ get_class( $list_screen ) ][ $column->get_type() ] = $column;
	}

	/**
	 * @param ListScreen $list_screen
	 *
	 * @return Column[]
	 */
	// todo: return Column Collection
	public function get_columns( ListScreen $list_screen ) {
		return $this->columns[ get_class( $list_screen ) ];
	}

}