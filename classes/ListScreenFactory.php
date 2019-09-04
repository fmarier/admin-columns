<?php

namespace AC;

class ListScreenFactory {

	/**
	 * @param string $type
	 * @param string $subtype
	 * @param array  $settings
	 *
	 * @return ListScreen\Post
	 * @throws \LogicException
	 */
	public function create( $type, $subtype = null, array $settings = null ) {
		if ( null === $settings ) {
			$settings = [];
		}

		$list_screen_type = $this->get_list_screen_type( $type, $subtype );

		if ( false === $list_screen_type ) {
			throw new \LogicException( sprintf( 'Invalid list screen type: %s.', $type ) );
		}

		$class_name = get_class( $list_screen_type );

		switch ( true ) {

			case $list_screen_type instanceof ListScreen\Post :
				return new $class_name( $subtype, ucfirst( $subtype ), $settings );

			default :
				return new $class_name( $settings );
		}
	}

	/**
	 * @param string $type
	 * @param string $subtype
	 *
	 * @return ListScreen|false
	 */
	private function get_list_screen_type( $type, $subtype ) {
		foreach ( ListScreenTypes::instance()->get_list_screens() as $list_screen ) {
			if ( $type === $list_screen->get_type() && $subtype === $list_screen->get_subtype() ) {
				return $list_screen;
			}
		}

		return false;
	}

	/**
	 * @param string $type
	 * @param int    $id Optional (layout) ID
	 *
	 * @return ListScreen|false
	 */
//	public static function create( $type, $id = null ) {
//		$list_screens = AC()->get_list_screens();
//
//		if ( ! isset( $list_screens[ $type ] ) ) {
//			return false;
//		}
//
//		$list_screen = clone $list_screens[ $type ];
//
//		//$list_screen->set_layout_id( $id );
//
//		return $list_screen;
//	}

	/**
	 * @param Request $request
	 *
	 * @return ListScreen|false
	 */
//	public static function create_from_request( Request $request ) {
//		$type = $request->filter( 'list_screen', '', FILTER_SANITIZE_STRING );
//		$id = $request->filter( 'layout', null, FILTER_SANITIZE_STRING );
//
//		return self::create( $type, $id );
//	}

}