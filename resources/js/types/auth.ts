import type { Role, UserProfile, TenantProfile } from './models';

export type AuthUser = {
    id: number;
    role_id: number;
    email: string;
    phone: string | null;
    is_active: boolean;
    email_verified_at: string | null;
    role: Role;
    userProfile: UserProfile | null;
    tenantProfile?: TenantProfile | null;
};

export type Auth = {
    user: AuthUser | null;
};

// Keep legacy User type for compatibility
export type User = AuthUser;
