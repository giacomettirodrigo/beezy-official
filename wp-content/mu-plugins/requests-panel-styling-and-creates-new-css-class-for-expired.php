<?php
function bee_mark_expired_requests_js_safe() {
    
    // SAFE CHECK: Only proceed if the current URL contains the request slug
    if ( strpos( $_SERVER['REQUEST_URI'], '/requests/' ) === false ) {
        return;
    }
    
    ?>
    <script type="text/javascript">
    
    // Function to calculate and format the remaining time (Unchanged)
    function formatTimeRemaining(ms) {
        if (ms <= 0) {
            return 'EXPIRED';
        }
        
        const seconds = Math.floor(ms / 1000);
        const days = Math.floor(seconds / (3600 * 24));
        const hours = Math.floor((seconds % (3600 * 24)) / 3600);
        const minutes = Math.floor((seconds % 3600) / 60);

        let parts = [];
        if (days > 0) {
            parts.push(days + (days === 1 ? ' day' : ' days'));
        }
        
        if (days === 0) {
             parts.push(hours + 'h');
             parts.push(minutes + 'm');
             parts.push((seconds % 60) + 's');
        } else {
             parts.push(hours + 'h');
             parts.push(minutes + 'm');
        }
        
        return parts.slice(0, 3).join(' ');
    }
    
    // Helper functions to apply inline styles (Unchanged)
    function applyCountdownStyles(element) {
        element.style.fontWeight = 'bold';
        element.style.color = '#28a745'; 
        element.style.backgroundColor = '#e2f9e7';
        element.style.border = '1px solid #28a745';
        element.style.padding = '2px 6px';
        element.style.fontSize = '0.7em'; 
    }
    
    function applyExpiredStyles(element) {
        element.style.fontWeight = 'bold';
        element.style.color = '#ffffff'; 
        element.style.backgroundColor = '#ff0000';
        element.style.border = '1px solid #ff0000';
        element.style.padding = '2px 6px';
        element.style.fontSize = '0.7em';
    }


    document.addEventListener('DOMContentLoaded', function() {
        
        const container = document.querySelector('.hp-requests .hp-row'); 
        const requestListings = document.querySelectorAll('.hp-requests .hp-listing');
        
        if (!container || requestListings.length === 0) return; 

        let allRequests = [];
        let activeCountdowns = []; 

        requestListings.forEach(listing => {
            const timeElement = listing.querySelector('time.hp-listing__task-date');
            
            if (timeElement) {
                const taskDateString = timeElement.getAttribute('datetime');
                const taskTime = new Date(taskDateString).getTime();
                const itemWrapper = listing.closest('.hp-grid__item');
                const currentTime = new Date().getTime(); 
                
                // --- POSITIONING CHANGE: Find the main content block ---
                const listingContent = listing.querySelector('.hp-listing__content');

                let unifiedLabel = listing.querySelector('.hp-unified-label');
                if (!unifiedLabel) {
                    unifiedLabel = document.createElement('span');
                    unifiedLabel.classList.add('hp-unified-label');
                    
                    // Inject the label into the listing content block
                    if (listingContent) {
                        listingContent.appendChild(unifiedLabel); 
                    }
                }

                if (taskTime < currentTime) {
                    // EXPIRED state
                    listing.classList.add('hp-listing--expired');
                    unifiedLabel.textContent = 'EXPIRED';
                    applyExpiredStyles(unifiedLabel);
                    
                } else {
                    // ACTIVE COUNTDOWN state
                    listing.classList.remove('hp-listing--expired');
                    applyCountdownStyles(unifiedLabel); 

                    activeCountdowns.push({ element: unifiedLabel, time: taskTime, listing: listing });
                }
                
                allRequests.push({ element: itemWrapper, time: taskTime });
            }
        });
        
        // --- LIVE COUNTDOWN TIMER FUNCTION (Unchanged) ---
        const updateCountdowns = () => {
            const now = new Date().getTime();
            
            activeCountdowns = activeCountdowns.filter(item => {
                let distance = item.time - now;
                
                if (distance > 0) {
                    item.element.textContent = formatTimeRemaining(distance);
                    return true;
                } else {
                    item.element.textContent = 'EXPIRED';
                    applyExpiredStyles(item.element); 
                    item.listing.classList.add('hp-listing--expired');
                    
                    return false;
                }
            });
            
            if (activeCountdowns.length > 0) {
                 requestAnimationFrame(updateCountdowns);
            }
        };

        updateCountdowns();

        // --- SORTING LOGIC (Unchanged) ---
        allRequests.sort((a, b) => b.time - a.time);
        container.innerHTML = ''; 
        allRequests.forEach(item => {
            container.appendChild(item.element);
        });
    });
    </script>
    <?php
}
add_action( 'wp_footer', 'bee_mark_expired_requests_js_safe', 110 );