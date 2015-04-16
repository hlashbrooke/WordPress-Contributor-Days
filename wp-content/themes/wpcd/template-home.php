<?php
/**
 * Template Name: Home
 */
global $wp_query;

get_header();
?>

    <!-- #content Starts -->
	<?php woo_content_before(); ?>

    <div id="content" class="col-full">

    	<div id="main-sidebar-container">

            <!-- #main Starts -->
            <?php woo_main_before(); ?>
            <section id="main">
			<?php
				woo_loop_before();

				if (have_posts()) { $count = 0;
					while (have_posts()) { the_post(); $count++;
						?>
						<article <?php post_class(); ?>>
						<?php
							woo_post_inside_before();
						?>

							<section class="entry">
							    <?php
							    	the_content();
							    ?>
							</section><!-- /.entry -->

						<?php
							woo_post_inside_after();
						?>
						</article><!-- /.post -->
						<?php
					}
				}

				woo_loop_after();

			?>
            </section><!-- /#main -->
            <?php woo_main_after(); ?>

            <?php get_sidebar(); ?>

		</div><!-- /#main-sidebar-container -->

		<?php get_sidebar( 'alt' ); ?>

    </div><!-- /#content -->
	<?php woo_content_after(); ?>

<?php get_footer(); ?>