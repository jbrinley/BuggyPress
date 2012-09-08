<?php
/**
 * The template for displaying issue archive pages.
 *
 * To override, create archive-issue.php in the template directory
 *
 * Learn more: http://codex.wordpress.org/Template_Hierarchy
 */

get_header(); ?>

<section id="primary">
	<div id="content" role="main">

		<header class="page-header">
			<h1 class="page-title">
				<?php _e('Issues', 'buggypress'); // TODO: include the project name and/or filter name, if applicable ?>
			</h1>
		</header>

		<?php twentyeleven_content_nav( 'nav-above' ); ?>

		<?php do_action('issue_filter_form', $wp_query); ?>


		<?php if ( have_posts() ) : ?>
		<div class="entry-content">
			<table class="buggypress-table issue-table">
				<thead>
					<tr>
						<th><?php _e('ID', 'buggypress'); ?></th>
						<th><?php _e('Title', 'buggypress'); ?></th>
						<th><?php _e('Priority', 'buggypress'); ?></th>
						<th><?php _e('Status', 'buggypress'); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php /* Start the Loop */ ?>
					<?php while ( have_posts() ) : the_post(); ?>
						<tr>
							<td><?php the_ID(); ?></td>
							<td><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></td>
							<td><?php bp_the_issue_priority(); ?></td>
							<td><?php bp_the_issue_status(); ?></td>
						</tr>
					<?php endwhile; ?>
				</tbody>
			</table>
		</div>

		<?php twentyeleven_content_nav( 'nav-below' ); ?>

		<?php else : ?>

		<article id="post-0" class="post no-results not-found">
			<header class="entry-header">
				<h1 class="entry-title"><?php _e( 'Nothing Found', 'twentyeleven' ); ?></h1>
			</header><!-- .entry-header -->

			<div class="entry-content">
				<p><?php _e( 'Apologies, but no results were found for the requested archive. Perhaps searching will help find a related post.', 'twentyeleven' ); ?></p>
				<?php get_search_form(); ?>
			</div><!-- .entry-content -->
		</article><!-- #post-0 -->

		<?php endif; ?>

	</div><!-- #content -->
</section><!-- #primary -->

<?php get_sidebar(); ?>
<?php get_footer(); ?>