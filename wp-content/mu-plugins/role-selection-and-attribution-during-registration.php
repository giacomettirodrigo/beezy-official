<?php

add_filter('hivepress/v1/forms/user_register', 'my_custom_add_user_type_field');

function my_custom_add_user_type_field($form) {
    $form['fields'] = array_merge($form['fields'], [
        'user_type' => [
            'type'    => 'select',
            'label'   => 'I want to...',
            'options' => [
                'requestor' => 'Ask for help',
                'bee'       => 'Help'
            ],
            'required' => true,
            '_order'   => 5
        ]
    ]);

    return $form;
}

add_action('hivepress/v1/models/user/register', 'my_custom_assign_user_role', 10, 2);

function my_custom_assign_user_role($user_id, $values) {
    if (isset($values['user_type'])) {
        $user_type = $values['user_type'];

        $user = get_user_by('id', $user_id);

        if ($user && in_array($user_type, ['requestor', 'bee'])) {
            $user->remove_role('subscriber');

            $user->add_role($user_type);
        }
    }
}