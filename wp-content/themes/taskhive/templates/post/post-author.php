<div class="post__author">
	<?php
	echo get_avatar( get_the_author_meta( 'ID' ), 150 );
	the_author_posts_link();
	?>
</div>
