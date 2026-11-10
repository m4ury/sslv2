<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { LayoutGrid, User, Wallet, HandCoins, Users, Stethoscope, UserCog, ShieldCheck } from '@lucide/vue';
import AppLogo from '@/components/AppLogo.vue';
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import  personas  from '@/routes/personas';
/* import  socios  from '@/routes/socios'; */
import type { NavItem } from '@/types';
import { usePermisos } from '@/composables/usePermisos';

const { puede } = usePermisos();

const todos = [
    { title: 'Personas', href: '/personas', icon: Users, permiso: 'personas.ver' },
    { title: 'Cuotas', href: '/cuotas', icon: Wallet, permiso: 'cuotas.ver' },
    { title: 'Aportes', href: '/aportes', icon: HandCoins, permiso: 'aportes.ver' },
    { title: 'Patologías', href: '/catalogos/patologias', icon: Stethoscope, permiso: 'patologias.catalogo' },
    { title: 'Usuarios', href: '/admin/usuarios', icon: UserCog, permiso: 'usuarios.gestionar' },
    { title: 'Profesiones', href: '/admin/profesiones', icon: ShieldCheck, permiso: 'profesiones.gestionar' },
];

const mainNavItems: NavItem[] = [
    {
        title: 'Tablero',
        href: dashboard(),
        icon: LayoutGrid,
    },
    {
        title: 'Personas',
        href: personas.index.url(),
        icon: User,
    },
    {
        title: 'Cuotas',
        href: '/cuotas',
        icon: Wallet
    },

    {
        title: 'Aportes',
        href: '/aportes',
        icon: HandCoins
    },

    {
        title: 'Usuarios',
        href: '/admin/usuarios',
        icon: Users
    },

    {
        title: 'Profesiones',
        href: '/admin/profesiones',
        icon: Stethoscope
    },
];



const footerNavItems: NavItem[] = [
    /* {
        title: 'Repository',
        href: 'https://github.com/laravel/vue-starter-kit',
        icon: FolderGit2,
    },
    {
        title: 'Documentation',
        href: 'https://laravel.com/docs/starter-kits#vue',
        icon: BookOpen,
    }, */
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboard()">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
