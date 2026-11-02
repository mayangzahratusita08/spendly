/*
 * Spendly Material 3 Expressive playground interactions.
 * Third-party component and theme APIs are pinned to M3 Expressive Web 1.8.0.
 */
import { createTheme } from 'https://esm.sh/@kyralabs/m3exp-web@1.8.0/theme?bundle';
import 'https://esm.sh/@kyralabs/m3e-elements@1.8.0/button-group.js?bundle';
import 'https://esm.sh/@kyralabs/m3e-elements@1.8.0/fab-menu.js?bundle';
import 'https://esm.sh/@kyralabs/m3e-elements@1.8.0/linear-progress.js?bundle';
import 'https://esm.sh/@kyralabs/m3e-elements@1.8.0/loading-indicator.js?bundle';

const root = document.documentElement;
const liveRegion = document.getElementById('lab-live-region');
const themeToggle = document.getElementById('lab-theme-toggle');
const themeFeedback = document.getElementById('lab-theme-feedback');
const progressRange = document.getElementById('lab-progress-range');
const progressValue = document.getElementById('lab-progress-value');
const demoProgress = document.getElementById('lab-demo-progress');
const periodGroup = document.getElementById('workbench-period');
const periodFeedback = document.getElementById('lab-period-feedback');
const cashFlowGroup = document.getElementById('cash-flow-range');

function announce(message) {
  if (liveRegion) liveRegion.textContent = message;
}

function updateThemeToggle() {
  const dark = root.dataset.theme === 'dark';
  themeToggle?.setAttribute('aria-pressed', String(dark));
  themeToggle?.setAttribute('aria-label', dark ? 'Switch to light theme' : 'Switch to dark theme');
  const icon = themeToggle?.querySelector('.m3e-icon');
  if (icon) icon.textContent = dark ? 'light_mode' : 'dark_mode';
}

function applyExpressiveSeed(seed, label) {
  try {
    const theme = createTheme({
      seed: seed,
      variant: 'expressive',
      mode: 'system',
      specVersion: '2025',
    });

    let style = document.getElementById('lab-generated-theme');
    if (!style) {
      style = document.createElement('style');
      style.id = 'lab-generated-theme';
      document.head.append(style);
    }
    style.textContent = theme.cssText;

    document.querySelectorAll('[data-seed]').forEach((button) => {
      const selected = button.dataset.seed?.toLowerCase() === seed.toLowerCase();
      button.classList.toggle('is-selected', selected);
      button.setAttribute('aria-pressed', String(selected));
    });

    if (themeFeedback) {
      themeFeedback.textContent = 'Expressive scheme · ' + label + ' seed';
    }

    announce(label + ' expressive color scheme applied.');
  } catch (error) {
    console.error('[Spendly M3E Lab] Theme generation failed:', error);
    announce('The color theme could not be generated. The default theme remains active.');
  }
}

themeToggle?.addEventListener('click', () => {
  root.dataset.theme = root.dataset.theme === 'dark' ? 'light' : 'dark';
  updateThemeToggle();
  announce((root.dataset.theme === 'dark' ? 'Dark' : 'Light') + ' appearance enabled.');
});

document.querySelectorAll('[data-seed]').forEach((button) => {
  button.addEventListener('click', () => {
    const seed = button.dataset.seed;
    const label = button.getAttribute('aria-label')?.replace(/\s+theme$/i, '') ?? 'Selected';
    if (seed) applyExpressiveSeed(seed, label);
  });
});

progressRange?.addEventListener('input', () => {
  const value = Number(progressRange.value);
  const fraction = Math.max(0, Math.min(100, value)) / 100;
  if (demoProgress) demoProgress.value = fraction;
  if (progressValue) progressValue.textContent = String(Math.round(fraction * 100)) + '%';
  demoProgress?.setAttribute('aria-label', 'Monthly savings goal, ' + Math.round(fraction * 100) + ' percent');
});

periodGroup?.addEventListener('selection-change', (event) => {
  const value = event.detail?.value ?? event.detail?.values?.[0] ?? 'week';
  if (periodFeedback) {
    const normalized = String(value);
    periodFeedback.textContent = normalized.charAt(0).toUpperCase() + normalized.slice(1) + ' selected. Try another period.';
  }
  announce('Dashboard period changed to ' + value + '.');
});

cashFlowGroup?.addEventListener('selection-change', (event) => {
  const value = event.detail?.value ?? event.detail?.values?.[0] ?? 'week';
  announce('Illustrative cash flow range changed to ' + value + '. The sample chart remains illustrative.');
});

document.querySelectorAll('[data-lab-action]').forEach((button) => {
  button.addEventListener('click', () => {
    const action = button.dataset.labAction;
    const messages = {
      'add-transaction': 'Preview only: this is where the Add Transaction flow would begin.',
      'budget-options': 'Preview only: budget actions would open here.',
      'primary-button': 'Filled button pressed. This is the highest-emphasis action.',
      'tonal-button': 'Tonal button pressed. A lower-emphasis action.',
      'outlined-button': 'Outlined button pressed. A secondary action.',
      'text-button': 'Text action pressed. A low-emphasis action.',
    };
    if (action && messages[action]) announce(messages[action]);
  });
});

document.querySelector('m3e-fab-menu')?.addEventListener('action', (event) => {
  const action = event.detail?.value ?? 'action';
  announce('Preview only: ' + action + ' action selected. No transaction was created.');
});

updateThemeToggle();
applyExpressiveSeed('#416B50', 'Forest green');

/* Keep native controls usable even if the remote module import fails. */
window.addEventListener('error', (event) => {
  if (String(event.message ?? '').includes('Failed to fetch dynamically imported module')) {
    announce('A remote M3 Expressive component did not load. Check the network connection and reload this page.');
  }
});
