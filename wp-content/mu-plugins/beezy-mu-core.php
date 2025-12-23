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
        add_filter('hivepress/v1/menus/user_account', [$this, 'add_vendor_view_to_menu'], 1000);

        // Remove Hourly Rate
        add_filter('hivepress/v1/forms/user_update', [$this, 'remove_hourly_rate_field'], 1000);

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
        add_action('wp_footer', [$this, 'render_offer_ui_guard'], 999);
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
        if (in_array('bee', (array) $user->roles) && !get_user_meta($user->ID, 'hp_doc_verified', true)) {
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
                function tweak(f) {
                    // Category & City placeholders
                    const map = { categories: 'Type of request', task_city: 'City' };
                    Object.keys(map).forEach(n => {
                        const $s = f.find(`select[name="${n}"]`);
                        if ($s.length && !$s.find('option[value=""]').length) {
                            // Prepend placeholder WITHOUT forced 'selected' attribute.
                            // This prevents resetting the selection after an AJAX update.
                            $s.prepend(`<option value="" disabled>${map[n]}</option>`);

                            // Only set selection to placeholder if no value is currently selected.
                            if (!$s.val()) {
                                $s.val('');
                            }
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
                }

                $(document).ready(function () {
                    // Initial tweak for any existing forms.
                    $('form.hp-form--request-submit').each(function () { tweak($(this)); });

                    // Use MutationObserver to handle forms that are added or replaced via AJAX.
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
        }
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
