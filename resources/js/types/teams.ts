export type TeamRole = 'owner' | 'admin' | 'member';

export type Team = {
    id: number;
    name: string;
    slug: string;
    isPersonal: boolean;
    role?: TeamRole;
    roleLabel?: string;
    isCurrent?: boolean;
};

export type TeamMember = {
    id: number;
    name: string;
    email: string;
    avatar?: string | null;
    role: TeamRole;
    role_label: string;
    resource_accesses?: TeamResourceAccess[];
};

export type TeamInvitation = {
    code: string;
    email: string;
    role: TeamRole;
    role_label: string;
    created_at: string;
};

export type TeamPermissions = {
    canUpdateTeam: boolean;
    canDeleteTeam: boolean;
    canAddMember: boolean;
    canUpdateMember: boolean;
    canRemoveMember: boolean;
    canCreateInvitation: boolean;
    canCancelInvitation: boolean;
};

export type RoleOption = {
    value: TeamRole;
    label: string;
};

export type ResourceAccessLevel = 'view' | 'manage';

export type TeamResourceType = 'unit' | 'form';

export type TeamResourceAccess = {
    resource_type: TeamResourceType;
    resource_id: number;
    access_level: ResourceAccessLevel;
};

export type ResourceAccessLevelOption = {
    value: ResourceAccessLevel;
    label: string;
};

export type TeamResourceOption = {
    id: number;
    name: string;
    slug: string;
};

export type TeamFormOption = TeamResourceOption & {
    leadAssignment: {
        mode: string;
        owner_id: number | null;
    };
};
