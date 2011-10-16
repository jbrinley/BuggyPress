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
							</div><!-- .entry-meta -->
						</header><!-- .entry-header -->

						<div class="entry-content">
							<?php the_content(); ?>
							<?php wp_link_pages( array( 'before' => '<div class="page-link"><span>' . __( 'Pages:', 'twentyeleven' ) . '</span>', 'after' => '</div>' ) ); ?>
							<?php
								$issues = new WP_Query(array(
									'post_type' => BuggyPress_Issue::POST_TYPE,
									'tax_query' => array(
										array(
											'taxonomy' => BuggyPress_Status::TAXONOMY_ID,
											'field' => 'slug',
											'terms' => 'open',
											'operator' => 'IN',
										),
									),
									'meta_query' => array(
										array(
											'key' => BuggyPress_MB_Issue_Project::META_KEY_PROJECT,
											'value' => get_the_ID(),
										),
									),
								));
							?>
							<div class="bp-issues">
								<?php if ( $issues->have_posts() ): ?>
									<h3><?php _e('Open Issues'); ?></h3>
									<ul>
										<?php while ( $issues->have_posts() ): ?>
											<?php $issues->the_post(); ?>
											<li>
												<h4><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h4>
												<p>
													<?php _e('Priority'); ?>: <?php bp_the_issue_priority(); ?>
													&bull;
													<?php _e('Assigned to'); ?>: <?php bp_the_issue_assignee(); ?>
												</p>
											</li>
										<?php endwhile; ?>
									</ul>
									<?php rewind_posts(); the_post(); ?>
								<?php else: ?>
									<h3><?php _e('No Open Issues'); ?></h3>
								<?php endif; ?>
							</div>
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