<script setup lang="ts">
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import { type AppPageProps, type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { BookMarked, Building2, LayoutGrid, MapPinned, PanelsTopLeft, School, ShieldCheck } from 'lucide-vue-next';
import { computed } from 'vue';
import AppLogo from './AppLogo.vue';

const page = usePage<AppPageProps>();
const roleName = computed(() => page.props.auth.user?.role?.name ?? '');

const canAccess = (...roles: string[]) => roles.includes(roleName.value);

const mainNavItems = computed<NavItem[]>(() => {
    const items: NavItem[] = [
        {
            title: 'Dashboard',
            href: dashboard(),
            icon: LayoutGrid,
        },
    ];

    if (canAccess('superadmin', 'admin_dpp', 'admin_dpw', 'admin_dpd')) {
        items.push({
            title: 'Anggota',
            href: '/anggota',
            icon: BookMarked,
        });
    }

    if (canAccess('superadmin', 'admin_dpp', 'admin_dpw')) {
        items.push({
            title: 'DPW Provinsi',
            href: '/dpw-provinsi',
            icon: ShieldCheck,
        });
    }

    if (canAccess('superadmin', 'admin_dpp', 'admin_dpw', 'admin_dpd')) {
        items.push(
            {
                title: 'DPD Kota/Kab',
                href: '/dpd-kota-kab',
                icon: Building2,
            },
            {
                title: 'DPC Kecamatan',
                href: '/dpc-kecamatan',
                icon: MapPinned,
            },
        );
    }

    if (canAccess('superadmin', 'admin_dpp', 'admin_dpw', 'admin_dpd', 'admin_dpc')) {
        items.push({
            title: 'Pagoruan',
            href: '/pagoruan',
            icon: School,
        });
    }

    if (canAccess('superadmin')) {
        items.push({
            title: 'Landing CMS',
            href: '/landing-cms',
            icon: PanelsTopLeft,
        });
    }

    return items;
});
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
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
