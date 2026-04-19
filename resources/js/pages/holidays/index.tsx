import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Badge } from '@/components/ui/badge';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Head, router, Link } from '@inertiajs/react';
import { Search, Plus, ChevronLeft, ChevronRight, Edit, Trash2, Calendar, Power } from 'lucide-react';
import { useState, useEffect } from 'react';

interface Holiday {
    id: number;
    date: string;
    name: string;
    type: 'national' | 'company' | 'weekend';
    description: string | null;
    is_active: boolean;
}

interface Props {
    holidays: {
        data: Holiday[];
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
    filters: {
        year: number;
        search: string;
        per_page: number;
    };
    years: number[];
}

export default function HolidaysIndex({ holidays, filters, years }: Props) {
    const [search, setSearch] = useState(filters.search || '');
    const [year, setYear] = useState(filters.year.toString());
    const perPage = filters.per_page;

    useEffect(() => {
        const timer = setTimeout(() => {
            router.get(
                '/holidays',
                { search, per_page: perPage, year },
                { preserveState: true, replace: true, only: ['holidays'] },
            );
        }, 300);

        return () => clearTimeout(timer);
    }, [search, year, perPage]);

    const handlePageChange = (page: number) => {
        router.get(
            '/holidays',
            { search, per_page: perPage, year, page },
            { preserveState: true, only: ['holidays'] },
        );
    };

    const handleDelete = (id: number) => {
        if (confirm('Apakah Anda yakin ingin menghapus hari libur ini?')) {
            router.delete(`/holidays/${id}`);
        }
    };

    const handleToggle = (id: number) => {
        router.patch(`/holidays/${id}/toggle`);
    };

    const formatDate = (dateString: string) => {
        const date = new Date(dateString);
        return date.toLocaleDateString('id-ID', { 
            weekday: 'long',
            day: '2-digit', 
            month: 'long', 
            year: 'numeric' 
        });
    };

    const getTypeBadge = (type: string) => {
        const variants: Record<string, { label: string; className: string }> = {
            national: { label: 'Nasional', className: 'bg-[#c62828]' },
            company: { label: 'Cuti Bersama', className: 'bg-[#ffd600] text-black' },
            weekend: { label: 'Weekend', className: 'bg-gray-500' },
        };

        const variant = variants[type] || variants.national;
        return <Badge className={variant.className}>{variant.label}</Badge>;
    };

    return (
        <AppLayout>
            <Head title="Hari Libur" />

            <div className="space-y-6 p-6">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-semibold">Hari Libur</h1>
                        <p className="text-sm text-muted-foreground">
                            Kelola hari libur nasional dan cuti bersama
                        </p>
                    </div>
                    <Link href="/holidays/create">
                        <Button>
                            <Plus className="mr-2 h-4 w-4" />
                            Tambah Hari Libur
                        </Button>
                    </Link>
                </div>

                <div className="flex items-center gap-4">
                    <div className="relative flex-1">
                        <Search className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                        <Input
                            placeholder="Cari nama hari libur..."
                            value={search}
                            onChange={(e) => setSearch(e.target.value)}
                            className="pl-9"
                        />
                    </div>
                    <div className="w-48">
                        <Select value={year} onValueChange={setYear}>
                            <SelectTrigger>
                                <Calendar className="mr-2 h-4 w-4" />
                                <SelectValue />
                            </SelectTrigger>
                            <SelectContent>
                                {years.map((y) => (
                                    <SelectItem key={y} value={y.toString()}>
                                        {y}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                    </div>
                </div>

                <div className="rounded-md border">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Tanggal</TableHead>
                                <TableHead>Nama Hari Libur</TableHead>
                                <TableHead>Tipe</TableHead>
                                <TableHead>Keterangan</TableHead>
                                <TableHead>Status</TableHead>
                                <TableHead className="text-right">Aksi</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {holidays.data.length === 0 ? (
                                <TableRow>
                                    <TableCell colSpan={6} className="text-center text-muted-foreground py-8">
                                        Tidak ada data hari libur
                                    </TableCell>
                                </TableRow>
                            ) : (
                                holidays.data.map((holiday) => (
                                    <TableRow key={holiday.id}>
                                        <TableCell className="font-medium">
                                            {formatDate(holiday.date)}
                                        </TableCell>
                                        <TableCell className="font-medium">{holiday.name}</TableCell>
                                        <TableCell>{getTypeBadge(holiday.type)}</TableCell>
                                        <TableCell>
                                            {holiday.description ? (
                                                <span className="text-sm">{holiday.description}</span>
                                            ) : (
                                                <span className="text-muted-foreground">-</span>
                                            )}
                                        </TableCell>
                                        <TableCell>
                                            <Button
                                                variant="ghost"
                                                size="sm"
                                                onClick={() => handleToggle(holiday.id)}
                                                className={holiday.is_active ? 'text-[#2e7d32]' : 'text-gray-500'}
                                            >
                                                <Power className="h-4 w-4 mr-1" />
                                                {holiday.is_active ? 'Aktif' : 'Nonaktif'}
                                            </Button>
                                        </TableCell>
                                        <TableCell className="text-right">
                                            <div className="flex justify-end gap-2">
                                                <Link href={`/holidays/${holiday.id}/edit`}>
                                                    <Button variant="ghost" size="sm">
                                                        <Edit className="h-4 w-4" />
                                                    </Button>
                                                </Link>
                                                <Button
                                                    variant="ghost"
                                                    size="sm"
                                                    onClick={() => handleDelete(holiday.id)}
                                                    className="text-[#c62828] hover:text-[#c62828]"
                                                >
                                                    <Trash2 className="h-4 w-4" />
                                                </Button>
                                            </div>
                                        </TableCell>
                                    </TableRow>
                                ))
                            )}
                        </TableBody>
                    </Table>
                </div>

                <div className="flex items-center justify-between">
                    <div className="text-sm text-muted-foreground">
                        {holidays.from && holidays.to ? (
                            <>
                                Menampilkan {holidays.from} - {holidays.to} dari {holidays.total} data
                            </>
                        ) : (
                            <>Tidak ada data</>
                        )}
                    </div>
                    <div className="flex items-center gap-2">
                        <Button
                            variant="outline"
                            size="sm"
                            onClick={() => handlePageChange(holidays.current_page - 1)}
                            disabled={holidays.current_page === 1}
                        >
                            <ChevronLeft className="h-4 w-4" />
                            Previous
                        </Button>
                        <div className="text-sm">
                            Page {holidays.current_page} of {holidays.last_page}
                        </div>
                        <Button
                            variant="outline"
                            size="sm"
                            onClick={() => handlePageChange(holidays.current_page + 1)}
                            disabled={holidays.current_page === holidays.last_page}
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
