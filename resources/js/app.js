import "../css/app.css";
import "./bootstrap";

import { createInertiaApp } from "@inertiajs/vue3";
import { resolvePageComponent } from "laravel-vite-plugin/inertia-helpers";
import { createApp, h } from "vue";
import { ZiggyVue } from "../../vendor/tightenco/ziggy";

/* Font Awesome Core */
import { library } from "@fortawesome/fontawesome-svg-core";
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome";
// USB is a Brand Icon
import { faUsb } from "@fortawesome/free-brands-svg-icons";

import Vue3Toastify from "vue3-toastify";
import "vue3-toastify/dist/index.css";
/* Import requested icons */
import {
    faArrowLeft,
    faArrowRightFromBracket,
    faArrowRotateLeft,
    faBagShopping,
    faBars,
    faBell,
    faBolt,
    faBox,
    faBuilding,
    faCakeCandles,
    faCamera,
    faChair,
    faChartBar,
    faCheck,
    faChevronDown,
    faChevronRight,
    faCircleCheck,
    faCircleNotch,
    faCircleXmark,
    faClipboardList,
    faClockRotateLeft,
    faComputerMouse,
    faDesktop,
    faEnvelope,
    faEye,
    faFileArrowUp,
    faFloppyDisk,
    faHeadset,
    faHourglassHalf,
    faKeyboard,
    faLaptop,
    faLink,
    faLock,
    faMobileScreen,
    faNetworkWired,
    faPlug,
    faPlus,
    faPlusCircle,
    faPrint,
    faRotateLeft,
    faScrewdriverWrench,
    faServer,
    faShieldHalved,
    faTableColumns,
    faTabletScreenButton,
    faTags,
    faTriangleExclamation,
    faUser,
    faUserPen,
    faUsers,
    faInbox,
    faSearch,
    faPen,
    faTrash,
    faBoxArchive,
} from "@fortawesome/free-solid-svg-icons";



/* Add all icons to the library */
library.add(
    faArrowLeft,
    faArrowRightFromBracket,
    faArrowRotateLeft,
    faBagShopping,
    faBars,
    faBell,
    faBolt,
    faBox,
    faBuilding,
    faCakeCandles,
    faCamera,
    faChair,
    faChartBar,
    faCheck,
    faChevronDown,
    faChevronRight,
    faCircleCheck,
    faCircleNotch,
    faCircleXmark,
    faClipboardList,
    faClockRotateLeft,
    faComputerMouse,
    faDesktop,
    faEye,
    faEnvelope,
    faFileArrowUp,
    faFloppyDisk,
    faHeadset,
    faKeyboard,
    faLaptop,
    faLink,
    faLock,
    faMobileScreen,
    faNetworkWired,
    faPlug,
    faPlus,
    faPlusCircle,
    faPrint,
    faRotateLeft,
    faScrewdriverWrench,
    faServer,
    faShieldHalved,
    faTableColumns,
    faTabletScreenButton,
    faTags,
    faTriangleExclamation,
    faUsb,
    faUser,
    faUserPen,
    faUsers,
    faHourglassHalf,
    faInbox,
    faSearch,
    faPen,
    faTrash,
    faBoxArchive,
    
);


const appName = import.meta.env.VITE_APP_NAME || "Laravel";

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob("./Pages/**/*.vue"),
        ),
    setup({ el, App, props, plugin }) {
        return (
            createApp({ render: () => h(App, props) })
                .use(plugin)
                .use(ZiggyVue)
                .use(Vue3Toastify, {
                    autoClose: 3000,
                    position: "top-right",
                    theme: "light",
                    clearOnUrlChange: false, // keeps toast visible during Inertia navigation
                    pauseOnHover: true,
                    closeOnClick: true,
                })
                // Registered globally as 'fa-icon' per your request
                .component("fa-icon", FontAwesomeIcon)
                .mount(el)
        );
    },
    progress: {
        color: "#4B5563",
    },
});
