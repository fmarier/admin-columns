<?php

namespace AC\Check;

use AC\Admin\Promo;
use AC\Ajax;
use AC\Message\Notice\Dismissible;
use AC\Preferences;
use AC\Registrable;
use AC\Screen;

final class Promotion
	implements Registrable {

	/** @var Promo */
	private $promo;

	public function __construct( Promo $promo ) {
		$this->promo = $promo;
	}

	public function register() {
		add_action( 'ac/screen', array( $this, 'display' ) );

		$this->get_ajax_handler()->register();
	}

	/**
	 * @return Ajax\Handler
	 */
	private function get_ajax_handler() {
		$handler = new Ajax\Handler();
		$handler
			->set_action( 'ac_dismiss_notice_promo_' . $this->get_individual_slug() )
			->set_callback( array( $this, 'ajax_dismiss_notice' ) );

		return $handler;
	}

	private function get_individual_slug() {
		return $this->promo->get_slug() . $this->promo->get_date_range()->get_start()->format( 'Ymd' );
	}

	/**
	 * @return Preferences\User
	 */
	private function get_preferences() {
		return new Preferences\User( 'check-promo-' . $this->get_individual_slug() );
	}

	/**
	 * Dismiss notice
	 */
	public function ajax_dismiss_notice() {
		$this->get_ajax_handler()->verify_request();
		$this->get_preferences()->set( 'dismiss-notice', true );
	}

	/**
	 * @param Screen $screen
	 */
	public function display( Screen $screen ) {
		if ( ! $this->promo->get_date_range()->in_range() ) {
			return;
		}

		$message = $this->promo->get_title();

		$notice = new Dismissible( $message, $this->get_ajax_handler() );
		$notice->register();
	}
}