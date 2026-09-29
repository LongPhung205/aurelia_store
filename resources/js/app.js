import './bootstrap';
import { initFlowbite } from 'flowbite';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

if (typeof window !== 'undefined') {
    window.addEventListener('DOMContentLoaded', () => {
        initFlowbite();
    });
}
