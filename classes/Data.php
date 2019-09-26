<?php
namespace AC;

use AC\Storage\DataObject;

interface Data {

	/**
	 * @param array $args
	 *
	 * @return array
	 */
	public function query( array $args );

	/**
	 * @param DataObject $data
	 *
	 * @return int
	 */
	public function create( DataObject $data );

	/**
	 * @param int $id
	 *
	 * @return DataObject
	 */
	public function read( $id );

	/**
	 * @param int        $id
	 * @param DataObject $data
	 *
	 * @return bool
	 */
	public function update( $id, DataObject $data );

	/**
	 * @param int $id
	 *
	 * @return bool
	 */
	public function delete( $id );

}