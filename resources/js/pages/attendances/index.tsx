import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Badge } from '@/components/ui/badge';
import { Head, router } from '@inertiajs/react';
import { Search, Calendar, ChevronLeft, ChevronRight } from 'lucide-react';
import { useState, useEffect } from 'react';

interface User {
    id: number;
    name: string;
    username: string;
    nip: string;
    position: string;
    department: string;
}

interface Attendance {
    id: number;
    user: User;
    date: string;
    check_in_time: string | null;
    check_out_time: string | null;
    working_hours: string | null;
    check_in_photo: string | null;
    check_out_photo: string | null;
    created_at: string;
}

interface Props {
    attendances: {
        data: Attendance[];
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
        search: string;
        per_page: number;
        start_date: string;
        end_date: string;
    };
}

export default function AttendancesIndex({ attendances, filters }: Props) {
    const [search, setSearch] = useState(filters.search || '');
    const [startDate, setStartDate] = useState(filters.start_date || '');
    const [endDate, setEndDate] = useState(filters.end_date || '');
    const perPage = filters.per_page;

    useEffect(() => {
        const timer = setTimeout(() => {
            router.get(
                '/attendances',
                { search, per_page: perPage, start_date: startDate, end_date: endDate },
                { preserveState: true, replace: true, only: ['attendances'] },
            );
        }, 300);

        return () => clearTimeout(timer);
    }, [search, startDate, endDate, perPage]);

    const handlePageChange = (page: number) => {
        router.get(
            '/attendances',
            { search, per_page: perPage, start_date: startDate, end_date: endDate, page },
            { preserveState: true, only: ['attendances'] },
        );
    };

    const formatDate = (dateString: string) => {
        const date = new Date(dateString);
        return date.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
    };

    const getStatusBadge = (attendance: Attendance) => {
        if (attendance.check_in_time && attendance.check_out_time) {
            return <Badge variant="default">Selesai</Badge>;
        } else if (attendance.check_in_time) {
            return <Badge className="bg-yellow-500">Check In</Badge>;
        } else {
            return <Badge variant="secondary">Belum Absen</Badge>;
        }
    };

    return (
        <AppLayout>
            <Head title="Data Absensi" />

            <div className="space-y-6 p-6">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-semibold">Data Absensi</h1>
                        <p className="text-sm text-muted-foreground">Riwayat absensi karyawan</p>
                    </div>
                </div>

                <div className="flex items-center gap-4">
                    <div className="relative flex-1">
                        <Search className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                        <Input
                            placeholder="Cari nama, username, atau NIP..."
                            value={search}
                            onChange={(e) => setSearch(e.target.value)}
                            className="pl-9"
                        />
                    </div>
                    <div className="relative w-48">
                        <Calendar className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                        <Input
                            type="date"
                            placeholder="Tanggal Mulai"
                            value={startDate}
                            onChange={(e) => setStartDate(e.target.value)}
                            className="pl-9"
                        />
                    </div>
                    <div className="relative w-48">
                        <Calendar className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                        <Input
                            type="date"
                            placeholder="Tanggal Akhir"
                            value={endDate}
                            onChange={(e) => setEndDate(e.target.value)}
                            className="pl-9"
                        />
                    </div>
                </div>

                <div className="rounded-md border">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Tanggal</TableHead>
                                <TableHead>Karyawan</TableHead>
                                <TableHead>NIP</TableHead>
                                <TableHead>Check In</TableHead>
                                <TableHead>Check Out</TableHead>
                                <TableHead>Jam Kerja</TableHead>
                                <TableHead>Status</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {attendances.data.length === 0 ? (
                                <TableRow>
                                    <TableCell colSpan={7} className="text-center text-muted-foreground">
                                        Tidak ada data absensi
                                    </TableCell>
                                </TableRow>
                            ) : (
                                attendances.data.map((attendance) => (
                                    <TableRow key={attendance.id}>
                                        <TableCell className="font-medium">{formatDate(attendance.date)}</TableCell>
                                        <TableCell>
                                            <div>
                                                <div className="font-medium">{attendance.user.name}</div>
                                                <div className="text-sm text-muted-foreground">
                                                    {attendance.user.position}
                                                </div>
                                            </div>
                                        </TableCell>
                                        <TableCell className="font-mono text-sm">{attendance.user.nip}</TableCell>
                                        <TableCell>
                                            {attendance.check_in_time ? (
                                                <span className="font-mono">{attendance.check_in_time}</span>
                                            ) : (
                                                <span className="text-muted-foreground">-</span>
                                            )}
                                        </TableCell>
                                        <TableCell>
                                            {attendance.check_out_time ? (
                                                <span className="font-mono">{attendance.check_out_time}</span>
                                            ) : (
                                                <span className="text-muted-foreground">-</span>
                                            )}
                                        </TableCell>
                                        <TableCell>
                                            {attendance.working_hours ? (
                                                <span className="font-mono">{attendance.working_hours}</span>
                                            ) : (
                                                <span className="text-muted-foreground">-</span>
                                            )}
                                        </TableCell>
                                        <TableCell>{getStatusBadge(attendance)}</TableCell>
                                    </TableRow>
                                ))
                            )}
                        </TableBody>
                    </Table>
                </div>

                <div className="flex items-center justify-between">
                    <div className="text-sm text-muted-foreground">
                        {attendances.from && attendances.to ? (
                            <>
                                Menampilkan {attendances.from} - {attendances.to} dari {attendances.total} data
                            </>
                        ) : (
                            <>Tidak ada data</>
                        )}
                    </div>
                    <div className="flex items-center gap-2">
                        <Button
                            variant="outline"
                            size="sm"
                            onClick={() => handlePageChange(attendances.current_page - 1)}
                            disabled={attendances.current_page === 1}
                        >
                            <ChevronLeft className="h-4 w-4" />
                            Previous
                        </Button>
                        <div className="text-sm">
                            Page {attendances.current_page} of {attendances.last_page}
                        </div>
                        <Button
                            variant="outline"
                            size="sm"
                            onClick={() => handlePageChange(attendances.current_page + 1)}
                            disabled={attendances.current_page === attendances.last_page}
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
