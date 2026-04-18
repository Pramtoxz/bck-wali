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

interface FieldDuty {
    id: number;
    user: User;
    start_date: string;
    end_date: string;
    total_days: number;
    destination: string;
    purpose: string;
    document_path: string;
    status: 'pending' | 'approved' | 'rejected';
    created_at: string;
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface Props {
    fieldDuties: {
        data: FieldDuty[];
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
        month: string;
        year: string;
        per_page: number;
    };
}

export default function FieldDutiesIndex({ fieldDuties, filters }: Props) {
    const [search, setSearch] = useState(filters.search || '');
    const [status, setStatus] = useState(filters.status || '');
    const [month, setMonth] = useState(filters.month);
    const [year, setYear] = useState(filters.year);
    const perPage = filters.per_page;

    useEffect(() => {
        const timer = setTimeout(() => {
            router.get(
                '/field-duties',
                { search, status, month, year, per_page: perPage },
                { preserveState: true, replace: true, only: ['fieldDuties'] }
            );
        }, 300);

        return () => clearTimeout(timer);
    }, [search, status, month, year, perPage]);

    const handlePageChange = (page: number) => {
        router.get(
            '/field-duties',
            { search, status, month, year, per_page: perPage, page },
            { preserveState: true, only: ['fieldDuties'] }
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

    const formatDate = (dateString: string) => {
        return new Date(dateString).toLocaleDateString('id-ID', {
            day: 'numeric',
            month: 'short',
            year: 'numeric',
        });
    };

    return (
        <AppLayout>
            <Head title="Dinas Luar" />

            <div className="space-y-6 p-6">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-semibold">Dinas Luar</h1>
                        <p className="text-sm text-muted-foreground">
                            Kelola pengajuan dinas luar karyawan
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
                                    <TableHead>Periode</TableHead>
                                    <TableHead>Tujuan</TableHead>
                                    <TableHead>Status</TableHead>
                                    <TableHead>Tanggal Ajuan</TableHead>
                                    <TableHead className="text-right">Aksi</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {fieldDuties.data.length === 0 ? (
                                    <TableRow>
                                        <TableCell colSpan={6} className="text-center text-muted-foreground">
                                            Tidak ada data pengajuan dinas
                                        </TableCell>
                                    </TableRow>
                                ) : (
                                    fieldDuties.data.map((duty) => (
                                        <TableRow key={duty.id}>
                                            <TableCell>
                                                <div>
                                                    <div className="font-medium">{duty.user.name}</div>
                                                    <div className="text-sm text-muted-foreground">
                                                        {duty.user.nip}
                                                    </div>
                                                    <div className="text-xs text-muted-foreground">
                                                        {duty.user.position}
                                                    </div>
                                                </div>
                                            </TableCell>
                                            <TableCell>
                                                <div className="text-sm">
                                                    {formatDate(duty.start_date)} - {formatDate(duty.end_date)}
                                                </div>
                                                <div className="text-xs text-muted-foreground">
                                                    {duty.total_days} hari
                                                </div>
                                            </TableCell>
                                            <TableCell>
                                                <div className="max-w-xs truncate">{duty.destination}</div>
                                            </TableCell>
                                            <TableCell>{getStatusBadge(duty.status)}</TableCell>
                                            <TableCell>{formatDate(duty.created_at)}</TableCell>
                                            <TableCell className="text-right">
                                                <Link href={`/field-duties/${duty.id}`}>
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
                            {fieldDuties.from && fieldDuties.to ? (
                                <>
                                    Menampilkan {fieldDuties.from} - {fieldDuties.to} dari {fieldDuties.total} data
                                </>
                            ) : (
                                <>Tidak ada data</>
                            )}
                        </div>
                        <div className="flex items-center gap-2">
                            <Button
                                variant="outline"
                                size="sm"
                                onClick={() => handlePageChange(fieldDuties.current_page - 1)}
                                disabled={fieldDuties.current_page === 1}
                            >
                                <ChevronLeft className="h-4 w-4" />
                                Previous
                            </Button>
                            <div className="text-sm">
                                Page {fieldDuties.current_page} of {fieldDuties.last_page}
                            </div>
                            <Button
                                variant="outline"
                                size="sm"
                                onClick={() => handlePageChange(fieldDuties.current_page + 1)}
                                disabled={fieldDuties.current_page === fieldDuties.last_page}
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
