<?php
declare(strict_types=1);

namespace ItalyStrap\Builders;

use \ItalyStrap\Config\ConfigInterface as Config;

/**
 * Class ParseAttr
 *
 * @TODO https://github.com/Rarst/advanced-hooks-api
 *
 * @package ItalyStrap\Builders
 */
class ParseAttr {

	const ACCEPTED_ARGS = 3;

	/**
	 * Default accepted args.
	 *
	 * @var int
	 */
	public static $accepted_args = self::ACCEPTED_ARGS;

	private $config;

	/**
	 * Parse_Attr constructor.
	 * @param Config $config
	 */
	public function __construct( Config $config ) {
		$this->config = $config;
	}

	/**
	 * @see \ItalyStrap\HTML\filter_attr()
	 */
	public function apply() {

		/**
		 * @var string|int|bool|array|callable $filter_attr
		 */
		foreach ( $this->config as $filter_name => $filter_attr ) {
			if ( \is_callable( $filter_attr ) ) {
				\add_filter( $filter_name, $filter_attr, 10, static::$accepted_args );
				continue;
			}

			if ( ! \is_array( $filter_attr ) ) {
				\add_filter( $filter_name, [ $this, 'parseScalar'], 10, static::$accepted_args );
				continue;
			}

			\add_filter( $filter_name, [ $this, 'parseArray'], 10, static::$accepted_args );
		}
	}

	/**
	 * @param array $attr
	 * @return array
	 */
	public function parseArray( array $attr ) {
		return \array_merge( $attr, $this->config->get( \current_filter(), [] ) );
	}

	/**
	 * @param  string|int|bool $value
	 * @return string|int|bool
	 */
	public function parseScalar( $value ) {
		return $this->config->get( \current_filter(), $value );
	}
}
