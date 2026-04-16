import { InertiaLinkProps } from '@inertiajs/vue3';
import type { LucideIcon } from 'lucide-vue-next';

export interface Auth {
    user: User | null;
}

export interface UserRole {
    id: number;
    name: string;
    display_name: string;
}

export interface BreadcrumbItem {
    title: string;
    href: string;
}

export interface NavItem {
    title: string;
    href: NonNullable<InertiaLinkProps['href']>;
    icon?: LucideIcon;
    isActive?: boolean;
}

export type AppPageProps<T extends Record<string, unknown> = Record<string, unknown>> = T & {
    name: string;
    quote: { message: string; author: string };
    auth: Auth;
    flash?: {
        success?: string;
        error?: string;
        info?: string;
    };
    sidebarOpen: boolean;
};

export interface User {
    id: string;
    username: string;
    display_name: string;
    email: string | null;
    avatar?: string;
    role?: UserRole | null;
    email_verified_at: string | null;
}

export type BreadcrumbItemType = BreadcrumbItem;
