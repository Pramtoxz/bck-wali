import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Head, router } from '@inertiajs/react';
import { Search, Calendar, ChevronLeft, ChevronRight, Users, UserCheck, UserX, Clock, Plane, FileText } from 'lucide-react';
import { useState, useEffect } from 'react';

interface UserAttendance {
    id: number;
    name: string;
    nip: string;
    position: string;
    department: string;
    avatar: string;
    attendance: {
        id: number;
        check_in_time: string;
        check_out_time: string | null;
        working_hours: string | null;
    } | null;
    status: string;
    status_label: string;
    status_color: string;
    description: string | null;
}

interface Props {
    users: {
        data: UserAttendance[];
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
        present: number;
        late: number;
        checked_in: number;
        field_duty: number;
        leave: number;
        absent: number;
    };
    filters: {
        search: string;
        per_page: number;
        date: string;
    };
}

export default function AttendancesIndex({ users, summary, filters }: Props) {
    const [search, setSearch] = useState(filters.search || '');
    const [date, setDate] = useState(filters.date || new Date().toISOString().split('T')[0]);
    const perPage = filters.per_page;

    useEffect(() => {
        const timer = setTimeout(() => {
            router.get(
                '/attendances',
                { search, per_page: perPage, date },
                { preserveState: true, replace: true, only: ['users', 'summary'] },
            );
        }, 300);

        return () => clearTimeout(timer);
    }, [search, date, perPage]);

    const handlePageChange = (page: number) => {
        router.get(
            '/attendances',
            { search, per_page: perPage, date, page },
            { preserveState: true, only: ['users'] },
        );
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

    const getStatusBadge = (user: UserAttendance) => {
        const variants: Record<string, string> = {
            success: 'bg-[#2e7d32] hover:bg-[#2e7d32]/90',
            destructive: 'bg-[#c62828] hover:bg-[#c62828]/90',
            warning: 'bg-[#ffd600] text-black hover:bg-[#ffd600]/90',
            info: 'bg-blue-500 hover:bg-blue-500/90',
            secondary: 'bg-gray-500 hover:bg-gray-500/90',
        };

        return (
            <Badge className={variants[user.status_color] || variants.secondary}>
                {user.status_label}
            </Badge>
        );
    };

    return (
        <AppLayout>
            <Head title="Data Absensi" />

            <div className="space-y-6 p-6">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-semibold">Data Absensi</h1>
                        <p className="text-sm text-muted-foreground">
                            {formatDate(date)}
                        </p>
                    </div>
                </div>

                {/* Summary Cards */}
                <div className="grid gap-4 md:grid-cols-4 lg:grid-cols-7">
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Total</CardTitle>
                            <Users className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{summary.total}</div>
                        </CardContent>
                    </Card>

                    <Card className="border-l-4 border-l-[#2e7d32]">
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Hadir</CardTitle>
                            <UserCheck className="h-4 w-4 text-[#2e7d32]" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-[#2e7d32]">{summary.present}</div>
                        </CardContent>
                    </Card>

                    <Card className="border-l-4 border-l-[#c62828]">
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Terlambat</CardTitle>
                            <Clock className="h-4 w-4 text-[#c62828]" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-[#c62828]">{summary.late}</div>
                        </CardContent>
                    </Card>

                    <Card className="border-l-4 border-l-[#ffd600]">
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Check In</CardTitle>
                            <Clock className="h-4 w-4 text-[#ffd600]" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-[#ffd600]">{summary.checked_in}</div>
                        </CardContent>
                    </Card>

                    <Card className="border-l-4 border-l-[#ffd600]">
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Dinas</CardTitle>
                            <Plane className="h-4 w-4 text-[#ffd600]" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-[#ffd600]">{summary.field_duty}</div>
                        </CardContent>
                    </Card>

                    <Card className="border-l-4 border-l-blue-500">
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Izin/Cuti</CardTitle>
                            <FileText className="h-4 w-4 text-blue-500" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-blue-500">{summary.leave}</div>
                        </CardContent>
                    </Card>

                    <Card className="border-l-4 border-l-gray-500">
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Tidak Hadir</CardTitle>
                            <UserX className="h-4 w-4 text-gray-500" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-gray-500">{summary.absent}</div>
                        </CardContent>
                    </Card>
                </div>

                {/* Filters */}
                <div className="flex items-center gap-4">
                    <div className="relative flex-1">
                        <Search className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                        <Input
                            placeholder="Cari nama, NIP..."
                            value={search}
                            onChange={(e) => setSearch(e.target.value)}
                            className="pl-9"
                        />
                    </div>
                    <div className="relative w-64">
                        <Calendar className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                        <Input
                            type="date"
                            value={date}
                            onChange={(e) => setDate(e.target.value)}
                            className="pl-9"
                        />
                    </div>
                </div>

                {/* Table */}
                <div className="rounded-md border">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Pegawai</TableHead>
                                <TableHead>NIP</TableHead>
                                <TableHead>Jabatan</TableHead>
                                <TableHead>Check In</TableHead>
                                <TableHead>Check Out</TableHead>
                                <TableHead>Jam Kerja</TableHead>
                                <TableHead>Status</TableHead>
                                <TableHead>Keterangan</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {users.data.length === 0 ? (
                                <TableRow>
                                    <TableCell colSpan={8} className="text-center text-muted-foreground py-8">
                                        Tidak ada data
                                    </TableCell>
                                </TableRow>
                            ) : (
                                users.data.map((user) => (
                                    <TableRow key={user.id}>
                                        <TableCell>
                                            <div className="flex items-center gap-3">
                                                <img
                                                    src={user.avatar}
                                                    alt={user.name}
                                                    className="h-10 w-10 rounded-full object-cover border-2 border-[#2e7d32]"
                                                />
                                                <div>
                                                    <div className="font-medium">{user.name}</div>
                                                    <div className="text-sm text-muted-foreground">
                                                        {user.department}
                                                    </div>
                                                </div>
                                            </div>
                                        </TableCell>
                                        <TableCell className="font-mono text-sm">{user.nip}</TableCell>
                                        <TableCell className="text-sm">{user.position}</TableCell>
                                        <TableCell>
                                            {user.attendance?.check_in_time ? (
                                                <span className="font-mono text-sm">
                                                    {user.attendance.check_in_time}
                                                </span>
                                            ) : (
                                                <span className="text-muted-foreground">-</span>
                                            )}
                                        </TableCell>
                                        <TableCell>
                                            {user.attendance?.check_out_time ? (
                                                <span className="font-mono text-sm">
                                                    {user.attendance.check_out_time}
                                                </span>
                                            ) : (
                                                <span className="text-muted-foreground">-</span>
                                            )}
                                        </TableCell>
                                        <TableCell>
                                            {user.attendance?.working_hours ? (
                                                <span className="font-mono text-sm">
                                                    {user.attendance.working_hours}
                                                </span>
                                            ) : (
                                                <span className="text-muted-foreground">-</span>
                                            )}
                                        </TableCell>
                                        <TableCell>{getStatusBadge(user)}</TableCell>
                                        <TableCell>
                                            {user.description ? (
                                                <span className="text-sm">{user.description}</span>
                                            ) : (
                                                <span className="text-muted-foreground">-</span>
                                            )}
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
                        {users.from && users.to ? (
                            <>
                                Menampilkan {users.from} - {users.to} dari {users.total} pegawai
                            </>
                        ) : (
                            <>Tidak ada data</>
                        )}
                    </div>
                    <div className="flex items-center gap-2">
                        <Button
                            variant="outline"
                            size="sm"
                            onClick={() => handlePageChange(users.current_page - 1)}
                            disabled={users.current_page === 1}
                        >
                            <ChevronLeft className="h-4 w-4" />
                            Previous
                        </Button>
                        <div className="text-sm">
                            Page {users.current_page} of {users.last_page}
                        </div>
                        <Button
                            variant="outline"
                            size="sm"
                            onClick={() => handlePageChange(users.current_page + 1)}
                            disabled={users.current_page === users.last_page}
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
