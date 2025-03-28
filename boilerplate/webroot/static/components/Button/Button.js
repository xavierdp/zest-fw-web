/**
 * Button Component JavaScript
 */
$(document).ready(function () {
    // Initialize Button component
    $('.zest-button').each(function () {
        const $button = $(this);

        // Handle loading state
        $button.on('loading.start', function () {
            $(this).addClass('zest-button-loading')
                .prop('disabled', true)
                .data('original-text', $(this).text())
                .text('');
        });

        $button.on('loading.stop', function () {
            $(this).removeClass('zest-button-loading')
                .prop('disabled', false)
                .text($(this).data('original-text'));
        });

        // Add ripple effect on click
        $button.on('click', function (e) {
            // Only add ripple if button is not disabled
            if (!$(this).prop('disabled') && !$(this).hasClass('zest-button-loading')) {
                const $this = $(this);

                // Create ripple element
                const $ripple = $('<span class="ripple"></span>');
                $this.append($ripple);

                // Get button position and dimensions
                const buttonPos = $this.offset();
                const xPos = e.pageX - buttonPos.left;
                const yPos = e.pageY - buttonPos.top;

                // Set ripple position and animate
                $ripple.css({
                    width: $this.width(),
                    height: $this.height(),
                    top: yPos + 'px',
                    left: xPos + 'px'
                }).addClass('animate');

                // Remove ripple after animation
                setTimeout(function () {
                    $ripple.remove();
                }, 600);
            }
        });
    });
});