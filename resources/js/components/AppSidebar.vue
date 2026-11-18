<script setup lang="ts">
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { LayoutGrid, User, Wallet, HandCoins, Users, Stethoscope, UserCog, ShieldCheck, Landmark, CalendarDays, Banknote, Package } from '@lucide/vue';
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
import type { NavItem } from '@/types';
import { usePermisos } from '@/composables/usePermisos';

const { puede } = usePermisos();

const todos = [
    { title: 'Personas', href: '/personas', icon: Users, permiso: 'personas.ver' },
    { title: 'Patologías', href: '/catalogos/patologias', icon: Stethoscope, permiso: 'patologias.catalogo' },
    { title: 'Agenda', href: '/agenda', icon: CalendarDays, permiso: 'agenda.ver' },

    { title: 'Caja', href: '/caja', icon: Landmark, permiso: 'caja.ver' },
    { title: 'Banco', href: '/banco', icon: Banknote, permiso: 'banco.ver' },
    { title: 'Cuotas', href: '/cuotas', icon: Wallet, permiso: 'cuotas.ver' },
    { title: 'Aportes', href: '/aportes', icon: HandCoins, permiso: 'aportes.ver' },

    { title: 'Usuarios', href: '/admin/usuarios', icon: UserCog, permiso: 'usuarios.gestionar' },
    { title: 'Profesiones', href: '/admin/profesiones', icon: ShieldCheck, permiso: 'profesiones.gestionar' },

    { title: 'Inventario', href: '/inventario', icon: Package, permiso: 'inventario.ver' },

];

const mainNavItems = computed(() =>
    [
        {
            title: 'Tablero',
            href: dashboard(),
            icon: LayoutGrid,
            permiso: null,
        },
        {
            title: 'Personas',
            href: '/personas?type=socio',
            icon: User,
            permiso: 'personas.ver',
        },
        {
            title: 'Agenda',
            href: '/agenda',
            icon: CalendarDays,
            permiso: 'agenda.ver',
        },
        {
            title: 'Caja',
            href: '/caja',
            icon: Landmark,
            permiso: 'caja.ver',
        },
        {
            title: 'Banco',
            href: '/banco',
            icon: Banknote,
            permiso: 'banco.ver',
        },
        {
            title: 'Cuotas',
            href: '/cuotas',
            icon: Wallet,
            permiso: 'cuotas.ver',
        },
        {
            title: 'Aportes',
            href: '/aportes',
            icon: HandCoins,
            permiso: 'aportes.ver',
        },
        {
            title: 'Usuarios',
            href: '/admin/usuarios',
            icon: Users,
            permiso: 'usuarios.gestionar',
        },
        {
            title: 'Profesiones',
            href: '/admin/profesiones',
            icon: Stethoscope,
            permiso: 'profesiones.gestionar',
        },
        {
            title: 'Inventario',
            href: '/inventario',
            icon: Package,
            permiso: 'inventario.ver',
        },
    ].filter((item) => !item.permiso || puede(item.permiso)),
);



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
                    <SidebarMenuButton size="lg" as-child class="group-data-[collapsible=icon]:h-10!">
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
