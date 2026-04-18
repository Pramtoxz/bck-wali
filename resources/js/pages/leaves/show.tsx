import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Alert, AlertDescription } from '@/components/ui/alert';
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
    AlertDialogTrigger,
} from '@/components/ui/alert-dialog';
import { Head, Link, router } from '@inertiajs/react';
import { ArrowLeft, CheckCircle, XCircle, FileText, Calendar, User } from 'lucide-react';
import { useState, useEffect } from 'react';
import { toast } from 'sonner';
import { usePage } from '@inertiajs/react';

interface User {
    id: number;
    name: string;
    nip: string;
    position: string;
    department: string;
    email: string;
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

interface Props {
    leave: Leave;
}

export default function LeavesShow({ leave }: Props) {
    const [processing, setProcessing] = useState(false);
    const page = usePage<{ flash?: { success?: string; error?: string } }>();
    const flash = page.props.flash;

    useEffect(() => {
        if (flash?.success) {
            toast.success(flash.success);
        }
    }, [flash]);

    const handleApprove = () => {
        setProcessing(true);
        router.patch(
            `/leaves/${leave.id}/status`,
            { status: 'approved' },
            {
                preserveScroll: true,
                onFinish: () => setProcessing(false),
            }
        );
    };

    const handleReject = () => {
        setProcessing(true);
        router.patch(
            `/leaves/${leave.id}/status`,
            { status: 'rejected' },
            {
                preserveScroll: true,
                onFinish: () => setProcessing(false),
            }
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
            <Badge variant={variants[status as keyof typeof variants]} className="text-sm">
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
            <Badge variant={variants[type as keyof typeof variants]} className="text-sm">
                {labels[type as keyof typeof labels]}
            </Badge>
        );
    };

    const formatDate = (dateString: string) => {
        return new Date(dateString).toLocaleDateString('id-ID', {
            day: 'numeric',
            month: 'long',
            year: 'numeric',
        });
    };

    return (
        <AppLayout>
            <Head title={`Detail Izin/Cuti - ${leave.user.name}`} />

            <div className="space-y-6 p-6">
                <div className="flex items-center justify-between">
                    <div className="flex items-center gap-4">
                        <Link href="/leaves">
                            <Button variant="outline" size="icon">
                                <ArrowLeft className="h-4 w-4" />
                            </Button>
                        </Link>
                        <div>
                            <h1 className="text-2xl font-semibold">Detail Pengajuan Izin/Cuti</h1>
                            <p className="text-sm text-muted-foreground">
                                ID Pengajuan: #{leave.id}
                            </p>
                        </div>
                    </div>
                    <div className="flex gap-2">
                        {getTypeBadge(leave.type)}
                        {getStatusBadge(leave.status)}
                    </div>
                </div>

                {leave.status === 'pending' && (
                    <Alert>
                        <AlertDescription>
                            Pengajuan ini menunggu persetujuan Anda. Silakan tinjau detail di bawah ini.
                        </AlertDescription>
                    </Alert>
                )}

                <div className="grid gap-6 md:grid-cols-2">
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <User className="h-5 w-5" />
                                Informasi Karyawan
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div>
                                <div className="text-sm font-medium text-muted-foreground">Nama</div>
                                <div className="text-base">{leave.user.name}</div>
                            </div>
                            <div>
                                <div className="text-sm font-medium text-muted-foreground">NIP</div>
                                <div className="text-base">{leave.user.nip}</div>
                            </div>
                            <div>
                                <div className="text-sm font-medium text-muted-foreground">Jabatan</div>
                                <div className="text-base">{leave.user.position}</div>
                            </div>
                            <div>
                                <div className="text-sm font-medium text-muted-foreground">Departemen</div>
                                <div className="text-base">{leave.user.department}</div>
                            </div>
                            <div>
                                <div className="text-sm font-medium text-muted-foreground">Email</div>
                                <div className="text-base">{leave.user.email}</div>
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <Calendar className="h-5 w-5" />
                                Periode Izin/Cuti
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div>
                                <div className="text-sm font-medium text-muted-foreground">Jenis</div>
                                <div className="text-base capitalize">{leave.type}</div>
                            </div>
                            <div>
                                <div className="text-sm font-medium text-muted-foreground">Tanggal Mulai</div>
                                <div className="text-base">{formatDate(leave.start_date)}</div>
                            </div>
                            <div>
                                <div className="text-sm font-medium text-muted-foreground">Tanggal Selesai</div>
                                <div className="text-base">{formatDate(leave.end_date)}</div>
                            </div>
                            <div>
                                <div className="text-sm font-medium text-muted-foreground">Total Hari</div>
                                <div className="text-base">{leave.total_days} hari</div>
                            </div>
                            <div>
                                <div className="text-sm font-medium text-muted-foreground">Tanggal Pengajuan</div>
                                <div className="text-base">{formatDate(leave.created_at)}</div>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle>Alasan/Keterangan</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="text-base whitespace-pre-wrap">{leave.reason}</div>
                    </CardContent>
                </Card>

                {leave.document_path && (
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <FileText className="h-5 w-5" />
                                Dokumen Pendukung
                            </CardTitle>
                            <CardDescription>Dokumen pendukung pengajuan izin/cuti</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <a
                                href={`/storage/${leave.document_path}`}
                                target="_blank"
                                rel="noopener noreferrer"
                            >
                                <Button variant="outline">
                                    <FileText className="mr-2 h-4 w-4" />
                                    Lihat Dokumen
                                </Button>
                            </a>
                        </CardContent>
                    </Card>
                )}

                {leave.status === 'pending' && (
                    <Card>
                        <CardHeader>
                            <CardTitle>Tindakan</CardTitle>
                            <CardDescription>Setujui atau tolak pengajuan izin/cuti ini</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div className="flex gap-4">
                                <AlertDialog>
                                    <AlertDialogTrigger asChild>
                                        <Button disabled={processing} className="flex-1">
                                            <CheckCircle className="mr-2 h-4 w-4" />
                                            Setujui
                                        </Button>
                                    </AlertDialogTrigger>
                                    <AlertDialogContent>
                                        <AlertDialogHeader>
                                            <AlertDialogTitle>Setujui Pengajuan Izin/Cuti</AlertDialogTitle>
                                            <AlertDialogDescription>
                                                Apakah Anda yakin ingin menyetujui pengajuan izin/cuti ini? Tindakan ini tidak dapat dibatalkan.
                                            </AlertDialogDescription>
                                        </AlertDialogHeader>
                                        <AlertDialogFooter>
                                            <AlertDialogCancel>Batal</AlertDialogCancel>
                                            <AlertDialogAction onClick={handleApprove}>
                                                Setujui
                                            </AlertDialogAction>
                                        </AlertDialogFooter>
                                    </AlertDialogContent>
                                </AlertDialog>

                                <AlertDialog>
                                    <AlertDialogTrigger asChild>
                                        <Button variant="destructive" disabled={processing} className="flex-1">
                                            <XCircle className="mr-2 h-4 w-4" />
                                            Tolak
                                        </Button>
                                    </AlertDialogTrigger>
                                    <AlertDialogContent>
                                        <AlertDialogHeader>
                                            <AlertDialogTitle>Tolak Pengajuan Izin/Cuti</AlertDialogTitle>
                                            <AlertDialogDescription>
                                                Apakah Anda yakin ingin menolak pengajuan izin/cuti ini? Tindakan ini tidak dapat dibatalkan.
                                            </AlertDialogDescription>
                                        </AlertDialogHeader>
                                        <AlertDialogFooter>
                                            <AlertDialogCancel>Batal</AlertDialogCancel>
                                            <AlertDialogAction 
                                                onClick={handleReject}
                                                className="bg-destructive text-destructive-foreground hover:bg-destructive/90"
                                            >
                                                Tolak
                                            </AlertDialogAction>
                                        </AlertDialogFooter>
                                    </AlertDialogContent>
                                </AlertDialog>
                            </div>
                        </CardContent>
                    </Card>
                )}
            </div>
        </AppLayout>
    );
}
