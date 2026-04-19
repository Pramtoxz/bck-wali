import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { Head, Link, useForm } from '@inertiajs/react';
import { ArrowLeft, Send, AlertCircle } from 'lucide-react';
import InputError from '@/components/input-error';
import { useState } from 'react';

interface User {
    id: number;
    name: string;
    nip: string;
}

interface Props {
    users: User[];
}

export default function NotificationsCreate({ users }: Props) {
    const { data, setData, post, processing, errors } = useForm({
        title: '',
        body: '',
        type: 'general',
        send_to: 'all',
        user_ids: [] as number[],
    });

    const [selectedUsers, setSelectedUsers] = useState<number[]>([]);

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        post('/notifications', {
            preserveScroll: true,
            onError: (errors) => {
                console.error('Validation errors:', errors);
            },
        });
    };

    const handleUserToggle = (userId: number) => {
        const newSelected = selectedUsers.includes(userId)
            ? selectedUsers.filter(id => id !== userId)
            : [...selectedUsers, userId];
        
        setSelectedUsers(newSelected);
        setData('user_ids', newSelected);
    };

    return (
        <AppLayout>
            <Head title="Kirim Notifikasi" />

            <div className="space-y-6 p-6">
                <div className="flex items-center gap-4">
                    <Link href="/notifications">
                        <Button variant="outline" size="icon">
                            <ArrowLeft className="h-4 w-4" />
                        </Button>
                    </Link>
                    <div>
                        <h1 className="text-2xl font-semibold">Kirim Notifikasi</h1>
                        <p className="text-sm text-muted-foreground">Kirim notifikasi ke user</p>
                    </div>
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle>Form Notifikasi</CardTitle>
                        <CardDescription>Lengkapi form di bawah untuk mengirim notifikasi</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <form onSubmit={handleSubmit} className="space-y-6">
                            {Object.keys(errors).length > 0 && !errors.title && !errors.body && !errors.type && !errors.send_to && !errors.user_ids && (
                                <Alert className="bg-red-50 border-red-300">
                                    <AlertCircle className="h-4 w-4 text-red-600" />
                                    <AlertDescription className="text-red-800">
                                        Terjadi kesalahan saat mengirim notifikasi. Silakan coba lagi.
                                    </AlertDescription>
                                </Alert>
                            )}

                            <div className="space-y-2">
                                <Label htmlFor="title">Judul Notifikasi</Label>
                                <Input
                                    id="title"
                                    value={data.title}
                                    onChange={(e) => setData('title', e.target.value)}
                                    placeholder="Pengumuman Penting"
                                />
                                <InputError message={errors.title} />
                            </div>

                            <div className="space-y-2">
                                <Label htmlFor="body">Isi Notifikasi</Label>
                                <Textarea
                                    id="body"
                                    value={data.body}
                                    onChange={(e) => setData('body', e.target.value)}
                                    placeholder="Isi pesan notifikasi..."
                                    rows={4}
                                />
                                <InputError message={errors.body} />
                            </div>

                            <div className="space-y-2">
                                <Label htmlFor="type">Tipe Notifikasi</Label>
                                <Select value={data.type} onValueChange={(value) => setData('type', value)}>
                                    <SelectTrigger>
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="general">Umum</SelectItem>
                                        <SelectItem value="attendance">Absensi</SelectItem>
                                        <SelectItem value="leave">Izin/Cuti</SelectItem>
                                        <SelectItem value="field_duty">Dinas</SelectItem>
                                        <SelectItem value="announcement">Pengumuman</SelectItem>
                                    </SelectContent>
                                </Select>
                                <InputError message={errors.type} />
                            </div>

                            <div className="space-y-2">
                                <Label htmlFor="send_to">Kirim Ke</Label>
                                <Select value={data.send_to} onValueChange={(value) => setData('send_to', value)}>
                                    <SelectTrigger>
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="all">Semua User</SelectItem>
                                        <SelectItem value="specific">User Tertentu</SelectItem>
                                    </SelectContent>
                                </Select>
                                <InputError message={errors.send_to} />
                            </div>

                            {data.send_to === 'specific' && (
                                <div className="space-y-2">
                                    <Label>Pilih User</Label>
                                    <div className="border rounded-md p-4 max-h-64 overflow-y-auto space-y-2">
                                        {users.map((user) => (
                                            <div key={user.id} className="flex items-center space-x-2">
                                                <input
                                                    type="checkbox"
                                                    id={`user-${user.id}`}
                                                    checked={selectedUsers.includes(user.id)}
                                                    onChange={() => handleUserToggle(user.id)}
                                                    className="rounded border-gray-300"
                                                />
                                                <Label htmlFor={`user-${user.id}`} className="font-normal cursor-pointer">
                                                    {user.name} ({user.nip})
                                                </Label>
                                            </div>
                                        ))}
                                    </div>
                                    <InputError message={errors.user_ids} />
                                    {selectedUsers.length > 0 && (
                                        <p className="text-sm text-muted-foreground">
                                            {selectedUsers.length} user dipilih
                                        </p>
                                    )}
                                </div>
                            )}

                            <div className="flex justify-end gap-2">
                                <Link href="/notifications">
                                    <Button type="button" variant="outline">
                                        Batal
                                    </Button>
                                </Link>
                                <Button type="submit" disabled={processing}>
                                    <Send className="mr-2 h-4 w-4" />
                                    {processing ? 'Mengirim...' : 'Kirim Notifikasi'}
                                </Button>
                            </div>
                        </form>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
