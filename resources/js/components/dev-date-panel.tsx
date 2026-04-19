import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import { Calendar, X, Info } from 'lucide-react';
import { useState } from 'react';

export function DevDatePanel({ testDateInfo }: { testDateInfo?: any }) {
    // Only show if test_date_info is provided (backend controls visibility)
    if (!testDateInfo) {
        return null;
    }

    const testMode = testDateInfo.test_mode;
    const testDate = testDateInfo.date;
    
    const [selectedDate, setSelectedDate] = useState(testDate || new Date().toISOString().split('T')[0]);
    const [isOpen, setIsOpen] = useState(false);

    const handleSetDate = () => {
        window.location.href = `/dev/set-date/${selectedDate}`;
    };

    const handleClearDate = () => {
        window.location.href = '/dev/clear-date';
    };

    const quickDates = [
        { label: 'Senin', date: '2026-04-20' },
        { label: 'Selasa', date: '2026-04-21' },
        { label: 'Rabu', date: '2026-04-22' },
        { label: 'Kamis', date: '2026-04-23' },
        { label: 'Jumat', date: '2026-04-24' },
        { label: 'Sabtu', date: '2026-04-25' },
        { label: 'Minggu (Hari ini)', date: '2026-04-19' },
    ];

    if (!isOpen) {
        return (
            <div className="fixed bottom-4 right-4 z-50">
                <Button
                    onClick={() => setIsOpen(true)}
                    variant="outline"
                    size="sm"
                    className="shadow-lg bg-yellow-50 border-yellow-300 hover:bg-yellow-100"
                >
                    <Calendar className="h-4 w-4 mr-2" />
                    {testMode ? 'Test Mode Active' : 'Date Testing'}
                </Button>
            </div>
        );
    }

    return (
        <div className="fixed bottom-4 right-4 z-50 w-96">
            <Card className="shadow-xl border-yellow-300">
                <CardHeader className="bg-yellow-50">
                    <div className="flex items-center justify-between">
                        <div className="flex items-center gap-2">
                            <Calendar className="h-5 w-5" />
                            <CardTitle className="text-lg">Date Testing Panel</CardTitle>
                        </div>
                        <Button
                            variant="ghost"
                            size="sm"
                            onClick={() => setIsOpen(false)}
                        >
                            <X className="h-4 w-4" />
                        </Button>
                    </div>
                    <CardDescription>
                        Override tanggal sistem untuk testing
                    </CardDescription>
                </CardHeader>
                <CardContent className="space-y-4 pt-4">
                    {testMode && (
                        <div className="p-3 bg-yellow-100 border border-yellow-300 rounded-md flex items-start gap-2">
                            <Info className="h-4 w-4 text-yellow-700 mt-0.5" />
                            <div className="text-sm text-yellow-800">
                                <strong>Test Mode Active</strong>
                                <br />
                                Current test date: {testDate}
                            </div>
                        </div>
                    )}

                    <div className="space-y-2">
                        <label className="text-sm font-medium">Pilih Tanggal</label>
                        <Input
                            type="date"
                            value={selectedDate}
                            onChange={(e) => setSelectedDate(e.target.value)}
                        />
                    </div>

                    <div className="space-y-2">
                        <label className="text-sm font-medium">Quick Select</label>
                        <div className="grid grid-cols-2 gap-2">
                            {quickDates.map((item) => (
                                <Button
                                    key={item.date}
                                    variant="outline"
                                    size="sm"
                                    onClick={() => setSelectedDate(item.date)}
                                    className={selectedDate === item.date ? 'bg-yellow-100' : ''}
                                >
                                    {item.label}
                                </Button>
                            ))}
                        </div>
                    </div>

                    <div className="flex gap-2">
                        <Button
                            onClick={handleSetDate}
                            className="flex-1 bg-yellow-500 hover:bg-yellow-600"
                        >
                            Set Test Date
                        </Button>
                        {testMode && (
                            <Button
                                onClick={handleClearDate}
                                variant="outline"
                            >
                                Clear
                            </Button>
                        )}
                    </div>

                    <div className="text-xs text-muted-foreground">
                        <strong>Cara Pakai:</strong>
                        <ul className="list-disc list-inside mt-1 space-y-1">
                            <li>Pilih tanggal untuk testing</li>
                            <li>Klik "Set Test Date"</li>
                            <li>Semua fitur akan menggunakan tanggal tersebut</li>
                            <li>Klik "Clear" untuk kembali ke tanggal real</li>
                        </ul>
                    </div>
                </CardContent>
            </Card>
        </div>
    );
}
