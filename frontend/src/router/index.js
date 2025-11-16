import { createRouter, createWebHistory } from 'vue-router';
import Login from '../views/Login.vue';
import Dashboard from '../views/Dashboard.vue';
import StudentList from '../views/StudentList.vue';
import AttendanceRecording from '../views/AttendanceRecording.vue';
import DailyReport from '../views/reports/DailyReport.vue';
import WeeklyReport from '../views/reports/WeeklyReport.vue';
import MonthlyReport from '../views/reports/MonthlyReport.vue';
import Classes from '../views/Classes.vue';
import Sections from '../views/Sections.vue';
import Holidays from '../views/Holidays.vue';
import Profile from '../views/Profile.vue';
import Settings from '../views/Settings.vue';

const routes = [
  {
    path: '/login',
    name: 'Login',
    component: Login,
  },
  {
    path: '/',
    name: 'Dashboard',
    component: Dashboard,
    meta: { requiresAuth: true },
  },
  {
    path: '/students',
    name: 'StudentList',
    component: StudentList,
    meta: { requiresAuth: true },
  },
  {
    path: '/attendance',
    name: 'AttendanceRecording',
    component: AttendanceRecording,
    meta: { requiresAuth: true },
  },
  {
    path: '/reports/daily',
    name: 'DailyReport',
    component: DailyReport,
    meta: { requiresAuth: true },
  },
  {
    path: '/reports/weekly',
    name: 'WeeklyReport',
    component: WeeklyReport,
    meta: { requiresAuth: true },
  },
  {
    path: '/reports/monthly',
    name: 'MonthlyReport',
    component: MonthlyReport,
    meta: { requiresAuth: true },
  },
  {
    path: '/classes',
    name: 'Classes',
    component: Classes,
    meta: { requiresAuth: true },
  },
  {
    path: '/sections',
    name: 'Sections',
    component: Sections,
    meta: { requiresAuth: true },
  },
  {
    path: '/holidays',
    name: 'Holidays',
    component: Holidays,
    meta: { requiresAuth: true },
  },
  {
    path: '/profile',
    name: 'Profile',
    component: Profile,
    meta: { requiresAuth: true },
  },
  {
    path: '/settings',
    name: 'Settings',
    component: Settings,
    meta: { requiresAuth: true },
  },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

router.beforeEach((to, from, next) => {
  const token = localStorage.getItem('auth_token');
  if (to.meta.requiresAuth && !token) {
    next('/login');
  } else if (to.path === '/login' && token) {
    next('/');
  } else {
    next();
  }
});

export default router;
