<?php
defined( 'ABSPATH' ) or die();

/**
 * @since NEWVERSION
 */
class AC_Column_Post_Tags extends CPAC_Column {

	public function init() {
		parent::init();

		$this->properties['type'] = 'tags';

		$this->properties['is_cloneable'] = false;
		$this->properties['original'] = true;

		$this->options['width'] = 15;
		$this->options['width_unit'] = '%';
	}

	public function apply_conditional() {
		return ac_helper()->post->is_registered_by_post_type( $this->get_post_type() );
	}

}