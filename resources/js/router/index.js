import { createRouter, createWebHistory, createWebHashHistory } from 'vue-router';
import Home from '../views/Home.vue';
import CreateReport from '../views/CreateReport.vue';
import ReportHistory from '../views/ReportHistory.vue';
import TemplateList from '../views/TemplateList.vue';
import TemplateCreate from '../views/TemplateCreate.vue';
import ReportPreview from '../views/ReportPreview.vue';

const routes = [
    {
        path: '/',
        name: 'home',
        component: Home,
        meta: { title: 'PELAPORAN PENGAWASAN LINGKUNGAN KANTOR BPSDM' },
    },
    {
        path: '/create',
        name: 'create-report',
        component: CreateReport,
        meta: { title: 'Buat Laporan Baru' },
    },
    {
        path: '/history',
        name: 'report-history',
        component: ReportHistory,
        meta: { title: 'Riwayat Laporan' },
    },
    {
        path: '/templates',
        name: 'template-list',
        component: TemplateList,
        meta: { title: 'Daftar Template' },
    },
    {
        path: '/templates/create',
        name: 'template-create',
        component: TemplateCreate,
        meta: { title: 'Buat Template' },
    },
    {
        path: '/preview/:id?',
        name: 'report-preview',
        component: ReportPreview,
        meta: { title: 'Preview Laporan' },
    },
    {
        path: '/:pathMatch(.*)*',
        redirect: '/',
    },
];

// In Capacitor (file:// or local bundle), hash mode is safest, but web history works with Web.
// Using web hash history allows seamless operation both in web and Capacitor native WebView!
const isCapacitorOrFile = window.location.protocol === 'file:' || window.location.hostname === 'localhost' && window.location.port === '';
const history = isCapacitorOrFile ? createWebHashHistory() : createWebHistory();

const router = createRouter({
    history,
    routes,
    scrollBehavior() {
        return { top: 0 };
    },
});

export default router;

