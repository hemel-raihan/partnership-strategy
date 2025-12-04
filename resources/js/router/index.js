import Vue from 'vue'
import VueRouter from 'vue-router'
import Login from '../views/auth/Login.vue';
import Main from '../components/layouts/Main';
import Dashboard from '../views/dashboard/DashboardIndex';
import NotFound from '../views/404/Index';
import {baseurl} from '../base_url'
import survey from '../views/survey/Index';
// import test from '../views/test/Index';

Vue.use(VueRouter);

const config = () => {
    let token = localStorage.getItem('token');
    return {
        headers: {Authorization: `Bearer ${token}`}
    };
}
const checkToken = (to, from, next) => {
    let token = localStorage.getItem('token');
    if (token === 'undefined' || token === null || token === '') {
        next(baseurl + 'login');
    } else {
        next();
    }
};

const activeToken = (to, from, next) => {
    let token = localStorage.getItem('token');
    if (token === 'undefined' || token === null || token === '') {
        next();
    } else {
        next(baseurl);
    }
};

const routes = [
    {
        path: baseurl,
        component: Main,
        redirect: {name: 'survey'},
        children: [
            {
                path: baseurl + 'dashboard',
                name: 'Dashboard',
                component: Dashboard
            },
            // {
            //     path: baseurl + 'config-settings',
            //     name: 'ConfigSettings',
            //     component: ConfigSettings
            // },

            // {
            //     path: baseurl + 'test',
            //     name: 'test',
            //     component: test
            // }
        ],
        beforeEnter(to, from, next) {
            checkToken(to, from, next);
        }
    },
    {
        path: baseurl + 'login',
        name: 'Login',
        component: Login,
        beforeEnter(to, from, next) {
            activeToken(to, from, next);
        }
    },
    {
        path: baseurl + 'survey',
        name: 'survey',
        component: survey
    },
    {
        path: baseurl + '*',
        name: 'NotFound',
        component: NotFound,
    },
]

const router = new VueRouter({
    mode: 'history',
    base: process.env.baseurl,
    routes
});

router.afterEach(() => {
    $('#preloader').hide();
});

export default router
