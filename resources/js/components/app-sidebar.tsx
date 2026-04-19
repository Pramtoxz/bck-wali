import { NavFooter } from '@/components/nav-footer';
import { NavMain } from '@/components/nav-main';
import { NavUser } from '@/components/nav-user';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavGroup, type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/react';
import { Clock, LayoutGrid, Users, MapPin, Briefcase, Building2, Plane, FileText, Calendar, CalendarDays, AlertCircle } from 'lucide-react';
import AppLogo from './app-logo';
import { Alert, AlertDescription } from './ui/alert';

interface TestDateInfo {
    test_mode: boolean;
    date: string;
}

interface PageProps extends Record<string, unknown> {
    test_date_info?: TestDateInfo;
}

const navGroups: NavGroup[] = [
        {
        title: 'Monitoring',
        items: [
            {
                title: 'Dashboard',
                url: '/dashboard',
                icon: LayoutGrid,
            },
        ],
    },
    {
        title: 'Master Data',
        items: [
            {
                title: 'Hari Libur',
                url: '/holidays',
                icon: CalendarDays,
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
        ],
    },
    {
        title: 'Menu Utama',
        items: [
            {
                title: 'Absensi',
                url: '/attendances',
                icon: Clock,
            },
            {
                title: 'Rekap Absensi',
                url: '/attendance-recap',
                icon: Calendar,
            },
        ],
    },
    {
        title: 'Pengajuan',
        items: [
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
        ],
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
    const { props } = usePage<PageProps>();
    const testDateInfo = props.test_date_info;
    const isTestMode = testDateInfo?.test_mode;
    const testDate = testDateInfo?.date;

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
                
                {isTestMode && (
                    <div className="px-2 py-2">
                        <Alert className="bg-yellow-50 border-yellow-300">
                            <AlertCircle className="h-4 w-4 text-yellow-600" />
                            <AlertDescription className="text-xs">
                                <div className="font-semibold text-yellow-800">Test Mode</div>
                                <div className="text-yellow-700 mt-1">
                                    {testDate && new Date(testDate).toLocaleDateString('id-ID', { 
                                        weekday: 'short',
                                        day: '2-digit', 
                                        month: 'short',
                                        year: 'numeric' 
                                    })}
                                </div>
                                <a 
                                    href="/dev/clear-date" 
                                    className="text-yellow-800 underline hover:text-yellow-900 text-xs mt-1 inline-block"
                                >
                                    Clear
                                </a>
                            </AlertDescription>
                        </Alert>
                    </div>
                )}
            </SidebarHeader>

            <SidebarContent>
                <NavMain groups={navGroups} />
            </SidebarContent>

            <SidebarFooter>
                <NavFooter items={footerNavItems} className="mt-auto" />
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}
