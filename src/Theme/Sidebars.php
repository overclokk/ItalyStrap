<?php
declare(strict_types=1);

namespace ItalyStrap\Theme;

use \ItalyStrap\Config\ConfigInterface as Config;
use function ItalyStrap\HTML\{open_tag, close_tag};

/**
 * Class for registering sidebars in template
 * There are a standard sidebar and 4 footer dynamic sidebars
 * @package ItalyStrap\Theme
 */
class Sidebars implements Registrable {

	/**
	 * @var Config
	 */
	private $config;

	/**
	 * Init sidebars registration
	 */
	function __construct( Config $config ) {
		$this->config = $config;
	}

	/**
	 * Register Sidebar in template on widget_init
	 */
	public function register() {
		foreach ( $this->config as $sidebar ) {
			\register_sidebar( $this->_default( $sidebar ) );
		}
	}

	private function _default( array $sidebar ) : array {

		$defaults = [
			'name'          => '',
			'id'            => '',
			'description'   => '',
			'class'         => '',
			'before_widget'	=> open_tag( $sidebar['id'] . '-wrapper', 'div', ['id' => '%1$s', 'class' => 'widget %2$s'] ),
			'after_widget'	=> close_tag( $sidebar['id'] . '-wrapper' ),
			'before_title'	=> open_tag( $sidebar['id'] . '-title', 'h3', [ 'class' => 'widget-title' ] ),
			'after_title'	=> close_tag( $sidebar['id'] . '-title' ),
		];

		return \array_merge( $defaults, $sidebar );
	}
}
