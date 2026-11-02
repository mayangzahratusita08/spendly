/*
 * Spendly M3 Expressive playground interactions.
 * M3 Expressive Web 1.8.0, with the upstream shape and spring ports.
 */
import { createTheme } from 'https://esm.sh/@kyralabs/m3exp-web@1.8.0/theme?bundle';
import { Morph, materialShape, materialShapeNames, morphToPath, polygonToPath, toSvgPathData } from 'https://esm.sh/@kyralabs/m3e-shapes@1.8.0?bundle';
import { springDurationMillis, springValue } from 'https://esm.sh/@kyralabs/m3e-motion@1.8.0?bundle';
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
const connectedGroup = document.getElementById('connected-period');
const periodFeedback = document.getElementById('lab-period-feedback');
const cashFlowGroup = document.getElementById('cash-flow-range');
const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)');

function announce(message) {
  if (liveRegion) liveRegion.textContent = message;
}

/* Dynamic expressive color scheme ---------------------------------------------------------- */

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
      seed,
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

    if (themeFeedback) themeFeedback.textContent = 'Expressive scheme · ' + label + ' seed';
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

/* Real AndroidX / Material 3 shape morphs --------------------------------------------------- */

const shapePath = document.getElementById('lab-morph-path');
const shapeSvg = document.getElementById('lab-morph-svg');
const shapePair = document.getElementById('lab-morph-pair');
const shapeProgress = document.getElementById('lab-morph-progress');
const shapeState = document.getElementById('lab-morph-state');
const shapePresetContainer = document.getElementById('lab-shape-presets');
const allShapesSelect = document.getElementById('lab-all-shapes');
const shapePlayButton = document.getElementById('lab-morph-play');
const shapeNextButton = document.getElementById('lab-morph-next');
const heroMorphPath = document.getElementById('lab-hero-morph-path');
const heroBackPath = document.getElementById('lab-hero-shape-back');

const shapeNames = [...materialShapeNames];

let activeShapeName = 'Cookie4Sided';
let targetShapeName = 'Heart';
let activeMorph = null;
let morphFrame = 0;
let morphSequenceTimer = 0;
let morphProgressValue = 0;
let morphSequenceRunning = false;
let heroMorphFrame = 0;
let heroMorphIndex = 0;
const heroPair = ['Cookie4Sided', 'Flower', 'Heart', 'Clover8Leaf', 'Sunny'];

function svgPathFromPolygon(name) {
  return toSvgPathData(polygonToPath(materialShape(name)));
}

function renderShapePresetThumbnails() {
  shapePresetContainer?.querySelectorAll('[data-shape]').forEach((button) => {
    const path = button.querySelector('path');
    if (path) path.setAttribute('d', svgPathFromPolygon(button.dataset.shape));
    const selected = button.dataset.shape === targetShapeName;
    button.classList.toggle('is-selected', selected);
    button.setAttribute('aria-pressed', String(selected));
  });
}

function renderMorphAt(morph, progress) {
  if (!shapePath) return;
  const path = morphToPath(morph, progress, {
    rotationPivotX: 0.5,
    rotationPivotY: 0.5,
  });
  shapePath.setAttribute('d', toSvgPathData(path));
  morphProgressValue = progress;
  if (shapeProgress) shapeProgress.textContent = Math.round(progress * 100) + '%';
}

function setMorphState(value) {
  if (shapeState) shapeState.textContent = value;
}

function morphTo(name, options = {}) {
  if (!shapePath || !name || name === activeShapeName && !morphFrame) return;

  if (morphFrame) cancelAnimationFrame(morphFrame);
  if (morphSequenceTimer) window.clearTimeout(morphSequenceTimer);
  if (!options.sequence) {
    morphSequenceRunning = false;
    shapePlayButton?.classList.remove('is-playing');
    if (shapePlayButton) {
      shapePlayButton.innerHTML = '<span class="m3e-icon" aria-hidden="true">play_arrow</span> Play sequence';
    }
  }

  const fromName = activeShapeName;
  targetShapeName = name;
  activeMorph = new Morph(materialShape(fromName), materialShape(name));
  if (shapePair) shapePair.textContent = fromName + ' → ' + name;
  renderShapePresetThumbnails();
  if (allShapesSelect) allShapesSelect.value = name;

  if (reduceMotion.matches || options.instant) {
    renderMorphAt(activeMorph, 1);
    activeShapeName = name;
    setMorphState('SETTLED');
    return;
  }

  const duration = springDurationMillis({ dampingRatio: 0.6, stiffness: 800 }, 0, 1);
  const startedAt = performance.now();
  setMorphState('MORPHING');

  const tick = (now) => {
    const elapsed = now - startedAt;
    const value = springValue(
      { dampingRatio: 0.6, stiffness: 800 },
      elapsed,
      0,
      1,
      0,
    );
    renderMorphAt(activeMorph, value);
    if (elapsed < duration + 80) {
      morphFrame = requestAnimationFrame(tick);
    } else {
      renderMorphAt(activeMorph, 1);
      activeShapeName = name;
      morphFrame = 0;
      setMorphState('SETTLED');
      if (morphSequenceRunning) scheduleNextMorph();
    }
  };
  morphFrame = requestAnimationFrame(tick);
}

function scheduleNextMorph() {
  morphSequenceTimer = window.setTimeout(() => {
    const current = shapeNames.indexOf(activeShapeName);
    const next = shapeNames[(current + 1) % shapeNames.length] ?? shapeNames[0];
    morphTo(next, { sequence: true });
  }, 340);
}

function startMorphSequence() {
  if (reduceMotion.matches) {
    announce('Shape sequence is disabled by reduced-motion preferences.');
    return;
  }
  morphSequenceRunning = true;
  shapePlayButton?.classList.add('is-playing');
  if (shapePlayButton) shapePlayButton.innerHTML = '<span class="m3e-icon" aria-hidden="true">pause</span> Stop sequence';
  setMorphState('SEQUENCE');
  const current = shapeNames.indexOf(activeShapeName);
  morphTo(shapeNames[(current + 1) % shapeNames.length] ?? shapeNames[0], { sequence: true });
  morphSequenceRunning = true;
}

function stopMorphSequence() {
  morphSequenceRunning = false;
  window.clearTimeout(morphSequenceTimer);
  if (morphFrame) cancelAnimationFrame(morphFrame);
  morphFrame = 0;
  shapePlayButton?.classList.remove('is-playing');
  if (shapePlayButton) shapePlayButton.innerHTML = '<span class="m3e-icon" aria-hidden="true">play_arrow</span> Play sequence';
  setMorphState('PAUSED');
}

if (allShapesSelect) {
  allShapesSelect.replaceChildren(...materialShapeNames.map((name) => {
    const option = document.createElement('option');
    option.value = name;
    option.textContent = name;
    return option;
  }));
  allShapesSelect.value = activeShapeName;
  allShapesSelect.addEventListener('change', () => {
    morphTo(allShapesSelect.value);
    announce(allShapesSelect.value + ' selected from the full Material Shapes collection.');
  });
}

shapePresetContainer?.querySelectorAll('[data-shape]').forEach((button) => {
  button.addEventListener('click', () => {
    morphTo(button.dataset.shape);
    announce((button.dataset.shape ?? 'Material') + ' shape selected. Morph animation started.');
  });
});

shapeNextButton?.addEventListener('click', () => {
  const current = shapeNames.indexOf(activeShapeName);
  const next = shapeNames[(current + 1) % shapeNames.length] ?? shapeNames[0];
  morphTo(next);
});

shapePlayButton?.addEventListener('click', () => {
  if (morphSequenceRunning) stopMorphSequence();
  else startMorphSequence();
});

/* Hero morph: continuously cycles through authentic Material shape presets. */
function animateHeroMorph() {
  if (!heroMorphPath || reduceMotion.matches) return;
  const from = heroPair[heroMorphIndex % heroPair.length] ?? 'Cookie4Sided';
  const to = heroPair[(heroMorphIndex + 1) % heroPair.length] ?? 'Heart';
  const morph = new Morph(materialShape(from), materialShape(to));
  const duration = springDurationMillis({ dampingRatio: 0.8, stiffness: 380 }, 0, 1);
  const start = performance.now();

  const tick = (now) => {
    const elapsed = now - start;
    const value = springValue({ dampingRatio: 0.8, stiffness: 380 }, elapsed, 0, 1);
    const path = morphToPath(morph, value, { rotationPivotX: 0.5, rotationPivotY: 0.5 });
    heroMorphPath.setAttribute('d', toSvgPathData(path));
    if (elapsed < duration + 60) {
      heroMorphFrame = requestAnimationFrame(tick);
    } else {
      const finalPath = morphToPath(morph, 1, { rotationPivotX: 0.5, rotationPivotY: 0.5 });
      heroMorphPath.setAttribute('d', toSvgPathData(finalPath));
      heroMorphIndex += 1;
      window.setTimeout(animateHeroMorph, 260);
    }
  };
  heroMorphFrame = requestAnimationFrame(tick);
}

if (heroMorphPath) {
  heroMorphPath.setAttribute('d', svgPathFromPolygon('Cookie4Sided'));
  animateHeroMorph();
}
if (heroBackPath) heroBackPath.setAttribute('d', svgPathFromPolygon('Sunny'));
if (shapePath) {
  shapePath.setAttribute('d', svgPathFromPolygon(activeShapeName));
  renderMorphAt(new Morph(materialShape(activeShapeName), materialShape(targetShapeName)), 0);
}
renderShapePresetThumbnails();
if (shapePair) shapePair.textContent = activeShapeName + ' → ' + targetShapeName;
setMorphState('READY');

/* Spring physics workbench ----------------------------------------------------------------- */

const springOrb = document.getElementById('lab-spring-orb');
const springTrack = document.getElementById('lab-spring-track');
const springName = document.getElementById('lab-spring-name');
const springDescription = document.getElementById('lab-spring-description');
const springDamping = document.getElementById('lab-spring-damping');
const springStiffness = document.getElementById('lab-spring-stiffness');
const springDuration = document.getElementById('lab-spring-duration');
const springPosition = document.getElementById('lab-spring-position');
const springReplay = document.getElementById('lab-spring-replay');

const springPresets = {
  'standard-fast': {
    label: 'Standard · Fast spatial',
    description: 'Quick movement with a restrained overshoot.',
    dampingRatio: 0.9,
    stiffness: 1400,
  },
  'expressive-fast': {
    label: 'Expressive · Fast spatial',
    description: 'More overshoot, lower damping, and a longer return to rest.',
    dampingRatio: 0.6,
    stiffness: 800,
  },
  'expressive-default': {
    label: 'Expressive · Default spatial',
    description: 'A softer, more buoyant transition with a longer travel time.',
    dampingRatio: 0.8,
    stiffness: 380,
  },
};

let activeSpringPreset = 'expressive-fast';
let springFrame = 0;

function animateSpring(presetKey) {
  const preset = springPresets[presetKey] ?? springPresets['expressive-fast'];
  if (!springOrb || !springTrack) return;
  if (springFrame) cancelAnimationFrame(springFrame);
  activeSpringPreset = presetKey;

  const spec = { dampingRatio: preset.dampingRatio, stiffness: preset.stiffness };
  const duration = springDurationMillis(spec, 0, 1);
  const start = performance.now();
  const trackWidth = springTrack.clientWidth;
  const maxX = Math.max(0, trackWidth - springOrb.offsetWidth - 24);
  springOrb.style.left = '12px';

  if (springName) springName.textContent = preset.label;
  if (springDescription) springDescription.textContent = preset.description;
  if (springDamping) springDamping.textContent = preset.dampingRatio.toFixed(1);
  if (springStiffness) springStiffness.textContent = String(preset.stiffness);
  if (springDuration) springDuration.textContent = Math.round(duration) + ' ms';

  document.querySelectorAll('[data-spring-preset]').forEach((button) => {
    const selected = button.dataset.springPreset === presetKey;
    button.classList.toggle('is-selected', selected);
    button.setAttribute('aria-pressed', String(selected));
  });

  const tick = (now) => {
    const elapsed = now - start;
    const value = springValue(spec, elapsed, 0, 1);
    const x = Math.max(0, Math.min(maxX, maxX * value));
    springOrb.style.transform = 'translateX(' + x.toFixed(2) + 'px)';
    if (springPosition) springPosition.textContent = value.toFixed(2);
    if (elapsed < duration + 80) {
      springFrame = requestAnimationFrame(tick);
    } else {
      springOrb.style.transform = 'translateX(' + maxX.toFixed(2) + 'px)';
      if (springPosition) springPosition.textContent = '1.00';
      springFrame = 0;
    }
  };

  if (reduceMotion.matches) {
    springOrb.style.transform = 'translateX(' + maxX.toFixed(2) + 'px)';
    if (springPosition) springPosition.textContent = '1.00';
  } else {
    springFrame = requestAnimationFrame(tick);
  }
}

document.querySelectorAll('[data-spring-preset]').forEach((button) => {
  button.addEventListener('click', () => animateSpring(button.dataset.springPreset));
});
springReplay?.addEventListener('click', () => animateSpring(activeSpringPreset));

/* Existing component workbench interactions ------------------------------------------------ */

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
    periodFeedback.textContent = normalized.charAt(0).toUpperCase() + normalized.slice(1) + ' selected. The pressed item expands against its neighbors.';
  }
  announce('Standard button group selection changed to ' + value + '.');
});

connectedGroup?.addEventListener('selection-change', (event) => {
  const value = event.detail?.value ?? event.detail?.values?.[0] ?? 'bars';
  announce('Connected button group selected ' + value + '. The selected shape has morphed.');
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

/* Accessibility: stop decorative morphing when the user requests less motion. */
function syncReducedMotion() {
  if (reduceMotion.matches) {
    if (heroMorphFrame) cancelAnimationFrame(heroMorphFrame);
    heroMorphFrame = 0;
    if (morphFrame) cancelAnimationFrame(morphFrame);
    morphFrame = 0;
    if (springFrame) cancelAnimationFrame(springFrame);
    springFrame = 0;
    if (activeMorph) renderMorphAt(activeMorph, 1);
    if (springPosition) springPosition.textContent = '1.00';
  } else {
    animateHeroMorph();
  }
}
reduceMotion.addEventListener?.('change', syncReducedMotion);

updateThemeToggle();
applyExpressiveSeed('#416B50', 'Forest green');
animateSpring('expressive-fast');

/* Keep native controls usable even if a remote component fails to initialize. */
window.addEventListener('error', (event) => {
  if (String(event.message ?? '').includes('Failed to fetch dynamically imported module')) {
    announce('A remote M3 Expressive component did not load. Check the network connection and reload this page.');
  }
});
