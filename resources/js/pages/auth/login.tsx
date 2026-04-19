import { Head, useForm } from '@inertiajs/react';
import { LoaderCircle, Lock, Mail, Eye, EyeOff } from 'lucide-react';
import { FormEventHandler, useState } from 'react';

import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import logoTransparan from '@/components/assets/images/logo-transparan.png';

interface LoginForm {
    email: string;
    password: string;
    remember: boolean;
    [key: string]: string | boolean;
}

interface LoginProps {
    status?: string;
    canResetPassword: boolean;
}

export default function Login({ status, canResetPassword }: LoginProps) {
    const { data, setData, post, processing, errors, reset } = useForm<LoginForm>({
        email: '',
        password: '',
        remember: false,
    });

    const [showPassword, setShowPassword] = useState(false);

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post(route('login'), {
            onFinish: () => reset('password'),
        });
    };

    return (
        <>
            <Head title="Login" />
            
            <div className="min-h-screen flex items-center justify-center bg-gradient-to-br from-[#2e7d32]/10 via-background to-[#ffd600]/10 p-4">
                {/* Background Pattern */}
                <div className="absolute inset-0 bg-grid-pattern opacity-5"></div>
                
                <div className="w-full max-w-md relative z-10">
                    {/* Logo & Title Section */}
                    <div className="text-center mb-8">
                        <div className="flex justify-center mb-4">
                            <div className="relative">
                                <div className="absolute inset-0 bg-[#2e7d32]/20 blur-2xl rounded-full"></div>
                                <img 
                                    src={logoTransparan} 
                                    alt="Logo Wali Nagari" 
                                    className="h-24 w-24 relative z-10 drop-shadow-2xl"
                                />
                            </div>
                        </div>
                        <h1 className="text-3xl font-bold bg-gradient-to-r from-[#2e7d32] to-[#1b5e20] bg-clip-text text-transparent">
                            Sistem Absensi
                        </h1>
                        <p className="text-muted-foreground mt-2">
                            Wali Nagari Padang Panjang
                        </p>
                    </div>

                    {/* Login Card */}
                    <Card className="border-2 shadow-2xl backdrop-blur-sm bg-background/95">
                        <CardHeader className="space-y-1 pb-6">
                            <CardTitle className="text-2xl font-bold text-center">
                                Selamat Datang
                            </CardTitle>
                            <CardDescription className="text-center">
                                Masukkan email dan password untuk login
                            </CardDescription>
                        </CardHeader>
                        
                        <CardContent>
                            {status && (
                                <div className="mb-4 p-3 rounded-lg bg-[#2e7d32]/10 border border-[#2e7d32]/20 text-[#2e7d32] text-sm text-center">
                                    {status}
                                </div>
                            )}

                            <form onSubmit={submit} className="space-y-5">
                                {/* Email Field */}
                                <div className="space-y-2">
                                    <Label htmlFor="email" className="text-sm font-medium">
                                        Email
                                    </Label>
                                    <div className="relative">
                                        <Mail className="absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-muted-foreground" />
                                        <Input
                                            id="email"
                                            type="email"
                                            required
                                            autoFocus
                                            autoComplete="email"
                                            value={data.email}
                                            onChange={(e) => setData('email', e.target.value)}
                                            placeholder="email@example.com"
                                            className="pl-10 h-11 border-2 focus:border-[#2e7d32] transition-colors"
                                        />
                                    </div>
                                    <InputError message={errors.email} />
                                </div>

                                {/* Password Field */}
                                <div className="space-y-2">
                                    <div className="flex items-center justify-between">
                                        <Label htmlFor="password" className="text-sm font-medium">
                                            Password
                                        </Label>
                                        {canResetPassword && (
                                            <a 
                                                href={route('password.request')} 
                                                className="text-sm text-[#2e7d32] hover:text-[#1b5e20] font-medium transition-colors"
                                            >
                                                Lupa password?
                                            </a>
                                        )}
                                    </div>
                                    <div className="relative">
                                        <Lock className="absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-muted-foreground" />
                                        <Input
                                            id="password"
                                            type={showPassword ? 'text' : 'password'}
                                            required
                                            autoComplete="current-password"
                                            value={data.password}
                                            onChange={(e) => setData('password', e.target.value)}
                                            placeholder="••••••••"
                                            className="pl-10 pr-10 h-11 border-2 focus:border-[#2e7d32] transition-colors"
                                        />
                                        <button
                                            type="button"
                                            onClick={() => setShowPassword(!showPassword)}
                                            className="absolute right-3 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-foreground transition-colors"
                                        >
                                            {showPassword ? (
                                                <EyeOff className="h-5 w-5" />
                                            ) : (
                                                <Eye className="h-5 w-5" />
                                            )}
                                        </button>
                                    </div>
                                    <InputError message={errors.password} />
                                </div>

                                {/* Remember Me */}
                                <div className="flex items-center space-x-2">
                                    <Checkbox 
                                        id="remember" 
                                        checked={data.remember}
                                        onCheckedChange={(checked) => setData('remember', checked as boolean)}
                                    />
                                    <Label 
                                        htmlFor="remember" 
                                        className="text-sm font-normal cursor-pointer"
                                    >
                                        Ingat saya
                                    </Label>
                                </div>

                                {/* Submit Button */}
                                <Button 
                                    type="submit" 
                                    className="w-full h-11 bg-[#2e7d32] hover:bg-[#1b5e20] text-white font-medium shadow-lg shadow-[#2e7d32]/30 transition-all duration-200 hover:shadow-xl hover:shadow-[#2e7d32]/40" 
                                    disabled={processing}
                                >
                                    {processing ? (
                                        <>
                                            <LoaderCircle className="mr-2 h-5 w-5 animate-spin" />
                                            Memproses...
                                        </>
                                    ) : (
                                        'Masuk'
                                    )}
                                </Button>
                            </form>
                        </CardContent>
                    </Card>

                    {/* Footer */}
                    <div className="mt-6 text-center">
                        <p className="text-sm text-muted-foreground">
                            © 2026 Wali Nagari Padang Panjang
                        </p>
                        <p className="text-xs text-muted-foreground mt-1">
                            Sistem Informasi Absensi Pegawai
                        </p>
                    </div>
                </div>
            </div>

            <style>{`
                .bg-grid-pattern {
                    background-image: 
                        linear-gradient(to right, rgba(46, 125, 50, 0.1) 1px, transparent 1px),
                        linear-gradient(to bottom, rgba(46, 125, 50, 0.1) 1px, transparent 1px);
                    background-size: 40px 40px;
                }
            `}</style>
        </>
    );
}
