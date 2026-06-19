import { createInertiaApp, router } from '@inertiajs/vue3';
import { createSSRApp, h } from 'vue';
import NProgress from 'nprogress';
import 'nprogress/nprogress.css';

const appName = import.meta.env.VITE_APP_NAME || 'Simako';

NProgress.configure({ showSpinner: false, minimum: 0.15, trickleSpeed: 150 });

router.on('start', () => NProgress.start());
router.on('finish', () => NProgress.done());

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    progress: false,
    setup({ el, App, props, plugin }) {
        createSSRApp({ render: () => h(App, props) })
            .use(plugin)
            .mount(el);
    },
});
