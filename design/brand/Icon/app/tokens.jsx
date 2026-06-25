// COTA tokens + global type setup
// Exposes window.COTA with the brand palette + sizing helpers used across the app.

const COTA = {
  BG: '#0b0d10',
  BG2: '#15181d',
  BG3: '#1a1e25',
  LINE: '#1d2026',
  LINE2: '#2a2e36',
  INK: '#f4efe2',
  INK2: '#c7c4b8',
  DIM: '#8b8a85',
  DIM2: '#5a5d63',
  ACCENT: '#e8ff36',
  ACCENT_DIM: 'rgba(232,255,54,0.12)',
  WIN: '#3ddc91',
  LOSS: '#ff5b3a',
  font: {
    title: '"Archivo Black", "Archivo", sans-serif',
    ui:    '"Space Grotesk", system-ui, sans-serif',
    mono:  '"JetBrains Mono", monospace',
  },
};

window.COTA = COTA;
