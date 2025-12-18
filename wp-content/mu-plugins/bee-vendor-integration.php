<?php

function bee_create_vendor_if_missing($user_id)
{
    $user = get_userdata($user_id);
    if (!$user) {
        error_log("Bee→Vendor: No user found for ID $user_id");
        return;
    }

    if (!in_array('bee', (array) $user->roles, true)) {
        // error_log("Bee→Vendor: User $user_id is not a Bee (roles: " . implode(',', $user->roles) . ")");
        return;
    }

    $existing = get_posts([
        'post_type' => 'hp_vendor',
        'author' => $user_id,
        'numberposts' => 1,
        'post_status' => 'any',
        'fields' => 'ids'
    ]);

    if ($existing) {
        error_log("Bee→Vendor: Vendor already exists for user $user_id (vendor ID " . $existing[0] . ")");
        return;
    }

    $vendor_id = wp_insert_post([
        'post_type' => 'hp_vendor',
        'post_status' => 'publish',
        'post_title' => $user->display_name ?: $user->user_login,
        'post_author' => $user_id,
    ]);

    if (is_wp_error($vendor_id)) {
        error_log("Bee→Vendor: Failed to create vendor for user $user_id — " . $vendor_id->get_error_message());
    } elseif ($vendor_id) {
        error_log("Bee→Vendor: SUCCESS — Vendor $vendor_id created for user $user_id");
    } else {
        error_log("Bee→Vendor: Unknown error creating vendor for user $user_id");
    }
}