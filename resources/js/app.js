import './bootstrap';

import Alpine from 'alpinejs';
import Chart from 'chart.js/auto';

window.Chart = Chart;
window.Alpine = Alpine;

Alpine.start();

// !! FUNGSI UNTUK DARK MODE TOGGLE !!

// Initialize dark mode on page load
function initTheme() {
    const htmlEl = document.documentElement;
    const savedTheme = localStorage.getItem('theme');

    if (savedTheme) {
        // Use saved preference
        if (savedTheme === 'dark') {
            htmlEl.classList.add('dark');
        } else {
            htmlEl.classList.remove('dark');
        }
    } else {
        // Check system preference
        if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
            htmlEl.classList.add('dark');
            localStorage.setItem('theme', 'dark');
        } else {
            htmlEl.classList.remove('dark');
            localStorage.setItem('theme', 'light');
        }
    }
}

// Toggle dark mode
function toggleTheme() {
    const htmlEl = document.documentElement;
    if (htmlEl.classList.contains('dark')) {
        htmlEl.classList.remove('dark');
        localStorage.setItem('theme', 'light');
    } else {
        htmlEl.classList.add('dark');
        localStorage.setItem('theme', 'dark');
    }

    // Dispatch event for any components that need to react to theme change
    window.dispatchEvent(new CustomEvent('theme-changed', {
        detail: { theme: htmlEl.classList.contains('dark') ? 'dark' : 'light' }
    }));
}

// Get current theme
function getTheme() {
    return document.documentElement.classList.contains('dark') ? 'dark' : 'light';
}

// Listen for system theme changes
if (window.matchMedia) {
    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
        if (!localStorage.getItem('theme')) {
            const htmlEl = document.documentElement;
            if (e.matches) {
                htmlEl.classList.add('dark');
            } else {
                htmlEl.classList.remove('dark');
            }
        }
    });
}

// Initialize theme immediately (before Alpine)
initTheme();

// Make functions global
window.toggleTheme = toggleTheme;
window.getTheme = getTheme;
window.initTheme = initTheme;

// !! AKHIR FUNGSI DARK MODE TOGGLE !!