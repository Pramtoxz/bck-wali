import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Head, router } from '@inertiajs/react';
import { Search, ChevronLeft, ChevronRight, Activity, Plus, Edit, Trash2 } from 'lucide-react';
import { useState, useEffect } from 'react';

interface UserAction {
    id: number;
    user: {
        id: number;
        name: string;
        nip: string;
        avatar: string;
    } | null;
    action: string;
    model_type: string;
    model_id: number | null;
    changes: Record<string, unknown> | null;
    ip_address: string;
    created_at: string;
}

interface Props {
    actions: {
        data: UserAction[];
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
        total_actions: number;
        today_actions: number;
        create_actions: number;
        update_actions: number;
        delete_actions: number;
    };
    filters: {
        search: string;
        per_page: number;
        action: string | null;
        date_from: string | null;
        date_to: string | null;
    };
}

export default function MonitoringActions({ actions, summary, filters }: Props) {
    const [search, setSearch] = useState(filters.search || '');
    const [actionFilter, setActionFilter] = useState(filters.action || 'all');
    const [dateFrom, setDateFrom] = useState(filters.date_from || new Date().toISOString().split('T')[0]);
    const [dateTo, setDateTo] = useState(filters.date_to || new Date().toISOString().split('T')[0]);
    const perPage = filters.per_page;

    useEffect(() => {
        const timer = setTimeout(() => {
            router.get(
                '/monitoring/actions',
                { 
                    search, 
                    per_page: perPage, 
                    action: actionFilter !== 'all' ? actionFilter : undefined,
                    date_from: dateFrom || undefined, 
                    date_to: dateTo || undefined 
                },
                { preserveState: true, replace: true, only: ['actions', 'summary'] },
            );
        }, 300);

        return () => clearTimeout(timer);
    }, [search, actionFilter, dateFrom, dateTo, perPage]);

    const handlePageChange = (page: number) => {
        router.get(
            '/monitoring/actions',
            { 
                search, 
                per_page: perPage, 
                action: actionFilter !== 'all' ? actionFilter : undefined,
                date_from: dateFrom || undefined, 
                date_to: dateTo || undefined,
                page 
            },
            { preserveState: true, only: ['actions'] },
        );
    };

    const getActionBadge = (action: string) => {
        const variants: Record<string, { color: string; icon: React.ReactNode }> = {
            create: { 
                color: 'bg-[#2e7d32] hover:bg-[#2e7d32]/90', 
                icon: <Plus className="h-3 w-3 mr-1" /> 
            },
            update: { 
                color: 'bg-[#ffd600] text-black hover:bg-[#ffd600]/90', 
                icon: <Edit className="h-3 w-3 mr-1" /> 
            },
            delete: { 
                color: 'bg-[#c62828] hover:bg-[#c62828]/90', 
                icon: <Trash2 className="h-3 w-3 mr-1" /> 
            },
            read: { 
                color: 'bg-blue-500 hover:bg-blue-500/90', 
                icon: <Activity className="h-3 w-3 mr-1" /> 
            },
        };

        const variant = variants[action] || { color: 'bg-gray-500 hover:bg-gray-500/90', icon: null };

        return (
            <Badge className={`${variant.color} flex items-center w-fit`}>
                {variant.icon}
                {action.toUpperCase()}
            </Badge>
        );
    };

    const getModelName = (modelType: string) => {
        const parts = modelType.split('\\');
        return parts[parts.length - 1];
    };

    return (
        <AppLayout>
            <Head title="User Actions Monitoring" />

            <div className="space-y-6 p-6">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-semibold">User Actions Monitoring</h1>
                        <p className="text-sm text-muted-foreground">
                            Tracking semua aksi CRUD pada model dari API
                        </p>
                    </div>
                </div>

                {/* Summary Cards */}
                <div className="grid gap-4 md:grid-cols-5">
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Total Actions</CardTitle>
                            <Activity className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{summary.total_actions}</div>
                        </CardContent>
                    </Card>

                    <Card className="border-l-4 border-l-blue-500">
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Hari Ini</CardTitle>
                            <Activity className="h-4 w-4 text-blue-500" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-blue-500">{summary.today_actions}</div>
                        </CardContent>
                    </Card>

                    <Card className="border-l-4 border-l-[#2e7d32]">
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Create</CardTitle>
                            <Plus className="h-4 w-4 text-[#2e7d32]" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-[#2e7d32]">{summary.create_actions}</div>
                        </CardContent>
                    </Card>

                    <Card className="border-l-4 border-l-[#ffd600]">
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Update</CardTitle>
                            <Edit className="h-4 w-4 text-[#ffd600]" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-[#ffd600]">{summary.update_actions}</div>
                        </CardContent>
                    </Card>

                    <Card className="border-l-4 border-l-[#c62828]">
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Delete</CardTitle>
                            <Trash2 className="h-4 w-4 text-[#c62828]" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-[#c62828]">{summary.delete_actions}</div>
                        </CardContent>
                    </Card>
                </div>

                {/* Filters */}
                <div className="flex items-center gap-4">
                    <div className="relative flex-1">
                        <Search className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                        <Input
                            placeholder="Cari model, nama user..."
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
                            <SelectItem value="create">Create</SelectItem>
                            <SelectItem value="update">Update</SelectItem>
                            <SelectItem value="delete">Delete</SelectItem>
                            <SelectItem value="read">Read</SelectItem>
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
                                <TableHead>Model</TableHead>
                                <TableHead>Model ID</TableHead>
                                <TableHead>IP Address</TableHead>
                                <TableHead>Waktu</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {actions.data.length === 0 ? (
                                <TableRow>
                                    <TableCell colSpan={6} className="text-center text-muted-foreground py-8">
                                        Tidak ada data
                                    </TableCell>
                                </TableRow>
                            ) : (
                                actions.data.map((action) => (
                                    <TableRow key={action.id}>
                                        <TableCell>
                                            {action.user ? (
                                                <div className="flex items-center gap-2">
                                                    <img
                                                        src={action.user.avatar}
                                                        alt={action.user.name}
                                                        className="h-8 w-8 rounded-full object-cover border-2 border-[#2e7d32]"
                                                    />
                                                    <div>
                                                        <div className="font-medium text-sm">{action.user.name}</div>
                                                        <div className="text-xs text-muted-foreground">{action.user.nip}</div>
                                                    </div>
                                                </div>
                                            ) : (
                                                <Badge variant="secondary">Guest</Badge>
                                            )}
                                        </TableCell>
                                        <TableCell>{getActionBadge(action.action)}</TableCell>
                                        <TableCell className="font-medium text-sm">
                                            {getModelName(action.model_type)}
                                        </TableCell>
                                        <TableCell className="font-mono text-sm">
                                            {action.model_id || <span className="text-muted-foreground">-</span>}
                                        </TableCell>
                                        <TableCell className="font-mono text-sm">{action.ip_address}</TableCell>
                                        <TableCell className="text-sm">
                                            {new Date(action.created_at).toLocaleString('id-ID', {
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
                        {actions.from && actions.to ? (
                            <>
                                Menampilkan {actions.from} - {actions.to} dari {actions.total} actions
                            </>
                        ) : (
                            <>Tidak ada data</>
                        )}
                    </div>
                    <div className="flex items-center gap-2">
                        <Button
                            variant="outline"
                            size="sm"
                            onClick={() => handlePageChange(actions.current_page - 1)}
                            disabled={actions.current_page === 1}
                        >
                            <ChevronLeft className="h-4 w-4" />
                            Previous
                        </Button>
                        <div className="text-sm">
                            Page {actions.current_page} of {actions.last_page}
                        </div>
                        <Button
                            variant="outline"
                            size="sm"
                            onClick={() => handlePageChange(actions.current_page + 1)}
                            disabled={actions.current_page === actions.last_page}
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
