<?php
/**
 * Enqueue parent theme styles.
 */
function taskhive_child_enqueue_styles()
{
	// Enqueue the parent theme's main stylesheet
	wp_enqueue_style('taskhive-style', get_template_directory_uri() . '/style.css');

	// Enqueue the child theme's stylesheet (it depends on the parent theme's style)
	wp_enqueue_style('taskhive-child-style', get_stylesheet_uri(), array('taskhive-style'), wp_get_theme()->get('Version'));
}
add_action('wp_enqueue_scripts', 'taskhive_child_enqueue_styles');


/**
 * Helper to render user meta images as clickable thumbnails.
 */
function render_user_meta_images($user_id, $meta_key)
{
	$output = '';
	$files = get_user_meta($user_id, $meta_key, true);

	// Normalize to array
	if (!is_array($files)) {
		$files = array_filter(array($files)); // Filter empty
	}

	if (empty($files)) {
		return '—';
	}

	foreach ($files as $file_id) {
		$image_url = '';
		$link_url = '';

		if (is_numeric($file_id)) {
			// It's likely an attachment ID
			$img_data = wp_get_attachment_image_src($file_id, 'thumbnail');
			$image_url = $img_data ? $img_data[0] : '';
			$link_url = wp_get_attachment_url($file_id);
		} elseif (filter_var($file_id, FILTER_VALIDATE_URL)) {
			// It's a URL
			$image_url = $file_id; // Use same URL for thumb if it's a direct link
			$link_url = $file_id;
		}

		if ($image_url) {
			$output .= sprintf(
				'<a href="%s" target="_blank" style="display:inline-block; margin-right:5px;"><img src="%s" style="max-width: 50px; height: auto; border: 1px solid #ccc;" /></a>',
				esc_url($link_url),
				esc_url($image_url)
			);
		}
	}
	return $output ? $output : '—';
}

/**
 * Add custom columns to the Users list.
 */
add_filter('manage_users_columns', function ($columns) {
	// Remove columns
	unset($columns['posts']);
	unset($columns['user_jetpack']); // Jetpack connection column

	$columns['proof_identity'] = 'Proof Identity';
	$columns['proof_school'] = 'Proof of School Enrollment';
	$columns['doc_verified'] = 'Verified';
	return $columns;
}, 20);

/**
 * Populate custom column content.
 */
add_filter('manage_users_custom_column', function ($val, $column_name, $user_id) {
	if ('proof_identity' === $column_name) {
		return render_user_meta_images($user_id, 'hp_proof_identity');
	}

	if ('proof_school' === $column_name) {
		return render_user_meta_images($user_id, 'hp_proof_school');
	}

	if ('doc_verified' === $column_name) {
		$verified = get_user_meta($user_id, 'hp_doc_verified', true);
		// Robust check for true/1/yes as strings or booleans
		$is_verified = in_array($verified, [1, '1', true, 'true', 'yes'], true);
		$checked = $is_verified ? 'checked' : '';
		$nonce = wp_create_nonce('verify_user_' . $user_id);
		return sprintf(
			'<input type="checkbox" class="verify-user-checkbox" data-user-id="%d" data-nonce="%s" %s />',
			$user_id,
			$nonce,
			$checked
		);
	}

	return $val;
}, 10, 3);

add_action('admin_footer', function () {
	?>
	<script>
		jQuery(document).ready(function ($) {
			$(document).on('change', '.verify-user-checkbox', function () {
				var checkbox = $(this);
				var userId = checkbox.data('user-id');
				var nonce = checkbox.data('nonce');
				var verified = checkbox.is(':checked') ? 1 : 0;

				checkbox.css('opacity', '0.5');

				$.post(ajaxurl, {
					action: 'toggle_user_verification',
					user_id: userId,
					nonce: nonce,
					verified: verified
				}, function (response) {
					checkbox.css('opacity', '1');
					if (!response.success) {
						checkbox.prop('checked', !verified); // Revert on failure
						alert('Failed to update verification status.');
					}
				});
			});
		});
	</script>
	<?php
});

add_action('wp_ajax_toggle_user_verification', function () {
	$user_id = intval($_POST['user_id']);
	if (!check_ajax_referer('verify_user_' . $user_id, 'nonce', false) || !current_user_can('edit_users')) {
		wp_send_json_error();
	}

	$verified = !empty($_POST['verified']) ? 1 : 0;
	update_user_meta($user_id, 'hp_doc_verified', $verified);
	wp_send_json_success();
});

/**
 * Customize Admin Dashboard.
 */
add_action('wp_dashboard_setup', function () {
	global $wp_meta_boxes;

	// 1. Remove all existing widgets
	$wp_meta_boxes['dashboard'] = [];

	// Specifically target some stubborn widgets if priority isn't enough
	remove_meta_box('wpforms_reports_widget_lite', 'dashboard', 'normal');
	remove_meta_box('duplicator_dashboard_widget', 'dashboard', 'normal');

	// 2. Add custom widgets
	wp_add_dashboard_widget('beezy_unverified_emails', 'Recently Registered (Unverified Email)', 'render_beezy_unverified_emails_widget');
	wp_add_dashboard_widget('beezy_pending_docs', 'Email Verified (Pending Doc Verification)', 'render_beezy_pending_docs_widget');
	wp_add_dashboard_widget('beezy_role_distribution', 'User Roles: Bee vs Requestors', 'render_beezy_role_distribution_widget');
}, 999);

/**
 * Render: Unverified Emails Widget.
 */
function render_beezy_unverified_emails_widget()
{
	$users = get_users([
		'meta_key' => 'hp_email_verify_key',
		'compare' => 'EXISTS',
		'number' => 10,
		'orderby' => 'user_registered',
		'order' => 'DESC',
	]);

	if (empty($users)) {
		echo '<p>No users waiting for email verification.</p>';
		return;
	}

	echo '<ul style="margin:0; padding:0; list-style:none;">';
	foreach ($users as $user) {
		printf(
			'<li style="padding:8px 0; border-bottom:1px solid #eee;"><strong>%s</strong> (%s)<br/><small>Joined: %s</small></li>',
			esc_html($user->display_name),
			esc_html($user->user_email),
			esc_html(date_i18n(get_option('date_format'), strtotime($user->user_registered)))
		);
	}
	echo '</ul>';
	echo '<p><a href="' . admin_url('users.php') . '" class="button">View All Users</a></p>';
}

/**
 * Render: Pending Doc Verification Widget.
 */
function render_beezy_pending_docs_widget()
{
	// Users with verified email (no verify key) BUT doc_verified is not 1
	$users = get_users([
		'meta_query' => [
			'relation' => 'AND',
			[
				'key' => 'hp_email_verify_key',
				'compare' => 'NOT EXISTS',
			],
			[
				'relation' => 'OR',
				[
					'key' => 'hp_doc_verified',
					'compare' => 'NOT EXISTS',
				],
				[
					'key' => 'hp_doc_verified',
					'value' => '1',
					'compare' => '!=',
				],
			],
		],
		'number' => 10,
	]);

	if (empty($users)) {
		echo '<p>No users pending document verification.</p>';
		return;
	}

	echo '<ul style="margin:0; padding:0; list-style:none;">';
	foreach ($users as $user) {
		printf(
			'<li style="padding:8px 0; border-bottom:1px solid #eee;"><strong>%s</strong><br/><a href="%s" class="button button-small" style="margin-top:5px;">Verify Now</a></li>',
			esc_html($user->display_name),
			esc_url(admin_url('users.php?s=' . urlencode($user->user_email)))
		);
	}
	echo '</ul>';
}

/**
 * Render: Role Distribution Widget.
 */
function render_beezy_role_distribution_widget()
{
	$counts = count_users();

	// Role slugs as confirmed by user
	$bee_count = isset($counts['avail_roles']['bee']) ? $counts['avail_roles']['bee'] : 0;
	$requestor_count = isset($counts['avail_roles']['requestor']) ? $counts['avail_roles']['requestor'] : 0;

	?>
	<div style="display:flex; justify-content: space-around; align-items: center; padding: 20px 0; text-align: center;">
		<div style="flex:1;">
			<span style="display:block; font-size: 32px; font-weight: bold; color: #2196F3;">BEE</span>
			<span style="display:block; font-size: 48px; line-height: 1;"><?php echo esc_html($bee_count); ?></span>
		</div>
		<div style="width:1px; height: 60px; background:#ddd;"></div>
		<div style="flex:1;">
			<span style="display:block; font-size: 32px; font-weight: bold; color: #673AB7;">REQUESTORS</span>
			<span style="display:block; font-size: 48px; line-height: 1;"><?php echo esc_html($requestor_count); ?></span>
		</div>
	</div>
	<p style="text-align: center; color: #666; font-style: italic;">Total users:
		<?php echo esc_html($counts['total_users']); ?>
	</p>
	<?php
}

/**
 * Add nice dashboard styles.
 */
add_action('admin_head', function () {
	$screen = get_current_screen();
	if ($screen && $screen->id === 'dashboard') {
		echo '<style>
			#dashboard-widgets .postbox { border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); border: none; }
			#dashboard-widgets .postbox h2 { border-bottom: 1px solid #f0f0f0; font-weight: 700; padding: 12px 15px; }
			.wp-dashboard-widget-content { padding: 15px !important; }
		</style>';
	}
});
