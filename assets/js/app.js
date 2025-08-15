// assets/js/app.js

document.addEventListener('DOMContentLoaded', function() {
    // This event listener ensures the DOM is fully loaded before running any JavaScript

    console.log('app.js loaded');

    // You can add global event listeners or functions here that might be used across the site.
    // For example, handling a common UI element or setting up initial behaviors.
});

// Example function (you can expand on this)
function displayMessage(message, type = 'info') {
    const messageContainer = document.createElement('div');
    messageContainer.textContent = message;
    messageContainer.classList.add('alert', `alert-${type}`); // You'd need to define these classes in your CSS
    document.body.prepend(messageContainer); // Or append to a specific container
    setTimeout(() => {
        messageContainer.remove();
    }, 3000); // Remove after 3 seconds
}

// You might have more specific JavaScript code in separate files (e.g., products.js, sales.js)
// and include them on the relevant pages.