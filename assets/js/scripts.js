// assets/js/scripts.js

document.addEventListener('DOMContentLoaded', () => {
    // Sidebar toggle
    const sidebarToggle = document.querySelector('.sidebar-toggle');
    const sidebar = document.querySelector('.sidebar');
    const container = document.querySelector('.container');

    if (sidebarToggle && sidebar && container) {
        sidebarToggle.addEventListener('click', () => {
            sidebar.classList.toggle('active');
            container.classList.toggle('shifted');
        });
    }

    // Theme toggle
    const themeToggle = document.getElementById('theme-toggle');
    if (themeToggle) {  // Only if button exists
        let currentTheme = localStorage.getItem('theme') || 'dark';
        const stylesheet = document.getElementById('theme-stylesheet');
        const basePath = 'assets/css/';  // Relative to base href
        if (currentTheme === 'light') {
            stylesheet.href = basePath + 'light-mode.css';
            themeToggle.textContent = 'Switch to Dark Mode';
        } else {
            stylesheet.href = basePath + 'style.css';
            themeToggle.textContent = 'Switch to Light Mode';
        }

        themeToggle.addEventListener('click', () => {
            currentTheme = currentTheme === 'light' ? 'dark' : 'light';
            if (currentTheme === 'light') {
                stylesheet.href = basePath + 'light-mode.css';
                themeToggle.textContent = 'Switch to Dark Mode';
            } else {
                stylesheet.href = basePath + 'style.css';
                themeToggle.textContent = 'Switch to Light Mode';
            }
            localStorage.setItem('theme', currentTheme);
            // Trigger a custom event to notify other elements (like modals) to update
            document.dispatchEvent(new Event('themeChanged'));
        });
    } else {
        // On pages without toggle, still apply stored theme
        const currentTheme = localStorage.getItem('theme') || 'dark';
        const stylesheet = document.getElementById('theme-stylesheet');
        if (stylesheet) {
            const basePath = 'assets/css/';
            stylesheet.href = basePath + (currentTheme === 'light' ? 'light-mode.css' : 'style.css');
        }
    }

    // Ask AI button - Toggle open/close
    const askAiButton = document.getElementById('ask-ai');
    const aiModal = document.getElementById('ai-modal');
    const closeModal = document.getElementById('close-modal');
    const aiForm = document.getElementById('ai-form');
    const aiResponse = document.getElementById('ai-response');
    const modalContent = aiModal ? aiModal.querySelector('.modal-content') : null;

    // Reorder: Move the form below the response div (chat box)
    if (modalContent && aiForm && aiResponse) {
        modalContent.appendChild(aiForm);  // Moves form to the end, below ai-response
    }

    // Make chat box scrollable
    if (aiResponse) {
        aiResponse.style.overflowY = 'auto';
        aiResponse.style.maxHeight = '300px';  // Adjust height as needed
    }

    if (askAiButton && aiModal) {
        askAiButton.addEventListener('click', () => {
            if (aiModal.style.display === 'block') {
                aiModal.style.display = 'none';  // Close if already open
            } else {
                aiModal.style.display = 'block';  // Open if closed
                // Initially hide the form (textarea + button)
                if (aiForm) aiForm.style.display = 'none';
                // Clear previous response/chat
                if (aiResponse) {
                    aiResponse.innerHTML = '';
                    aiResponse.style.display = 'none';
                }
            }
        });
    }

    if (closeModal && aiModal) {
        closeModal.addEventListener('click', () => {
            aiModal.style.display = 'none';
        });
    }

    // Close modal if clicking outside the content
    if (aiModal) {
        window.addEventListener('click', (event) => {
            if (event.target === aiModal) {
                aiModal.style.display = 'none';
            }
        });
    }

    // Tutorial Modal Handling
    const tutorialModal = document.getElementById('tutorial-modal');
    const closeTutorialModal = document.getElementById('close-tutorial-modal');

    if (closeTutorialModal && tutorialModal) {
        closeTutorialModal.addEventListener('click', () => {
            tutorialModal.style.display = 'none';
        });
    }

    if (tutorialModal) {
        window.addEventListener('click', (event) => {
            if (event.target === tutorialModal) {
                tutorialModal.style.display = 'none';
            }
        });
    }

    // Function to show tutorial popup (add your actual tutorial mappings)
    window.showTutorial = function(question) {  // Make it global if called from HTML
        const titleElem = document.getElementById('tutorial-title');
        const imgElem = document.getElementById('tutorial-image');
        const descElem = document.getElementById('tutorial-description');

        // Example tutorial data - expand this object with real images/descriptions
        const tutorials = {
            'How to register?': {
                image: 'assets/images/tutorials/register.png',
                desc: 'Step-by-step: 1. Go to register page...',
            },
            'How to login?': {
                image: 'assets/images/tutorials/login.png',
                desc: 'Step-by-step: 1. Enter username...',
            },
            // Add more for other questions...
            'Need more help?': {
                image: '',
                desc: 'For more help, use the chat box below.',
            }
        };

        const tutorial = tutorials[question] || { image: '', desc: 'No tutorial available yet.' };
        if (titleElem) titleElem.textContent = question;
        if (imgElem) {
            imgElem.src = tutorial.image;
            imgElem.style.display = tutorial.image ? 'block' : 'none';
        }
        if (descElem) descElem.textContent = tutorial.desc;

        if (tutorialModal) tutorialModal.style.display = 'block';

        // Special behavior for "Need more help?" - close tutorial, show AI form, and focus textarea
        if (question === 'Need more help?') {
            setTimeout(() => {
                tutorialModal.style.display = 'none';  // Close tutorial modal
                if (aiModal) aiModal.style.display = 'block';  // Ensure AI modal is open
                if (aiForm) {
                    aiForm.style.display = 'block';  // Show the form (textarea + button)
                    const aiQuery = document.getElementById('ai-query');
                    if (aiQuery) {
                        aiQuery.focus();  // Focus the textarea for open questions
                    }
                }
            }, 0);  // Short delay if you want to briefly show the tutorial message (adjust or remove)
        }
    };

    // Other scripts (e.g., form validations, etc.)
    // Add any additional JS functions here if needed
});

// Function to handle AI query (placeholder for now) - keep outside if needed, or move inside
function handleAiQuery(event) {
    event.preventDefault();
    const query = document.getElementById('ai-query').value;
    const responseDiv = document.getElementById('ai-response');
    
    // Append user query to chat history (like Messenger)
    const userMessage = document.createElement('p');
    userMessage.innerHTML = `<strong>You:</strong> ${query}`;
    responseDiv.appendChild(userMessage);
    
    // Placeholder AI response
    const aiMessage = document.createElement('p');
    aiMessage.innerHTML = `<strong>AI:</strong> This is a placeholder response. Feature coming soon!`;
    responseDiv.appendChild(aiMessage);
    
    // Show the response div and scroll to bottom
    responseDiv.style.display = 'block';
    responseDiv.scrollTop = responseDiv.scrollHeight;
    
    // Clear the textarea for next input
    document.getElementById('ai-query').value = '';
}