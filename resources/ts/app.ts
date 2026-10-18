import '@fontsource-variable/inter';
import '@fontsource-variable/fraunces';
import '../css/app.css';

import { initNavDrawer } from './modules/nav-drawer';
import { initFlash } from './modules/flash';
import { initConfirmDialog } from './modules/confirm-dialog';
import { initSubmitGuard } from './modules/submit-guard';
import { initTransactionForm } from './modules/transaction-form';

document.addEventListener('DOMContentLoaded', () => {
    initNavDrawer();
    initFlash();
    initConfirmDialog();
    initSubmitGuard();
    initTransactionForm();
});
