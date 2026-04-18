import { NavFooter } from '@/components/nav-footer';
import { NavMain } from '@/components/nav-main';
import { NavUser } from '@/components/nav-user';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/react';
import { Clock, LayoutGrid, Users, MapPin, Briefcase, Building2, Plane, FileText } from 'lucide-react';
import AppLogo from './app-logo';

const navItems: NavItem[] = [
    {
        title: 'Dashboard',
        url: '/dashboard',
        icon: LayoutGrid,
    },
    {
        title: 'Absensi',
        url: '/attendances',
        icon: Clock,
    },
    {
        title: 'Dinas Luar',
        url: '/field-duties',
        icon: Plane,
    },
    {
        title: 'Izin & Cuti',
        url: '/leaves',
        icon: FileText,
    },
    {
        title: 'Manajemen User',
        url: '/users',
        icon: Users,
    },
    {
        title: 'Jabatan',
        url: '/positions',
        icon: Briefcase,
    },
    {
        title: 'Departemen',
        url: '/departments',
        icon: Building2,
    },
    {
        title: 'Lokasi Kantor',
        url: '/office-locations',
        icon: MapPin,
    },
];

const footerNavItems: NavItem[] = [
    // {
    //     title: 'Repository',
    //     url: 'https://github.com/laravel/react-starter-kit',
    //     icon: Folder,
    // },
    // {
    //     title: 'Documentation',
    //     url: 'https://laravel.com/docs/starter-kits',
    //     icon: BookOpen,
    // },
];

export function AppSidebar() {
    return (
        <Sidebar collapsible="icon" variant="inset">
            <SidebarHeader>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton size="lg" asChild>
                            <Link href="/dashboard" prefetch>
                                <AppLogo />
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarHeader>

            <SidebarContent>
                <NavMain items={navItems} />
            </SidebarContent>

            <SidebarFooter>
                <NavFooter items={footerNavItems} className="mt-auto" />
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}
