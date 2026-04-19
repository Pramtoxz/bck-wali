import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/react';
import { Users, Clock, TrendingUp, FileText, Plane, UserCheck, UserX, CheckCircle, XCircle, Building2, Briefcase } from 'lucide-react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { ChartContainer, ChartTooltip, ChartTooltipContent, ChartLegend, ChartLegendContent, type ChartConfig } from '@/components/ui/chart';
import { Bar, BarChart, CartesianGrid, XAxis, YAxis, Line, LineChart, Pie, PieChart, Cell, Legend, ResponsiveContainer } from 'recharts';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];

interface Statistics {
    total_users: number;
    total_admins: number;
    pending_approvals: number;
    attendance_rate: number;
    present_count: number;
    late_count: number;
    pending_field_duties: number;
    pending_leaves: number;
    approved_field_duties: number;
    approved_leaves: number;
    rejected_field_duties: number;
    rejected_leaves: number;
}

interface ChartData {
    attendance_weekly: Array<{ date: string; hadir: number; terlambat: number }>;
    attendance_monthly: Array<{ month: string; hadir: number; terlambat: number }>;
    department_stats: Array<{ department: string; count: number }>;
    position_stats: Array<{ position: string; count: number }>;
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
    charts: ChartData;
    recent_attendances: RecentAttendance[];
    recent_approvals: RecentApproval[];
}

const attendanceChartConfig = {
    hadir: {
        label: 'Hadir',
        color: '#2e7d32',
    },
    terlambat: {
        label: 'Terlambat',
        color: '#c62828',
    },
} satisfies ChartConfig;

const COLORS = ['#2e7d32', '#ffd600', '#c62828', '#454744', '#1976d2', '#f57c00'];

export default function Dashboard({ statistics, charts, recent_attendances, recent_approvals }: Props) {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Dashboard" />
            <div className="flex h-full flex-1 flex-col gap-6 p-6">
                {/* Statistics Cards */}
                <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                    <Card className="border-l-4 border-l-[#2e7d32]">
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Total Pegawai</CardTitle>
                            <Users className="h-5 w-5 text-[#2e7d32]" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-3xl font-bold">{statistics.total_users}</div>
                            <p className="text-xs text-muted-foreground mt-1">
                                {statistics.total_admins} Admin
                            </p>
                        </CardContent>
                    </Card>

                    <Card className="border-l-4 border-l-[#ffd600]">
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Persetujuan Pending</CardTitle>
                            <Clock className="h-5 w-5 text-[#ffd600]" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-3xl font-bold">{statistics.pending_approvals}</div>
                            <p className="text-xs text-muted-foreground mt-1">
                                {statistics.pending_field_duties} Dinas, {statistics.pending_leaves} Izin
                            </p>
                        </CardContent>
                    </Card>

                    <Card className="border-l-4 border-l-[#2e7d32]">
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Tingkat Kehadiran</CardTitle>
                            <TrendingUp className="h-5 w-5 text-[#2e7d32]" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-3xl font-bold">{statistics.attendance_rate}%</div>
                            <p className="text-xs text-muted-foreground mt-1">Bulan ini</p>
                        </CardContent>
                    </Card>

                    <Card className="border-l-4 border-l-[#c62828]">
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Hadir vs Terlambat</CardTitle>
                            <UserCheck className="h-5 w-5 text-[#c62828]" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-3xl font-bold">{statistics.present_count}</div>
                            <p className="text-xs text-muted-foreground mt-1">
                                {statistics.late_count} Terlambat
                            </p>
                        </CardContent>
                    </Card>
                </div>

                {/* Charts Row 1 */}
                <div className="grid gap-4 md:grid-cols-2">
                    {/* Weekly Attendance Chart */}
                    <Card>
                        <CardHeader>
                            <CardTitle>Kehadiran 7 Hari Terakhir</CardTitle>
                            <CardDescription>Grafik kehadiran dan keterlambatan</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <ChartContainer config={attendanceChartConfig} className="h-[300px] w-full">
                                <BarChart data={charts.attendance_weekly} accessibilityLayer>
                                    <CartesianGrid strokeDasharray="3 3" vertical={false} />
                                    <XAxis
                                        dataKey="date"
                                        tickLine={false}
                                        tickMargin={10}
                                        axisLine={false}
                                    />
                                    <YAxis tickLine={false} axisLine={false} />
                                    <ChartTooltip content={<ChartTooltipContent />} />
                                    <ChartLegend content={<ChartLegendContent />} />
                                    <Bar dataKey="hadir" fill="var(--color-hadir)" radius={[4, 4, 0, 0]} />
                                    <Bar dataKey="terlambat" fill="var(--color-terlambat)" radius={[4, 4, 0, 0]} />
                                </BarChart>
                            </ChartContainer>
                        </CardContent>
                    </Card>

                    {/* Monthly Attendance Trend */}
                    <Card>
                        <CardHeader>
                            <CardTitle>Tren Kehadiran 6 Bulan</CardTitle>
                            <CardDescription>Perbandingan kehadiran bulanan</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <ChartContainer config={attendanceChartConfig} className="h-[300px] w-full">
                                <LineChart data={charts.attendance_monthly} accessibilityLayer>
                                    <CartesianGrid strokeDasharray="3 3" />
                                    <XAxis
                                        dataKey="month"
                                        tickLine={false}
                                        tickMargin={10}
                                        axisLine={false}
                                    />
                                    <YAxis tickLine={false} axisLine={false} />
                                    <ChartTooltip content={<ChartTooltipContent />} />
                                    <ChartLegend content={<ChartLegendContent />} />
                                    <Line
                                        type="monotone"
                                        dataKey="hadir"
                                        stroke="var(--color-hadir)"
                                        strokeWidth={2}
                                        dot={{ fill: 'var(--color-hadir)', r: 4 }}
                                    />
                                    <Line
                                        type="monotone"
                                        dataKey="terlambat"
                                        stroke="var(--color-terlambat)"
                                        strokeWidth={2}
                                        dot={{ fill: 'var(--color-terlambat)', r: 4 }}
                                    />
                                </LineChart>
                            </ChartContainer>
                        </CardContent>
                    </Card>
                </div>

                {/* Charts Row 2 - Employee Distribution */}
                <div className="grid gap-4 md:grid-cols-2">
                    {/* Department Distribution */}
                    <Card>
                        <CardHeader>
                            <CardTitle>Distribusi Departemen</CardTitle>
                            <CardDescription>Jumlah pegawai per departemen</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div className="h-[300px] w-full">
                                <ResponsiveContainer width="100%" height="100%">
                                    <PieChart>
                                        <Pie
                                            data={charts.department_stats}
                                            cx="50%"
                                            cy="50%"
                                            labelLine={false}
                                            label={({ department, percent }) =>
                                                `${department}: ${(percent * 100).toFixed(0)}%`
                                            }
                                            outerRadius={80}
                                            fill="#8884d8"
                                            dataKey="count"
                                        >
                                            {charts.department_stats.map((entry, index) => (
                                                <Cell key={`cell-${index}`} fill={COLORS[index % COLORS.length]} />
                                            ))}
                                        </Pie>
                                        <ChartTooltip />
                                    </PieChart>
                                </ResponsiveContainer>
                            </div>
                        </CardContent>
                    </Card>

                    {/* Position Distribution */}
                    <Card>
                        <CardHeader>
                            <CardTitle>Distribusi Jabatan</CardTitle>
                            <CardDescription>Jumlah pegawai per jabatan</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div className="space-y-3">
                                {charts.position_stats.map((item, index) => (
                                    <div key={index} className="flex items-center justify-between">
                                        <div className="flex items-center gap-2">
                                            <div
                                                className="h-3 w-3 rounded-full"
                                                style={{ backgroundColor: COLORS[index % COLORS.length] }}
                                            />
                                            <span className="text-sm font-medium">{item.position}</span>
                                        </div>
                                        <div className="flex items-center gap-2">
                                            <div className="h-2 w-32 bg-muted rounded-full overflow-hidden">
                                                <div
                                                    className="h-full"
                                                    style={{
                                                        width: `${(item.count / statistics.total_users) * 100}%`,
                                                        backgroundColor: COLORS[index % COLORS.length],
                                                    }}
                                                />
                                            </div>
                                            <span className="text-sm font-bold w-8 text-right">{item.count}</span>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </CardContent>
                    </Card>
                </div>

                {/* Approval Status Cards */}
                <div className="grid gap-4 md:grid-cols-3">
                    <Card className="bg-gradient-to-br from-green-50 to-white dark:from-green-950/20 dark:to-background">
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Disetujui</CardTitle>
                            <CheckCircle className="h-5 w-5 text-[#2e7d32]" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-[#2e7d32]">
                                {statistics.approved_field_duties + statistics.approved_leaves}
                            </div>
                            <p className="text-xs text-muted-foreground mt-1">
                                {statistics.approved_field_duties} Dinas, {statistics.approved_leaves} Izin
                            </p>
                        </CardContent>
                    </Card>

                    <Card className="bg-gradient-to-br from-yellow-50 to-white dark:from-yellow-950/20 dark:to-background">
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Menunggu</CardTitle>
                            <Clock className="h-5 w-5 text-[#ffd600]" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-[#ffd600]">
                                {statistics.pending_approvals}
                            </div>
                            <p className="text-xs text-muted-foreground mt-1">
                                {statistics.pending_field_duties} Dinas, {statistics.pending_leaves} Izin
                            </p>
                        </CardContent>
                    </Card>

                    <Card className="bg-gradient-to-br from-red-50 to-white dark:from-red-950/20 dark:to-background">
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Ditolak</CardTitle>
                            <XCircle className="h-5 w-5 text-[#c62828]" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-[#c62828]">
                                {statistics.rejected_field_duties + statistics.rejected_leaves}
                            </div>
                            <p className="text-xs text-muted-foreground mt-1">
                                {statistics.rejected_field_duties} Dinas, {statistics.rejected_leaves} Izin
                            </p>
                        </CardContent>
                    </Card>
                </div>

                {/* Recent Activity */}
                <div className="grid gap-4 md:grid-cols-2">
                    <Card>
                        <CardHeader>
                            <CardTitle>Absensi Terbaru</CardTitle>
                            <CardDescription>5 absensi terakhir hari ini</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div className="space-y-4">
                                {recent_attendances.length === 0 ? (
                                    <p className="text-sm text-muted-foreground text-center py-8">
                                        Belum ada data absensi
                                    </p>
                                ) : (
                                    recent_attendances.map((attendance) => (
                                        <div
                                            key={attendance.id}
                                            className="flex items-center justify-between p-3 rounded-lg border bg-card hover:bg-accent/50 transition-colors"
                                        >
                                            <div className="flex items-center gap-3">
                                                <div className="h-10 w-10 rounded-full bg-[#2e7d32]/10 flex items-center justify-center">
                                                    <UserCheck className="h-5 w-5 text-[#2e7d32]" />
                                                </div>
                                                <div className="space-y-1">
                                                    <p className="text-sm font-medium">{attendance.user_name}</p>
                                                    <p className="text-xs text-muted-foreground">{attendance.date}</p>
                                                </div>
                                            </div>
                                            <div className="flex items-center gap-3">
                                                <div className="text-right text-xs">
                                                    <div className="font-medium">Masuk: {attendance.check_in_time}</div>
                                                    {attendance.check_out_time && (
                                                        <div className="text-muted-foreground">
                                                            Keluar: {attendance.check_out_time}
                                                        </div>
                                                    )}
                                                </div>
                                                <Badge
                                                    variant={attendance.status === 'late' ? 'destructive' : 'default'}
                                                    className={
                                                        attendance.status === 'late'
                                                            ? 'bg-[#c62828]'
                                                            : 'bg-[#2e7d32]'
                                                    }
                                                >
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
                            <CardDescription>Memerlukan persetujuan Anda</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div className="space-y-4">
                                {recent_approvals.length === 0 ? (
                                    <p className="text-sm text-muted-foreground text-center py-8">
                                        Tidak ada persetujuan pending
                                    </p>
                                ) : (
                                    recent_approvals.map((approval) => (
                                        <Link
                                            key={`${approval.type}-${approval.id}`}
                                            href={
                                                approval.type === 'field_duty'
                                                    ? `/field-duties/${approval.id}`
                                                    : `/leaves/${approval.id}`
                                            }
                                            className="flex items-center justify-between p-3 rounded-lg border bg-card hover:bg-accent transition-colors"
                                        >
                                            <div className="flex items-center gap-3">
                                                <div
                                                    className={`h-10 w-10 rounded-full flex items-center justify-center ${
                                                        approval.type === 'field_duty'
                                                            ? 'bg-[#ffd600]/10'
                                                            : 'bg-blue-500/10'
                                                    }`}
                                                >
                                                    {approval.type === 'field_duty' ? (
                                                        <Plane className="h-5 w-5 text-[#ffd600]" />
                                                    ) : (
                                                        <FileText className="h-5 w-5 text-blue-500" />
                                                    )}
                                                </div>
                                                <div className="space-y-1">
                                                    <p className="text-sm font-medium">{approval.user_name}</p>
                                                    <p className="text-xs text-muted-foreground">
                                                        {approval.description}
                                                    </p>
                                                </div>
                                            </div>
                                            <div className="text-right">
                                                <p className="text-xs text-muted-foreground mb-1">{approval.date}</p>
                                                <Badge variant="secondary" className="bg-[#ffd600]/20 text-[#ffd600]">
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
