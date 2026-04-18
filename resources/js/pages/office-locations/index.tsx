import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Head, Link } from '@inertiajs/react';
import { MapPin, Pencil, MapPinned } from 'lucide-react';

interface OfficeLocation {
    id: number;
    name: string;
    latitude: number;
    longitude: number;
    radius: number;
    is_active: boolean;
    created_at: string;
}

interface Props {
    location: OfficeLocation | null;
}

export default function OfficeLocationsIndex({ location }: Props) {
    return (
        <AppLayout>
            <Head title="Lokasi Kantor" />

            <div className="space-y-6 p-6">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-semibold">Lokasi Kantor</h1>
                        <p className="text-sm text-muted-foreground">
                            Lokasi kantor untuk validasi absensi karyawan
                        </p>
                    </div>
                </div>

                {location ? (
                    <Card>
                        <CardHeader>
                            <div className="flex items-center justify-between">
                                <div className="flex items-center gap-3">
                                    <div className="flex h-12 w-12 items-center justify-center rounded-lg bg-primary/10">
                                        <MapPinned className="h-6 w-6 text-primary" />
                                    </div>
                                    <div>
                                        <CardTitle>{location.name}</CardTitle>
                                        <CardDescription>Lokasi aktif untuk validasi absensi</CardDescription>
                                    </div>
                                </div>
                                <Link href={`/office-locations/${location.id}/edit`}>
                                    <Button>
                                        <Pencil className="mr-2 h-4 w-4" />
                                        Edit Lokasi
                                    </Button>
                                </Link>
                            </div>
                        </CardHeader>
                        <CardContent className="space-y-6">
                            <div className="grid gap-6 md:grid-cols-2">
                                <div className="space-y-2">
                                    <div className="text-sm font-medium text-muted-foreground">Koordinat GPS</div>
                                    <div className="flex items-center gap-2">
                                        <code className="rounded bg-muted px-3 py-2 font-mono text-sm">
                                            {location.latitude.toFixed(6)}, {location.longitude.toFixed(6)}
                                        </code>
                                        <a
                                            href={`https://www.google.com/maps?q=${location.latitude},${location.longitude}`}
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            className="text-sm text-primary hover:underline"
                                        >
                                            Lihat di Maps
                                        </a>
                                    </div>
                                </div>

                                <div className="space-y-2">
                                    <div className="text-sm font-medium text-muted-foreground">Radius Validasi</div>
                                    <div className="flex items-center gap-2">
                                        <code className="rounded bg-muted px-3 py-2 font-mono text-sm">
                                            {location.radius} meter
                                        </code>
                                        <Badge variant="default">Aktif</Badge>
                                    </div>
                                </div>
                            </div>

                            <div className="rounded-lg border bg-muted/50 p-4">
                                <div className="flex items-start gap-3">
                                    <MapPin className="mt-0.5 h-5 w-5 text-muted-foreground" />
                                    <div className="space-y-1">
                                        <p className="text-sm font-medium">Informasi Lokasi</p>
                                        <p className="text-sm text-muted-foreground">
                                            Karyawan harus berada dalam radius {location.radius} meter dari koordinat ini
                                            untuk dapat melakukan absensi check-in dan check-out.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div className="space-y-4">
                                <div className="rounded-lg border">
                                    <div className="flex items-center justify-between border-b p-4">
                                        <p className="text-sm font-medium">Peta Lokasi</p>
                                        <a
                                            href={`https://www.google.com/maps?q=${location.latitude},${location.longitude}`}
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            className="text-sm text-primary hover:underline"
                                        >
                                            Buka di Google Maps
                                        </a>
                                    </div>
                                    <div className="relative h-[400px] w-full overflow-hidden">
                                        <iframe
                                            width="100%"
                                            height="100%"
                                            frameBorder="0"
                                            style={{ border: 0 }}
                                            src={`https://www.openstreetmap.org/export/embed.html?bbox=${location.longitude - 0.01},${location.latitude - 0.01},${location.longitude + 0.01},${location.latitude + 0.01}&layer=mapnik&marker=${location.latitude},${location.longitude}`}
                                            allowFullScreen
                                            title="Office Location Map"
                                        />
                                    </div>
                                    <div className="border-t p-3 text-center">
                                        <p className="text-xs text-muted-foreground">
                                            Peta dari OpenStreetMap | 📍 Kontribusi OpenStreetMap ❤️ Data Dinas Keletakan Sipil, Web dan API
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                ) : (
                    <Card>
                        <CardContent className="flex flex-col items-center justify-center py-12">
                            <MapPin className="mb-4 h-12 w-12 text-muted-foreground" />
                            <h3 className="mb-2 text-lg font-semibold">Belum Ada Lokasi Kantor</h3>
                            <p className="mb-4 text-center text-sm text-muted-foreground">
                                Lokasi kantor belum dikonfigurasi. Silakan hubungi administrator sistem.
                            </p>
                        </CardContent>
                    </Card>
                )}
            </div>
        </AppLayout>
    );
}
