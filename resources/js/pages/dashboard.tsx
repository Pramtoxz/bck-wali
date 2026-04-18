import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/react';
import { Users, Clock, TrendingUp, FileText, Plane } from 'lucide-react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];

interface Statistics {
    total_users: number;
    pending_approvals: number;
    attendance_rate: number;
}

interface RecentAttendance {
    id: number;
    user_name: string;
    date: string;
    check_in_time: string;
    check_out_time: string | null;
    status: 'present' | 'late';
}

interface RecentApproval {
    id: number;
    type: 'field_duty' | 'leave';
    user_name: string;
    description: string;
    date: string;
    status: string;
}

interface Props {
    statistics: Statistics;
    recent_attendances: RecentAttendance[];
    recent_approvals: RecentApproval[];
}

export default function Dashboard({ statistics, recent_attendances, recent_approvals }: Props) {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Dashboard" />
            <div className="flex h-full flex-1 flex-col gap-6 p-6">
                <div className="grid gap-4 md:grid-cols-3">
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Total Pegawai</CardTitle>
                            <Users className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{statistics.total_users}</div>
                            <p className="text-xs text-muted-foreground">Pegawai aktif</p>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Persetujuan Pending</CardTitle>
                            <Clock className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{statistics.pending_approvals}</div>
                            <p className="text-xs text-muted-foreground">Menunggu persetujuan</p>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Tingkat Kehadiran</CardTitle>
                            <TrendingUp className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{statistics.attendance_rate}%</div>
                            <p className="text-xs text-muted-foreground">Bulan ini</p>
                        </CardContent>
                    </Card>
                </div>

                <div className="grid gap-4 md:grid-cols-2">
                    <Card>
                        <CardHeader>
                            <CardTitle>Absensi Terbaru</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="space-y-4">
                                {recent_attendances.length === 0 ? (
                                    <p className="text-sm text-muted-foreground">Belum ada data absensi</p>
                                ) : (
                                    recent_attendances.map((attendance) => (
                                        <div key={attendance.id} className="flex items-center justify-between">
                                            <div className="space-y-1">
                                                <p className="text-sm font-medium">{attendance.user_name}</p>
                                                <p className="text-xs text-muted-foreground">{attendance.date}</p>
                                            </div>
                                            <div className="flex items-center gap-2">
                                                <div className="text-right text-xs">
                                                    <div>Masuk: {attendance.check_in_time}</div>
                                                    {attendance.check_out_time && (
                                                        <div>Keluar: {attendance.check_out_time}</div>
                                                    )}
                                                </div>
                                                <Badge variant={attendance.status === 'late' ? 'destructive' : 'default'}>
                                                    {attendance.status === 'late' ? 'Terlambat' : 'Hadir'}
                                                </Badge>
                                            </div>
                                        </div>
                                    ))
                                )}
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle>Persetujuan Pending</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="space-y-4">
                                {recent_approvals.length === 0 ? (
                                    <p className="text-sm text-muted-foreground">Tidak ada persetujuan pending</p>
                                ) : (
                                    recent_approvals.map((approval) => (
                                        <Link
                                            key={`${approval.type}-${approval.id}`}
                                            href={
                                                approval.type === 'field_duty'
                                                    ? `/field-duties/${approval.id}`
                                                    : `/leaves/${approval.id}`
                                            }
                                            className="flex items-center justify-between hover:bg-accent rounded-lg p-2 transition-colors"
                                        >
                                            <div className="flex items-center gap-3">
                                                {approval.type === 'field_duty' ? (
                                                    <Plane className="h-4 w-4 text-muted-foreground" />
                                                ) : (
                                                    <FileText className="h-4 w-4 text-muted-foreground" />
                                                )}
                                                <div className="space-y-1">
                                                    <p className="text-sm font-medium">{approval.user_name}</p>
                                                    <p className="text-xs text-muted-foreground">
                                                        {approval.description}
                                                    </p>
                                                </div>
                                            </div>
                                            <div className="text-right">
                                                <p className="text-xs text-muted-foreground">{approval.date}</p>
                                                <Badge variant="secondary" className="mt-1">
                                                    Pending
                                                </Badge>
                                            </div>
                                        </Link>
                                    ))
                                )}
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </AppLayout>
    );
}
