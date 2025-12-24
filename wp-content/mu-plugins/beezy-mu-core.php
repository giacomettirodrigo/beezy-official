<?php
/**
 * Plugin Name: Beezy Core (Consolidated)
 * Description: Unified core logic for Beezy Bees platform. Consolidates multiple tweaks for roles, UI, and guards.
 * Version: 1.0.0
 * Author: Antigravity
 */

if (!defined('ABSPATH'))
    exit;

use HivePress\Helpers as hp;

/**
 * Beezy Core Class
 */
final class Beezy_Core
{

    /**
     * Singleton instance.
     */
    private static $instance = null;

    /**
     * Get singleton instance.
     */
    public static function get_instance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor.
     */
    private function __construct()
    {
        // Module 1: User State & Body Classes
        $this->init_user_state();

        // Module 2: UI & Navigation Tweaks
        $this->init_ui_tweaks();

        // Module 3: Security & Access Guards
        $this->init_guards();

        // Module 4: Form & Model Integrations
        $this->init_integrations();

        // Module 6: WooCommerce / Acceptance Integration
        $this->init_acceptance_integration();

        // Module 7: Messaging Restrictions
        $this->init_messaging_restrictions();

        // Module 5: Admin Tweaks
        $this->init_admin();
    }

    /* ---------------------------------------------------------
       MODULE 1: User State & Body Classes
       --------------------------------------------------------- */
    private function init_user_state()
    {
        add_filter('body_class', [$this, 'add_user_state_body_classes']);
        add_action('wp_footer', [$this, 'render_role_specific_scripts'], 999);
    }

    public function add_user_state_body_classes($classes)
    {
        if (is_user_logged_in()) {
            $user = wp_get_current_user();
            if ($user->roles) {
                $classes[] = 'user-role-' . $user->roles[0];
            }
            if ($this->is_user_verified($user->ID)) {
                $classes[] = 'user-doc-verified';
            }
        }
        return $classes;
    }

    public function render_role_specific_scripts()
    {
        if (!is_user_logged_in())
            return;

        $user = wp_get_current_user();
        if (in_array('requestor', (array) $user->roles)) {
            ?>
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    // Hide school enrollment for requestors (Module 1/2 overlap)
                    const schoolField = document.querySelector('.hp-form__field:has(input[name="proof_school"])');
                    if (schoolField) schoolField.style.display = 'none';
                });
            </script>
            <?php
        }
    }

    /* ---------------------------------------------------------
       MODULE 2: UI & Navigation Tweaks
       --------------------------------------------------------- */
    private function init_ui_tweaks()
    {
        add_action('wp_head', [$this, 'render_global_ui_styles']);
        add_action('wp_footer', [$this, 'render_ui_js_tweaks'], 110);

        // Vendor Profile Page (Centralization)
        add_filter('hivepress/v1/templates/vendor_view_page/blocks', [$this, 'modify_vendor_view_blocks'], 1000, 2);

        // Relocate View Profile link
        add_filter('hivepress/v1/templates/user_edit_settings_page/blocks', [$this, 'remove_vendor_view_link'], 2000, 2);
        add_filter('hivepress/v1/menus/user_account', [$this, 'ensure_messages_menu_item'], 1000);

        // Remove HivePress redirect when messages are empty
        add_filter('hivepress/v1/routes', function ($routes) {
            if (isset($routes['messages_thread_page'])) {
                unset($routes['messages_thread_page']['redirect']);
            }
            if (isset($routes['messages_view_page'])) {
                unset($routes['messages_view_page']['redirect']);
            }
            return $routes;
        }, 2000);
        // Remove Hourly Rate
        add_filter('hivepress/v1/forms/user_update', [$this, 'remove_hourly_rate_field'], 1000);

    }

    public function ensure_messages_menu_item($menu)
    {
        // 1. Handle Messages Link (Prevent Duplicates)
        // Remove any messages_thread_page to prevent duplicates
        if (isset($menu['items']['messages_thread_page'])) {
            unset($menu['items']['messages_thread_page']);
        }

        // Ensure single Messages link exists
        if (!isset($menu['items']['messages'])) {
            $menu['items']['messages'] = [
                'label' => 'Messages',
                'url' => hivepress()->router->get_url('messages_view_page'),
                '_order' => 10,
            ];
        } else {
            $menu['items']['messages']['_order'] = 10;
        }

        // 2. Handle Requests Link & Notification Highlights
        $user_id = get_current_user_id();
        if ($user_id && isset($menu['items']['requests'])) {
            $unread_count = $this->get_unread_offer_count($user_id);
            if ($unread_count > 0) {
                $menu['items']['requests']['label'] .= ' <span class="beezy-menu-badge" style="background:#e53e3e; color:white; font-size:10px; padding:2px 6px; border-radius:10px; vertical-align:middle; margin-left:5px;">' . $unread_count . '</span>';

                // Add a marker to the top-level user menu trigger (via JS mostly, but we can try injecting a script here)
                add_action('wp_footer', function () {
                    echo '<script>jQuery(".hp-menu__item--user-account > a").append("<span class=\"beezy-dot-badge\" style=\"background:#e53e3e; width:8px; height:8px; border-radius:50%; display:inline-block; position:absolute; top:5px; right:5px;\"></span>");</script>';
                }, 999);
            }
        }

        return $menu;
    }

    private function get_unread_offer_count($user_id)
    {
        // Count offers on user's requests that are NOT marked as seen
        // Offers are comments on Requests
        $args = [
            'post_type' => 'hp_request',
            'author' => $user_id,
            'post_status' => 'publish',
            'fields' => 'ids',
            'posts_per_page' => -1
        ];
        $request_ids = get_posts($args);

        if (empty($request_ids))
            return 0;

        $comments = get_comments([
            'post__in' => $request_ids,
            'type' => 'hp_offer', // Assuming 'hp_offer' is the comment type for offers
            'meta_query' => [
                [
                    'key' => '_beezy_offer_seen',
                    'compare' => 'NOT EXISTS' // Unseen
                ]
            ],
            'count' => true
        ]);

        return $comments;
    }

    public function render_global_ui_styles()
    {
        ?>
        <style>
            /* Hide List a Service button for everyone */
            .hp-menu--site-header .hp-menu__item--listing-submit {
                display: none !important;
            }

            /* Centralize Vendor Profile Page Sidebar */
            .hp-vendor--view-page .hp-page__content {
                display: none !important;
            }

            .hp-vendor--view-page .hp-page__sidebar {
                margin: 0 auto !important;
                float: none !important;
                flex: 0 0 100% !important;
                max-width: 500px !important;
            }

            .hp-vendor--view-page .hp-row {
                justify-content: center !important;
            }

            /* Mandatory Docs Alert Styling */
            .beezy-mandatory-alert {
                background: #fff5f5 !important;
                border: 2px solid #feb2b2 !important;
                color: #c53030 !important;
                padding: 15px !important;
                border-radius: 8px !important;
                margin-bottom: 20px !important;
                text-align: center !important;
                font-weight: bold !important;
                font-size: 1.1em !important;
                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05) !important;
                border-left: 5px solid #f56565 !important;
            }

            .beezy-verified-header {
                margin-bottom: 20px !important;
            }

            .beezy-field-error {
                border: 2px solid #feb2b2 !important;
                background: #fffafa !important;
                padding: 15px !important;
                border-radius: 8px !important;
                margin-bottom: 20px !important;
            }

            .beezy-field-error label {
                color: #c53030 !important;
                font-weight: bold !important;
            }

            .beezy-field-success {
                border: 2px solid #c6f6d5 !important;
                background: #f0fff4 !important;
                padding: 15px !important;
                border-radius: 8px !important;
                margin-bottom: 20px !important;
            }

            .beezy-field-success label {
                color: #2f855a !important;
                font-weight: bold !important;
            }

            /* Hide View Profile button from settings footer */
            .hp-form__action--vendor-view {
                display: none !important;
            }

            /* Hide Hourly Rate field from settings */
            .hp-form__field--hourly-rate,
            .hp-form__field--hp-hourly-rate {
                display: none !important;
            }
        </style>
        <?php
    }

    public function render_ui_js_tweaks()
    {
        ?>
        <?php

        if (is_user_logged_in() && current_user_can('bee')) {
            ?>
            <script>
                jQuery(document).ready(function ($) {
                    // Change Post a Request to Current Requests for Bees
                    const $button = $('.hp-menu--site-header .hp-menu__item--request-submit');
                    if ($button.length) {
                        $button.attr('href', '<?php echo esc_url(home_url('/requests/')); ?>');
                        $button.find('span').text('Current Requests');
                    }
                });
            </script>
            <?php
        } elseif (is_user_logged_in() && in_array('requestor', (array) wp_get_current_user()->roles)) {
            ?>
            <script>
                jQuery(document).ready(function ($) {
                    // Change button to "Submit a request" for Requestors
                    const $button = $('.hp-menu--site-header .hp-menu__item--request-submit');
                    if ($button.length) {
                        $button.attr('href', '<?php echo esc_url(home_url('/submit-request/')); ?>');
                        $button.find('span').text('Submit a request');
                    }
                });
            </script>
            <?php
        }

        // Redirect user name click to settings page (for all logged-in users)
        if (is_user_logged_in()) {
            ?>
            <script>
                jQuery(document).ready(function ($) {
                    const updateAccountLink = function () {
                        const settingsUrl = '<?php echo esc_url(home_url('/account/settings/')); ?>';
                        const accountUrl = '<?php echo esc_url(home_url('/account/')); ?>';

                        // Target the specific user account menu item in header and mobile menu
                        $('a.hp-menu__item--user-account, .hp-menu__item--user-account a').each(function () {
                            $(this).attr('href', settingsUrl);
                        });

                        // Fallback: any link that points exactly to /account/
                        $('a[href="' + accountUrl + '"], a[href="' + accountUrl.slice(0, -1) + '"]').each(function () {
                            $(this).attr('href', settingsUrl);
                        });
                    };

                    updateAccountLink();
                    // Handle dynamic updates (e.g. if header is re-rendered via AJAX)
                    if (window.MutationObserver) {
                        new MutationObserver(updateAccountLink).observe(document.body, { childList: true, subtree: true });
                    }

                    // Top Nav Verification Badge
                    const isVerified = <?php echo $this->is_user_verified(get_current_user_id()) ? 'true' : 'false'; ?>;
                    if (isVerified) {
                        const injectNavBadge = function () {
                            const $navUser = $('.hp-menu__item--user-account span, .hp-menu__item--user-login span');
                            if ($navUser.length && !$('.beezy-nav-verified').length) {
                                $navUser.append('<i class="fas fa-check-circle beezy-nav-verified" style="color: #48bb78; margin-left: 5px;" title="Identity Verified"></i>');
                            }
                        };
                        injectNavBadge();
                        if (window.MutationObserver) {
                            new MutationObserver(injectNavBadge).observe(document.body, { childList: true, subtree: true });
                        }
                    }
                });
            </script>
            <?php
        }

        // Expired Requests Styling & Sorting (Logic from requests-panel-styling...)
        if (strpos($_SERVER['REQUEST_URI'], '/requests/') !== false) {
            $this->render_request_countdown_script();
        }

        // Mandatory Docs Highlighting
        if (strpos($_SERVER['REQUEST_URI'], '/account/settings/') !== false) {
            $this->render_mandatory_docs_js();
        }
    }

    /**
     * Consolidate Vendor View modifications: Centralization.
     */
    public function modify_vendor_view_blocks($blocks, $template)
    {
        $blocks = $this->modify_vendor_template_blocks_recursive($blocks);
        return $blocks;
    }

    /**
     * Helper to recursively modify vendor template blocks.
     */
    private function modify_vendor_template_blocks_recursive($blocks)
    {
        foreach ($blocks as $id => &$block) {
            // 1. Centralize: Remove the page content (listings)
            if ($id === 'page_content') {
                $block['blocks'] = [];
            }

            // 2. Centralize: Adjust sidebar classes to allow centering
            if ($id === 'page_sidebar') {
                if (isset($block['attributes']['class']) && is_array($block['attributes']['class'])) {
                    foreach ($block['attributes']['class'] as $key => $class) {
                        if ($class === 'hp-col-sm-4') {
                            $block['attributes']['class'][$key] = 'hp-col-sm-12';
                        }
                    }
                }
            }

            // Recurse
            if (isset($block['blocks']) && is_array($block['blocks'])) {
                $block['blocks'] = $this->modify_vendor_template_blocks_recursive($block['blocks']);
            }
        }
        return $blocks;
    }

    /**
     * Remove the redundant "View Profile" link from the bottom of the settings page.
     */
    public function remove_vendor_view_link($blocks, $template)
    {
        return hp\merge_trees($blocks, [
            'user_update_form' => [
                'footer' => [
                    'form_actions' => [
                        'blocks' => [
                            'vendor_view_link' => [
                                'type' => 'content',
                                'content' => '',
                            ],
                        ],
                    ],
                ],
            ],
        ]);
    }

    /**
     * Add "View Profile" to the user account menu.
     */
    public function add_vendor_view_to_menu($menu)
    {
        if (isset($menu['items']['user_edit_settings'])) {
            $menu['items']['user_edit_settings']['_order'] = 1;
        }

        $user_id = get_current_user_id();
        if ($user_id) {
            $vendor_id = get_posts([
                'post_type' => 'hp_vendor',
                'author' => $user_id,
                'post_status' => 'publish',
                'fields' => 'ids',
                'numberposts' => 1,
            ]);

            if ($vendor_id) {
                $vendor_id = $vendor_id[0];
                $menu['items']['vendor_view'] = [
                    'label' => 'View Profile',
                    'url' => get_permalink($vendor_id),
                    '_order' => 2,
                ];
            }
        }

        return $menu;
    }

    /**
     * Remove the "Hourly rate" field from the user update form.
     */
    public function remove_hourly_rate_field($form)
    {
        unset($form['fields']['hourly_rate']);
        unset($form['fields']['hp_hourly_rate']);
        return $form;
    }

    /**
     * Render the language switcher HTML with UK and NL flags.
     */

    /**
     * Add mandatory documents header to settings page if docs are missing.
     */
    public function add_mandatory_docs_header($blocks, $template)
    {
        if (!is_user_logged_in())
            return $blocks;

        $user_id = get_current_user_id();
        $user = get_userdata($user_id);
        $roles = (array) $user->roles;

        $id_doc = get_user_meta($user_id, 'hp_proof_identity', true);
        $school_doc = get_user_meta($user_id, 'hp_proof_school', true);
        $is_verified = $this->is_user_verified($user_id);

        if ($is_verified) {
            return $blocks;
        }

        $missing_docs = false;
        if (in_array('bee', $roles)) {
            if (!$id_doc || !$school_doc) {
                $missing_docs = true;
            }
        } elseif (in_array('requestor', $roles)) {
            if (!$id_doc) {
                $missing_docs = true;
            }
        }

        if ($missing_docs && isset($blocks['page_content'])) {
            $blocks['page_content']['blocks']['mandatory_docs_notice'] = [
                'type' => 'content',
                'content' => '<div class="beezy-mandatory-alert"><i class="fas fa-exclamation-triangle"></i> Please upload the mandatory documents to complete your registration</div>',
                '_order' => 1,
            ];
        }

        return $blocks;
    }

    /**
     * Render JS to highlight missing mandatory fields.
     */
    private function render_mandatory_docs_js()
    {
        $user_id = get_current_user_id();
        $user = get_userdata($user_id);
        $roles = (array) $user->roles;

        $id_missing = !get_user_meta($user_id, 'hp_proof_identity', true);
        $school_missing = !get_user_meta($user_id, 'hp_proof_school', true);
        $is_verified = $this->is_user_verified($user_id);

        $is_bee = in_array('bee', $roles);
        $is_requestor = in_array('requestor', $roles);

        ?>
        <script>
            jQuery(document).ready(function ($) {
                const isVerified = <?php echo $is_verified ? 'true' : 'false'; ?>;
                const isBee = <?php echo $is_bee ? 'true' : 'false'; ?>;
                const isRequestor = <?php echo $is_requestor ? 'true' : 'false'; ?>;
                const idMissing = <?php echo $id_missing ? 'true' : 'false'; ?>;
                const schoolMissing = <?php echo $school_missing ? 'true' : 'false'; ?>;

                // Removed settings-only verified header injection (now handled globally in nav)

                const $formFields = $('.hp-form--user-update .hp-form__fields');
                const $idField = $('.hp-form__field:has([name="proof_identity"])');
                const $schoolField = $('.hp-form__field:has([name="proof_school"])');

                if ($formFields.length) {
                    $formFields.css({ display: 'flex', flexDirection: 'column' });

                    // Hide (Optional) labels for mandatory fields
                    $idField.find('.hp-field__label small').hide();
                    $schoolField.find('.hp-field__label small').hide();

                    if (isVerified) {
                        // If verified, disable the fields but keep them visible
                        [$idField, $schoolField].forEach($f => {
                            if ($f.length) {
                                $f.css('opacity', '0.7');
                                $f.find('input, button, .hp-field--attachment-upload').css({
                                    'pointer-events': 'none',
                                    'cursor': 'not-allowed'
                                });
                                // Target specifically the select file button if it exists
                                $f.find('.hp-field__button').attr('disabled', 'disabled');
                            }
                        });

                        // Set orders
                        if (isBee) {
                            $idField.css('order', -20);
                            $schoolField.css('order', -15);
                        } else if (isRequestor) {
                            $idField.css('order', -20);
                            $schoolField.hide();
                        }
                        return; // Exit early, no success/error highlights needed
                    }

                    if (isBee) {
                        $idField.css('order', -20);
                        $schoolField.css('order', -15);

                        if (idMissing) {
                            $idField.addClass('beezy-field-error')
                                .append('<div style="color:#c53030; font-size:0.9em; margin-top:5px;">Please upload your proof of identity to verify your account. After uploading don\'t forget to click "Save changes" on the bottom of this page.</div>');
                        } else {
                            $idField.addClass('beezy-field-success')
                                .append('<div style="color:#2f855a; font-size:0.9em; margin-top:5px;">Thanks for sending your proof of identity, it is now being verified. You will receive a confirmation email in 2 working days</div>');
                        }

                        if (schoolMissing) {
                            $schoolField.addClass('beezy-field-error')
                                .append('<div style="color:#c53030; font-size:0.9em; margin-top:5px;">Please upload your proof of school enrollment to complete your profile</div>');
                        } else {
                            $schoolField.addClass('beezy-field-success')
                                .append('<div style="color:#2f855a; font-size:0.9em; margin-top:5px;">Thanks for sending your proof of enrollment, it is now being verified. You will receive a confirmation email in 2 working days</div>');
                        }
                    } else if (isRequestor) {
                        $idField.css('order', -20);
                        $schoolField.hide(); // Requestors don't need school proof

                        if (idMissing) {
                            $idField.addClass('beezy-field-error')
                                .append('<div style="color:#c53030; font-size:0.9em; margin-top:5px;">Please upload your proof of identity to verify your account. After uploading don\'t forget to click "Save changes" on the bottom of this page.</div>');
                        } else {
                            $idField.addClass('beezy-field-success')
                                .append('<div style="color:#2f855a; font-size:0.9em; margin-top:5px;">Thanks for sending your proof of identity, it is now being verified. You will receive a confirmation email in 2 working days</div>');
                        }
                    }
                }
            });
        </script>
        <?php
    }

    private function render_request_countdown_script()
    {
        ?>
        <script type="text/javascript">
            (function ($) {
                function formatRemaining(ms) {
                    if (ms <= 0) return 'EXPIRED';
                    const s = Math.floor(ms / 1000);
                    const d = Math.floor(s / 86400), h = Math.floor((s % 86400) / 3600), m = Math.floor((s % 3600) / 60);
                    return d > 0 ? `${d}d ${h}h ${m}m` : `${h}h ${m}m ${s % 60}s`;
                }
                $(document).ready(function () {
                    const $container = $('.hp-requests .hp-row');
                    const $listings = $('.hp-requests .hp-listing');
                    if (!$container.length || !$listings.length) return;

                    let items = [];
                    let active = [];

                    $listings.each(function () {
                        const $l = $(this);
                        const $time = $l.find('time.hp-listing__task-date');
                        if (!$time.length) return;

                        const taskTime = new Date($time.attr('datetime')).getTime();
                        const now = Date.now();
                        let $label = $l.find('.hp-unified-label');
                        if (!$label.length) {
                            $label = $('<span class="hp-unified-label"></span>').appendTo($l.find('.hp-listing__content'));
                        }

                        if (taskTime < now) {
                            $l.addClass('hp-listing--expired');
                            $label.text('EXPIRED').css({ fontWeight: 'bold', color: '#fff', background: '#ff0000', padding: '2px 6px', fontSize: '0.7em' });
                        } else {
                            $label.css({ fontWeight: 'bold', color: '#28a745', background: '#e2f9e7', border: '1px solid #28a745', padding: '2px 6px', fontSize: '0.7em' });
                            active.push({ el: $label, time: taskTime, l: $l });
                        }
                        items.push({ el: $l.closest('.hp-grid__item'), time: taskTime });
                    });

                    const tick = () => {
                        const now = Date.now();
                        active = active.filter(item => {
                            let dist = item.time - now;
                            if (dist > 0) {
                                item.el.text(formatRemaining(dist));
                                return true;
                            }
                            item.el.text('EXPIRED').css({ color: '#fff', background: '#ff0000', border: 'none' });
                            item.l.addClass('hp-listing--expired');
                            return false;
                        });
                        if (active.length) requestAnimationFrame(tick);
                    };
                    tick();

                    items.sort((a, b) => b.time - a.time);
                    $container.empty().append(items.map(i => i.el));
                });
            })(jQuery);
        </script>
        <?php
    }

    /* ---------------------------------------------------------
       MODULE 3: Security & Access Guards
       --------------------------------------------------------- */
    private function init_guards()
    {
        // Admin Bar Guard (Hide for non-admins)
        add_action('after_setup_theme', function () {
            if (!current_user_can('manage_options')) {
                show_admin_bar(false);
            }
        });

        // T&C Guard
        add_action('wp_head', [$this, 'enforce_terms_modal']);
        add_action('wp_ajax_user_terms_action', [$this, 'handle_terms_ajax']);

        // Verification Guards
        add_action('template_redirect', [$this, 'restrict_unverified_access'], 1);
        add_filter('rest_pre_dispatch', [$this, 'guard_rest_offers'], 10, 3);
        add_filter('hivepress/v1/models/offer/create', [$this, 'guard_model_offers'], 9);
        add_filter('hivepress/v1/models/offer/validate', [$this, 'validate_offer_content'], 10, 2);
        add_filter('hivepress/v1/models/request/validate', [$this, 'validate_request_content'], 10, 2);
        add_action('wp_footer', [$this, 'render_offer_ui_guard'], 999);
        add_action('wp_footer', [$this, 'render_acceptance_ui'], 999);
    }

    public function enforce_terms_modal()
    {
        if (!is_user_logged_in() || is_admin())
            return;

        $user = wp_get_current_user();
        $tc_key = 'terms_accepted_v1';
        if (get_user_meta($user->ID, $tc_key, true) || get_query_var('pagename') === 'terms-and-conditions')
            return;

        $target = in_array('bee', (array) $user->roles) ? '/requests/' : '/submit-request/details/';

        add_action('wp_footer', function () use ($user, $target, $tc_key) {
            $page = get_page_by_path('terms-and-conditions');
            $content = $page ? apply_filters('the_content', $page->post_content) : 'Terms not found.';
            ?>
            <style>
                #beezy-tc-modal {
                    position: fixed;
                    z-index: 99999;
                    left: 0;
                    top: 0;
                    width: 100%;
                    height: 100%;
                    background: rgba(0, 0, 0, 0.8);
                    display: flex;
                    align-items: center;
                    justify-content: center;
                }

                #beezy-tc-content {
                    background: #fff;
                    padding: 20px;
                    width: 90%;
                    max-width: 700px;
                    height: 80vh;
                    display: flex;
                    flex-direction: column;
                    border-radius: 8px;
                }

                #beezy-tc-body {
                    flex: 1;
                    overflow-y: auto;
                    margin: 15px 0;
                    border: 1px solid #eee;
                    padding: 15px;
                }

                #beezy-tc-footer {
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                }

                button:disabled {
                    opacity: 0.5;
                    cursor: not-allowed;
                }
            </style>
            <div id="beezy-tc-modal">
                <div id="beezy-tc-content">
                    <h2>Mandatory Terms and Conditions</h2>
                    <div id="beezy-tc-body"><?php echo $content; ?></div>
                    <div id="beezy-tc-footer">
                        <button id="beezy-tc-decline" class="button">Decline & Logout</button>
                        <span id="beezy-tc-prompt">Scroll to the bottom to agree.</span>
                        <button id="beezy-tc-agree" class="button button--primary" disabled>Agree & Continue</button>
                    </div>
                </div>
            </div>
            <script>
                jQuery(function ($) {
                    const $m = $('#beezy-tc-modal'), $b = $('#beezy-tc-body'), $a = $('#beezy-tc-agree'), $d = $('#beezy-tc-decline');
                    $b.on('scroll', function () {
                        if (this.scrollHeight - this.scrollTop <= this.clientHeight + 20) {
                            $a.prop('disabled', false);
                            $('#beezy-tc-prompt').text('Thank you for reading.');
                        }
                    }).trigger('scroll');
                    $a.on('click', function () {
                        $a.prop('disabled', true).text('Saving...');
                        $.post('<?php echo admin_url('admin-ajax.php'); ?>', {
                            action: 'user_terms_action',
                            user_id: <?php echo $user->ID; ?>,
                            type: 'accept',
                            nonce: '<?php echo wp_create_nonce('terms_nonce'); ?>'
                        }, res => res.success && (window.location.href = '<?php echo home_url($target); ?>'));
                    });
                    $d.on('click', function () {
                        if (confirm("Decline and logout?")) {
                            $.post('<?php echo admin_url('admin-ajax.php'); ?>', {
                                action: 'user_terms_action',
                                user_id: <?php echo $user->ID; ?>,
                                type: 'decline',
                                nonce: '<?php echo wp_create_nonce('terms_nonce'); ?>'
                            }, () => window.location.href = '<?php echo home_url(); ?>');
                        }
                    });
                });
            </script>
            <?php
        }, 99);
    }

    public function handle_terms_ajax()
    {
        check_ajax_referer('terms_nonce', 'nonce');
        $uid = (int) $_POST['user_id'];
        if (get_current_user_id() !== $uid)
            wp_send_json_error();

        if ($_POST['type'] === 'accept') {
            update_user_meta($uid, 'terms_accepted_v1', time());
            wp_send_json_success();
        } else {
            wp_logout();
            wp_send_json_success();
        }
    }

    public function restrict_unverified_access()
    {
        if (!is_user_logged_in())
            return;

        $user = wp_get_current_user();
        if (in_array('bee', (array) $user->roles) && strpos($_SERVER['REQUEST_URI'], '/submit-request/') !== false) {
            wp_safe_redirect(home_url('/'));
            exit;
        }

        if (in_array('requestor', (array) $user->roles) && untrailingslashit(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)) === '/submit-request/details') {
            if (!get_user_meta($user->ID, 'hp_doc_verified', true)) {
                $this->render_verification_required_page();
            }
        }

        // Redirect /account/ to /account/settings/ (Requested by user)
        if (untrailingslashit(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)) === '/account') {
            wp_safe_redirect(home_url('/account/settings/'));
            exit;
        }
    }

    private function render_verification_required_page()
    {
        get_header();
        ?>
        <div style="text-align:center; max-width:600px; margin:50px auto;">
            <h1>Identity Verification Required</h1>
            <p>Please visit your <a href="<?php echo home_url('/account/settings/'); ?>"><b>account settings</b></a> to upload
                proof of identity.</p>
        </div>
        <?php
        get_footer();
        exit;
    }

    public function guard_rest_offers($result, $server, $request)
    {
        if ($request->get_method() !== 'POST' || strpos($request->get_route(), '/hivepress/v1/offers') === false)
            return $result;
        if (!is_user_logged_in())
            return new WP_Error('login_required', 'Login required', ['status' => 401]);

        $user = wp_get_current_user();
        if (in_array('bee', (array) $user->roles) && !get_user_meta($user->ID, 'hp_doc_verified', true)) {
            return new WP_Error('verification_required', 'Verify identity first', ['status' => 403]);
        }
        return $result;
    }

    public function guard_model_offers($args)
    {
        if (!is_user_logged_in())
            return $args;
        $user = wp_get_current_user();
        if (in_array('bee', (array) $user->roles) && !get_user_meta($user->ID, 'hp_doc_verified', true)) {
            return new WP_Error('verification_required', 'Verify identity first');
        }
        return $args;
    }

    public function render_offer_ui_guard()
    {
        if (!is_user_logged_in())
            return;

        $user = wp_get_current_user();
        $is_bee = in_array('bee', (array) $user->roles);
        $is_verified = get_user_meta($user->ID, 'hp_doc_verified', true);

        // Guard 1: Verification restriction for Bees
        if ($is_bee && !$is_verified) {
            ?>
            <script>
                (function ($) {
                    function apply() {
                        $('a[href^="#offer_make_modal"], .hp-listing__action--offer').each(function () {
                            const $t = $(this);
                            if ($t.data('bee-guarded')) return;
                            $t.data('bee-guarded', 1);
                            $('<button class="button button--warning" style="width:100%"><i class="fas fa-user-shield"></i> Verify ID to offer</button>')
                                .on('click', () => window.location.href = '<?php echo home_url('/account/settings/'); ?>')
                                .insertAfter($t.hide());
                        });
                    }
                    new MutationObserver(apply).observe(document.body, { childList: true, subtree: true });
                    apply();
                })(jQuery);
            </script>
            <?php
        }

        // Guard 2: Content Moderation for Bidding Modal
        if ($is_bee) {
            ?>
            <script>
                jQuery(document).ready(function ($) {
                    const forbidden = <?php echo json_encode($this->get_forbidden_keywords()); ?>;
                    const addressPatterns = ['straat', 'weg', 'laan', 'plein', 'gracht', 'dijk', 'kade', 'singel', 'plantsoen', 'street', 'road', 'avenue', 'drive', 'lane', 'court', 'boulevard', 'square', 'postcode'];

                    const checkContent = function (text) {
                        const lowText = text.toLowerCase();
                        let errors = [];
                        if (forbidden.some(word => new RegExp('\\b' + word + '\\b').test(lowText)) || /(.)\1{4,}/.test(lowText)) errors.push("Offensive, harmful or spammy language is prohibited.");
                        if (/[a-zA-Z0-9._%+-]+@([a-zA-Z0-9.-]+\.[a-zA-Z]{2,})/.test(text) || /(\+?[0-9][0-9\s\-\(\)]{8,})/.test(text)) errors.push("Sharing contact details (email/phone) is not allowed.");
                        if (addressPatterns.some(word => new RegExp('\\b' + word + '\\b').test(lowText)) || /\b[1-9][0-9]{3}\s?[a-zA-Z]{2}\b/.test(text)) errors.push("Mentioning addresses or location details is prohibited.");
                        return errors;
                    };

                    $(document).on('submit', 'form.hp-form--offer-make', function (e) {
                        const $form = $(this);
                        const data = {};
                        $form.serializeArray().forEach(item => data[item.name] = item.value);
                        const errors = checkContent(data.text || '');

                        if (errors.length > 0) {
                            e.preventDefault();
                            e.stopImmediatePropagation();
                            $form.find('.beezy-mod-error').remove();
                            const html = `<div class="beezy-mod-error" style="background: #fff5f5; border: 1px solid #c53030; color: #c53030; padding: 12px; border-radius: 4px; margin-bottom: 15px; font-size: 0.9em;">
                                <strong style="display:block; margin-bottom:5px;"><i class="fas fa-exclamation-circle"></i> Content Policy Violation</strong>
                                <ul style="margin:0; padding-left:20px;">${errors.map(err => `<li>${err}</li>`).join('')}</ul>
                            </div>`;
                            $form.find('.hp-form__fields').prepend(html);
                            $form[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
                            return false;
                        }
                    });
                });
            </script>
            <?php
        }
    }

    /**
     * Backend validation for offers.
     */
    public function validate_offer_content($errors, $model)
    {
        $text = $model->get_text();
        if ($text && !empty($this->check_for_red_flags($text))) {
            $errors[] = "Your offer contains restricted content (offensive language or personal details). Please revise your message.";
        }
        return $errors;
    }

    /**
     * Backend validation for requests.
     */
    public function validate_request_content($errors, $model)
    {
        $content = $model->get_title() . ' ' . $model->get_description();
        if ($content && !empty($this->check_for_red_flags($content))) {
            $errors[] = "Your request contains restricted content (offensive language or personal details). Please revise your title and description.";
        }
        return $errors;
    }

    /* ---------------------------------------------------------
       MODULE 4: Form & Model Integrations
       --------------------------------------------------------- */
    private function init_integrations()
    {
        // Registration
        add_filter('hivepress/v1/forms/user_register', [$this, 'add_registration_role_field']);
        add_action('hivepress/v1/models/user/register', [$this, 'assign_registration_role'], 10, 2);

        // Vendor Creation
        add_action('user_register', [$this, 'ensure_bee_vendor'], 20);
        add_action('wp_login', function ($login, $user) {
            $this->ensure_bee_vendor($user->ID);
        }, 20, 2);

        // Request Form Tweaks
        add_action('wp_footer', [$this, 'apply_request_form_js_tweaks']);

        // Content Moderation
        add_filter('hivepress/v1/models/request/validate', [$this, 'validate_request_content'], 10, 2);
        add_filter('hivepress/v1/models/offer/validate', [$this, 'validate_offer_content'], 10, 2);

        // Disable automatic product conversion for requests
        add_action('init', function () {
            if (function_exists('hivepress')) {
                remove_action('hivepress/v1/models/request/create', [hivepress()->offer, 'update_request'], 10);
                remove_action('hivepress/v1/models/request/update', [hivepress()->offer, 'update_request'], 10);
            }
        }, 20);
    }

    /* ---------------------------------------------------------
       MODULE 6: WooCommerce / Acceptance Integration
       --------------------------------------------------------- */
    private function init_acceptance_integration()
    {
        // Override HivePress offer acceptance route
        add_filter('hivepress/v1/routes', [$this, 'override_offer_accept_route'], 100);

        // Handle order status updates
        add_action('woocommerce_order_status_changed', [$this, 'handle_beezy_order_status_change'], 20, 4);
    }

    public function render_acceptance_ui()
    {
        if (!is_user_logged_in())
            return;
        ?>
        <style>
            #beezy-accept-modal-overlay {
                position: fixed;
                z-index: 999999;
                left: 0;
                top: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.6);
                backdrop-filter: blur(4px);
                display: none;
                align-items: center;
                justify-content: center;
                opacity: 0;
                transition: opacity 0.3s ease;
            }

            #beezy-accept-modal {
                background: #fff;
                border-radius: 16px;
                width: 90%;
                max-width: 450px;
                padding: 32px;
                box-shadow: 0 20px 50px rgba(0, 0, 0, 0.3);
                transform: translateY(20px);
                transition: transform 0.3s ease;
                text-align: center;
            }

            #beezy-accept-modal.show {
                transform: translateY(0);
            }

            body.beezy-modal-open {
                overflow: hidden;
            }

            .beezy-accept-title {
                font-size: 24px;
                font-weight: 700;
                color: #1a1a1a;
                margin-bottom: 16px;
            }

            .beezy-accept-text {
                font-size: 16px;
                line-height: 1.6;
                color: #555;
                margin-bottom: 24px;
            }

            .beezy-accept-btn-confirm {
                background: #ffcc00;
                color: #000;
                padding: 12px 24px;
                border-radius: 8px;
                font-weight: 700;
                border: none;
                cursor: pointer;
                display: inline-block;
                width: 100%;
                margin-bottom: 12px;
                font-size: 16px;
                transition: background 0.2s;
            }

            .beezy-accept-btn-confirm:hover {
                background: #e6b800;
            }

            .beezy-accept-btn-cancel {
                background: transparent;
                color: #888;
                border: none;
                cursor: pointer;
                font-size: 14px;
                text-decoration: underline;
            }
        </style>
        <div id="beezy-accept-modal-overlay">
            <div id="beezy-accept-modal">
                <div class="beezy-accept-title">Accept this offer?</div>
                <div class="beezy-accept-text">
                    By accepting, you will be redirected to the payment page to cover the platform service fee.
                    Once paid, you'll be able to message the Bee to align on the execution of the requested service.
                </div>
                <button id="beezy-accept-confirm" class="beezy-accept-btn-confirm">Yes, Proceed to Payment</button>
                <button id="beezy-accept-cancel" class="beezy-accept-btn-cancel">Cancel</button>
            </div>
        </div>
        <script>
            jQuery(document).ready(function ($) {
                let pendingUrl = '';
                $(document).on('click', 'a[href*="/accept-offer/"]', function (e) {
                    const $t = $(this);
                    if ($t.data('beezy-confirmed')) return true;

                    e.preventDefault();
                    pendingUrl = $t.attr('href');

                    $('#beezy-accept-modal-overlay').css('display', 'flex').hide().fadeIn(200, function () {
                        $(this).css('opacity', '1');
                        $('#beezy-accept-modal').addClass('show');
                        $('body').addClass('beezy-modal-open');
                    });
                });

                $('#beezy-accept-confirm').on('click', function () {
                    if (pendingUrl) {
                        window.location.href = pendingUrl;
                    }
                });

                $('#beezy-accept-cancel, #beezy-accept-modal-overlay').on('click', function (e) {
                    if (e.target !== this && this.id !== 'beezy-accept-cancel') return;
                    $('#beezy-accept-modal').removeClass('show');
                    $('#beezy-accept-modal-overlay').fadeOut(200, function () {
                        $(this).css('opacity', '0');
                        $('body').removeClass('beezy-modal-open');
                    });
                });
            });
        </script>
        <?php
    }

    public function override_offer_accept_route($routes)
    {
        if (isset($routes['offer_accept_page'])) {
            $routes['offer_accept_page']['redirect'] = [
                [
                    'callback' => [$this, 'handle_offer_acceptance_override'],
                    '_order' => 1,
                ]
            ];
        }
        return $routes;
    }

    /**
     * Custom handler for offer acceptance that creates a manual WC order.
     */
    public function handle_offer_acceptance_override()
    {
        if (!function_exists('hivepress') || !class_exists('WooCommerce')) {
            return null;
        }

        $offer_id = hivepress()->request->get_param('offer_id');
        if (!$offer_id) {
            return null;
        }

        $offer = \HivePress\Models\Offer::query()->get_by_id($offer_id);
        if (!$offer || !$offer->is_approved()) {
            return null;
        }

        $request = $offer->get_request();
        if (!$request) {
            return null;
        }

        // Security check: Only the requestor can accept the offer
        if (get_current_user_id() !== $request->get_user__id()) {
            return null;
        }

        $placeholder_product_id = 318; // Fixed Platform Service Fee Product
        $product = wc_get_product($placeholder_product_id);

        if (!$product) {
            wp_die('Beezy Error: Placeholder product 318 not found. Please contact admin.');
        }

        // The Payment amount is ONLY the fixed platform fee (Product 318)
        $total_price = (float) $product->get_price();

        // Create the WooCommerce order
        $order = wc_create_order();

        // Add the product at its fixed price
        $item_id = $order->add_product($product, 1, [
            'subtotal' => $total_price,
            'total' => $total_price,
        ]);

        // Link the vendor (Bee) for HivePress messaging and tracking
        $vendor_id = \HivePress\Models\Vendor::query()->filter([
            'user' => $offer->get_bidder__id(),
        ])->get_first_id();

        if ($vendor_id) {
            $order->update_meta_data('hp_vendor', $vendor_id);

            // "No Commission": Platform keeps 100% of this fee.
            // Vendor cut is 0% of the site payment (as they are paid offline).
            $order->update_meta_data('hp_commission_rate', 0);
            $order->update_meta_data('hp_commission_fee', 0);
        }

        // Custom Beezy Meta
        $order->update_meta_data('hp_offer', $offer_id);
        $order->update_meta_data('hp_request', $request->get_id());
        $order->update_meta_data('_beezy_bid_amount', (float) $offer->get_price());
        $order->update_meta_data('_beezy_order_type', 'platform_fee_acceptance');

        $order->set_customer_id(get_current_user_id());
        $order->calculate_totals();
        $order->save();

        // Empty the cart to ensure only this payment goes through
        if (WC()->cart) {
            WC()->cart->empty_cart();
        }

        // Return the checkout payment URL
        return $order->get_checkout_payment_url();
    }

    /**
     * Handle updating Request status when an order is paid.
     */
    public function handle_beezy_order_status_change($order_id, $old_status, $new_status, $order)
    {
        if (!is_object($order) || !method_exists($order, 'get_meta')) {
            return;
        }

        // Auto-complete Beezy orders
        if ($new_status === 'processing' && $order->get_meta('_beezy_order_type') === 'platform_fee_acceptance') {
            $order->update_status('completed', 'Beezy Auto-complete for platform fee.');
            return;
        }

        $request_id = $order->get_meta('hp_request');
        $offer_id = $order->get_meta('hp_offer'); // Get offer ID from order meta
        if (!$request_id) {
            return;
        }

        // If order moved to a paid/accepted status
        if (in_array($new_status, ['processing', 'completed', 'on-hold'], true)) {
            $request = \HivePress\Models\Request::query()->get_by_id($request_id);
            if ($request) {
                $request->set_status('private')->save_status();
            }

            if ($offer_id) {
                update_comment_meta($offer_id, 'hp_accepted', 1);

                // Send Automated Greeting Message
                $this->send_beezy_welcome_message($order, $request_id, $offer_id);
            }
        }

    }

    private function send_beezy_welcome_message($order, $request_id, $offer_id)
    {
        $buyer_id = $order->get_customer_id();
        $vendor_post_id = $order->get_meta('hp_vendor');
        $vendor_user_id = $vendor_post_id ? (int) get_post_field('post_author', $vendor_post_id) : 0;

        if (!$buyer_id || !$vendor_user_id)
            return;

        $buyer = get_userdata($buyer_id);
        $vendor = get_userdata($vendor_user_id);

        $message_text = "Hello! This is an automated message to confirm the service acceptance.\n\n" .
            "To the Bee: Congratulations, you won the offer!\n" .
            "To the Requestor: Please get in touch with the Bee to align on the execution of the requested service.";

        $message = new \HivePress\Models\Message();
        $message->fill([
            'sender' => $buyer_id,
            'sender__display_name' => $buyer ? $buyer->display_name : 'System',
            'sender__email' => $buyer ? $buyer->user_email : '',
            'recipient' => $vendor_user_id,
            'text' => $message_text,
            'request' => $request_id,
            'read' => 0,
        ]);

        $message->save();
    }

    /* ---------------------------------------------------------
       MODULE 7: Messaging Restrictions
       --------------------------------------------------------- */
    private function init_messaging_restrictions()
    {
        // Backend Validation
        add_filter('hivepress/v1/models/message/errors', [$this, 'validate_message_window'], 100, 2);

        // Template/Block Guards (UI)
        $templates = [
            'listing_view_block',
            'listing_view_page',
            'listings_view_page',
            'vendor_view_block',
            'vendor_view_page',
            'vendors_view_page',
            'user_view_block',
            'user_view_page',
            'messages_view_page',
            'message_view_page',
            'order_footer_block'
        ];

        foreach ($templates as $tpl) {
            add_filter("hivepress/v1/templates/{$tpl}", [$this, 'guard_messaging_ui'], 1000);
        }

        // Add Empty Messages Placeholder to proper templates
        add_filter('hivepress/v1/templates/messages_thread_page', [$this, 'add_empty_messages_placeholder'], 2000);
        add_filter('hivepress/v1/templates/messages_view_page', [$this, 'add_empty_messages_placeholder'], 2000);

        // Track Offer Views
        add_action('wp_insert_comment', [$this, 'mark_new_offer_unseen'], 10, 2);
        add_action('template_redirect', [$this, 'mark_request_offers_seen']);
        add_filter('hivepress/v1/templates/request_view_block', [$this, 'highlight_unseen_request_card'], 1000, 2);
    }

    public function mark_new_offer_unseen($id, $comment)
    {
        if ($comment->comment_type === 'hp_offer') {
            // It is unseen by default (meta not exists), but we can confirm or just leave it.
            // No action needed if we use 'NOT EXISTS' strategy. 
        }
    }

    public function mark_request_offers_seen()
    {
        if (is_singular('hp_request')) {
            $post = get_post();
            if ($post && $post->post_author == get_current_user_id()) {
                // User is viewing their own request
                $comments = get_comments([
                    'post_id' => $post->ID,
                    'type' => 'hp_offer',
                    'meta_query' => [
                        [
                            'key' => '_beezy_offer_seen',
                            'compare' => 'NOT EXISTS'
                        ]
                    ]
                ]);

                foreach ($comments as $comment) {
                    update_comment_meta($comment->comment_ID, '_beezy_offer_seen', time());
                }
            }
        }
    }

    public function highlight_unseen_request_card($template, $model)
    {
        // Safety checks to prevent fatal errors
        if (!is_object($template) || !is_object($model)) {
            return $template;
        }

        if (!method_exists($model, 'get_user__id') || !method_exists($model, 'get_id')) {
            return $template;
        }

        $user_id = get_current_user_id();
        if (!$user_id || $model->get_user__id() !== $user_id) {
            return $template;
        }

        // Check for unseen offers on this specific request
        $unseen_count = get_comments([
            'post_id' => $model->get_id(),
            'type' => 'hp_offer',
            'meta_query' => [['key' => '_beezy_offer_seen', 'compare' => 'NOT EXISTS']],
            'count' => true
        ]);

        if ($unseen_count > 0) {
            $template->merge_blocks([
                'request_content' => [
                    'blocks' => [
                        'beezy_new_offer_badge' => [
                            'type' => 'content',
                            'content' => '<div style="background:#e53e3e; color:white; font-size:11px; font-weight:bold; padding:4px 8px; border-radius:4px; display:inline-block; margin-bottom:10px;"><i class="fas fa-bell"></i> New Offer!</div>',
                            '_order' => 1
                        ]
                    ]
                ]
            ]);
        }

        return $template;
    }

    public function add_empty_messages_placeholder($template)
    {
        if (!is_object($template))
            return $template;

        $user_id = get_current_user_id();
        if (!$user_id)
            return $template;

        // Safety check to avoid critical errors if models are missing
        if (!class_exists('\HivePress\Models\Message'))
            return $template;

        $count = \HivePress\Models\Message::query()->filter([
            'sender|recipient' => $user_id,
        ])->get_count();

        if ($count === 0) {
            // Determine which block to inject into based on template
            $context = hivepress()->router->get_current_route_name();
            // Default safe injection
            $block_name = 'page_content';

            $html = '<div class="hp-message hp-message--notice" style="background:#ebf8ff; border:1px dashed #4299e1; padding:30px; border-radius:12px; text-align:center; margin-bottom:30px;">' .
                '<h3 style="color:#2b6cb0; margin-top:0;">No Messages Yet</h3>' .
                '<p style="color:#4a5568; font-size:1.1em;">To send and receive messages you have to accept an offer or win one. Then the messaging feature will be enabled.</p>' .
                '</div>';

            $template->merge_blocks([
                $block_name => [
                    'blocks' => [
                        'beezy_empty_placeholder' => [
                            'type' => 'content',
                            'content' => $html,
                            '_order' => 1
                        ]
                    ]
                ],
                'message_list' => ['type' => 'content', 'content' => ''], // Hide list if present
            ]);
        }
        return $template;
    }

    public function render_messaging_css_guard()
    {
        if (current_user_can('manage_options'))
            return;
        ?>
        <style id="beezy-messaging-lockdown">
            /* Hide messaging icons/buttons globally by default in lists/cards */
            /* We only show them via template logic if allowed */
            .hp-listing__action--message,
            .hp-vendor__action--message,
            .hp-user__action--message,
            .hp-link--message,
            .hp-button--message,
            .hp-message-send-link {
                display: none !important;
            }
        </style>
        <?php
    }

    public function filter_message_blocks($block, $component)
    {
        if (current_user_can('manage_options') || !is_object($component) || !method_exists($component, 'get_context')) {
            return $block;
        }

        $recipient_user_id = 0;
        $context = $component->get_context();

        if (isset($context['listing']) && is_object($context['listing']) && method_exists($context['listing'], 'get_user__id')) {
            $recipient_user_id = $context['listing']->get_user__id();
        } elseif (isset($context['vendor']) && is_object($context['vendor']) && method_exists($context['vendor'], 'get_user__id')) {
            $recipient_user_id = $context['vendor']->get_user__id();
        } elseif (isset($context['user']) && is_object($context['user']) && method_exists($context['user'], 'get_id')) {
            $recipient_user_id = $context['user']->get_id();
        } elseif (isset($context['request']) && is_object($context['request']) && method_exists($context['request'], 'get_user__id')) {
            $recipient_user_id = $context['request']->get_user__id();
        } elseif (isset($context['recipient']) && is_object($context['recipient']) && method_exists($context['recipient'], 'get_id')) {
            $recipient_user_id = $context['recipient']->get_id();
        }

        if ($recipient_user_id && $recipient_user_id !== get_current_user_id()) {
            if (!$this->can_users_communicate(get_current_user_id(), $recipient_user_id)) {
                return null;
            }
        }
        return $block;
    }

    private function can_users_communicate($user_a_id, $user_b_id)
    {
        if (current_user_can('manage_options')) {
            return true;
        }

        if (!$user_a_id || !$user_b_id || $user_a_id === $user_b_id || !function_exists('wc_get_orders')) {
            return false;
        }

        // Search for orders where User A is customer
        $orders = wc_get_orders([
            'customer' => $user_a_id,
            'status' => ['completed', 'processing', 'on-hold'], // Added on-hold/processing for safety
            'limit' => -1,
            'meta_key' => '_beezy_order_type',
            'meta_value' => 'platform_fee_acceptance',
        ]);

        // Also search where User B is customer (to cover both directions)
        $orders_b = wc_get_orders([
            'customer' => $user_b_id,
            'status' => ['completed', 'processing', 'on-hold'],
            'limit' => -1,
            'meta_key' => '_beezy_order_type',
            'meta_value' => 'platform_fee_acceptance',
        ]);

        $all_orders = array_merge($orders, $orders_b);
        $now = time();

        foreach ($all_orders as $order) {
            $customer_id = (int) $order->get_customer_id();
            $vendor_post_id = (int) $order->get_meta('hp_vendor');
            $vendor_user_id = $vendor_post_id ? (int) get_post_field('post_author', $vendor_post_id) : 0;

            if (!$vendor_user_id)
                continue;

            // Check if these two users are the ones in this order
            $involved = ($customer_id === (int) $user_a_id && $vendor_user_id === (int) $user_b_id) ||
                ($customer_id === (int) $user_b_id && $vendor_user_id === (int) $user_a_id);

            if ($involved) {
                // Must be before Task Date
                $request_id = $order->get_meta('hp_request');
                if ($request_id) {
                    $task_date = get_post_meta($request_id, 'hp_task_date', true);

                    // Debug Log
                    // error_log("Beezy Check: Involved Users A:$user_a_id B:$user_b_id for Order {$order->get_id()}. Task Date: $task_date");

                    if ($task_date) {
                        $expiry = strtotime($task_date);
                        // Extend expiry to end of day just in case
                        if ($expiry && $now <= ($expiry + 86400)) {
                            return true;
                        }
                    } else {
                        // If no task date is set but order is completed, allow.
                        return true;
                    }
                }
            }
        }
        return false;
    }

    public function validate_message_window($errors, $message)
    {
        if (current_user_can('manage_options'))
            return $errors;
        if (!is_object($message) || !method_exists($message, 'get_sender__id'))
            return $errors;

        if (!$this->can_users_communicate($message->get_sender__id(), $message->get_recipient__id())) {
            $errors[] = "Communication is only allowed for the specific Requestor and Bee of a paid service, until the task date.";
        }
        return $errors;
    }

    public function guard_messaging_ui($template)
    {
        // Allow admins full access to all messaging features
        if (current_user_can('manage_options')) {
            return $template;
        }
        // Original check for object and method existence
        if (!is_object($template) || !method_exists($template, 'get_context')) {
            return $template;
        }

        $recipient_user_id = 0;
        $context = $template->get_context();

        // Find recipient in various template contexts
        if (isset($context['listing']) && is_object($context['listing']) && method_exists($context['listing'], 'get_user__id')) {
            $recipient_user_id = $context['listing']->get_user__id();
        } elseif (isset($context['vendor']) && is_object($context['vendor']) && method_exists($context['vendor'], 'get_user__id')) {
            $recipient_user_id = $context['vendor']->get_user__id();
        } elseif (isset($context['user']) && is_object($context['user']) && method_exists($context['user'], 'get_id')) {
            $recipient_user_id = $context['user']->get_id();
        } elseif (isset($context['request']) && is_object($context['request']) && method_exists($context['request'], 'get_user__id')) {
            $recipient_user_id = $context['request']->get_user__id();
        } elseif (isset($context['recipient']) && is_object($context['recipient']) && method_exists($context['recipient'], 'get_id')) {
            $recipient_user_id = $context['recipient']->get_id();
        } elseif (isset($context['order']) && is_object($context['order']) && method_exists($context['order'], 'get_customer_id')) {
            $order = $context['order'];
            $buyer_id = $order->get_customer_id();
            $vendor_id = $order->get_meta('hp_vendor');
            $seller_id = $vendor_id ? get_post_field('post_author', $vendor_id) : 0;
            $recipient_user_id = (get_current_user_id() === (int) $buyer_id) ? $seller_id : $buyer_id;
        }

        if ($recipient_user_id && $recipient_user_id !== get_current_user_id()) {
            if (!$this->can_users_communicate(get_current_user_id(), $recipient_user_id)) {
                // Use merge_blocks for reliable removal
                $template->merge_blocks([
                    'message_send_modal' => ['type' => 'content', 'content' => ''],
                    'message_send_link' => ['type' => 'content', 'content' => ''],
                    'message_send_form' => ['type' => 'content', 'content' => ''],
                ]);
            } else {
                // Force show them if allowed (override CSS)
                $template->merge_blocks([
                    'beezy_messaging_unlock' => [
                        'type' => 'content',
                        'content' => '<style>.hp-listing__action--message, .hp-vendor__action--message, .hp-user__action--message, .hp-link--message, .hp-button--message, .hp-message-send-link { display: inline-block !important; border: 1px solid transparent; }</style>',
                        '_order' => 1
                    ]
                ]);
            }
        }
        return $template;
    }

    public function add_registration_role_field($form)
    {
        $form['fields']['user_type'] = [
            'type' => 'select',
            'label' => 'I want to...',
            'options' => ['requestor' => 'Ask for help', 'bee' => 'Help'],
            'required' => true,
            '_order' => 5
        ];
        return $form;
    }

    public function assign_registration_role($user_id, $values)
    {
        if (isset($values['user_type'])) {
            $user = get_userdata($user_id);
            if ($user && in_array($values['user_type'], ['requestor', 'bee'])) {
                $user->remove_role('subscriber');
                $user->add_role($values['user_type']);
            }
        }
    }

    public function ensure_bee_vendor($user_id)
    {
        $user = get_userdata($user_id);
        if (!$user || !in_array('bee', (array) $user->roles))
            return;

        $existing = get_posts(['post_type' => 'hp_vendor', 'author' => $user_id, 'post_status' => 'any', 'fields' => 'ids']);
        if ($existing)
            return;

        wp_insert_post([
            'post_type' => 'hp_vendor',
            'post_status' => 'publish',
            'post_title' => $user->display_name ?: $user->user_login,
            'post_author' => $user_id,
        ]);
    }

    public function apply_request_form_js_tweaks()
    {
        ?>
        <script>
            (function ($) {
                // Centralized moderation keywords for JS
                const forbidden = <?php echo json_encode($this->get_forbidden_keywords()); ?>;
                const addressPatterns = ['straat', 'weg', 'laan', 'plein', 'gracht', 'dijk', 'kade', 'singel', 'plantsoen', 'street', 'road', 'avenue', 'drive', 'lane', 'court', 'boulevard', 'square', 'postcode', 'postal code', 'zip code', 'woonachtig', 'wonen in'];

                function checkContent(text) {
                    const lowText = text.toLowerCase();
                    let errors = [];

                    // Harmful content
                    if (forbidden.some(word => new RegExp('\\b' + word + '\\b').test(lowText)) || /(.)\1{4,}/.test(lowText)) {
                        errors.push("Offensive, harmful or spammy language is prohibited.");
                    }
                    // Highly offensive roots
                    const roots = ['fuck', 'nigger', 'faggot', 'kanker', 'neuken'];
                    if (roots.some(root => lowText.includes(root))) {
                        errors.push("Highly offensive term detected.");
                    }

                    // Contact details
                    if (/[a-zA-Z0-9._%+-]+@([a-zA-Z0-9.-]+\.[a-zA-Z]{2,})/.test(text) || /(\+?[0-9][0-9\s\-\(\)]{8,})/.test(text)) {
                        errors.push("Sharing contact details (email/phone) is not allowed.");
                    }

                    // Address details
                    if (addressPatterns.some(word => new RegExp('\\b' + word + '\\b').test(lowText)) || /\b[1-9][0-9]{3}\s?[a-zA-Z]{2}\b/.test(text)) {
                        errors.push("Mentioning addresses or location details is prohibited.");
                    }
                    return [...new Set(errors)]; // Return unique errors
                }

                function tweak(f) {
                    // Category & City placeholders
                    const map = { categories: 'Type of request', task_city: 'City' };
                    Object.keys(map).forEach(n => {
                        const $s = f.find(`select[name="${n}"]`);
                        if ($s.length && !$s.find('option[value=""]').length) {
                            $s.prepend(`<option value="" disabled>${map[n]}</option>`);
                            if (!$s.val()) $s.val('');
                        }
                    });

                    // Character limits
                    const limits = { title: 60, description: 800 };
                    Object.keys(limits).forEach(n => {
                        const $i = f.find(`[name="${n}"]`);
                        if ($i.length && !$i.data('limiter-added')) {
                            $i.data('limiter-added', 1);
                            const $c = $(`<small class="hp-field__description">${$i.val().length}/${limits[n]}</small>`).insertAfter($i);
                            $i.on('input', () => $c.text(`${$i.val().length}/${limits[n]}`));
                        }
                    });

                    // Budget description
                    const $budget = f.find('[name="budget"]');
                    if ($budget.length && !$budget.data('desc-added')) {
                        $budget.data('desc-added', 1);
                        $('<small class="hp-field__description">(value in Euros)</small>').insertAfter($budget);
                    }

                    // Remove images field if present
                    f.find('.hp-form__field:has(input[name="images"])').remove();

                    // Form Moderation Listener
                    if (!f.data('moderator-added')) {
                        f.data('moderator-added', 1);
                        // Intercept HivePress AJAX requests for request submission
                        f.on('hivepress/v1/models/request/add', function (e, data, xhr) {
                            const title = data.title || '';
                            const description = data.description || '';
                            const errors = [...new Set([...checkContent(title), ...checkContent(description)])];

                            if (errors.length > 0) {
                                e.preventDefault(); // Prevent the default HivePress AJAX submission
                                e.stopImmediatePropagation();

                                f.find('.beezy-mod-error').remove();
                                const html = `<div class="beezy-mod-error" style="background: #fff5f5; border: 1px solid #c53030; color: #c53030; padding: 15px; border-radius: 6px; margin-bottom: 20px;">
                                    <strong style="display:block; margin-bottom:5px;"><i class="fas fa-exclamation-triangle"></i> Content Moderation Warning</strong>
                                    <ul style="margin:0; padding-left:20px;">${errors.map(err => `<li>${err}</li>`).join('')}</ul>
                                </div>`;
                                f.prepend(html);
                                f[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
                                return false;
                            }
                        });

                        // Fallback for direct form submission (if any)
                        f.on('submit', function (e) {
                            const data = {};
                            f.serializeArray().forEach(item => data[item.name] = item.value);
                            const errors = [...new Set([...checkContent(data.title || ''), ...checkContent(data.description || '')])];

                            if (errors.length > 0) {
                                e.preventDefault();
                                e.stopImmediatePropagation();
                                f.find('.beezy-mod-error').remove();
                                const html = `<div class="beezy-mod-error" style="background: #fff5f5; border: 1px solid #c53030; color: #c53030; padding: 15px; border-radius: 6px; margin-bottom: 20px;">
                                    <strong style="display:block; margin-bottom:5px;"><i class="fas fa-exclamation-triangle"></i> Content Moderation Warning</strong>
                                    <ul style="margin:0; padding-left:20px;">${errors.map(err => `<li>${err}</li>`).join('')}</ul>
                                </div>`;
                                f.prepend(html);
                                f[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
                                return false;
                            }
                        });
                    }
                }

                $(document).ready(function () {
                    $('form.hp-form--request-submit').each(function () { tweak($(this)); });
                    new MutationObserver(() => {
                        $('form.hp-form--request-submit').each(function () { tweak($(this)); });
                    }).observe(document.body, { childList: true, subtree: true });
                });
            })(jQuery);
        </script>
        <?php
    }
    /* ---------------------------------------------------------
       MODULE 5: Admin Tweaks
       --------------------------------------------------------- */
    private function init_admin()
    {
        if (is_admin()) {
            add_action('show_user_profile', [$this, 'render_admin_user_tc_info']);
            add_action('edit_user_profile', [$this, 'render_admin_user_tc_info']);
            // Priority 1000 ensures we run AFTER themes that clear the dashboard (like taskhive-child)
            add_action('wp_dashboard_setup', [$this, 'add_dashboard_widgets'], 1000);
            add_action('wp_ajax_approve_beezy_request', [$this, 'handle_approve_request']);
        }
    }

    /**
     * Register custom dashboard widgets.
     */
    public function add_dashboard_widgets()
    {
        wp_add_dashboard_widget(
            'beezy_pending_requests',
            'Pending Requests (Moderation)',
            [$this, 'render_pending_requests_widget']
        );
    }

    /**
     * Render the pending requests dashboard widget.
     */
    public function render_pending_requests_widget()
    {
        $requestors = get_users(['role' => 'requestor', 'fields' => 'ID']);

        echo '<style>
            .beezy-dash-request { border: 1px solid #ccd0d4; padding: 12px; margin-bottom: 12px; border-radius: 4px; background: #f9f9f9; position: relative; }
            .beezy-dash-request h4 { margin: 0 0 8px 0; color: #0073aa; padding-right: 80px; }
            .beezy-dash-meta { font-size: 0.9em; color: #666; margin-bottom: 8px; }
            .beezy-dash-desc { font-style: italic; color: #444; margin-bottom: 10px; background: #fff; padding: 8px; border: 1px solid #eee; border-radius: 3px; }
            .beezy-red-flag { background: #fbeaea; border-left: 4px solid #dc3232; padding: 8px; margin: 10px 0; }
            .beezy-red-flag-title { color: #dc3232; font-weight: bold; margin-bottom: 4px; display: block; }
            .beezy-red-flag-list { margin: 0; padding-left: 20px; color: #dc3232; font-size: 0.9em; }
            .beezy-dash-actions { display: flex; gap: 8px; align-items: center; }
            .beezy-approve-btn { background: #46b450 !important; border-color: #349a3e !important; color: #fff !important; }
            .beezy-approve-btn:hover { background: #349a3e !important; }
            .beezy-approve-btn.loading { opacity: 0.5; pointer-events: none; }
        </style>';

        if (empty($requestors)) {
            echo '<p>No requestors found in the system.</p>';
            // Continue to show requests anyway
        }

        $all_counts = wp_count_posts('hp_request');
        $pending_count = isset($all_counts->pending) ? $all_counts->pending : 0;

        $requests = get_posts([
            'post_type' => 'hp_request',
            'post_status' => 'pending',
            'posts_per_page' => -1,
        ]);

        if (empty($requests)) {
            echo '<p><strong>Great job!</strong> No requests pending review at the moment (Total pending: ' . $pending_count . ').</p>';
            return;
        }

        foreach ($requests as $post) {
            $categories = wp_get_post_terms($post->ID, 'hp_request_category', ['fields' => 'names']);
            $budget = get_post_meta($post->ID, 'hp_budget', true);
            if (!$budget) {
                $budget = get_post_meta($post->ID, 'budget', true);
            }

            $content_to_check = $post->post_title . ' ' . $post->post_content;
            $flags = $this->check_for_red_flags($content_to_check);

            echo '<div class="beezy-dash-request" id="beezy-request-' . $post->ID . '">';
            echo '<h4>' . esc_html($post->post_title) . '</h4>';
            echo '<div class="beezy-dash-meta">';
            echo '<strong>Category:</strong> ' . esc_html(implode(', ', $categories)) . ' | ';
            echo '<strong>Budget:</strong> ' . esc_html($budget ? '€' . $budget : 'Not set');
            echo '</div>';
            echo '<div class="beezy-dash-desc">' . nl2br(esc_html($post->post_content)) . '</div>';

            if (!empty($flags)) {
                echo '<div class="beezy-red-flag">';
                echo '<span class="beezy-red-flag-title"><span class="dashicons dashicons-flag"></span> RED FLAG DETECTED</span>';
                echo '<ul class="beezy-red-flag-list">';
                foreach ($flags as $flag) {
                    echo '<li>' . esc_html($flag) . '</li>';
                }
                echo '</ul>';
                echo '</div>';
            }

            echo '<div class="beezy-dash-actions">';
            echo '<button class="button button-primary beezy-approve-btn" data-id="' . $post->ID . '">Approve & Publish</button>';
            echo '<a href="' . get_edit_post_link($post->ID) . '" class="button">Edit Detailedly</a>';
            echo '</div>';
            echo '</div>';
        }

        ?>
        <script>
            jQuery(document).ready(function ($) {
                $('.beezy-approve-btn').on('click', function () {
                    var $btn = $(this);
                    var postId = $btn.data('id');
                    var $container = $('#beezy-request-' + postId);

                    if (!confirm('Are you sure you want to approve and publish this request?')) return;

                    $btn.addClass('loading').text('Publishing...');

                    $.post(ajaxurl, {
                        action: 'approve_beezy_request',
                        post_id: postId,
                        nonce: '<?php echo wp_create_nonce("beezy_approve_" . get_current_user_id()); ?>'
                    }, function (res) {
                        if (res.success) {
                            $container.css('background', '#e7f7ed').fadeOut(1000, function () {
                                $(this).remove();
                                if ($('.beezy-dash-request').length === 0) {
                                    $('#beezy_pending_requests .inside').html('<p><strong>Great job!</strong> No requests pending review at the moment.</p>');
                                }
                            });
                        } else {
                            alert('Error: ' + (res.data || 'Unknown error'));
                            $btn.removeClass('loading').text('Approve & Publish');
                        }
                    });
                });
            });
        </script>
        <?php
    }

    /**
     * AJAX handler to approve/publish a request.
     */
    public function handle_approve_request()
    {
        check_ajax_referer("beezy_approve_" . get_current_user_id(), 'nonce');

        $post_id = isset($_POST['post_id']) ? (int) $_POST['post_id'] : 0;

        if (!$post_id || !current_user_can('edit_post', $post_id)) {
            wp_send_json_error('Insufficient permissions or invalid ID.');
        }

        $result = wp_update_post([
            'ID' => $post_id,
            'post_status' => 'publish'
        ]);

        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message());
        }

        wp_send_json_success();
    }

    /**
     * Helper to check for harmful content, contact info, and addresses.
     */
    private function check_for_red_flags($text)
    {
        $flags = [];
        $low_text = strtolower($text);
        $forbidden = $this->get_forbidden_keywords();

        // Check for forbidden words
        foreach ($forbidden as $word) {
            if (preg_match('/\b' . preg_quote($word, '/') . '\b/', $low_text)) {
                $flags[] = "Harmful content suspected: '$word'";
            }
        }

        // Substring check for very sensitive roots
        $roots = ['fuck', 'nigger', 'faggot', 'kanker', 'neuken'];
        foreach ($roots as $root) {
            if (strpos($low_text, $root) !== false) {
                $flags[] = "Highly offensive term detected.";
            }
        }

        // Email address
        if (preg_match('/[a-zA-Z0-9._%+-]+@([a-zA-Z0-9.-]+\.[a-zA-Z]{2,})/', $text)) {
            $flags[] = "Email mention detected";
        }

        // Phone number
        if (preg_match('/(\+?[0-9][0-9\s\-\(\)]{8,})/', $text)) {
            $flags[] = "Potential phone number detected";
        }

        // Address reference (Keywords for street/postal codes)
        $address_keywords = [
            'straat',
            'weg',
            'laan',
            'plein',
            'gracht',
            'dijk',
            'kade',
            'singel',
            'plantsoen',
            'street',
            'road',
            'avenue',
            'drive',
            'lane',
            'court',
            'boulevard',
            'square',
            'postcode',
            'postal code',
            'zip code',
            'woonachtig',
            'wonen in'
        ];

        foreach ($address_keywords as $word) {
            if (preg_match('/\b' . preg_quote($word, '/') . '\b/', $low_text)) {
                $flags[] = "Address or location reference: '$word'";
                break;
            }
        }

        // Dutch Postal Code Pattern (1234 AB)
        if (preg_match('/\b[1-9][0-9]{3}\s?[a-zA-Z]{2}\b/', $text)) {
            $flags[] = "Dutch postal code detected";
        }

        return array_unique($flags);
    }

    /**
     * Central dictionary for keywords.
     */
    private function get_forbidden_keywords()
    {
        return [
            // English
            'sex',
            'porn',
            'sexual',
            'nude',
            'explicit',
            'escort',
            'date',
            'drugs',
            'weapon',
            'bomb',
            'kill',
            'suicide',
            'abuse',
            'violence',
            'money laundering',
            'crypto scam',
            'human trafficking',
            'children',
            'girl',
            'boy',
            'exploitation',
            'fuck',
            'shit',
            'asshole',
            'bastard',
            'bitch',
            'cock',
            'dick',
            'cunt',
            'pussy',
            'slut',
            'whore',
            'negro',
            'nigger',
            'faggot',
            'retard',
            'scam',
            'crypto',
            'bitcoin',
            'eth',
            'wallet',
            'whatsapp',
            'instagram',
            'snapchat',
            'facebook',
            'tg',
            'telegram',
            // Dutch
            'seks',
            'porno',
            'naakt',
            'escort',
            'drugs',
            'wapen',
            'bom',
            'doden',
            'zelfmoord',
            'misbruik',
            'geweld',
            'witwassen',
            'scam',
            'mensenhandel',
            'kind',
            'meisje',
            'jongen',
            'exploitatie',
            'neuken',
            'kut',
            'lul',
            'klootzak',
            'hufter',
            'hoer',
            'slet',
            'kanker',
            'tyfus',
            'tering',
            'godverdomme',
            'stront',
            'nederwiet',
            'hennep',
            'coke',
            'speed',
            'xtc',
            'heroine'
        ];
    }

    /**
     * Display T&C agreement status and timestamp in User Profile.
     */
    public function render_admin_user_tc_info($user)
    {
        $accepted = get_user_meta($user->ID, 'terms_accepted_v1', true);
        ?>
        <hr />
        <h3>Beezy Platform Status</h3>
        <table class="form-table">
            <tr>
                <th><label>Terms & Conditions</label></th>
                <td>
                    <?php if ($accepted): ?>
                        <span class="dashicons dashicons-yes"
                            style="color: #46b450; font-size: 20px; vertical-align: middle;"></span>
                        <strong style="color: #46b450;">Accepted</strong>
                        <p class="description">
                            Agreed on: <code><?php echo date('Y-m-d H:i:s', (int) $accepted); ?></code>
                        </p>
                    <?php else: ?>
                        <span class="dashicons dashicons-no"
                            style="color: #dc3232; font-size: 20px; vertical-align: middle;"></span>
                        <strong style="color: #dc3232;">Not Accepted Yet</strong>
                        <p class="description">User has not yet interacted with the T&C modal.</p>
                    <?php endif; ?>
                </td>
            </tr>
        </table>
        <?php
    }

    /* ---------------------------------------------------------
       HELPERS & UTILITIES
       --------------------------------------------------------- */
    private function is_user_verified($user_id)
    {
        if (!$user_id)
            return false;

        // Check both hp_ and non-hp keys for robustness
        $val_hp = get_user_meta($user_id, 'hp_doc_verified', true);
        $val_direct = get_user_meta($user_id, 'doc_verified', true);

        $valid_trues = [1, '1', true, 'true', 'yes', 'Yes'];

        return in_array($val_hp, $valid_trues) || in_array($val_direct, $valid_trues);
    }
}

// Initialize the core.
Beezy_Core::get_instance();
