<?php
/**
 * Resources Content Template
 *
 * @package WooFramework
 * @subpackage Template
 */

$title_before = '<h1 class="title entry-title">';
$title_after = '</h1>';

$url = get_post_meta( get_the_ID(), '_resource_url', true );
if( $url ) {
	$title_before = $title_before . '<a href="' . esc_url( $url ) . '" target="_blank" title="' . the_title_attribute( array( 'echo' => 0 ) ) . '">';
	$title_after = '</a>' . $title_after;
}

woo_post_before();
?>
<article <?php post_class(); ?>>
<?php
	woo_post_inside_before();
?>
	<header>
		<?php the_title( $title_before, $title_after ); ?>
	</header>
<?php
	woo_post_meta();
?>
	<section class="entry">
	    <?php

    	the_content();

    	$media = get_children( array( 'post_parent' => get_the_ID(), 'post_type' => 'attachment', 'posts_per_page' => -1, 'post_status' => 'any' ) );

    	if( ! empty( $media ) ) {

    		$media_list = '<h4 class="resource-media-title">' . __( 'Download file(s):', 'wpcd' ) . '</h4>';

			$c = 0;
    		foreach( $media as $file ) {

    			$filename = wp_get_attachment_url( $file->ID );
    			$ext = strtoupper( pathinfo( $filename, PATHINFO_EXTENSION ) );
    			$title = pathinfo( $filename, PATHINFO_BASENAME );

				if( $c > 0 ) {
					$media_list .= ' | ';
				}
				$media_list .= '<a href="' . esc_url( $filename ) . '" title="' . esc_attr( $title ) . '" target="_blank">' . esc_html( $ext ) . '</a>';

    			++$c;
    		}

	    	echo $media_list;
    	}

    	echo '<hr/>';

	    ?>
	</section><!-- /.entry -->
<?php
	woo_post_inside_after();
?>
</article><!-- /.post -->
<?php
	woo_post_after();
	comments_template();
?>