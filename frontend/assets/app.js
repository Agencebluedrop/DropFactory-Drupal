/*
 * Welcome to your app's main JavaScript file!
 *
 * This file will be included onto the page via the importmap() Twig function,
 * which should already be in your base.html.twig.
 */
import './styles/app.css';
import Alpine from 'alpinejs';
import Choices from 'choices.js';


Alpine.start();
window.clearSymfonyCache = () => {
    fetch('/clear-cache', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest', 
        },
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            alert(data.message);
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while clearing the cache.');
    });
}
window.choicesInstances = new Map();

window.initializeChoices = () => {
    const selectElements = document.querySelectorAll('select');

    if (selectElements.length > 0) {
        selectElements.forEach((select) => {
            if (!window.choicesInstances.has(select)) {
                const choices = new Choices(select, {
                    searchEnabled: false,
                    searchChoices: false,
                    shouldSort: false,
                    itemSelectText: ''
                });
                // Store the instance
                window.choicesInstances.set(select, choices);
            }
        });
    }
};

window.initializeChoices();


