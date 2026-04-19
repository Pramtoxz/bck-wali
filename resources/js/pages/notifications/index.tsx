import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Head, router, Link } from '@inertiajs/react';
import { Search, Plus, ChevronLeft, ChevronRight, Trash2, Bell, BellRing, CheckCircle, Users } from 'lucide-react';
import { useState, useEffect } from 'react';

interface Notification {
    id: number;
    user: {
        id: number;
        name: string;
        nip: string;
    } | null;
    title: string;
    body: string;
    type: string;
    is_read: boolean;
    is_broadcast: boolean;
    created_at: string;
}

interface Props {
    notifications: {
        data: Notification[];
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
        total: number;
        broadcast: number;
        read: number;
        unread: number;
    };
    filters: {
        search: string;
        per_page: number;
        type: string | null;
    };
}

export default function NotificationsIndex({ notifications, summary, filters }: Props) {
    const [search, setSearch] = useState(filters.search || '');
    const [typeFilter, setTypeFilter] = useState(filters.type || 'all');
    const perPage = filters.per_page;

    useEffect(() => {
        const timer = setTimeout(() => {
            router.get(
                '/notifications',
                { search, per_page: perPage, type: typeFilter !== 'all' ? typeFilter : undefined },
                { preserveState: true, replace: true, only: ['notifications', 'summary'] },
            );
        }, 300);

        return () => clearTimeout(timer);
    }, [search, typeFilter, perPage]);

    const handlePageChange = (page: number) => {
        router.get(
            '/notifications',
            { search, per_page: perPage, type: typeFilter !== 'all' ? typeFilter : undefined, page },
            { preserveState: true, only: ['notifications'] },
        );
    };

    const handleDelete = (id: number) => {
        if (confirm('Apakah Anda yakin ingin menghapus notifikasi ini?')) {
            router.delete(`/notifications/${id}`);
        }
    };

    const getTypeBadge = (type: string) => {
        const variants: Record<string, { label: string; className: string }> = {
            general: { label: 'Umum', className: 'bg-gray-500' },
            attendance: { label: 'Absensi', className: 'bg-[#2e7d32]' },
            leave: { label: 'Izin/Cuti', className: 'bg-blue-500' },
            field_duty: { label: 'Dinas', className: 'bg-[#ffd600] text-black' },
            announcement: { label: 'Pengumuman', className: 'bg-[#c62828]' },
        };

        const variant = variants[type] || variants.general;
        return <Badge className={variant.className}>{variant.label}</Badge>;
    };

    return (
        <AppLayout>
            <Head title="Manajemen Notifikasi" />

            <div className="space-y-6 p-6">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-semibold">Manajemen Notifikasi</h1>
                        <p className="text-sm text-muted-foreground">
                            Kelola dan kirim notifikasi ke user
                        </p>
                    </div>
                    <Link href="/notifications/create">
                        <Button>
                            <Plus className="mr-2 h-4 w-4" />
                            Kirim Notifikasi
                        </Button>
                    </Link>
                </div>

                <div className="grid gap-4 md:grid-cols-4">
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Total</CardTitle>
                            <Bell className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{summary.total}</div>
                        </CardContent>
                    </Card>

                    <Card className="border-l-4 border-l-[#2e7d32]">
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Broadcast</CardTitle>
                            <Users className="h-4 w-4 text-[#2e7d32]" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-[#2e7d32]">{summary.broadcast}</div>
                        </CardContent>
                    </Card>

                    <Card className="border-l-4 border-l-blue-500">
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Sudah Dibaca</CardTitle>
                            <CheckCircle className="h-4 w-4 text-blue-500" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-blue-500">{summary.read}</div>
                        </CardContent>
                    </Card>

                    <Card className="border-l-4 border-l-[#ffd600]">
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Belum Dibaca</CardTitle>
                            <BellRing className="h-4 w-4 text-[#ffd600]" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-[#ffd600]">{summary.unread}</div>
                        </CardContent>
                    </Card>
                </div>

                <div className="flex items-center gap-4">
                    <div className="relative flex-1">
                        <Search className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                        <Input
                            placeholder="Cari judul, isi, nama user..."
                            value={search}
                            onChange={(e) => setSearch(e.target.value)}
                            className="pl-9"
                        />
                    </div>
                    <Select value={typeFilter} onValueChange={setTypeFilter}>
                        <SelectTrigger className="w-48">
                            <SelectValue placeholder="Semua Tipe" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">Semua Tipe</SelectItem>
                            <SelectItem value="general">Umum</SelectItem>
                            <SelectItem value="attendance">Absensi</SelectItem>
                            <SelectItem value="leave">Izin/Cuti</SelectItem>
                            <SelectItem value="field_duty">Dinas</SelectItem>
                            <SelectItem value="announcement">Pengumuman</SelectItem>
                        </SelectContent>
                    </Select>
                </div>

                <div className="rounded-md border">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>User</TableHead>
                                <TableHead>Judul</TableHead>
                                <TableHead>Isi</TableHead>
                                <TableHead>Tipe</TableHead>
                                <TableHead>Status</TableHead>
                                <TableHead>Waktu</TableHead>
                                <TableHead className="text-right">Aksi</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {notifications.data.length === 0 ? (
                                <TableRow>
                                    <TableCell colSpan={7} className="text-center text-muted-foreground py-8">
                                        Tidak ada data notifikasi
                                    </TableCell>
                                </TableRow>
                            ) : (
                                notifications.data.map((notification) => (
                                    <TableRow key={notification.id}>
                                        <TableCell>
                                            {notification.is_broadcast ? (
                                                <Badge className="bg-[#2e7d32]">
                                                    <Users className="h-3 w-3 mr-1" />
                                                    Semua User
                                                </Badge>
                                            ) : notification.user ? (
                                                <div>
                                                    <div className="font-medium text-sm">{notification.user.name}</div>
                                                    <div className="text-xs text-muted-foreground">{notification.user.nip}</div>
                                                </div>
                                            ) : (
                                                <span className="text-muted-foreground">-</span>
                                            )}
                                        </TableCell>
                                        <TableCell className="font-medium">{notification.title}</TableCell>
                                        <TableCell className="max-w-xs truncate">{notification.body}</TableCell>
                                        <TableCell>{getTypeBadge(notification.type)}</TableCell>
                                        <TableCell>
                                            {notification.is_read ? (
                                                <Badge className="bg-blue-500">
                                                    <CheckCircle className="h-3 w-3 mr-1" />
                                                    Dibaca
                                                </Badge>
                                            ) : (
                                                <Badge className="bg-[#ffd600] text-black">
                                                    <BellRing className="h-3 w-3 mr-1" />
                                                    Belum Dibaca
                                                </Badge>
                                            )}
                                        </TableCell>
                                        <TableCell className="text-sm">
                                            {new Date(notification.created_at).toLocaleString('id-ID', {
                                                day: '2-digit',
                                                month: 'short',
                                                year: 'numeric',
                                                hour: '2-digit',
                                                minute: '2-digit',
                                            })}
                                        </TableCell>
                                        <TableCell className="text-right">
                                            <Button
                                                variant="ghost"
                                                size="sm"
                                                onClick={() => handleDelete(notification.id)}
                                                className="text-[#c62828] hover:text-[#c62828]"
                                            >
                                                <Trash2 className="h-4 w-4" />
                                            </Button>
                                        </TableCell>
                                    </TableRow>
                                ))
                            )}
                        </TableBody>
                    </Table>
                </div>

                <div className="flex items-center justify-between">
                    <div className="text-sm text-muted-foreground">
                        {notifications.from && notifications.to ? (
                            <>
                                Menampilkan {notifications.from} - {notifications.to} dari {notifications.total} notifikasi
                            </>
                        ) : (
                            <>Tidak ada data</>
                        )}
                    </div>
                    <div className="flex items-center gap-2">
                        <Button
                            variant="outline"
                            size="sm"
                            onClick={() => handlePageChange(notifications.current_page - 1)}
                            disabled={notifications.current_page === 1}
                        >
                            <ChevronLeft className="h-4 w-4" />
                            Previous
                        </Button>
                        <div className="text-sm">
                            Page {notifications.current_page} of {notifications.last_page}
                        </div>
                        <Button
                            variant="outline"
                            size="sm"
                            onClick={() => handlePageChange(notifications.current_page + 1)}
                            disabled={notifications.current_page === notifications.last_page}
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
