<div <?php post_class( 'post post--single post--main' ); ?>>
	<?php if ( has_post_thumbnail() ) : ?>
		<div class="post__image">
			<?php
			the_post_thumbnail( 'ht_landscape_large' );

			get_template_part( 'templates/post/post-date' );
			?>
		</div>
	<?php endif; ?>
	<div class="post__content">
		<div class="post__text">
			<?php the_content(); ?>
		</div>
		<?php
		wp_link_pages(
			[
				'before'      => '<nav class="pagination"><div class="nav-links">',
				'after'       => '</div></nav>',
				'link_before' => '<span class="page-numbers">',
				'link_after'  => '</span>',
			]
		);

		if ( has_tag() ) :
			?>
			<div class="post__tags">
				<div class="tagcloud">
					<?php the_tags( '', '' ); ?>
				</div>
			</div>
			<?php
		endif;
		?>
	</div>
</div>
