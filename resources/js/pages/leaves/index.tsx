import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { Badge } from '@/components/ui/badge';
import { Head, Link, router } from '@inertiajs/react';
import { Search, Eye, ChevronLeft, ChevronRight } from 'lucide-react';
import { useState, useEffect } from 'react';

interface User {
    id: number;
    name: string;
    nip: string;
    position: string;
    department: string;
}

interface Leave {
    id: number;
    user: User;
    start_date: string;
    end_date: string;
    total_days: number;
    type: 'sakit' | 'izin' | 'cuti';
    reason: string;
    document_path: string | null;
    status: 'pending' | 'approved' | 'rejected';
    created_at: string;
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface Props {
    leaves: {
        data: Leave[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        from: number | null;
        to: number | null;
        links: PaginationLink[];
    };
    filters: {
        search: string;
        status: string;
        type: string;
        month: string;
        year: string;
        per_page: number;
    };
}

export default function LeavesIndex({ leaves, filters }: Props) {
    const [search, setSearch] = useState(filters.search || '');
    const [status, setStatus] = useState(filters.status || '');
    const [type, setType] = useState(filters.type || '');
    const [month, setMonth] = useState(filters.month);
    const [year, setYear] = useState(filters.year);
    const perPage = filters.per_page;

    useEffect(() => {
        const timer = setTimeout(() => {
            router.get(
                '/leaves',
                { search, status, type, month, year, per_page: perPage },
                { preserveState: true, replace: true, only: ['leaves'] }
            );
        }, 300);

        return () => clearTimeout(timer);
    }, [search, status, type, month, year, perPage]);

    const handlePageChange = (page: number) => {
        router.get(
            '/leaves',
            { search, status, type, month, year, per_page: perPage, page },
            { preserveState: true, only: ['leaves'] }
        );
    };

    const getStatusBadge = (status: string) => {
        const variants = {
            pending: 'secondary',
            approved: 'default',
            rejected: 'destructive',
        } as const;

        const labels = {
            pending: 'Menunggu',
            approved: 'Disetujui',
            rejected: 'Ditolak',
        };

        return (
            <Badge variant={variants[status as keyof typeof variants]}>
                {labels[status as keyof typeof labels]}
            </Badge>
        );
    };

    const getTypeBadge = (type: string) => {
        const variants = {
            sakit: 'destructive',
            izin: 'secondary',
            cuti: 'default',
        } as const;

        const labels = {
            sakit: 'Sakit',
            izin: 'Izin',
            cuti: 'Cuti',
        };

        return (
            <Badge variant={variants[type as keyof typeof variants]}>
                {labels[type as keyof typeof labels]}
            </Badge>
        );
    };

    const formatDate = (dateString: string) => {
        return new Date(dateString).toLocaleDateString('id-ID', {
            day: 'numeric',
            month: 'short',
            year: 'numeric',
        });
    };

    return (
        <AppLayout>
            <Head title="Izin & Cuti" />

            <div className="space-y-6 p-6">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-semibold">Izin & Cuti</h1>
                        <p className="text-sm text-muted-foreground">
                            Kelola pengajuan izin dan cuti karyawan
                        </p>
                    </div>
                </div>

                <div className="flex items-center gap-4">
                    <div className="relative flex-1">
                        <Search className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                        <Input
                            placeholder="Cari nama, NIP, atau username..."
                            value={search}
                            onChange={(e) => setSearch(e.target.value)}
                            className="pl-9"
                        />
                    </div>
                    <div className="w-32">
                        <select
                            value={month}
                            onChange={(e) => setMonth(e.target.value)}
                            className="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
                        >
                            <option value="1">Januari</option>
                            <option value="2">Februari</option>
                            <option value="3">Maret</option>
                            <option value="4">April</option>
                            <option value="5">Mei</option>
                            <option value="6">Juni</option>
                            <option value="7">Juli</option>
                            <option value="8">Agustus</option>
                            <option value="9">September</option>
                            <option value="10">Oktober</option>
                            <option value="11">November</option>
                            <option value="12">Desember</option>
                        </select>
                    </div>
                    <div className="w-28">
                        <select
                            value={year}
                            onChange={(e) => setYear(e.target.value)}
                            className="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
                        >
                            {Array.from({ length: 5 }, (_, i) => new Date().getFullYear() - i).map((y) => (
                                <option key={y} value={y}>
                                    {y}
                                </option>
                            ))}
                        </select>
                    </div>
                    <div className="w-48">
                        <select
                            value={type}
                            onChange={(e) => setType(e.target.value)}
                            className="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
                        >
                            <option value="">Semua Jenis</option>
                            <option value="sakit">Sakit</option>
                            <option value="izin">Izin</option>
                            <option value="cuti">Cuti</option>
                        </select>
                    </div>
                    <div className="w-48">
                        <select
                            value={status}
                            onChange={(e) => setStatus(e.target.value)}
                            className="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
                        >
                            <option value="">Semua Status</option>
                            <option value="pending">Menunggu</option>
                            <option value="approved">Disetujui</option>
                            <option value="rejected">Ditolak</option>
                        </select>
                    </div>
                </div>

                <div className="rounded-md border">
                    <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Karyawan</TableHead>
                                    <TableHead>Jenis</TableHead>
                                    <TableHead>Periode</TableHead>
                                    <TableHead>Alasan</TableHead>
                                    <TableHead>Status</TableHead>
                                    <TableHead>Tanggal Ajuan</TableHead>
                                    <TableHead className="text-right">Aksi</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {leaves.data.length === 0 ? (
                                    <TableRow>
                                        <TableCell colSpan={7} className="text-center text-muted-foreground">
                                            Tidak ada data pengajuan izin/cuti
                                        </TableCell>
                                    </TableRow>
                                ) : (
                                    leaves.data.map((leave) => (
                                        <TableRow key={leave.id}>
                                            <TableCell>
                                                <div>
                                                    <div className="font-medium">{leave.user.name}</div>
                                                    <div className="text-sm text-muted-foreground">
                                                        {leave.user.nip}
                                                    </div>
                                                    <div className="text-xs text-muted-foreground">
                                                        {leave.user.position}
                                                    </div>
                                                </div>
                                            </TableCell>
                                            <TableCell>{getTypeBadge(leave.type)}</TableCell>
                                            <TableCell>
                                                <div className="text-sm">
                                                    {formatDate(leave.start_date)} - {formatDate(leave.end_date)}
                                                </div>
                                                <div className="text-xs text-muted-foreground">
                                                    {leave.total_days} hari
                                                </div>
                                            </TableCell>
                                            <TableCell>
                                                <div className="max-w-xs truncate">{leave.reason}</div>
                                            </TableCell>
                                            <TableCell>{getStatusBadge(leave.status)}</TableCell>
                                            <TableCell>{formatDate(leave.created_at)}</TableCell>
                                            <TableCell className="text-right">
                                                <Link href={`/leaves/${leave.id}`}>
                                                    <Button variant="outline" size="sm">
                                                        <Eye className="mr-2 h-4 w-4" />
                                                        Detail
                                                    </Button>
                                                </Link>
                                            </TableCell>
                                        </TableRow>
                                    ))
                                )}
                            </TableBody>
                        </Table>
                    </div>

                    <div className="flex items-center justify-between p-6">
                        <div className="text-sm text-muted-foreground">
                            {leaves.from && leaves.to ? (
                                <>
                                    Menampilkan {leaves.from} - {leaves.to} dari {leaves.total} data
                                </>
                            ) : (
                                <>Tidak ada data</>
                            )}
                        </div>
                        <div className="flex items-center gap-2">
                            <Button
                                variant="outline"
                                size="sm"
                                onClick={() => handlePageChange(leaves.current_page - 1)}
                                disabled={leaves.current_page === 1}
                            >
                                <ChevronLeft className="h-4 w-4" />
                                Previous
                            </Button>
                            <div className="text-sm">
                                Page {leaves.current_page} of {leaves.last_page}
                            </div>
                            <Button
                                variant="outline"
                                size="sm"
                                onClick={() => handlePageChange(leaves.current_page + 1)}
                                disabled={leaves.current_page === leaves.last_page}
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
