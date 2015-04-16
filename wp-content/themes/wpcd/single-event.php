<?php
/**
 * The template for displaying a single event
 *
 * Please note that since 1.7, this template is not used by default. You can edit the 'event details'
 * by using the event-meta-event-single.php template.
 *
 * Or you can edit the entire single event template by creating a single-event.php template
 * in your theme. You can use this template as a guide.
 *
 * For a list of available functions (outputting dates, venue details etc) see http://codex.wp-event-organiser.com/
 *
 * @package Event Organiser (plug-in)
 * @since 1.0.0
 */

global $woo_options;
get_header(); ?>

<?php woo_content_before(); ?>
<div id="content" class="col-full">
	<div id="main-sidebar-container">

		<?php woo_main_before(); ?>
        <section id="main" class="full-width">

	        <?php woo_loop_before(); ?>

			<?php while ( have_posts() ) : the_post(); ?>

				<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

				<header class="entry-header">

					<!-- Display event title -->
					<h1 class="entry-title"><?php the_title(); ?></h1>

				</header><!-- .entry-header -->

				<section class="entry">
					<!-- Get event information, see template: event-meta-event-single.php -->
					<?php eo_get_template_part('event-meta','event-single'); ?>

					<!-- The content or the description of the event-->
					<?php the_content(); ?>
				</section><!-- .entry-content -->

				</article><!-- #post-<?php the_ID(); ?> -->

				<!-- If comments are enabled, show them -->
				<div class="comments-template">
					<?php comments_template(); ?>
				</div>

			<?php endwhile; // end of the loop. ?>

			<?php woo_loop_after(); ?>

		</section>

		<?php woo_main_after(); ?>

		<?php //get_sidebar(); ?>

	</div><!-- #content -->

	<?php //get_sidebar('alt'); ?>

</div><!-- #primary -->

<?php woo_content_after(); ?>

<!-- Call template footer -->
<?php get_footer(); ?>
