<div class="post__details">
	<?php
	if ( is_single() ) :
		get_template_part( 'templates/post/post-author' );
	endif;

	if ( ! is_single() && is_sticky() ) :
		?>
		<div class="post__sticky"><i class="fas fa-thumbtack"></i><span><?php echo esc_html_x( 'Pinned', 'post', 'taskhive' ); ?></span></div>
	<?php elseif ( ! has_post_thumbnail() ) : ?>
		<time datetime="<?php echo esc_attr( get_the_time( 'Y-m-d' ) ); ?>" class="post__date"><?php echo esc_html( get_the_date() ); ?></time>
		<?php
	endif;

	if ( comments_open() && ! post_password_required() ) :
		?>
		<a href="<?php comments_link(); ?>" class="post__comments"><?php comments_number(); ?></a>
		<?php
	endif;

	if ( ! is_single() ) :
		?>
		<a href="<?php the_permalink(); ?>" class="post__readmore"><span><?php esc_html_e( 'Read More', 'taskhive' ); ?></span><i class="fas fa-chevron-right"></i></a>
	<?php endif; ?>
</div>
