import { createRouter, createWebHistory } from 'vue-router';
import type { RouteRecordRaw } from 'vue-router';

// Import route modules
import authRoutes from './routes/auth';
import clientRoutes from './routes/client';
import ticketRoutes from './routes/tickets';
import errorRoutes from './routes/errors';
import adminRoutes from './routes/admin';

// Combine all routes
const routes: RouteRecordRaw[] = [...authRoutes, ...clientRoutes, ...ticketRoutes, ...adminRoutes, ...errorRoutes];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

export default router;
