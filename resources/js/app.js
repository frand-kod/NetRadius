import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createNotivue } from 'notivue';
import 'notivue/notification.css';
import 'notivue/animations.css';

const notivue = createNotivue({
    position: 'top-right',
    limit: 5,
    enqueue: true,
    pauseOnHover: true,
    pauseOnTabChange: true,
    avoidDuplicates: true,
    transition: 'transform 0.3s cubic-bezier(0.5, 1, 0.25, 1)',
    notifications: {
        success: { duration: 4000 },
        info: { duration: 4500 },
        warning: { duration: 5000 },
        error: { duration: 7000 },
    },
});

createInertiaApp({
    title: (title) => title ? `${title} - NetRadius` : 'NetRadius',
    resolve: (name) => resolvePageComponent(
        `./Pages/${name}.vue`,
        import.meta.glob('./Pages/**/*.vue')
    ),
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(notivue)
            .mount(el);
    },
});
