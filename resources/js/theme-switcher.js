(function () {
    function applyTheme(theme) {
        if (theme === 'dark') {
            document.documentElement.classList.add('dark');
            document.documentElement.setAttribute('data-theme', 'dark');
        } else {
            document.documentElement.classList.remove('dark');
            document.documentElement.setAttribute('data-theme', 'light');
        }
    }

    // On load, prefer stored theme, otherwise default to light (ignore system preference)
    const stored = localStorage.getItem('sor3-theme');
    if (stored) {
        applyTheme(stored);
    } else {
        // Enforce light mode by default, ignoring system/browser profile
        applyTheme('light');
    }

    // Expose simple toggler
    window.sor3Theme = {
        set: function (t) { 
            localStorage.setItem('sor3-theme', t); 
            // Also update Filament's storage to keep them in sync if possible, 
            // though Filament uses its own logic.
            localStorage.setItem('theme', t); 
            applyTheme(t); 
        },
        clear: function () { 
            localStorage.removeItem('sor3-theme'); 
            localStorage.removeItem('theme');
            applyTheme('light'); 
        }
    };

    // Note: header toggle binding/injection intentionally disabled per project preference.
    // Keep theme initialization and API `window.sor3Theme` available for manual usage.
})();
