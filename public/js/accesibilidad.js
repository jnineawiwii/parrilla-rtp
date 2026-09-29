(function () {
    const STORAGE_KEY = 'rtp-accessibility-settings';
    const defaults = { theme: 'system', fontSize: 100, contrast: false, accent: '#a51d4b' };
    const body = document.body;
    const panel = document.getElementById('accessibilityPanel');
    const toggle = document.getElementById('accessibilityToggle');
    if (!panel || !toggle) return;

    let settings = { ...defaults };
    try {
        settings = { ...defaults, ...JSON.parse(localStorage.getItem(STORAGE_KEY) || '{}') };
    } catch (error) {
        localStorage.removeItem(STORAGE_KEY);
    }

    function applySettings() {
        const systemPrefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        body.dataset.theme = settings.theme === 'system' && systemPrefersDark ? 'dark' : settings.theme;
        body.classList.toggle('high-contrast', settings.contrast);
        body.style.fontSize = `${settings.fontSize}%`;
        body.style.setProperty('--vino', settings.accent);
        document.getElementById('highContrast').checked = settings.contrast;
        document.getElementById('accessibilityFontSize').textContent = `${settings.fontSize}%`;
        panel.querySelectorAll('[data-theme]').forEach(button => {
            button.setAttribute('aria-pressed', String(button.dataset.theme === settings.theme));
        });
        panel.querySelectorAll('[data-accent]').forEach(button => {
            button.style.setProperty('--swatch', button.dataset.accent);
            button.setAttribute('aria-pressed', String(button.dataset.accent === settings.accent));
        });
    }

    function saveSettings() {
        localStorage.setItem(STORAGE_KEY, JSON.stringify(settings));
        const saved = document.getElementById('accessibilitySaved');
        saved.textContent = 'Guardado';
        window.setTimeout(() => { saved.textContent = 'Auto-guardado'; }, 1200);
        applySettings();
    }

    toggle.addEventListener('click', () => {
        const open = panel.hidden;
        panel.hidden = !open;
        toggle.setAttribute('aria-expanded', String(open));
    });

    panel.addEventListener('click', event => {
        const themeButton = event.target.closest('[data-theme]');
        const accentButton = event.target.closest('[data-accent]');
        const fontButton = event.target.closest('[data-font-step]');
        if (themeButton) settings.theme = themeButton.dataset.theme;
        if (accentButton) settings.accent = accentButton.dataset.accent;
        if (fontButton) settings.fontSize = Math.max(85, Math.min(130, settings.fontSize + Number(fontButton.dataset.fontStep) * 5));
        if (themeButton || accentButton || fontButton) saveSettings();
    });

    document.getElementById('highContrast').addEventListener('change', event => {
        settings.contrast = event.target.checked;
        saveSettings();
    });

    document.getElementById('accessibilityReset').addEventListener('click', () => {
        settings = { ...defaults };
        saveSettings();
    });

    document.addEventListener('click', event => {
        if (!event.target.closest('.accessibility-menu')) {
            panel.hidden = true;
            toggle.setAttribute('aria-expanded', 'false');
        }
    });

    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => {
        if (settings.theme === 'system') applySettings();
    });

    applySettings();
})();