import { createRouter, createWebHistory } from 'vue-router'
import Login from '@/pages/LoginView.vue'
import Register from '@/pages/RegisterView.vue'
import Dashboard from '@/pages/teacher/DashboardView.vue'
import Settings from '@/pages/SettingsView.vue'
import StudentDashboard from '@/pages/student/DashboardView.vue'
import StudentsView from '@/pages/teacher/StudentsView.vue'
import CoursesView from '@/pages/teacher/courses/CoursesView.vue'
import CourseInfoView from '@/pages/teacher/courses/CourseInfoView.vue'
import ActivityFormView from '@/pages/teacher/courses/ActivityFormView.vue'
import { USER_TYPE } from '@/shared/constants'
import { useAuthStore } from '@/stores/auth'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      redirect: '/login',
    },
    {
      path: '/login',
      name: 'login',
      component: Login,
      beforeEnter: (to, from, next) => {
        const authStore = useAuthStore()
        if (authStore.isAuthenticated) {
          const userType = authStore.currentUser?.type

          if (userType == USER_TYPE.TEACHER) {
            return next('/dashboard')
          }
          return next('/student/dashboard')
        }
        next()
      },
    },
    {
      path: '/register',
      name: 'register',
      component: Register,
      beforeEnter: (to, from, next) => {
        const authStore = useAuthStore()
        if (authStore.isAuthenticated) {
          const userType = authStore.currentUser?.type

          if (userType == USER_TYPE.TEACHER) {
            return next('/dashboard')
          }
          return next('/student/dashboard')
        }
        next()
      },
    },
    {
      path: '/settings',
      name: 'settings',
      meta: { title: 'Settings', description: 'Manage your account preference, and security options.'},
      component: Settings,
      beforeEnter: (to, from, next) => {
        const authStore = useAuthStore()
        if (!authStore.isAuthenticated) {
          return next('/login')
        }
        next()
      },
    },

    // Teacher Routes Parent (Single Checker)
    {
      path: '/',
      beforeEnter: (to, from, next) => {
        const authStore = useAuthStore()
        if (!authStore.isAuthenticated) {
          return next('/login')
        }
        if (authStore.currentUser?.type !== USER_TYPE.TEACHER) {
          return next('/student/dashboard')
        }
        next()
      },
      children: [
        {
          path: 'dashboard',
          name: 'teacher-dashboard',
          component: Dashboard,
        },
        {
          path: 'students',
          name: 'teacher-students',
          component: StudentsView,
          meta: { title: 'Students', description: 'Manage & track student enrollments'},
        },
        // Courses 
        {
          path: 'courses',
          name: 'teacher-courses',
          component: CoursesView,
          meta: { title: 'Courses', description: 'Manage learning programs' },
        },
        {
          path: 'courses/:id',
          name: 'course-info',
          component: CourseInfoView,
          meta: { title: 'Course', description: '' }
        },
        {
          path: 'courses/:id/activities/create',
          name: 'activity-create',
          component: ActivityFormView
        },
        {
          path: 'courses/:id/activities/:activityId/edit',
          name: 'activity-edit',
          component: ActivityFormView
        }
      ],
    },

    // Student Routes Parent (Single Checker)
    {
      path: '/student',
      beforeEnter: (to, from, next) => {
        const authStore = useAuthStore()
        if (!authStore.isAuthenticated) {
          return next('/login')
        }
        if (authStore.currentUser?.type === USER_TYPE.TEACHER) {
          return next('/dashboard')
        }
        next()
      },
      children: [
        {
          path: '',
          redirect: '/student/dashboard',
        },
        {
          path: 'dashboard',
          name: 'student-dashboard',
          component: StudentDashboard,
        },
      ],
    },

    // Catch-all 404 Route
    {
      path: '/:pathMatch(.*)*',
      redirect: '/',
    },
  ],
})

export default router