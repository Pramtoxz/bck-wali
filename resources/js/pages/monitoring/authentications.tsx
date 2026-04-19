import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Head, router } from '@inertiajs/react';
import { Search, ChevronLeft, ChevronRight, LogIn, LogOut, Shield } from 'lucide-react';
import { useState, useEffect } from 'react';

interface UserAuthentication {
    id: number;
    user: {
        id: number;
        name: string;
        nip: string;
        avatar: string;
    };
    action: string;
    ip_address: string;
    device: string | null;
    platform: string | null;
    browser: string | null;
    authenticated_at: string;
    created_at: string;
}

interface Props {
    authentications: {
        data: UserAuthentication[];
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
        total_authentications: number;
        today_logins: number;
        total_logins: number;
        total_logouts: number;
    };
    filters: {
        search: string;
        per_page: number;
        action: string | null;
        date_from: string | null;
        date_to: string | null;
    };
}

export default function MonitoringAuthentications({ authentications, summary, filters }: Props) {
    const [search, setSearch] = useState(filters.search || '');
    const [actionFilter, setActionFilter] = useState(filters.action || 'all');
    const [dateFrom, setDateFrom] = useState(filters.date_from || new Date().toISOString().split('T')[0]);
    const [dateTo, setDateTo] = useState(filters.date_to || new Date().toISOString().split('T')[0]);
    const perPage = filters.per_page;

    useEffect(() => {
        const timer = setTimeout(() => {
            router.get(
                '/monitoring/authentications',
                { 
                    search, 
                    per_page: perPage, 
                    action: actionFilter !== 'all' ? actionFilter : undefined,
                    date_from: dateFrom || undefined, 
                    date_to: dateTo || undefined 
                },
                { preserveState: true, replace: true, only: ['authentications', 'summary'] },
            );
        }, 300);

        return () => clearTimeout(timer);
    }, [search, actionFilter, dateFrom, dateTo, perPage]);

    const handlePageChange = (page: number) => {
        router.get(
            '/monitoring/authentications',
            { 
                search, 
                per_page: perPage, 
                action: actionFilter !== 'all' ? actionFilter : undefined,
                date_from: dateFrom || undefined, 
                date_to: dateTo || undefined,
                page 
            },
            { preserveState: true, only: ['authentications'] },
        );
    };

    const getActionBadge = (action: string) => {
        if (action === 'login') {
            return (
                <Badge className="bg-[#2e7d32] hover:bg-[#2e7d32]/90 flex items-center w-fit">
                    <LogIn className="h-3 w-3 mr-1" />
                    LOGIN
                </Badge>
            );
        }
        return (
            <Badge className="bg-[#c62828] hover:bg-[#c62828]/90 flex items-center w-fit">
                <LogOut className="h-3 w-3 mr-1" />
                LOGOUT
            </Badge>
        );
    };

    return (
        <AppLayout>
            <Head title="Authentication Monitoring" />

            <div className="space-y-6 p-6">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-semibold">Authentication Monitoring</h1>
                        <p className="text-sm text-muted-foreground">
                            Tracking login dan logout user dari mobile app
                        </p>
                    </div>
                </div>

                {/* Summary Cards */}
                <div className="grid gap-4 md:grid-cols-4">
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Total Auth</CardTitle>
                            <Shield className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{summary.total_authentications}</div>
                        </CardContent>
                    </Card>

                    <Card className="border-l-4 border-l-[#2e7d32]">
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Login Hari Ini</CardTitle>
                            <LogIn className="h-4 w-4 text-[#2e7d32]" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-[#2e7d32]">{summary.today_logins}</div>
                        </CardContent>
                    </Card>

                    <Card className="border-l-4 border-l-blue-500">
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Total Login</CardTitle>
                            <LogIn className="h-4 w-4 text-blue-500" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-blue-500">{summary.total_logins}</div>
                        </CardContent>
                    </Card>

                    <Card className="border-l-4 border-l-[#c62828]">
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Total Logout</CardTitle>
                            <LogOut className="h-4 w-4 text-[#c62828]" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-[#c62828]">{summary.total_logouts}</div>
                        </CardContent>
                    </Card>
                </div>

                {/* Filters */}
                <div className="flex items-center gap-4">
                    <div className="relative flex-1">
                        <Search className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                        <Input
                            placeholder="Cari nama user, NIP..."
                            value={search}
                            onChange={(e) => setSearch(e.target.value)}
                            className="pl-9"
                        />
                    </div>
                    <Select value={actionFilter} onValueChange={setActionFilter}>
                        <SelectTrigger className="w-40">
                            <SelectValue placeholder="Semua Aksi" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">Semua Aksi</SelectItem>
                            <SelectItem value="login">Login</SelectItem>
                            <SelectItem value="logout">Logout</SelectItem>
                        </SelectContent>
                    </Select>
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
                                <TableHead>Action</TableHead>
                                <TableHead>IP Address</TableHead>
                                <TableHead>Device</TableHead>
                                <TableHead>Browser</TableHead>
                                <TableHead>Waktu</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {authentications.data.length === 0 ? (
                                <TableRow>
                                    <TableCell colSpan={6} className="text-center text-muted-foreground py-8">
                                        Tidak ada data
                                    </TableCell>
                                </TableRow>
                            ) : (
                                authentications.data.map((auth) => (
                                    <TableRow key={auth.id}>
                                        <TableCell>
                                            <div className="flex items-center gap-2">
                                                <img
                                                    src={auth.user.avatar}
                                                    alt={auth.user.name}
                                                    className="h-8 w-8 rounded-full object-cover border-2 border-[#2e7d32]"
                                                />
                                                <div>
                                                    <div className="font-medium text-sm">{auth.user.name}</div>
                                                    <div className="text-xs text-muted-foreground">{auth.user.nip}</div>
                                                </div>
                                            </div>
                                        </TableCell>
                                        <TableCell>{getActionBadge(auth.action)}</TableCell>
                                        <TableCell className="font-mono text-sm">{auth.ip_address}</TableCell>
                                        <TableCell className="text-sm">
                                            {auth.device && auth.platform ? (
                                                <div>
                                                    <div>{auth.device}</div>
                                                    <div className="text-xs text-muted-foreground">{auth.platform}</div>
                                                </div>
                                            ) : (
                                                <span className="text-muted-foreground">-</span>
                                            )}
                                        </TableCell>
                                        <TableCell className="text-sm">
                                            {auth.browser || <span className="text-muted-foreground">-</span>}
                                        </TableCell>
                                        <TableCell className="text-sm">
                                            {new Date(auth.authenticated_at).toLocaleString('id-ID', {
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
                        {authentications.from && authentications.to ? (
                            <>
                                Menampilkan {authentications.from} - {authentications.to} dari {authentications.total} authentications
                            </>
                        ) : (
                            <>Tidak ada data</>
                        )}
                    </div>
                    <div className="flex items-center gap-2">
                        <Button
                            variant="outline"
                            size="sm"
                            onClick={() => handlePageChange(authentications.current_page - 1)}
                            disabled={authentications.current_page === 1}
                        >
                            <ChevronLeft className="h-4 w-4" />
                            Previous
                        </Button>
                        <div className="text-sm">
                            Page {authentications.current_page} of {authentications.last_page}
                        </div>
                        <Button
                            variant="outline"
                            size="sm"
                            onClick={() => handlePageChange(authentications.current_page + 1)}
                            disabled={authentications.current_page === authentications.last_page}
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
