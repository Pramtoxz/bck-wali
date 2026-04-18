import { Link } from '@inertiajs/react'; // Gunakan Link untuk navigasi SPA
import Logo from '@/components/assets/images/logo-transparan.png';

export default function AppLogo() {
    return (
        <Link href="/" className="flex items-center gap-2 py-2 px-1">
            <div className="flex aspect-square size-8 items-center justify-center rounded-lg bg-sidebar-primary/10 overflow-hidden">
                <img 
                    src={Logo} 
                    alt="Logo SI-ABSEN" 
                    className="size-6 object-contain" 
                />
            </div>
            <div className="grid flex-1 text-left">
                <span className="truncate text-[11px] font-bold leading-tight uppercase tracking-wider text-sidebar-primary">
                    SI-ABSEN
                </span>
                <div className="my-0.5 border-b border-sidebar-border w-full opacity-50"></div>
                <span className="truncate text-[10px] font-medium leading-none text-muted-foreground">
                    Wali Nagari Pesisir Selatan
                </span>
            </div>
        </Link>
    );
}