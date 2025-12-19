<?php
require_once('../../../../../wp-load.php');
if (!current_user_can('manage_options')) {
    die('Unauthorized');
}
$roles = get_editable_roles();
foreach ($roles as $slug => $role) {
    echo $slug . ': ' . $role['name'] . "\n";
}
unlink(__FILE__);
