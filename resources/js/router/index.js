import { createRouter, createWebHistory } from 'vue-router';
import { useAuth } from '../composables/useAuth';

// Lazy load page components
const Login = () => import('../pages/auth/Login.vue');
const Register = () => import('../pages/auth/Register.vue');
const ForgotPassword = () => import('../pages/auth/ForgotPassword.vue');
const ResetPassword = () => import('../pages/auth/ResetPassword.vue');

// Placeholder components (to be implemented)
const Dashboard = () => import('../pages/Dashboard.vue');
const ProjectBoard = () => import('../pages/projects/Board.vue');
const NotFound = () => import('../pages/NotFound.vue');

const routes = [
    // Auth Routes (public)
    {
        path: '/auth/login',
        name: 'login',
        component: Login,
        meta: {
            public: true,
            layout: 'auth',
        },
    },
    {
        path: '/auth/register',
        name: 'register',
        component: Register,
        meta: {
            public: true,
            layout: 'auth',
        },
    },
    {
        path: '/auth/forgot-password',
        name: 'forgot-password',
        component: ForgotPassword,
        meta: {
            public: true,
            layout: 'auth',
        },
    },
    {
        path: '/auth/reset-password',
        name: 'reset-password',
        component: ResetPassword,
        meta: {
            public: true,
            layout: 'auth',
        },
    },

    // Protected Routes
    {
        path: '/dashboard',
        name: 'dashboard',
        component: Dashboard,
        meta: {
            requiresAuth: true,
            layout: 'app',
        },
    },
    {
        path: '/projects/:id/board',
        name: 'project-board',
        component: ProjectBoard,
        meta: {
            requiresAuth: true,
            layout: 'app',
        },
    },

    // Catch-all 404
    {
        path: '/:pathMatch(.*)*',
        name: 'not-found',
        component: NotFound,
    },

    // Redirect root to dashboard or login
    {
        path: '/',
        redirect: () => {
            const { isAuthenticated } = useAuth();
            return isAuthenticated.value ? '/dashboard' : '/auth/login';
        },
    },
];

const router = createRouter({
    history: createWebHistory(import.meta.env.BASE_URL),
    routes,
});

// Global navigation guards
router.beforeEach((to, from, next) => {
    const { isAuthenticated, fetchCurrentUser } = useAuth();

    // Try to fetch current user if we have a token but no user data
    if (isAuthenticated.value && !useAuth().user.value) {
        fetchCurrentUser().catch(() => {
            // Token is invalid, redirect to login
            next('/auth/login');
        });
    }

    // Check if route requires authentication
    if (to.meta.requiresAuth && !isAuthenticated.value) {
        // Redirect to login
        next({
            name: 'login',
            query: { redirect: to.fullPath },
        });
        return;
    }

    // Redirect authenticated users away from auth pages
    if (to.meta.public && isAuthenticated.value && to.path.startsWith('/auth')) {
        next('/dashboard');
        return;
    }

    next();
});

// Navigation error handling
router.afterEach((to) => {
    // Update page title based on route
    document.title = to.meta.title || 'ProjectHub';
});

export default router;
