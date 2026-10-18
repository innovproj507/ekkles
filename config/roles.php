<?php
/**
 * Definición de Roles RBAC (Control de Acceso Basado en Roles)
 * --------------------------------------------------------------
 * Cada rol tiene un nivel numérico y una lista de permisos.
 * Los niveles más altos heredan los permisos de los niveles inferiores.
 */

// Excepciones comunes para roles con vista restringida a Ministerios, Eventos y acceso a Discipulado
$commonRestrictedExceptions = [
    'deny' => [
        'dashboard.view',
        'finance.view', 'finance.create', 'finance.edit', 'finance.delete', 'finance.reports',
        'donations.view', 'donations.create', 'donations.edit', 'donations.delete', 'donations.receipts',
        'members.view', 'members.create', 'members.edit', 'members.delete', 'members.view_notes', 'members.edit_notes',
        'families.view', 'families.create', 'families.edit', 'families.delete',
        'attendance.view', 'attendance.create', 'attendance.delete',
        'reports.view', 'reports.export', 'reports.financial',
        'volunteers.view', 'volunteers.schedule', 'volunteers.confirm',
        'events.create', 'events.edit', 'events.delete', 'events.manage_teams',
        'ministries.create', 'ministries.edit', 'ministries.delete'
    ],
    'allow' => [
        'pastoral.view', 'pastoral.create', 'pastoral.edit',
        'ministries.view',
        'events.view'
    ]
];

// Excepciones para Líder de Ministerio y Secretario
$liderMinisterioExceptions = [
    'deny' => [
        'dashboard.view',
        'finance.view', 'finance.create', 'finance.edit', 'finance.delete', 'finance.reports',
        'donations.view', 'donations.create', 'donations.edit', 'donations.delete', 'donations.receipts',
        'members.view', 'members.create', 'members.edit', 'members.delete', 'members.view_notes', 'members.edit_notes',
        'families.view', 'families.create', 'families.edit', 'families.delete',
        'attendance.view', 'attendance.create', 'attendance.delete',
        'reports.view', 'reports.export', 'reports.financial'
    ],
    'allow' => [
        'events.view', 'events.create', 'events.edit', 'events.manage_teams'
    ]
];

// Excepciones para Tesorero
$tesoreroExceptions = [
    'deny' => [
        'dashboard.view',
        'users.view', 'users.create', 'users.edit',
        'members.view', 'members.create', 'members.edit', 'members.view_notes', 'members.edit_notes',
        'families.view', 'families.create', 'families.edit',
        'attendance.view', 'attendance.create',
        'events.view', 'events.create', 'events.edit', 'events.manage_teams',
        'ministries.view', 'ministries.edit',
        'volunteers.view', 'volunteers.schedule', 'volunteers.confirm',
        'finance.view', 'finance.create', 'finance.edit', 'finance.reports',
        'pastoral.view', 'pastoral.create', 'pastoral.edit',
        'reports.view', 'reports.export', 'reports.financial'
    ]
];

return [

    // ==========================================================
    // DEFINICIÓN DE ROLES
    // ==========================================================
    'roles' => [
        'super_admin'      => ['level' => 10, 'label' => 'Super Administrador'],
        'ministro_principal' => ['level' => 9,  'label' => 'Ministro Principal'],
        'evangelista'      => ['level' => 8,  'label' => 'Evangelista'],
        'administrador'    => ['level' => 7,  'label' => 'Administrador'],
        'tesorero'         => [
            'level' => 6,  
            'label' => 'Tesorero',
            'exceptions' => $tesoreroExceptions
        ],
        'lider_ministerio' => [
            'level' => 5,  
            'label' => 'Líder de Ministerio',
            'exceptions' => $liderMinisterioExceptions
        ],
        'coordinador'      => ['level' => 4,  'label' => 'Coordinador', 'exceptions' => $commonRestrictedExceptions],
        'secretario'       => [
            'level' => 5,  
            'label' => 'Secretario',
            'exceptions' => $liderMinisterioExceptions
        ],
        'anciano'          => ['level' => 2,  'label' => 'Anciano', 'exceptions' => $commonRestrictedExceptions],
        'diacono'          => ['level' => 1,  'label' => 'Diácono', 'exceptions' => $commonRestrictedExceptions],
    ],

    // ==========================================================
    // MATRIZ DE PERMISOS POR MÓDULO
    // Formato: 'permiso' => nivel_mínimo_requerido
    // ==========================================================
    'permissions' => [
        // Dashboard
        'dashboard.view'       => 1,

        // Gestión de usuarios del sistema
        'users.view'           => 7,
        'users.create'         => 7,
        'users.edit'           => 7,
        'users.delete'         => 9,
        'users.assign_role'    => 9,

        // Miembros
        'members.view'         => 2,
        'members.create'       => 3,
        'members.edit'         => 3,
        'members.delete'       => 8,
        'members.view_notes'   => 5,
        'members.edit_notes'   => 5,

        // Familias
        'families.view'        => 2,
        'families.create'      => 3,
        'families.edit'        => 3,
        'families.delete'      => 7,

        // Asistencia
        'attendance.view'      => 2,
        'attendance.create'    => 3,
        'attendance.delete'    => 7,

        // Eventos
        'events.view'          => 1,
        'events.create'        => 4,
        'events.edit'          => 4,
        'events.delete'        => 7,
        'events.manage_teams'  => 4,

        // Ministerios
        'ministries.view'      => 1,
        'ministries.create'    => 7,
        'ministries.edit'      => 5,
        'ministries.delete'    => 8,

        // Voluntarios
        'volunteers.view'      => 3,
        'volunteers.schedule'  => 5,
        'volunteers.confirm'   => 2,

        // Finanzas — acceso restringido
        'finance.view'         => 6,
        'finance.create'       => 6,
        'finance.edit'         => 6,
        'finance.delete'       => 9,  // Solo borrado lógico
        'finance.reports'      => 6,

        // Donaciones
        'donations.view'       => 6,
        'donations.create'     => 6,
        'donations.edit'       => 6,
        'donations.delete'     => 9,
        'donations.receipts'   => 6,

        // CRM Pastoral
        'pastoral.view'        => 5,
        'pastoral.create'      => 5,
        'pastoral.edit'        => 5,
        'pastoral.delete'      => 8,

        // Reportes
        'reports.view'         => 5,
        'reports.export'       => 5,
        'reports.financial'    => 6,

        // Notificaciones
        'notifications.view'   => 1,
        'notifications.send'   => 5,
        'notifications.manage' => 7,

        // Configuración del sistema
        'settings.view'        => 7,
        'settings.edit'        => 9,

        // Auditoría
        'audit.view'           => 8,

        // Multi-tenant: gestión de iglesias
        'churches.manage'      => 10,
    ],
];
