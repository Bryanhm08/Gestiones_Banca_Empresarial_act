// resources/js/app.js
import "../css/app.css"
import "./bootstrap"

import { createInertiaApp } from "@inertiajs/vue3"
import { resolvePageComponent } from "laravel-vite-plugin/inertia-helpers"
import { createApp, h } from "vue"
import { ZiggyVue } from "../../vendor/tightenco/ziggy"

import PrimeVue from "primevue/config"
import Aura from "@primeuix/themes/aura"
import { es } from "primelocale/js/es.js"
import ConfirmationService from "primevue/confirmationservice"
import ToastService from "primevue/toastservice"

import InputText from "primevue/inputtext"
import Password from "primevue/password"
import Checkbox from "primevue/checkbox"
import Button from "primevue/button"
import Divider from "primevue/divider"
import Card from "primevue/card"
import FloatLabel from "primevue/floatlabel"
import Message from "primevue/message"
import Tag from "primevue/tag"
import Toast from "primevue/toast"
import Carousel from "primevue/carousel"
import Paginator from "primevue/paginator"
import Menu from "primevue/menu"
import TabView from "primevue/tabview"
import TabPanel from "primevue/tabpanel"
import Column from "primevue/column"
import DataTable from "primevue/datatable"
import Dropdown from "primevue/dropdown"
import Dialog from "primevue/dialog"
import MultiSelect from "primevue/multiselect"
import Textarea from "primevue/textarea"
import FileUpload from "primevue/fileupload"
import ProgressBar from "primevue/progressbar"
import ConfirmDialog from "primevue/confirmdialog"
import InputMask from "primevue/inputmask"
import Tooltip from "primevue/tooltip"

const appName = import.meta.env.VITE_APP_NAME || "SGBE"

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob("./Pages/**/*.vue")
        ),
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) })

        app.use(plugin)
        app.use(ZiggyVue)
        app.use(ToastService)
        app.use(ConfirmationService)
        app.use(PrimeVue, {
            theme: {
                preset: Aura,
                options: { darkModeSelector: "none" },
            },
            locale: es,
            ripple: true,
        })

        const pv = {
            "p-input-text": InputText,
            "p-inputtext": InputText,
            "p-password": Password,
            "p-checkbox": Checkbox,
            "p-button": Button,
            "p-divider": Divider,
            "p-card": Card,
            "p-float-label": FloatLabel,
            "p-floatlabel": FloatLabel,
            "p-message": Message,
            "p-tag": Tag,
            "p-toast": Toast,
            "p-carousel": Carousel,
            "p-paginator": Paginator,
            "p-menu": Menu,
            "p-tab-view": TabView,
            "p-tabview": TabView,
            "p-tab-panel": TabPanel,
            "p-tabpanel": TabPanel,
            "p-column": Column,
            "p-data-table": DataTable,
            "p-dropdown": Dropdown,
            "p-dialog": Dialog,
            "p-multi-select": MultiSelect,
            "p-textarea": Textarea,
            "p-file-upload": FileUpload,
            "p-progress-bar": ProgressBar,
            "p-confirm-dialog": ConfirmDialog,
            "p-confirmdialog": ConfirmDialog,
            "p-inputmask": InputMask,
            "p-tooltip": Tooltip,
        }

        Object.entries(pv).forEach(([name, comp]) => app.component(name, comp))

        app.mount(el)
    },
    progress: { color: "#4B5563" },
})
