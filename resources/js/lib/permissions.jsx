import React, { createContext, useContext, useMemo } from 'react';
import { usePage } from '@inertiajs/react';

const PermissionContext = createContext({ permissions: [], roles: [] });

export function PermissionProvider({ children, initial = [] }) {
    return (
        <PermissionContext.Provider value={{ initial }}>
            {children}
        </PermissionContext.Provider>
    );
}

export function usePermissions() {
    const page = usePage();
    const auth = page?.props?.auth || {};
    const permissions = auth.permissions || [];
    const roles = auth.roles || [];
    return useMemo(() => ({
        permissions,
        roles,
        can: (perm) => {
            if (!perm) return true;
            if (roles.includes('super-admin')) return true;
            return permissions.includes(perm);
        },
        hasRole: (role) => roles.includes(role),
    }), [permissions.join(','), roles.join(',')]);
}

export function Can({ permission, children, fallback = null }) {
    const { can } = usePermissions();
    return can(permission) ? <>{children}</> : fallback;
}
