/**
 * ZestPHP Web - Main JavaScript file
 * This file contains common JavaScript functionality for your ZestPHP Web application
 */

// Wait for the document to be fully loaded
$(document).ready(function() {
    console.log('ZestPHP Web application initialized');
    
    // Example of a simple component
    $('.zest-alert').each(function() {
        const $alert = $(this);
        const $closeBtn = $alert.find('.zest-alert-close');
        
        // Add close button functionality
        if ($closeBtn.length) {
            $closeBtn.on('click', function() {
                $alert.fadeOut(300, function() {
                    $(this).remove();
                });
            });
            
            // Auto-close after 5 seconds if it's a temporary alert
            if ($alert.hasClass('zest-alert-temp')) {
                setTimeout(function() {
                    $closeBtn.trigger('click');
                }, 5000);
            }
        }
    });
    
    // Example of AJAX request using the ZestPHP API
    $('.zest-api-demo').on('click', function(e) {
        e.preventDefault();
        const $button = $(this);
        const $result = $button.siblings('.zest-api-result');
        
        // Show loading state
        $button.prop('disabled', true).text('Loading...');
        
        // Make AJAX request to the API
        $.ajax({
            url: '/api/hello',
            method: 'GET',
            dataType: 'json',
            success: function(data) {
                // Display the result
                $result.html(
                    '<div class="p-4 bg-green-100 text-green-800 rounded mt-4">' +
                    '<p><strong>API Response:</strong></p>' +
                    '<pre class="mt-2 bg-white p-2 rounded">' + JSON.stringify(data, null, 2) + '</pre>' +
                    '</div>'
                ).show();
            },
            error: function(xhr) {
                // Display error
                $result.html(
                    '<div class="p-4 bg-red-100 text-red-800 rounded mt-4">' +
                    '<p><strong>Error:</strong> Failed to fetch API data</p>' +
                    '</div>'
                ).show();
            },
            complete: function() {
                // Reset button state
                $button.prop('disabled', false).text('Try API');
            }
        });
    });
});
