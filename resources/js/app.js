import '../css/app.css';
import './bootstrap';

import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createApp, h } from 'vue';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';

/* Font Awesome Core */
import { library } from '@fortawesome/fontawesome-svg-core';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';

/* Import requested icons */
import { 
    faUser, 
    faEnvelope, 
    faLock, 
    faCircleCheck, 
    faCircleXmark, 
    faBox, 
    faTags, 
    faScrewdriverWrench, 
    faClipboardList, 
    faClockRotateLeft, 
    faLink, 
    faUsers, 
    faBuilding, 
    faFileArrowUp,
    faChartBar,
    faPlusCircle,
    faBell,
    faChevronDown,
    faChevronRight,
    faBars,
    faUserPen,
    faArrowRightFromBracket,
    faShieldHalved,
    faTriangleExclamation
} from '@fortawesome/free-solid-svg-icons';

import Vue3Toastify from 'vue3-toastify';
import 'vue3-toastify/dist/index.css';

/* Add all icons to the library */
library.add(
    faUser, faEnvelope, faLock, faCircleCheck, faCircleXmark, 
    faBox, faTags, faScrewdriverWrench, faClipboardList, 
    faClockRotateLeft, faLink, faUsers, faBuilding, 
    faFileArrowUp, faChartBar, faPlusCircle, faBell, 
    faChevronDown, faChevronRight, faBars, faUserPen, 
    faArrowRightFromBracket, faShieldHalved, faTriangleExclamation
);

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob('./Pages/**/*.vue'),
        ),
    setup({ el, App, props, plugin }) {
        return createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .use(Vue3Toastify, {
                autoClose:        3000,
                position:         'top-right',
                theme:            'light',
                clearOnUrlChange: false,   // keeps toast visible during Inertia navigation
                pauseOnHover:     true,
                closeOnClick:     true,
            })
            // Registered globally as 'fa-icon' per your request
            .component('fa-icon', FontAwesomeIcon)
            .mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});