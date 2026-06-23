document.addEventListener("DOMContentLoaded", () => {
    let theme = localStorage.getItem('backendTheme'),
        $body = document.querySelector('body'),
        txtLang = (window.skinI18n && window.skinI18n.darkTheme) || 'Dark theme',
        menu = document.querySelector('.mainmenu-accountmenu');

    if (!menu || !$body) {
        return;
    }

    theme == 'dark' ? $body.classList.add('dark') : 0;

    let toggleTheme = document.createElement('li');
    toggleTheme.className = 'dark-theme';

    let wrapper = document.createElement('div');
    wrapper.className = 'checkbox custom-checkbox';
    wrapper.style.margin = '5px 30px 4px';

    let checkTheme = document.createElement('input');
    checkTheme.id = 'darkTheme';
    checkTheme.setAttribute('name', 'checkbox');
    checkTheme.setAttribute('type', 'checkbox');
    theme == 'dark' ? checkTheme.setAttribute('checked', 'checked') : 0;

    let label = document.createElement('label');
    label.setAttribute('for', 'darkTheme');
    label.textContent = txtLang;

    wrapper.appendChild(checkTheme);
    wrapper.appendChild(label);
    toggleTheme.appendChild(wrapper);

    let anchor = menu.querySelector('li.divider') || menu.querySelector('ul');
    if (!anchor) {
        return;
    }

    if (anchor.matches('li.divider')) {
        anchor.before(toggleTheme);
    } else {
        anchor.appendChild(toggleTheme);
    }

    checkTheme.addEventListener('input', () => {
        if (checkTheme.checked) {
            $body.classList.add('dark');
            localStorage.setItem('backendTheme', 'dark');
        }
        else {
            $body.classList.remove('dark');
            localStorage.removeItem('backendTheme');
        }
    });
});
