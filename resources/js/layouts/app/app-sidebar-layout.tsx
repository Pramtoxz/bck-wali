import { AppContent } from '@/components/app-content';
import { AppShell } from '@/components/app-shell';
import { AppSidebar } from '@/components/app-sidebar';
import { AppSidebarHeader } from '@/components/app-sidebar-header';
import { DevDatePanel } from '@/components/dev-date-panel';
import { Toaster } from '@/components/ui/sonner';
import { type BreadcrumbItem } from '@/types';
import { usePage } from '@inertiajs/react';

interface TestDateInfo {
    test_mode: boolean;
    date: string;
}

interface PageProps {
    test_date_info?: TestDateInfo;
}

interface AppSidebarLayoutProps {
    children: React.ReactNode;
    breadcrumbs?: BreadcrumbItem[];
}

export default function AppSidebarLayout({ children, breadcrumbs = [] }: AppSidebarLayoutProps) {
    const { props } = usePage<PageProps>();
    const testDateInfo = props.test_date_info;

    return (
        <AppShell variant="sidebar">
            <AppSidebar />
            <AppContent variant="sidebar">
                <AppSidebarHeader breadcrumbs={breadcrumbs} />
                {children}
            </AppContent>
            <DevDatePanel testDateInfo={testDateInfo} />
            <Toaster />
        </AppShell>
    );
}
