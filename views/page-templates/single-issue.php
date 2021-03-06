<?php
/**
 * The Template for displaying all single posts.
 *
 * @package WordPress
 * @subpackage Twenty_Eleven
 * @since Twenty Eleven 1.0
 */

get_header(); ?>

		<div id="primary">
			<div id="content" role="main">

				<?php while ( have_posts() ) : the_post(); ?>

					<nav id="nav-single">
						<h3 class="assistive-text"><?php _e( 'Post navigation', 'twentyeleven' ); ?></h3>
						<span class="nav-previous"><?php previous_post_link( '%link', __( '<span class="meta-nav">&larr;</span> Previous', 'twentyeleven' ) ); ?></span>
						<span class="nav-next"><?php next_post_link( '%link', __( 'Next <span class="meta-nav">&rarr;</span>', 'twentyeleven' ) ); ?></span>
					</nav><!-- #nav-single -->

					<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
						<header class="entry-header">
							<h1 class="entry-title"><?php the_title(); ?></h1>

							<div class="entry-meta">
								<?php printf( __( '<span class="sep">Posted on </span><a href="%1$s" title="%2$s" rel="bookmark"><time class="entry-date" datetime="%3$s" pubdate>%4$s</time></a><span class="by-author"> <span class="sep"> by </span> <span class="author vcard"><a class="url fn n" href="%5$s" title="%6$s" rel="author">%7$s</a></span></span>', 'twentyeleven' ),
									esc_url( get_permalink() ),
									esc_attr( get_the_time() ),
									esc_attr( get_the_date( 'c' ) ),
									esc_html( get_the_date() ),
									esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ),
									sprintf( esc_attr__( 'View all posts by %s', 'twentyeleven' ), get_the_author() ),
									esc_html( get_the_author() )
								); ?>
							</div><!-- .entry-meta -->
							<div class="issue-details">
								<p><?php _e('Project'); ?>: <?php bp_the_project_link(); ?></p>
								<p><?php _e('Issue Type'); ?>: <?php bp_the_issue_type(); ?></p>
								<p><?php _e('Priority'); ?>: <?php bp_the_issue_priority(); ?></p>
								<p><?php _e('Status'); ?>: <?php bp_the_issue_status(); ?></p>
								<p><?php _e('Assigned to'); ?>: <?php bp_the_issue_assignee(); ?></p>
							</div>
						</header><!-- .entry-header -->

						<div class="entry-content">
							<?php the_content(); ?>
							<?php wp_link_pages( array( 'before' => '<div class="page-link"><span>' . __( 'Pages:', 'twentyeleven' ) . '</span>', 'after' => '</div>' ) ); ?>
						</div><!-- .entry-content -->

						<footer class="entry-meta">
							<?php
								/* translators: used between list items, there is a space after the comma */
								$categories_list = get_the_category_list( __( ', ', 'twentyeleven' ) );

								/* translators: used between list items, there is a space after the comma */
								$tag_list = get_the_tag_list( '', __( ', ', 'twentyeleven' ) );
								if ( '' != $tag_list ) {
									$utility_text = __( 'This entry was posted in %1$s and tagged %2$s by <a href="%6$s">%5$s</a>. Bookmark the <a href="%3$s" title="Permalink to %4$s" rel="bookmark">permalink</a>.', 'twentyeleven' );
								} elseif ( '' != $categories_list ) {
									$utility_text = __( 'This entry was posted in %1$s by <a href="%6$s">%5$s</a>. Bookmark the <a href="%3$s" title="Permalink to %4$s" rel="bookmark">permalink</a>.', 'twentyeleven' );
								} else {
									$utility_text = __( 'This entry was posted by <a href="%6$s">%5$s</a>. Bookmark the <a href="%3$s" title="Permalink to %4$s" rel="bookmark">permalink</a>.', 'twentyeleven' );
								}

								printf(
									$utility_text,
									$categories_list,
									$tag_list,
									esc_url( get_permalink() ),
									the_title_attribute( 'echo=0' ),
									get_the_author(),
									esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) )
								);
							?>
							<?php edit_post_link( __( 'Edit', 'twentyeleven' ), '<span class="edit-link">', '</span>' ); ?>

						</footer><!-- .entry-meta -->
					</article><!-- #post-<?php the_ID(); ?> -->

					<?php comments_template( '', true ); ?>

				<?php endwhile; // end of the loop. ?>

			</div><!-- #content -->
		</div><!-- #primary -->

<?php get_footer(); ?>