<?php
declare(strict_types=1);
namespace ItalyStrap;

use Auryn\Injector;
use ItalyStrap\Asset\AssetInterface;
use ItalyStrap\Asset\AssetManager;
use ItalyStrap\Asset\AssetsManagerOld;
use ItalyStrap\Asset\ScriptOld;
use ItalyStrap\Asset\StyleOld;
use ItalyStrap\Builders\BuilderInterface;
use ItalyStrap\Config\{Config, ConfigFactory, ConfigInterface};
use ItalyStrap\Empress\AurynConfig;
use ItalyStrap\Event\EventDispatcher;
use ItalyStrap\Event\EventResolverExtension;
use ItalyStrap\Event\EventDispatcherInterface;
use ItalyStrap\Event\SubscriberRegister;
use ItalyStrap\Event\SubscriberRegisterInterface;
use ItalyStrap\Finder\FileInfoFactory;
use ItalyStrap\Finder\FileInfoFactoryInterface;
use ItalyStrap\Finder\FilesHierarchyIterator;
use ItalyStrap\Finder\Finder;
use ItalyStrap\Finder\FinderFactory;
use ItalyStrap\Finder\FinderInterface;
use ItalyStrap\Finder\SearchFileStrategy;
use ItalyStrap\HTML\Attributes;
use ItalyStrap\HTML\AttributesInterface;
use ItalyStrap\HTML\Tag;
use ItalyStrap\Asset\AssetsSubscriber;
use ItalyStrap\Theme\{NavMenus, Sidebars, Support, TextDomain, Thumbnails, TypeSupport};
use ItalyStrap\HTML\TagInterface;
use ItalyStrap\View\ViewInterface;
use Walker_Nav_Menu;
use function ItalyStrap\Config\{get_config_file_content};
use function ItalyStrap\Factory\get_config;

return [

	/**
	 * ==========================================================
	 *
	 * Autoload Shared Classes
	 *
	 * string|object
	 *
	 * ==========================================================
	 */
	AurynConfig::SHARING				=> [
		EventDispatcher::class,
		SubscriberRegister::class,

		/**
		 * Make sure the config is shared.
		 * Already shared in bootstrap.php or in ACM if is active.
		 */
		Config::class,

		Attributes::class,
		Tag::class,
		View\View::class,
	],

	/**
	 * ==========================================================
	 *
	 * Autoload Aliases Class
	 *
	 * string
	 *
	 * ==========================================================
	 */
	AurynConfig::ALIASES				=> [
		EventDispatcherInterface::class		=> EventDispatcher::class,
		SubscriberRegisterInterface::class	=> SubscriberRegister::class,

		ConfigInterface::class				=> Config::class,
//		Config_Interface::class				=> Config::class,

		AttributesInterface::class			=> Attributes::class,
		TagInterface::class					=> Tag::class,

		FileInfoFactoryInterface::class		=> FileInfoFactory::class,
		SearchFileStrategy::class			=> FilesHierarchyIterator::class,
		FinderInterface::class				=> Finder::class,

		ViewInterface::class				=> View\View::class,
		Walker_Nav_Menu::class				=> Navbar\BootstrapNavMenu::class,
		BuilderInterface::class				=> Builders\Builder::class,
	],

	/**
	 * ==========================================================
	 *
	 * Autoload Classes definitions
	 *
	 * @example :
	 * 'walker'		=> 'ItalyStrap\Navbar\Bootstrap_Nav_Menu',
	 * ':walker'	=> new \ItalyStrap\Navbar\Bootstrap_Nav_Menu(),
	 * '+walker'	=> function () {
	 * 		return new \ItalyStrap\Navbar\Bootstrap_Nav_Menu();
	 * },
	 *
	 * ==========================================================
	 */
	AurynConfig::DEFINITIONS			=> [

		Theme\Sidebars::class	=> [
			 ':config'	=> ConfigFactory::make( get_config_file_content( 'theme/sidebars' ) ),
		],
		Theme\Thumbnails::class	=> [
			':config'	=> ConfigFactory::make( get_config_file_content( 'theme/thumbnails' ) ),
		],
		Theme\Support::class	=> [
			':config'	=> ConfigFactory::make( get_config_file_content( 'theme/supports' ) ),
		],
		Theme\TypeSupport::class	=> [
			':config'	=> ConfigFactory::make( get_config_file_content( 'theme/type-supports' ) ),
		],
		Theme\NavMenus::class	=> [
			':config'	=> ConfigFactory::make( get_config_file_content( 'theme/nav-menus' ) ),
		],

		Builders\Builder::class	=> [
			':config'	=> ConfigFactory::make( get_config_file_content( 'structure' ) ),
		],

//		Builders\Parse_Attr::class	=> [
//			':config'	=> Config_Factory::make(
//				array_replace_recursive(
//					get_config_file_content( 'html_attrs' ),
//					get_config_file_content( 'schema' )
//				)
//			),
//		],

		Components\Navigations\Navbar::class	=> [
			':fallback_cb' => [ Navbar\BootstrapNavMenu::class, 'fallback' ],
		],
		Components\Navigations\Pagination::class	=> [
			':config'	=> ConfigFactory::make( get_config_file_content( 'components/pagination' ) ),
		],
		Components\Navigations\Pager::class	=> [
			':config'	=> ConfigFactory::make( get_config_file_content( 'components/pager' ) ),
		],
	],

	/**
	 * ==========================================================
	 *
	 * Autoload Parameters Definitions
	 *
	 * string
	 *
	 * ==========================================================
	 */
	AurynConfig::DEFINE_PARAM			=> [
//		':theme_mods'	=> function () : array {
//			return get_config()->all();
//		},
		'theme_mods'	=> get_config()->all(),
		':wp_query'		=> function (): \WP_Query {
			global $wp_query;
			return $wp_query;
		},
		':query'			=> function (): \WP_Query  {
			global $wp_query;
			return $wp_query;
		},
	],

	/**
	 * ========================================================================
	 *
	 * Autoload Delegates
	 * @link https://github.com/rdlowrey/auryn#instantiation-delegates
	 * 'MyComplexClass'	=> $complexClassFactory,
	 * 'SomeClassWithDelegatedInstantiation'	=> 'MyFactory',
	 * 'SomeClassWithDelegatedInstantiation'	=> 'MyFactory::factoryMethod'
	 *
	 * string
	 *
	 * ========================================================================
	 */
	AurynConfig::DELEGATIONS			=> [],

	/**
	 * ========================================================================
	 *
	 * Autoload Prepares
	 * @link https://github.com/rdlowrey/auryn#prepares-and-setter-injection
	 *
	 * string
	 *
	 * ========================================================================
	 */
	AurynConfig::PREPARATIONS			=> [

		/**
		 * This class is lazy loaded
		 */
		AssetManager::class			=> function ( AssetManager $manager, Injector $injector ) {

			/** @var EventDispatcher $event_dispatcher */
			$event_dispatcher = $injector->make(EventDispatcher::class);

			$styles = $event_dispatcher->filter(
				'italystrap_config_enqueue_style',
				get_config_file_content( 'assets/styles' )
			);

			$scripts = $event_dispatcher->filter(
				'italystrap_config_enqueue_script',
				get_config_file_content( 'assets/scripts' )
			);

			foreach ( $styles as $style ) {
				$assets[] = new \ItalyStrap\Asset\Style( ConfigFactory::make( $style ) );
			}
			foreach ( $scripts as $style ) {
				$assets[] = new \ItalyStrap\Asset\Script( ConfigFactory::make( $style ) );
			}

			$manager->withAssets(...$assets);

//			$event_dispatcher->addListener('shutdown', function (){
//			});
		},
		AssetsManagerOld::class		=> function ( AssetsManagerOld $assets_manager, Injector $injector ): void {
return;
			/** @var EventDispatcher $event_dispatcher */
			$event_dispatcher = $injector->make(EventDispatcher::class);

			/** @var Finder $finder */
			$finder = (new FinderFactory())->make();

			$config = get_config();
			$dirs = [
				$config->CHILDPATH,
				$config->CHILDPATH . '/assets',
				$config->PARENTPATH . '/assets',
			];

			$finder->in($dirs);

			$custom = $finder->firstFile('css/custom', 'css', '.');

			if ( \ItalyStrap\Core\is_debug() ) {
				$event_dispatcher->addListener(
					\Inpsyde\Assets\AssetManager::ACTION_SETUP,
					function (
						\Inpsyde\Assets\AssetManager $asset_manager
					) use ($config, $custom) {
						$assets = (new \Inpsyde\Assets\Loader\ArrayLoader)->load(
							[
								[
									'handle'	=> CURRENT_TEMPLATE_SLUG . '-foo',
									'url'		=> $config->get('STYLESHEETURL') . '/css/custom.css',
//									'url'		=> '//italystrap.test' . '/css/custom.css',
									'filePath'	=> $custom->getRealPath(),
									'version'	=> \ItalyStrap\Core\is_debug() ? \strval( rand( 0, 100000 ) ) : '',
									'type'		=> \Inpsyde\Assets\Style::class
								]
							]
						);

						foreach ($assets as $asset ) {
							$asset_manager->register( $asset );
						}
					}
				);
			}

			$loaded = false;
			$event_dispatcher->addListener(
				'wp_enqueue_scripts',
				function () use ( $assets_manager, &$loaded, $event_dispatcher ) {

					$style = $event_dispatcher->filter(
						'italystrap_config_enqueue_style',
						get_config_file_content( 'assets/styles' )
					);
					$script = $event_dispatcher->filter(
						'italystrap_config_enqueue_script',
						get_config_file_content( 'assets/scripts' )
					);

					$loaded = true;
					$assets_manager->withAssets(
						new StyleOld( ConfigFactory::make( $style ) ),
						new ScriptOld( ConfigFactory::make( $script ) )
					);
				}, 1 );

			$event_dispatcher->addListener('shutdown', function () use (&$loaded){
				if ( ! $loaded && \function_exists( 'debug' ) ) {
					\debug(\sprintf(
						'Assets are not loaded properly, called in: %s',
						__FILE__
					));
				}
			});
		},

		Builders\Builder::class	=> function( Builders\Builder $builder, Injector $injector ) {
			$builder->set_injector( $injector );
		},

		Finder::class	=> function( FinderInterface $finder, Injector $injector ) {
			$config = get_config();

			$dirs = [
				$config->CHILDPATH . '/' . $config->template_dir,
				$config->PARENTPATH . '/' . $config->template_dir,
			];

			$finder->in( $dirs );
		},
	],

	/**
	 * ========================================================================
	 *
	 * Lazyload Classes
	 * Loaded on admin and front-end
	 *
	 * Useful if you need to load lazily your classes, for example
	 * if you want classes are loaded only when are really needed.
	 *
	 * ========================================================================
	 */
	AurynConfig::PROXY					=> [
		AssetManager::class,
	],

	/**
	 * ========================================================================
	 *
	 * Autoload Subscribers Classes
	 * Loaded on admin and front-end
	 *
	 * If you use key => value pair make sure to bind the key with an option name
	 * to activate or deactivate the service from an option panel.
	 *
	 * ========================================================================
	 */
	EventResolverExtension::SUBSCRIBERS				=> [

		/**
		 * Register Theme stuff
		 */
		NavMenus::class,
		Sidebars::class,
		Support::class,
		TextDomain::class,
		Thumbnails::class,
		TypeSupport::class,


		Custom\Metabox\Register::class,

		Admin\Nav_Menu\ItemCustomFields::class,
		Customizer\ThemeCustomizer::class,
		Css\Css::class,
		User\ContactMethods::class,

		// This is the class that build the page
		Builders\Director::class,

		/**
		 * With this class I can layload the AssetManager::class
		 * see above in the PROXY config.
		 */
		AssetsSubscriber::class,
	],
];
