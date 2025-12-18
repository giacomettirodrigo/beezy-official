<?php

/**
 * Bee offer verification — REST + model guard + friendly modal UX
 *
 * Paste this as a single snippet (Code Snippets or child theme functions.php).
 * IMPORTANT: Remove/disable previous guard snippets before activating this.
 */

/* ---------------------------
   0) Helpers / options
   --------------------------- */
define( 'BEE_GUARD_DEBUG', true ); // set false when done
// change if your settings page differs
$bee_guard_account_url = home_url( '/account/settings/' );

/* ---------------------------
   1) REST-level authoritative guard (cannot be bypassed)
   --------------------------- */
/**
 * rest_pre_dispatch callback signature: ($result, WP_REST_Server $server, WP_REST_Request $request)
 * Return WP_Error to short-circuit the REST route.
 */
add_filter( 'rest_pre_dispatch', function( $result, $server, $request ) {
    // Only care about POSTs
    try {
        $method = strtoupper( $request->get_method() );
    } catch ( Throwable $e ) {
        // If $request is not what we expect, skip (defensive)
        return $result;
    }

    if ( 'POST' !== $method ) {
        return $result;
    }

    $route = $request->get_route(); // e.g. /hivepress/v1/offers
    if ( false === strpos( $route, '/hivepress/v1/offers' ) ) {
        return $result;
    }

    // require login
    if ( ! is_user_logged_in() ) {
        return new WP_Error( 'rest_not_logged_in', 'You must be logged in to create an offer.', [ 'status' => 401 ] );
    }

    $user = wp_get_current_user();
    if ( ! $user ) {
        return new WP_Error( 'rest_user_error', 'User not found', [ 'status' => 401 ] );
    }

    // only enforce for Bees (change if desired)
    if ( in_array( 'bee', (array) $user->roles, true ) ) {
        $is_verified = get_user_meta( $user->ID, 'hp_doc_verified', true );

        if ( empty( $is_verified ) ) {
            if ( defined( 'WP_DEBUG' ) && WP_DEBUG && BEE_GUARD_DEBUG ) {
                error_log( sprintf( '[BeeGuard REST BLOCK] uid=%d route=%s ip=%s', $user->ID, $route, $_SERVER['REMOTE_ADDR'] ?? 'unknown' ) );
            }
            return new WP_Error(
                'verification_required',
                'You must verify your identity before submitting offers.',
                [ 'status' => 403 ]
            );
        }
    }

    return $result;
}, 10, 3 );

/* ---------------------------
   2) HivePress model-level guard (defense-in-depth)
   --------------------------- */
/**
 * hivepress/v1/models/offer/create usually passes $args only (not request),
 * so the callback should accept single argument.
 */
add_filter( 'hivepress/v1/models/offer/create', function( $args ) {
    if ( ! is_user_logged_in() ) {
        return $args;
    }
    $user = wp_get_current_user();
    if ( ! $user ) return $args;

    if ( in_array( 'bee', (array) $user->roles, true ) ) {
        $is_verified = get_user_meta( $user->ID, 'hp_doc_verified', true );
        if ( empty( $is_verified ) ) {
            if ( defined( 'WP_DEBUG' ) && WP_DEBUG && BEE_GUARD_DEBUG ) {
                error_log( sprintf( '[BeeGuard MODEL BLOCK] uid=%d', $user->ID ) );
            }
            return new WP_Error( 'verification_required', 'You must verify your identity before submitting offers.' );
        }
    }

    return $args;
}, 9 );

/* ---------------------------
   3) Client-side UX: Button Replacement with Silent Redirect
   --------------------------- */
add_action( 'wp_footer', function() use ( $bee_guard_account_url ) {
    $current_user = wp_get_current_user();
    $is_logged_in  = is_user_logged_in();
    $is_bee        = ( $is_logged_in && in_array( 'bee', (array) $current_user->roles, true ) );
    $is_verified   = ( $is_logged_in ) ? (bool) get_user_meta( $current_user->ID, 'hp_doc_verified', true ) : false;
    $account_url   = esc_url( $bee_guard_account_url );

    if ( ! $is_bee || $is_verified ) {
        return;
    }

    ?>
    <script type="text/javascript">
    (function(){
        const HP = {
            // Target URL is the account settings page
            accountUrl: <?php echo json_encode( $account_url ); ?>
        };
        let observer;

        // The simple redirect function - NO modal, NO alert()
        function immediateRedirect(e) {
            e && e.preventDefault();
            e && e.stopPropagation();
            // Silent, immediate redirection to /account/settings/
            window.location.href = HP.accountUrl;
        }
       
        function applyUiGuards() {
            try {
                // Selector targets the original "Make an Offer" button
                const selectorTriggers = 'a[href^="#offer_make_modal"], [data-fancybox][href^="#offer_make_modal"], button[data-target^="#offer_make_modal"], .hp-offer-trigger, .hp-offer-button, a.hp-offer, .hp-listing__action--offer';
                const triggers = Array.from(document.querySelectorAll(selectorTriggers));

                if (observer) {
                    observer.disconnect(); // Prevent the infinite loop
                }

                triggers.forEach(trigger => {
                    if (trigger.dataset.hpBeeGuard === '1') return;
                    trigger.dataset.hpBeeGuard = '1';

                    // Replace with a button that redirects
                    const disabled = document.createElement('button');
                    // Retain original styling, add disabled class
                    disabled.className = (trigger.className || '') + ' hp-offer-disabled button button--warning';
                    disabled.innerHTML = '<i class="hp-icon fas fa-user-shield"></i> Verify ID to make an offer';
                    disabled.type = 'button';
                    disabled.title = 'Verify ID to make an offer';
                    disabled.style.cursor = 'pointer';
                    disabled.style.opacity = '1';
                   
                    disabled.addEventListener('click', immediateRedirect);

                    try {
                        trigger.parentNode && trigger.parentNode.replaceChild(disabled, trigger);
                    } catch (err) {
                        trigger.style.display = 'none';
                    }
                });

                // 2) Defense-in-depth: Intercept form submit
                const forms = Array.from(document.querySelectorAll('form.hp-form--offer-make, form.hp-form--offer, form[data-model="offer"]'));
                forms.forEach(form => {
                    if (form.dataset.hpBeeGuardForm === '1') return;
                    form.dataset.hpBeeGuardForm = '1';

                    form.addEventListener('submit', function(e){
                        // Block and redirect
                        immediateRedirect(e);
                        return false;
                    }, { capture: true, passive: false });
                });
            } catch (err) {
                if ( window.console ) console.warn('hp-bee-guard UI error', err);
            } finally {
                if (observer) {
                    observer.observe(document.body, { childList: true, subtree: true });
                }
            }
        }

        // MutationObserver to watch for dynamic content
        observer = new MutationObserver(function() {
            applyUiGuards();
        });
        observer.observe(document.body, { childList: true, subtree: true });

        // Run once now
        applyUiGuards();
    })();
    </script>
    <?php
}, 999 );