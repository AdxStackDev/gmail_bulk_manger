/**
 * UI: light/dark theme toggle with persistence.
 *
 * Loaded as a classic script; functions are global so inline handlers and the
 * page bootstrap can call them.
 */

function toggleTheme() {
    const html = document.documentElement;
    const sun = document.getElementById('sunIcon');
    const moon = document.getElementById('moonIcon');

    if (html.classList.contains('dark')) {
        html.classList.remove('dark');
        localStorage.setItem('theme', 'light');
        if (sun) sun.classList.add('hidden');
        if (moon) moon.classList.remove('hidden');
    } else {
        html.classList.add('dark');
        localStorage.setItem('theme', 'dark');
        if (sun) sun.classList.remove('hidden');
        if (moon) moon.classList.add('hidden');
    }
}

function initTheme() {
    if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
        document.documentElement.classList.add('dark');
        const sun = document.getElementById('sunIcon');
        const moon = document.getElementById('moonIcon');
        if (sun) sun.classList.remove('hidden');
        if (moon) moon.classList.add('hidden');
    }
}
