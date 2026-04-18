import { Sun } from 'lucide-react';
import { HTMLAttributes } from 'react';

export default function AppearanceToggleDropdown({ className = '', ...props }: HTMLAttributes<HTMLDivElement>) {
    return (
        <div className={className} {...props}>
            <div className="flex h-9 w-9 items-center justify-center rounded-md">
                <Sun className="h-5 w-5" />
                <span className="sr-only">Light mode</span>
            </div>
        </div>
    );
}
