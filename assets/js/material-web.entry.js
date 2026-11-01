/**
 * Spendly Material Web entry point.
 *
 * Keep imports selective so PHP pages only ship the Material 3 components
 * currently used by the application.
 */
import '@material/web/button/filled-button.js';
import '@material/web/button/outlined-button.js';
import '@material/web/button/text-button.js';
import '@material/web/textfield/outlined-text-field.js';
import '@material/web/select/outlined-select.js';
import '@material/web/select/select-option.js';
import '@material/web/progress/linear-progress.js';

import { styles as typeScaleStyles } from '@material/web/typography/md-typescale-styles.js';

if ('adoptedStyleSheets' in Document.prototype && typeScaleStyles?.styleSheet) {
  document.adoptedStyleSheets = [
    ...document.adoptedStyleSheets,
    typeScaleStyles.styleSheet,
  ];
}
