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

/* code copied to use the functionnality "tagging" in a textfield
 *  used for Aliases field
 *  Widget is "tags" and each tag becomes a value added to a hidden symfony field.
*/
window.initializeAliasTags = () => {
    document.querySelectorAll('[data-alias-tags]').forEach((container) => {
        if (container.dataset.initialized === 'true') {
            return;
        }

        container.dataset.initialized = 'true';

        const tagsList = container.querySelector('[data-alias-tags-list]');
        const input = container.querySelector('[data-alias-tag-input]');

        if (!tagsList || !input) {
            return;
        }

        let index = Number.parseInt(container.dataset.index || '0', 10);

        const normalizeAlias = (value) => {
            return value
                .trim()
                .replace(/^https?:\/\//i, '')
                .replace(/,$/, '')
                .trim();
        };

        const getExistingAliases = () => {
            return Array.from(container.querySelectorAll('[data-alias-domain-input]'))
                .map((field) => normalizeAlias(field.value))
                .filter((value) => value !== '');
        };

        const attachRemoveButton = (tag) => {
            const button = tag.querySelector('[data-remove-alias-tag]');

            if (!button) {
                return;
            }

            button.addEventListener('click', () => {
                tag.remove();
            });
        };

        container.querySelectorAll('[data-alias-tag]').forEach(attachRemoveButton);

        const createTag = (rawValue) => {
            const value = normalizeAlias(rawValue);

            if (value === '') {
                return;
            }

            if (getExistingAliases().includes(value)) {
                input.value = '';
                return;
            }

            const prototype = container.dataset.prototype;

            if (!prototype) {
                return;
            }

            const html = prototype.replace(/__name__/g, String(index));
            index += 1;
            container.dataset.index = String(index);

            const template = document.createElement('template');
            template.innerHTML = html.trim();

            const hiddenInput = template.content.querySelector('[data-alias-domain-input]');

            if (!hiddenInput) {
                return;
            }

            hiddenInput.value = value;

            const tag = document.createElement('div');
            tag.classList.add('tag');
            tag.dataset.aliasTag = '';

            const span = document.createElement('span');
            span.textContent = value;

            const button = document.createElement('button');
            button.type = 'button';
            button.textContent = 'x';
            button.dataset.removeAliasTag = '';

            tag.appendChild(span);
            tag.appendChild(button);
            tag.appendChild(hiddenInput);

            tagsList.appendChild(tag);
            attachRemoveButton(tag);

            input.value = '';
        };

        input.addEventListener('keydown', (event) => {
            if (
                (event.key === 'Enter' || event.key === ',' || event.key === 'Tab')
                && input.value.trim() !== ''
            ) {
                event.preventDefault();
                createTag(input.value);
            }

            if (event.key === 'Backspace' && input.value === '') {
                const lastTag = tagsList.querySelector('[data-alias-tag]:last-child');

                if (lastTag) {
                    lastTag.remove();
                }
            }
        });

        input.addEventListener('blur', () => {
            if (input.value.trim() !== '') {
                createTag(input.value);
            }
        });

        container.addEventListener('click', () => {
            input.focus();
        });
    });
};

window.initializeAliasTags();