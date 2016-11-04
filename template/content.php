<?php
/**
 * The main template file.
 *
 * By default, WordPress sets your site’s home page to display your latest blog posts.
 * This page is called the blog posts index.
 * You can also set your blog posts to display on a separate static page.
 * The template file home.php is used to render the blog posts index,
 * whether it is being used as the front page or on separate static page.
 * If home.php does not exist, WordPress will use index.php.
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package ItalyStrap
 * @since 4.0.0
 */

namespace ItalyStrap;

if ( ! defined( 'ABSPATH' ) or ! ABSPATH ) {
	die();
}

get_header();
do_action( 'italystrap_before_main' );
?>
<!-- Main Content -->
	<main id="<?php echo esc_attr( $file_name ); ?>">
		<div class="container">
			<div class="row">
				<?php do_action( 'italystrap_before_content' ); ?>
				<div <?php Core\get_attr( 'content', array( 'itemscope' => true, 'itemtype' => 'http://schema.org/CollectionPage' ), true ); ?>>
					<?php
					do_action( 'italystrap_before_loop' );

					if ( have_posts() ) :

						do_action( 'italystrap_before_while' );

						while ( have_posts() ) :

							the_post();

							$file_type = get_post_type();

							if ( 'single' === CURRENT_TEMPLATE_SLUG ) {
								$file_type = 'single';
							}

							if ( 'search' === CURRENT_TEMPLATE_SLUG ) {
								$file_type = 'post';
							}

							get_template_part( 'loops/content', $file_type );

						endwhile;

						do_action( 'italystrap_after_while' );

					else :

						do_action( 'italystrap_content_none' );

					endif;

					do_action( 'italystrap_after_loop' );
					?>
				</div><!-- / .col-md-8 -->
				<?php do_action( 'italystrap_after_content' ); ?>
			</div><!-- / .row -->
		</div><!-- / .container -->
	</main><!-- / main -->
<?php
do_action( 'italystrap_after_main' );
get_footer();
