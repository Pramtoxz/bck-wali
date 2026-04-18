import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Head, Link, router } from '@inertiajs/react';
import { Search, Plus, Pencil, Trash2, ChevronLeft, ChevronRight } from 'lucide-react';
import { useState, useEffect } from 'react';

interface Position {
    id: number;
    name: string;
    description: string | null;
    created_at: string;
}

interface Props {
    positions: {
        data: Position[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        from: number | null;
        to: number | null;
    };
    filters: {
        search: string;
        per_page: number;
    };
}

export default function PositionsIndex({ positions, filters }: Props) {
    const [search, setSearch] = useState(filters.search || '');
    const perPage = filters.per_page;

    useEffect(() => {
        const timer = setTimeout(() => {
            router.get(
                '/positions',
                { search, per_page: perPage },
                { preserveState: true, replace: true, only: ['positions'] }
            );
        }, 300);

        return () => clearTimeout(timer);
    }, [search, perPage]);

    const handleDelete = (id: number, name: string) => {
        if (confirm(`Apakah Anda yakin ingin menghapus jabatan ${name}?`)) {
            router.delete(`/positions/${id}`);
        }
    };

    const handlePageChange = (page: number) => {
        router.get(
            '/positions',
            { search, per_page: perPage, page },
            { preserveState: true, only: ['positions'] }
        );
    };

    return (
        <AppLayout>
            <Head title="Manajemen Jabatan" />

            <div className="space-y-6 p-6">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-semibold">Manajemen Jabatan</h1>
                        <p className="text-sm text-muted-foreground">Kelola data jabatan pegawai</p>
                    </div>
                    <Link href="/positions/create">
                        <Button>
                            <Plus className="mr-2 h-4 w-4" />
                            Tambah Jabatan
                        </Button>
                    </Link>
                </div>

                <div className="flex items-center gap-4">
                    <div className="relative flex-1">
                        <Search className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                        <Input
                            placeholder="Cari nama jabatan..."
                            value={search}
                            onChange={(e) => setSearch(e.target.value)}
                            className="pl-9"
                        />
                    </div>
                </div>

                <div className="rounded-md border">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Nama Jabatan</TableHead>
                                <TableHead>Deskripsi</TableHead>
                                <TableHead className="text-right">Aksi</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {positions.data.length === 0 ? (
                                <TableRow>
                                    <TableCell colSpan={3} className="text-center text-muted-foreground">
                                        Tidak ada data jabatan
                                    </TableCell>
                                </TableRow>
                            ) : (
                                positions.data.map((position) => (
                                    <TableRow key={position.id}>
                                        <TableCell className="font-medium">{position.name}</TableCell>
                                        <TableCell>{position.description || '-'}</TableCell>
                                        <TableCell className="text-right">
                                            <div className="flex justify-end gap-2">
                                                <Link href={`/positions/${position.id}/edit`}>
                                                    <Button variant="outline" size="icon">
                                                        <Pencil className="h-4 w-4" />
                                                    </Button>
                                                </Link>
                                                <Button
                                                    variant="outline"
                                                    size="icon"
                                                    onClick={() => handleDelete(position.id, position.name)}
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
                        {positions.from && positions.to ? (
                            <>Menampilkan {positions.from} - {positions.to} dari {positions.total} jabatan</>
                        ) : (
                            <>Tidak ada data</>
                        )}
                    </div>
                    <div className="flex items-center gap-2">
                        <Button
                            variant="outline"
                            size="sm"
                            onClick={() => handlePageChange(positions.current_page - 1)}
                            disabled={positions.current_page === 1}
                        >
                            <ChevronLeft className="h-4 w-4" />
                            Previous
                        </Button>
                        <div className="text-sm">
                            Page {positions.current_page} of {positions.last_page}
                        </div>
                        <Button
                            variant="outline"
                            size="sm"
                            onClick={() => handlePageChange(positions.current_page + 1)}
                            disabled={positions.current_page === positions.last_page}
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
