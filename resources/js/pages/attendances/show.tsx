import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { ArrowLeft, MapPin, Clock, User } from 'lucide-react';

interface User {
    id: number;
    name: string;
    nip: string;
    position: string;
    department: string;
}

interface Location {
    latitude: number;
    longitude: number;
}

interface Attendance {
    id: number;
    date: string;
    day_name: string;
    user: User;
    check_in_time: string;
    check_out_time: string | null;
    check_in_photo: string | null;
    check_out_photo: string | null;
    check_in_location: Location;
    check_out_location: Location | null;
    working_hours: string | null;
    is_late: boolean;
}

interface Props {
    attendance: Attendance;
}

export default function AttendanceShow({ attendance }: Props) {
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Dashboard', href: '/dashboard' },
        { title: 'Absensi', href: '/attendances' },
        { title: 'Detail', href: `/attendances/${attendance.id}` },
    ];

    const formatDate = (date: string, dayName: string) => {
        const d = new Date(date);
        return `${dayName}, ${d.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' })}`;
    };

    const getMapUrl = (lat: number, lng: number) => {
        return `https://www.google.com/maps?q=${lat},${lng}&hl=id&z=18&output=embed`;
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Detail Absensi - ${attendance.user.name}`} />
            <div className="flex h-full flex-1 flex-col gap-6 p-6">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-bold">Detail Absensi</h1>
                        <p className="text-sm text-muted-foreground">
                            {formatDate(attendance.date, attendance.day_name)}
                        </p>
                    </div>
                    <Link href="/attendances">
                        <Button variant="outline">
                            <ArrowLeft className="mr-2 h-4 w-4" />
                            Kembali
                        </Button>
                    </Link>
                </div>

                <div className="grid gap-6 md:grid-cols-2">
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <User className="h-5 w-5" />
                                Informasi Pegawai
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-3">
                            <div>
                                <p className="text-sm text-muted-foreground">Nama</p>
                                <p className="font-medium">{attendance.user.name}</p>
                            </div>
                            <div>
                                <p className="text-sm text-muted-foreground">NIP</p>
                                <p className="font-medium">{attendance.user.nip}</p>
                            </div>
                            <div>
                                <p className="text-sm text-muted-foreground">Jabatan</p>
                                <p className="font-medium">{attendance.user.position}</p>
                            </div>
                            <div>
                                <p className="text-sm text-muted-foreground">Departemen</p>
                                <p className="font-medium">{attendance.user.department}</p>
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <Clock className="h-5 w-5" />
                                Waktu Absensi
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-3">
                            <div>
                                <p className="text-sm text-muted-foreground">Check In</p>
                                <div className="flex items-center gap-2">
                                    <p className="font-medium">{attendance.check_in_time}</p>
                                    {attendance.is_late && (
                                        <Badge variant="destructive" className="text-xs">
                                            Terlambat
                                        </Badge>
                                    )}
                                </div>
                            </div>
                            <div>
                                <p className="text-sm text-muted-foreground">Check Out</p>
                                <p className="font-medium">{attendance.check_out_time || '-'}</p>
                            </div>
                            <div>
                                <p className="text-sm text-muted-foreground">Jam Kerja</p>
                                <p className="font-medium">{attendance.working_hours || '-'}</p>
                            </div>
                            <div>
                                <p className="text-sm text-muted-foreground">Status</p>
                                <Badge variant={attendance.is_late ? 'destructive' : 'default'}>
                                    {attendance.is_late ? 'Terlambat' : 'Tepat Waktu'}
                                </Badge>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <div className="grid gap-6 md:grid-cols-2">
                    <Card>
                        <CardHeader>
                            <CardTitle>Foto Check In</CardTitle>
                        </CardHeader>
                        <CardContent>
                            {attendance.check_in_photo ? (
                                <img
                                    src={attendance.check_in_photo}
                                    alt="Check In"
                                    className="w-full rounded-lg object-cover"
                                />
                            ) : (
                                <div className="flex h-64 items-center justify-center rounded-lg bg-muted">
                                    <p className="text-sm text-muted-foreground">Foto tidak tersedia</p>
                                </div>
                            )}
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle>Foto Check Out</CardTitle>
                        </CardHeader>
                        <CardContent>
                            {attendance.check_out_photo ? (
                                <img
                                    src={attendance.check_out_photo}
                                    alt="Check Out"
                                    className="w-full rounded-lg object-cover"
                                />
                            ) : (
                                <div className="flex h-64 items-center justify-center rounded-lg bg-muted">
                                    <p className="text-sm text-muted-foreground">
                                        {attendance.check_out_time ? 'Foto tidak tersedia' : 'Belum check out'}
                                    </p>
                                </div>
                            )}
                        </CardContent>
                    </Card>
                </div>

                <div className="grid gap-6 md:grid-cols-2">
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <MapPin className="h-5 w-5" />
                                Lokasi Check In
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="space-y-3">
                                <div>
                                    <p className="text-sm text-muted-foreground">Koordinat</p>
                                    <p className="font-mono text-sm">
                                        {attendance.check_in_location.latitude.toFixed(6)},{' '}
                                        {attendance.check_in_location.longitude.toFixed(6)}
                                    </p>
                                </div>
                                <div className="aspect-video w-full overflow-hidden rounded-lg">
                                    <iframe
                                        src={getMapUrl(
                                            attendance.check_in_location.latitude,
                                            attendance.check_in_location.longitude
                                        )}
                                        className="h-full w-full border-0"
                                        loading="lazy"
                                        title="Lokasi Check In"
                                    />
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <MapPin className="h-5 w-5" />
                                Lokasi Check Out
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            {attendance.check_out_location ? (
                                <div className="space-y-3">
                                    <div>
                                        <p className="text-sm text-muted-foreground">Koordinat</p>
                                        <p className="font-mono text-sm">
                                            {attendance.check_out_location.latitude.toFixed(6)},{' '}
                                            {attendance.check_out_location.longitude.toFixed(6)}
                                        </p>
                                    </div>
                                    <div className="aspect-video w-full overflow-hidden rounded-lg">
                                        <iframe
                                            src={getMapUrl(
                                                attendance.check_out_location.latitude,
                                                attendance.check_out_location.longitude
                                            )}
                                            className="h-full w-full border-0"
                                            loading="lazy"
                                            title="Lokasi Check Out"
                                        />
                                    </div>
                                </div>
                            ) : (
                                <div className="flex h-64 items-center justify-center rounded-lg bg-muted">
                                    <p className="text-sm text-muted-foreground">
                                        {attendance.check_out_time ? 'Lokasi tidak tersedia' : 'Belum check out'}
                                    </p>
                                </div>
                            )}
                        </CardContent>
                    </Card>
                </div>
            </div>
        </AppLayout>
    );
}
