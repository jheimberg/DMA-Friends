<?php
/**
 * Template Name: No Asides
 *
 * Remove the Primary, Secondary and Subsidiary widget areas.
 *
 * @package Nifty
 * @subpackage Template
 */

get_header(); ?>

	<?php do_action( 'nifty_before_content' ); ?>

	<div id="content">

		<?php do_action( 'nifty_open_content' ); ?>

		<div class="hfeed">

<?php if ( have_posts() ) while ( have_posts() ) : the_post(); ?>

			<?php do_action( 'nifty_before_entry' ); ?>

			<div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

				<?php do_action( 'nifty_open_entry' ); ?>

				<div class="entry-content">

					<?php the_content(); ?>

					<?php wp_link_pages( array( 'before' => '<p class="pages">' . __( 'Pages:', 'nifty' ), 'after' => '</p>' ) ); ?>

				</div><!-- .entry-content -->

				<?php do_action( 'nifty_close_entry' ); ?>

			</div><!-- .hentry -->

			<?php do_action( 'nifty_after_entry' ); ?>

			<?php do_action( 'nifty_after_singular' ); ?>

<?php endwhile; // End the loop. Whew. ?>

		</div> <!-- .hfeed -->

		<?php do_action( 'nifty_close_content' ); ?>

	</div><!-- #content -->

	<?php do_action( 'nifty_after_content' ); ?>

<?php get_footer(); ?>
