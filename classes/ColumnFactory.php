<?php
namespace AC;

class ColumnFactory {

	/** @var ColumnTypes */
	private $column_types;

	public function __construct( ColumnTypes $column_types ) {
		$this->column_types = $column_types;
	}

	/**
	 * @param array      $data
	 * @param ListScreen $list_screen
	 *
	 * @return Column|null
	 */
	public function create( array $data, ListScreen $list_screen ) {
		$column_types = $this->column_types->get_columns( $list_screen );

		$type = $data['type'];

		if ( ! isset( $column_types[ $type ] ) ) {
			return null;
		}

		$classname = get_class( $column_types[ $type ] );

		/* @var Column $column */
		$column = new $classname();
		$column->set_list_screen( $list_screen )
		       ->set_type( $type );

		if ( isset( $data['name'] ) ) {
			$column->set_name( $data['name'] );
		}

		// todo: test
		if ( isset( $data['label'] ) ) {
			$column->set_label( $data['label'] );
		}

		// Mark as original
		// todo
		//		if ( $this->is_original_column( $data['type'] ) ) {
		//			$column->set_original( true );
		//			$column->set_name( $data['type'] );
		//		}

		$column->set_options( $data );

		return $column;
	}

}