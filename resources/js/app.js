import Alpine from 'alpinejs';

import registerDialog from './modules/dialog.js';
import registerToast from './modules/toast.js';
import registerReservation from './modules/reservation.js';
import registerExport from './modules/export.js';
import registerLazyModal from './modules/lazy-modal.js';
import registerProgress from './modules/progress.js';
import registerScrollLock from './modules/scroll-lock.js';
import registerLayout from './modules/layout.js';
import registerTour from './modules/tour.js';

window.Alpine = Alpine;

// Register all stores/components and wire delegated listeners before starting.
[
    registerDialog,
    registerToast,
    registerReservation,
    registerExport,
    registerLazyModal,
    registerProgress,
    registerScrollLock,
    registerLayout,
    registerTour,
].forEach((register) => register(Alpine));

Alpine.start();
