<?php

/**
 * Project-level role permissions matrix
 *
 * Defines what actions each role can perform at the project level.
 * Used by policies and frontend permission checks.
 */

return [
    'roles' => [
        'owner' => [
            'label' => 'Owner',
            'description' => 'Full control over the project',
            'permissions' => [
                'project.view' => true,
                'project.edit' => true,
                'project.delete' => true,
                'member.view' => true,
                'member.add' => true,
                'member.remove' => true,
                'member.invite' => true,
                'member.role_change' => true,
                'role.view' => true,
                'role.manage' => true,
                'task.view' => true,
                'task.create' => true,
                'task.edit' => true,
                'task.delete' => true,
                'task.assign' => true,
                'task.comment' => true,
                'label.manage' => true,
            ],
        ],
        'lead' => [
            'label' => 'Lead',
            'description' => 'Can create, edit, delete tasks and assign work',
            'permissions' => [
                'project.view' => true,
                'project.edit' => false,
                'project.delete' => false,
                'member.view' => true,
                'member.add' => false,
                'member.remove' => false,
                'member.invite' => true,
                'member.role_change' => false,
                'role.view' => true,
                'role.manage' => false,
                'task.view' => true,
                'task.create' => true,
                'task.edit' => true,
                'task.delete' => true,
                'task.assign' => true,
                'task.comment' => true,
                'label.manage' => true,
            ],
        ],
        'member' => [
            'label' => 'Member',
            'description' => 'Can create and edit own tasks, self-assign',
            'permissions' => [
                'project.view' => true,
                'project.edit' => false,
                'project.delete' => false,
                'member.view' => true,
                'member.add' => false,
                'member.remove' => false,
                'member.invite' => false,
                'member.role_change' => false,
                'role.view' => true,
                'role.manage' => false,
                'task.view' => true,
                'task.create' => true,
                'task.edit' => true,  // own tasks only (enforced in policy)
                'task.delete' => true, // own tasks only (enforced in policy)
                'task.assign' => true, // self-assign only (enforced in policy)
                'task.comment' => true,
                'label.manage' => false,
            ],
        ],
        'viewer' => [
            'label' => 'Viewer',
            'description' => 'Read-only access to project',
            'permissions' => [
                'project.view' => true,
                'project.edit' => false,
                'project.delete' => false,
                'member.view' => true,
                'member.add' => false,
                'member.remove' => false,
                'member.invite' => false,
                'member.role_change' => false,
                'role.view' => true,
                'role.manage' => false,
                'task.view' => true,
                'task.create' => false,
                'task.edit' => false,
                'task.delete' => false,
                'task.assign' => false,
                'task.comment' => false,
                'label.manage' => false,
            ],
        ],
    ],

    /**
     * Helper to check if a role has a specific permission
     */
    'can' => function (string $role, string $permission): bool {
        $config = config('permissions');
        return $config['roles'][$role]['permissions'][$permission] ?? false;
    },
];
