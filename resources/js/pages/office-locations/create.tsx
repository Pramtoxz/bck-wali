import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Checkbox } from '@/components/ui/checkbox';
import InputError from '@/components/input-error';
import { Head, Link, useForm } from '@inertiajs/react';
import { MapPin, ArrowLeft } from 'lucide-react';

export default function OfficeLocationsCreate() {
    const { data, setData, post, processing, errors } = useForm({
        name: '',
        latitude: 0,
        longitude: 0,
        radius: 20,
        is_active: true as boolean,
    });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        post('/office-locations');
    };

    return (
        <AppLayout>
            <Head title="Tambah Lokasi Kantor" />

            <div className="p-6">
                <Card className="max-w-2xl">
                    <CardHeader>
                        <div className="flex items-center gap-4">
                            <Link href="/office-locations">
                                <Button variant="outline" size="icon">
                                    <ArrowLeft className="h-4 w-4" />
                                </Button>
                            </Link>
                            <div>
                                <CardTitle className="flex items-center gap-2">
                                    <MapPin className="h-5 w-5" />
                                    Tambah Lokasi Kantor
                                </CardTitle>
                                <CardDescription>Tambahkan lokasi kantor baru untuk validasi absensi</CardDescription>
                            </div>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <form onSubmit={handleSubmit} className="space-y-6">
                            <div className="space-y-2">
                                <Label htmlFor="name">Nama Kantor</Label>
                                <Input
                                    id="name"
                                    value={data.name}
                                    onChange={(e) => setData('name', e.target.value)}
                                    placeholder="Kantor Wali Nagari Padang Panjang"
                                    required
                                />
                                <InputError message={errors.name} />
                            </div>

                            <div className="space-y-4">
                                <div className="flex items-center justify-between">
                                    <Label>Koordinat Lokasi</Label>
                                    <Button
                                        type="button"
                                        variant="outline"
                                        size="sm"
                                        onClick={() =>
                                            window.open('https://www.google.com/maps', '_blank', 'noopener,noreferrer')
                                        }
                                    >
                                        <MapPin className="mr-2 h-4 w-4" />
                                        Buka Google Maps
                                    </Button>
                                </div>
                                <div className="rounded-lg border bg-muted/50 p-4 text-sm text-muted-foreground">
                                    <p className="font-medium mb-2">Cara mendapatkan koordinat:</p>
                                    <ol className="list-decimal list-inside space-y-1">
                                        <li>Klik tombol "Buka Google Maps" di atas</li>
                                        <li>Cari lokasi kantor yang diinginkan</li>
                                        <li>Klik kanan pada titik lokasi di peta</li>
                                        <li>Klik koordinat yang muncul (contoh: -0.9492, 100.3543)</li>
                                        <li>Koordinat akan otomatis tersalin, paste di form di bawah</li>
                                    </ol>
                                </div>

                                <div className="grid grid-cols-2 gap-4">
                                    <div className="space-y-2">
                                        <Label htmlFor="latitude">Latitude</Label>
                                        <Input
                                            id="latitude"
                                            type="number"
                                            step="any"
                                            value={data.latitude}
                                            onChange={(e) => setData('latitude', parseFloat(e.target.value) || 0)}
                                            placeholder="-0.9492"
                                            required
                                        />
                                        <InputError message={errors.latitude} />
                                    </div>

                                    <div className="space-y-2">
                                        <Label htmlFor="longitude">Longitude</Label>
                                        <Input
                                            id="longitude"
                                            type="number"
                                            step="any"
                                            value={data.longitude}
                                            onChange={(e) => setData('longitude', parseFloat(e.target.value) || 0)}
                                            placeholder="100.3543"
                                            required
                                        />
                                        <InputError message={errors.longitude} />
                                    </div>
                                </div>
                            </div>

                            <div className="space-y-2">
                                <Label htmlFor="radius">Radius (meter)</Label>
                                <Input
                                    id="radius"
                                    type="number"
                                    value={data.radius}
                                    onChange={(e) => setData('radius', parseInt(e.target.value) || 0)}
                                    placeholder="20"
                                    min="1"
                                    max="1000"
                                    required
                                />
                                <InputError message={errors.radius} />
                                <p className="text-sm text-muted-foreground">
                                    Jarak maksimal dari lokasi kantor untuk validasi absensi
                                </p>
                            </div>

                            <div className="flex items-center space-x-2">
                                <Checkbox
                                    id="is_active"
                                    checked={data.is_active}
                                    onCheckedChange={(checked) => setData('is_active', !!checked)}
                                />
                                <Label htmlFor="is_active" className="cursor-pointer">
                                    Aktifkan lokasi ini
                                </Label>
                            </div>
                            <p className="text-sm text-muted-foreground">
                                Hanya satu lokasi yang dapat aktif. Mengaktifkan lokasi ini akan menonaktifkan lokasi
                                lainnya.
                            </p>

                            <div className="flex gap-2">
                                <Button type="submit" disabled={processing}>
                                    {processing ? 'Menyimpan...' : 'Simpan'}
                                </Button>
                                <Link href="/office-locations">
                                    <Button type="button" variant="outline">
                                        Batal
                                    </Button>
                                </Link>
                            </div>
                        </form>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
