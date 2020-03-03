<?php
class EventTest extends \Codeception\Test\Unit {

	/**
	 * @var \UnitTester
	 */
	protected $tester;
	
	protected function _before() {
		\tad\FunctionMockerLe\define( 'add_filter', function ( $filtername, $value ) {
			return true;
		} );
		\tad\FunctionMockerLe\define( 'remove_filter', function ( $filtername ) {
			return true;
		} );
	}

	protected function _after() {
	}

	public function getIntance() {
		$sut = new ItalyStrap\Event\Manager();
		$this->assertInstanceOf( \ItalyStrap\Event\Manager::class, $sut, '' );
		return $sut;
	}

	/**
	 * @test
	 */
	public function ItShouldBeinstantiable() {
		$this->getIntance();
	}

	/**
	 * @test
	 */
	public function ItShouldBeAddSubscriberWithoutErrors() {
		$sut = $this->getIntance();

		$subscriber = new class implements \ItalyStrap\Event\Subscriber_Interface {
			/**
			 * @inheritDoc
			 */
			public static function get_subscribed_events(): array {
				return [
					'italystrap_theme_load'	=> [
						\ItalyStrap\Event\Manager::CALLBACK	=> 'start',
						\ItalyStrap\Event\Manager::PRIORITY	=> 20,
					]
				];
			}
		};

		$sut->add_subscriber( $subscriber );
	}
}