import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Head, router } from '@inertiajs/react';
import { Calendar as CalendarIcon, Users, TrendingUp, Clock, CheckCircle, XCircle, Briefcase, FileText } from 'lucide-react';
import { useState } from 'react';
import { cn } from '@/lib/utils';

interface User {
    id: number;
    name: string;
    nip: string;
    position: string;
    department: string;
}

interface Summary {
    total_days: number;
    working_days: number;
    present: number;
    late: number;
    absent: number;
    field_duty: number;
    leave: number;
}

interface CalendarDay {
    status: 'present' | 'late' | 'absent' | 'field_duty' | 'leave';
    check_in?: string;
    check_out?: string;
    is_late?: boolean;
    description?: string;
}

interface DetailDay {
    date: string;
    day_name: string;
    status: 'present' | 'late' | 'absent' | 'field_duty' | 'leave';
    check_in: string | null;
    check_out: string | null;
    working_hours: string | null;
    is_late: boolean;
    description?: string;
}

interface RecapData {
    month: string;
    month_name: string;
    year: number;
    summary: Summary;
    calendar: Record<string, CalendarDay>;
    details: DetailDay[];
}

interface Props {
    users: User[];
    selectedUser: User | null;
    recapData: RecapData | null;
    filters: {
        month: string;
        user_id: number | null;
    };
}

export default function AttendanceRecapIndex({ users, selectedUser, recapData, filters }: Props) {
    const [selectedUserId, setSelectedUserId] = useState<string>(filters.user_id?.toString() || '');
    const [selectedMonth, setSelectedMonth] = useState(filters.month);

    const handleUserChange = (userId: string) => {
        setSelectedUserId(userId);
        router.get('/attendance-recap', { user_id: userId, month: selectedMonth }, { preserveState: true });
    };

    const handleMonthChange = (month: string) => {
        setSelectedMonth(month);
        if (selectedUserId) {
            router.get('/attendance-recap', { user_id: selectedUserId, month }, { preserveState: true });
        }
    };

    const getStatusColor = (status: string) => {
        const colors = {
            present: 'bg-primary',
            late: 'bg-destructive',
            field_duty: 'bg-yellow-500',
            leave: 'bg-blue-500',
            absent: 'bg-muted',
        };
        return colors[status as keyof typeof colors] || 'bg-muted';
    };

    const getStatusBadge = (status: string) => {
        const variants = {
            present: { variant: 'default' as const, label: 'Hadir' },
            late: { variant: 'destructive' as const, label: 'Terlambat' },
            field_duty: { variant: 'secondary' as const, label: 'Dinas' },
            leave: { variant: 'outline' as const, label: 'Izin' },
            absent: { variant: 'secondary' as const, label: 'Tidak Hadir' },
        };

        const config = variants[status as keyof typeof variants];
        return <Badge variant={config.variant}>{config.label}</Badge>;
    };

    const generateMonthOptions = () => {
        const options = [];
        const currentDate = new Date();
        for (let i = 0; i < 12; i++) {
            const date = new Date(currentDate.getFullYear(), currentDate.getMonth() - i, 1);
            const value = `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}`;
            const label = date.toLocaleDateString('id-ID', { month: 'long', year: 'numeric' });
            options.push({ value, label });
        }
        return options;
    };

    const renderCalendar = () => {
        if (!recapData) return null;

        const [year, month] = recapData.month.split('-').map(Number);
        const firstDay = new Date(year, month - 1, 1);
        const lastDay = new Date(year, month, 0);
        const daysInMonth = lastDay.getDate();
        const startDayOfWeek = firstDay.getDay();

        const days = [];
        for (let i = 0; i < startDayOfWeek; i++) {
            days.push(<div key={`empty-${i}`} className="aspect-square" />);
        }

        for (let day = 1; day <= daysInMonth; day++) {
            const dateString = `${year}-${String(month).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
            const dayData = recapData.calendar[dateString];
            const date = new Date(year, month - 1, day);
            const isWeekend = date.getDay() === 0 || date.getDay() === 6;

            days.push(
                <div
                    key={day}
                    className={cn(
                        'aspect-square p-2 border rounded-lg flex flex-col items-center justify-center',
                        isWeekend && 'bg-muted/50',
                        dayData && 'cursor-pointer hover:border-primary'
                    )}
                >
                    <div className="text-sm font-medium">{day}</div>
                    {dayData && (
                        <div className={cn('w-2 h-2 rounded-full mt-1', getStatusColor(dayData.status))} />
                    )}
                </div>
            );
        }

        return (
            <div className="grid grid-cols-7 gap-2">
                {['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'].map((day) => (
                    <div key={day} className="text-center text-sm font-medium text-muted-foreground py-2">
                        {day}
                    </div>
                ))}
                {days}
            </div>
        );
    };

    const formatTime = (time: string | null) => {
        if (!time) return '-';
        return time.substring(0, 5);
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
            <Head title="Rekap Absensi" />

            <div className="space-y-6 p-6">
                <div>
                    <h1 className="text-2xl font-semibold">Rekap Absensi</h1>
                    <p className="text-sm text-muted-foreground">Lihat rekapitulasi kehadiran karyawan</p>
                </div>

                <div className="grid gap-4 md:grid-cols-2">
                    <div className="space-y-2">
                        <label className="text-sm font-medium">Pilih Karyawan</label>
                        <Select value={selectedUserId} onValueChange={handleUserChange}>
                            <SelectTrigger>
                                <SelectValue placeholder="Pilih karyawan..." />
                            </SelectTrigger>
                            <SelectContent>
                                {users.map((user) => (
                                    <SelectItem key={user.id} value={user.id.toString()}>
                                        {user.name} - {user.nip}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                    </div>

                    <div className="space-y-2">
                        <label className="text-sm font-medium">Pilih Bulan</label>
                        <Select value={selectedMonth} onValueChange={handleMonthChange}>
                            <SelectTrigger>
                                <SelectValue />
                            </SelectTrigger>
                            <SelectContent>
                                {generateMonthOptions().map((option) => (
                                    <SelectItem key={option.value} value={option.value}>
                                        {option.label}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                    </div>
                </div>

                {selectedUser && recapData && (
                    <>
                        <Card>
                            <CardHeader>
                                <CardTitle className="flex items-center gap-2">
                                    <Users className="h-5 w-5" />
                                    Informasi Karyawan
                                </CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div className="grid gap-4 md:grid-cols-4">
                                    <div>
                                        <div className="text-sm font-medium text-muted-foreground">Nama</div>
                                        <div className="text-base font-medium">{selectedUser.name}</div>
                                    </div>
                                    <div>
                                        <div className="text-sm font-medium text-muted-foreground">NIP</div>
                                        <div className="text-base">{selectedUser.nip}</div>
                                    </div>
                                    <div>
                                        <div className="text-sm font-medium text-muted-foreground">Jabatan</div>
                                        <div className="text-base">{selectedUser.position}</div>
                                    </div>
                                    <div>
                                        <div className="text-sm font-medium text-muted-foreground">Departemen</div>
                                        <div className="text-base">{selectedUser.department}</div>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>

                        <div className="grid gap-4 md:grid-cols-4">
                            <Card>
                                <CardHeader className="pb-3">
                                    <CardTitle className="text-sm font-medium flex items-center gap-2">
                                        <CheckCircle className="h-4 w-4 text-primary" />
                                        Hadir
                                    </CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <div className="text-2xl font-bold">{recapData.summary.present}</div>
                                    <p className="text-xs text-muted-foreground">hari</p>
                                </CardContent>
                            </Card>

                            <Card>
                                <CardHeader className="pb-3">
                                    <CardTitle className="text-sm font-medium flex items-center gap-2">
                                        <Clock className="h-4 w-4 text-destructive" />
                                        Terlambat
                                    </CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <div className="text-2xl font-bold">{recapData.summary.late}</div>
                                    <p className="text-xs text-muted-foreground">hari</p>
                                </CardContent>
                            </Card>

                            <Card>
                                <CardHeader className="pb-3">
                                    <CardTitle className="text-sm font-medium flex items-center gap-2">
                                        <Briefcase className="h-4 w-4 text-yellow-600" />
                                        Dinas
                                    </CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <div className="text-2xl font-bold">{recapData.summary.field_duty}</div>
                                    <p className="text-xs text-muted-foreground">hari</p>
                                </CardContent>
                            </Card>

                            <Card>
                                <CardHeader className="pb-3">
                                    <CardTitle className="text-sm font-medium flex items-center gap-2">
                                        <XCircle className="h-4 w-4 text-muted-foreground" />
                                        Tidak Hadir
                                    </CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <div className="text-2xl font-bold">{recapData.summary.absent}</div>
                                    <p className="text-xs text-muted-foreground">hari</p>
                                </CardContent>
                            </Card>
                        </div>

                        <Card>
                            <CardHeader>
                                <CardTitle className="flex items-center gap-2">
                                    <CalendarIcon className="h-5 w-5" />
                                    Kalender Kehadiran - {recapData.month_name} {recapData.year}
                                </CardTitle>
                                <CardDescription>
                                    Total hari kerja: {recapData.summary.working_days} hari
                                </CardDescription>
                            </CardHeader>
                            <CardContent>{renderCalendar()}</CardContent>
                        </Card>

                        <Card>
                            <CardHeader>
                                <CardTitle className="flex items-center gap-2">
                                    <FileText className="h-5 w-5" />
                                    Detail Kehadiran
                                </CardTitle>
                                <CardDescription>
                                    Menampilkan 10 data terbaru
                                </CardDescription>
                            </CardHeader>
                            <CardContent>
                                <div className="space-y-3">
                                    {recapData.details.slice(0, 10).map((detail) => (
                                        <div
                                            key={detail.date}
                                            className="flex items-center justify-between p-4 border rounded-lg"
                                        >
                                            <div className="flex-1">
                                                <div className="flex items-center gap-3">
                                                    <div>
                                                        <div className="font-medium">
                                                            {detail.day_name}, {formatDate(detail.date)}
                                                        </div>
                                                        {detail.description && (
                                                            <div className="text-sm text-muted-foreground">
                                                                {detail.description}
                                                            </div>
                                                        )}
                                                    </div>
                                                </div>
                                            </div>
                                            <div className="flex items-center gap-4">
                                                {detail.check_in && (
                                                    <div className="text-sm">
                                                        <div className="text-muted-foreground">Masuk</div>
                                                        <div className="font-medium">{formatTime(detail.check_in)}</div>
                                                    </div>
                                                )}
                                                {detail.check_out && (
                                                    <div className="text-sm">
                                                        <div className="text-muted-foreground">Keluar</div>
                                                        <div className="font-medium">{formatTime(detail.check_out)}</div>
                                                    </div>
                                                )}
                                                {detail.working_hours && (
                                                    <div className="text-sm">
                                                        <div className="text-muted-foreground">Jam Kerja</div>
                                                        <div className="font-medium">{formatTime(detail.working_hours)}</div>
                                                    </div>
                                                )}
                                                <div>{getStatusBadge(detail.status)}</div>
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            </CardContent>
                        </Card>
                    </>
                )}

                {!selectedUserId && (
                    <Card>
                        <CardContent className="flex flex-col items-center justify-center py-12">
                            <Users className="h-12 w-12 text-muted-foreground mb-4" />
                            <p className="text-muted-foreground">Pilih karyawan untuk melihat rekap absensi</p>
                        </CardContent>
                    </Card>
                )}
            </div>
        </AppLayout>
    );
}
