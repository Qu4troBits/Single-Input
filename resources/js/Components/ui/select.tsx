import * as React from 'react';
import { cn } from '@/lib/utils';
import { ChevronDown, Check } from 'lucide-react';

// ─── Context ───────────────────────────────────────────────────────────────
interface SelectContextValue {
    value: string;
    onValueChange: (value: string) => void;
    open: boolean;
    setOpen: (open: boolean) => void;
}

const SelectContext = React.createContext<SelectContextValue>({
    value: '',
    onValueChange: () => {},
    open: false,
    setOpen: () => {},
});

// ─── Root ───────────────────────────────────────────────────────────────────
interface SelectProps {
    value?: string;
    defaultValue?: string;
    onValueChange?: (value: string) => void;
    children: React.ReactNode;
    disabled?: boolean;
}

function Select({ value: controlledValue, defaultValue = '', onValueChange, children, disabled }: SelectProps) {
    const [internalValue, setInternalValue] = React.useState(defaultValue);
    const [open, setOpen] = React.useState(false);

    const value = controlledValue !== undefined ? controlledValue : internalValue;

    const handleValueChange = (newValue: string) => {
        if (controlledValue === undefined) setInternalValue(newValue);
        onValueChange?.(newValue);
        setOpen(false);
    };

    // Close on outside click
    const containerRef = React.useRef<HTMLDivElement>(null);
    React.useEffect(() => {
        const handler = (e: MouseEvent) => {
            if (containerRef.current && !containerRef.current.contains(e.target as Node)) {
                setOpen(false);
            }
        };
        document.addEventListener('mousedown', handler);
        return () => document.removeEventListener('mousedown', handler);
    }, []);

    return (
        <SelectContext.Provider value={{ value, onValueChange: handleValueChange, open, setOpen }}>
            <div ref={containerRef} className={cn('relative', disabled && 'opacity-50 pointer-events-none')}>
                {children}
            </div>
        </SelectContext.Provider>
    );
}

// ─── Trigger ────────────────────────────────────────────────────────────────
const SelectTrigger = React.forwardRef<HTMLButtonElement, React.ButtonHTMLAttributes<HTMLButtonElement>>(
    ({ className, children, ...props }, ref) => {
        const { open, setOpen } = React.useContext(SelectContext);
        return (
            <button
                ref={ref}
                type="button"
                role="combobox"
                aria-expanded={open}
                onClick={() => setOpen(!open)}
                className={cn(
                    'flex h-9 w-full items-center justify-between rounded-md border border-gray-300 dark:border-gray-700',
                    'bg-white dark:bg-gray-900 px-3 py-2 text-sm text-gray-900 dark:text-white',
                    'shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500',
                    'disabled:cursor-not-allowed disabled:opacity-50',
                    className
                )}
                {...props}
            >
                {children}
                <ChevronDown size={14} className={cn('ml-2 opacity-50 transition-transform', open && 'rotate-180')} />
            </button>
        );
    }
);
SelectTrigger.displayName = 'SelectTrigger';

// ─── Value ───────────────────────────────────────────────────────────────────
function SelectValue({ placeholder }: { placeholder?: string }) {
    const { value } = React.useContext(SelectContext);
    return (
        <span className={cn('block truncate', !value && 'text-gray-400 dark:text-gray-500')}>
            {value || placeholder}
        </span>
    );
}

// ─── Content ─────────────────────────────────────────────────────────────────
const SelectContent = React.forwardRef<HTMLDivElement, React.HTMLAttributes<HTMLDivElement>>(
    ({ className, children, ...props }, ref) => {
        const { open } = React.useContext(SelectContext);
        if (!open) return null;
        return (
            <div
                ref={ref}
                className={cn(
                    'absolute z-50 mt-1 w-full rounded-md border border-gray-200 dark:border-gray-700',
                    'bg-white dark:bg-gray-900 shadow-lg py-1',
                    'animate-in fade-in-0 zoom-in-95',
                    className
                )}
                {...props}
            >
                {children}
            </div>
        );
    }
);
SelectContent.displayName = 'SelectContent';

// ─── Item ─────────────────────────────────────────────────────────────────────
interface SelectItemProps extends React.HTMLAttributes<HTMLDivElement> {
    value: string;
}

const SelectItem = React.forwardRef<HTMLDivElement, SelectItemProps>(
    ({ className, children, value, ...props }, ref) => {
        const { value: selectedValue, onValueChange } = React.useContext(SelectContext);
        const isSelected = selectedValue === value;

        return (
            <div
                ref={ref}
                role="option"
                aria-selected={isSelected}
                onClick={() => onValueChange(value)}
                className={cn(
                    'relative flex cursor-pointer select-none items-center px-3 py-2 text-sm',
                    'text-gray-700 dark:text-gray-300',
                    'hover:bg-gray-100 dark:hover:bg-gray-800',
                    isSelected && 'bg-indigo-50 dark:bg-indigo-950 text-indigo-700 dark:text-indigo-300 font-medium',
                    className
                )}
                {...props}
            >
                {isSelected && <Check size={14} className="mr-2 flex-shrink-0" />}
                {!isSelected && <span className="mr-6" />}
                {children}
            </div>
        );
    }
);
SelectItem.displayName = 'SelectItem';

// ─── Separator ───────────────────────────────────────────────────────────────
const SelectSeparator = React.forwardRef<HTMLDivElement, React.HTMLAttributes<HTMLDivElement>>(
    ({ className, ...props }, ref) => (
        <div ref={ref} className={cn('my-1 h-px bg-gray-200 dark:bg-gray-700', className)} {...props} />
    )
);
SelectSeparator.displayName = 'SelectSeparator';

export { Select, SelectContent, SelectItem, SelectSeparator, SelectTrigger, SelectValue };