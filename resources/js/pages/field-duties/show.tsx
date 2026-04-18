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
import { ArrowLeft, CheckCircle, XCircle, FileText, Calendar, MapPin, User } from 'lucide-react';
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

interface Props {
    fieldDuty: FieldDuty;
}

export default function FieldDutiesShow({ fieldDuty }: Props) {
    const [processing, setProcessing] = useState(false);
    const page = usePage();
    const flash = (page.props as any).flash;

    useEffect(() => {
        if (flash?.success) {
            toast.success(flash.success);
        }
    }, [flash]);

    const handleApprove = () => {
        setProcessing(true);
        router.patch(
            `/field-duties/${fieldDuty.id}/status`,
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
            `/field-duties/${fieldDuty.id}/status`,
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

    const formatDate = (dateString: string) => {
        return new Date(dateString).toLocaleDateString('id-ID', {
            day: 'numeric',
            month: 'long',
            year: 'numeric',
        });
    };

    return (
        <AppLayout>
            <Head title={`Detail Dinas - ${fieldDuty.user.name}`} />

            <div className="space-y-6 p-6">
                <div className="flex items-center justify-between">
                    <div className="flex items-center gap-4">
                        <Link href="/field-duties">
                            <Button variant="outline" size="icon">
                                <ArrowLeft className="h-4 w-4" />
                            </Button>
                        </Link>
                        <div>
                            <h1 className="text-2xl font-semibold">Detail Pengajuan Dinas</h1>
                            <p className="text-sm text-muted-foreground">
                                ID Pengajuan: #{fieldDuty.id}
                            </p>
                        </div>
                    </div>
                    <div>{getStatusBadge(fieldDuty.status)}</div>
                </div>

                {fieldDuty.status === 'pending' && (
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
                                <div className="text-base">{fieldDuty.user.name}</div>
                            </div>
                            <div>
                                <div className="text-sm font-medium text-muted-foreground">NIP</div>
                                <div className="text-base">{fieldDuty.user.nip}</div>
                            </div>
                            <div>
                                <div className="text-sm font-medium text-muted-foreground">Jabatan</div>
                                <div className="text-base">{fieldDuty.user.position}</div>
                            </div>
                            <div>
                                <div className="text-sm font-medium text-muted-foreground">Departemen</div>
                                <div className="text-base">{fieldDuty.user.department}</div>
                            </div>
                            <div>
                                <div className="text-sm font-medium text-muted-foreground">Email</div>
                                <div className="text-base">{fieldDuty.user.email}</div>
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <Calendar className="h-5 w-5" />
                                Periode Dinas
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div>
                                <div className="text-sm font-medium text-muted-foreground">Tanggal Mulai</div>
                                <div className="text-base">{formatDate(fieldDuty.start_date)}</div>
                            </div>
                            <div>
                                <div className="text-sm font-medium text-muted-foreground">Tanggal Selesai</div>
                                <div className="text-base">{formatDate(fieldDuty.end_date)}</div>
                            </div>
                            <div>
                                <div className="text-sm font-medium text-muted-foreground">Total Hari</div>
                                <div className="text-base">{fieldDuty.total_days} hari</div>
                            </div>
                            <div>
                                <div className="text-sm font-medium text-muted-foreground">Tanggal Pengajuan</div>
                                <div className="text-base">{formatDate(fieldDuty.created_at)}</div>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle className="flex items-center gap-2">
                            <MapPin className="h-5 w-5" />
                            Detail Dinas
                        </CardTitle>
                    </CardHeader>
                    <CardContent className="space-y-4">
                        <div>
                            <div className="text-sm font-medium text-muted-foreground">Tujuan Dinas</div>
                            <div className="text-base">{fieldDuty.destination}</div>
                        </div>
                        <div>
                            <div className="text-sm font-medium text-muted-foreground">Maksud & Tujuan</div>
                            <div className="text-base whitespace-pre-wrap">{fieldDuty.purpose}</div>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle className="flex items-center gap-2">
                            <FileText className="h-5 w-5" />
                            Dokumen Surat Tugas
                        </CardTitle>
                        <CardDescription>Dokumen pendukung pengajuan dinas</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <a
                            href={`/storage/${fieldDuty.document_path}`}
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

                {fieldDuty.status === 'pending' && (
                    <Card>
                        <CardHeader>
                            <CardTitle>Tindakan</CardTitle>
                            <CardDescription>Setujui atau tolak pengajuan dinas ini</CardDescription>
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
                                            <AlertDialogTitle>Setujui Pengajuan Dinas</AlertDialogTitle>
                                            <AlertDialogDescription>
                                                Apakah Anda yakin ingin menyetujui pengajuan dinas ini? Tindakan ini tidak dapat dibatalkan.
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
                                            <AlertDialogTitle>Tolak Pengajuan Dinas</AlertDialogTitle>
                                            <AlertDialogDescription>
                                                Apakah Anda yakin ingin menolak pengajuan dinas ini? Tindakan ini tidak dapat dibatalkan.
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
