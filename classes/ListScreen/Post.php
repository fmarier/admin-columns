<?php
namespace AC\ListScreen;

use AC\ListScreen;
use LogicException;
use WP_Screen;

class Post extends ListScreen {

	const TYPE = 'post';

	/**
	 * @throws LogicException
	 */
	public function __construct( $post_type, $label, $settings = null ) {
		if ( ! $post_type ) {
			throw new LogicException( 'Post type can not be empty.' );
		}

		parent::__construct( self::TYPE, 'post', $label, $settings, $post_type );
	}

	/**
	 * @return string
	 */
	public function get_post_type() {
		return $this->get_subtype();
	}

	public function set_value_callback() {
		add_action( "manage_" . $this->get_post_type() . "_posts_custom_column", function() {
			// todo: fetch column value
			return 'column value';
		}, 100, 2 );
	}

	public function is_valid( WP_Screen $wp_screen ) {
		return 'edit' === $wp_screen->base && 'edit-' . $this->get_post_type() === $wp_screen->id;
	}

	public function get_url() {
		return sprintf( 'admin.php/post/%s', $this->get_subtype() );
	}

}