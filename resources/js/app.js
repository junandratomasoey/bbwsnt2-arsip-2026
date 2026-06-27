import './bootstrap';

import Alpine from 'alpinejs';
import persist from '@alpinejs/persist';

// Plugin persist untuk menyimpan state sidebar ke localStorage
Alpine.plugin(persist);

// Start Alpine
window.Alpine = Alpine;
Alpine.start();
