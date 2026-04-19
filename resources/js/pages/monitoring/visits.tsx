import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Head, router } from '@inertiajs/react';
import { Search, ChevronLeft, ChevronRight, MousePointerClick, Users, UserCheck, Globe } from 'lucide-react';
import { useState, useEffect } from 'react';

interface UserVisit {
    id: number;
    user: {
        id: number;
        name: string;
        nip: string;
        avatar: string;
    } | null;
    url: string;
    method: string;
    ip_address: string;
    device: string | null;
    platform: string | null;
    browser: string | null;
    created_at: string;
}

interface Props {
    visits: {
        data: UserVisit[];
        links: Array<{
            url: string | null;
            label: string;
            active: boolean;
        }>;
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        from: number | null;
        to: number | null;
    };
    summary: {
        total_visits: number;
        today_visits: number;
        unique_users: number;
        guest_visits: number;
    };
    filters: {
        search: string;
        per_page: number;
        date_from: string | null;
        date_to: string | null;
    };
}

export default function MonitoringVisits({ visits, summary, filters }: Props) {
    const [search, setSearch] = useState(filters.search || '');
    const [dateFrom, setDateFrom] = useState(filters.date_from || new Date().toISOString().split('T')[0]);
    const [dateTo, setDateTo] = useState(filters.date_to || new Date().toISOString().split('T')[0]);
    const perPage = filters.per_page;

    useEffect(() => {
        const timer = setTimeout(() => {
            router.get(
                '/monitoring/visits',
                { search, per_page: perPage, date_from: dateFrom || undefined, date_to: dateTo || undefined },
                { preserveState: true, replace: true, only: ['visits', 'summary'] },
            );
        }, 300);

        return () => clearTimeout(timer);
    }, [search, dateFrom, dateTo, perPage]);

    const handlePageChange = (page: number) => {
        router.get(
            '/monitoring/visits',
            { search, per_page: perPage, date_from: dateFrom || undefined, date_to: dateTo || undefined, page },
            { preserveState: true, only: ['visits'] },
        );
    };

    const getMethodBadge = (method: string) => {
        const variants: Record<string, string> = {
            GET: 'bg-[#2e7d32] hover:bg-[#2e7d32]/90',
            POST: 'bg-blue-500 hover:bg-blue-500/90',
            PUT: 'bg-[#ffd600] text-black hover:bg-[#ffd600]/90',
            PATCH: 'bg-orange-500 hover:bg-orange-500/90',
            DELETE: 'bg-[#c62828] hover:bg-[#c62828]/90',
        };

        return (
            <Badge className={variants[method] || 'bg-gray-500 hover:bg-gray-500/90'}>
                {method}
            </Badge>
        );
    };

    return (
        <AppLayout>
            <Head title="API Visits Monitoring" />

            <div className="space-y-6 p-6">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-semibold">API Visits Monitoring</h1>
                        <p className="text-sm text-muted-foreground">
                            Tracking semua API requests dari mobile app
                        </p>
                    </div>
                </div>

                {/* Summary Cards */}
                <div className="grid gap-4 md:grid-cols-4">
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Total Visits</CardTitle>
                            <MousePointerClick className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{summary.total_visits}</div>
                        </CardContent>
                    </Card>

                    <Card className="border-l-4 border-l-[#2e7d32]">
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Hari Ini</CardTitle>
                            <Globe className="h-4 w-4 text-[#2e7d32]" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-[#2e7d32]">{summary.today_visits}</div>
                        </CardContent>
                    </Card>

                    <Card className="border-l-4 border-l-blue-500">
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Unique Users</CardTitle>
                            <UserCheck className="h-4 w-4 text-blue-500" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-blue-500">{summary.unique_users}</div>
                        </CardContent>
                    </Card>

                    <Card className="border-l-4 border-l-gray-500">
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Guest Visits</CardTitle>
                            <Users className="h-4 w-4 text-gray-500" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-gray-500">{summary.guest_visits}</div>
                        </CardContent>
                    </Card>
                </div>

                {/* Filters */}
                <div className="flex items-center gap-4">
                    <div className="relative flex-1">
                        <Search className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                        <Input
                            placeholder="Cari URL, IP, nama user..."
                            value={search}
                            onChange={(e) => setSearch(e.target.value)}
                            className="pl-9"
                        />
                    </div>
                    <div className="flex gap-2">
                        <Input
                            type="date"
                            value={dateFrom}
                            onChange={(e) => setDateFrom(e.target.value)}
                            placeholder="Dari"
                            className="w-40"
                        />
                        <Input
                            type="date"
                            value={dateTo}
                            onChange={(e) => setDateTo(e.target.value)}
                            placeholder="Sampai"
                            className="w-40"
                        />
                    </div>
                </div>

                {/* Table */}
                <div className="rounded-md border">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>User</TableHead>
                                <TableHead>Method</TableHead>
                                <TableHead>URL</TableHead>
                                <TableHead>IP Address</TableHead>
                                <TableHead>Device</TableHead>
                                <TableHead>Browser</TableHead>
                                <TableHead>Waktu</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {visits.data.length === 0 ? (
                                <TableRow>
                                    <TableCell colSpan={7} className="text-center text-muted-foreground py-8">
                                        Tidak ada data
                                    </TableCell>
                                </TableRow>
                            ) : (
                                visits.data.map((visit) => (
                                    <TableRow key={visit.id}>
                                        <TableCell>
                                            {visit.user ? (
                                                <div className="flex items-center gap-2">
                                                    <img
                                                        src={visit.user.avatar}
                                                        alt={visit.user.name}
                                                        className="h-8 w-8 rounded-full object-cover border-2 border-[#2e7d32]"
                                                    />
                                                    <div>
                                                        <div className="font-medium text-sm">{visit.user.name}</div>
                                                        <div className="text-xs text-muted-foreground">{visit.user.nip}</div>
                                                    </div>
                                                </div>
                                            ) : (
                                                <Badge variant="secondary">Guest</Badge>
                                            )}
                                        </TableCell>
                                        <TableCell>{getMethodBadge(visit.method)}</TableCell>
                                        <TableCell className="max-w-xs truncate text-sm" title={visit.url}>
                                            {visit.url}
                                        </TableCell>
                                        <TableCell className="font-mono text-sm">{visit.ip_address}</TableCell>
                                        <TableCell className="text-sm">
                                            {visit.device && visit.platform ? (
                                                <div>
                                                    <div>{visit.device}</div>
                                                    <div className="text-xs text-muted-foreground">{visit.platform}</div>
                                                </div>
                                            ) : (
                                                <span className="text-muted-foreground">-</span>
                                            )}
                                        </TableCell>
                                        <TableCell className="text-sm">
                                            {visit.browser || <span className="text-muted-foreground">-</span>}
                                        </TableCell>
                                        <TableCell className="text-sm">
                                            {new Date(visit.created_at).toLocaleString('id-ID', {
                                                day: '2-digit',
                                                month: 'short',
                                                year: 'numeric',
                                                hour: '2-digit',
                                                minute: '2-digit',
                                            })}
                                        </TableCell>
                                    </TableRow>
                                ))
                            )}
                        </TableBody>
                    </Table>
                </div>

                {/* Pagination */}
                <div className="flex items-center justify-between">
                    <div className="text-sm text-muted-foreground">
                        {visits.from && visits.to ? (
                            <>
                                Menampilkan {visits.from} - {visits.to} dari {visits.total} visits
                            </>
                        ) : (
                            <>Tidak ada data</>
                        )}
                    </div>
                    <div className="flex items-center gap-2">
                        <Button
                            variant="outline"
                            size="sm"
                            onClick={() => handlePageChange(visits.current_page - 1)}
                            disabled={visits.current_page === 1}
                        >
                            <ChevronLeft className="h-4 w-4" />
                            Previous
                        </Button>
                        <div className="text-sm">
                            Page {visits.current_page} of {visits.last_page}
                        </div>
                        <Button
                            variant="outline"
                            size="sm"
                            onClick={() => handlePageChange(visits.current_page + 1)}
                            disabled={visits.current_page === visits.last_page}
                        >
                            Next
                            <ChevronRight className="h-4 w-4" />
                        </Button>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
