<?php
/**
 * routes.php — Definición de todas las rutas de Ekkles
 * -------------------------------------------------------
 * Se registra en App::registerRoutes() que es llamado por App::run().
 * Sintaxis: $router->METHOD('/ruta', 'Controlador@método', ['middleware1', ...]);
 *
 * @package Ekkles
 */

use App\Core\Router;

// ─────────────────────────────────────────────────────────
// AUTENTICACIÓN (pública)
// ─────────────────────────────────────────────────────────
$router->get( '/login',  'Auth\AuthController@showLogin');
$router->post('/login',  'Auth\AuthController@login');
$router->get( '/mfa',    'Auth\AuthController@showMfa');
$router->post('/mfa',    'Auth\AuthController@verifyMfa');
$router->get( '/logout', 'Auth\AuthController@logout');

// ─────────────────────────────────────────────────────────
// DASHBOARD
// ─────────────────────────────────────────────────────────
$router->get('/dashboard', 'Dashboard\DashboardController@index', ['auth', 'role:dashboard.view']);
$router->get('/',          'Dashboard\DashboardController@index', ['auth', 'role:dashboard.view']);

// ─────────────────────────────────────────────────────────
// MIEMBROS
// ─────────────────────────────────────────────────────────
$router->get( '/miembros',               'Members\MemberController@index',   ['auth', 'role:members.view']);
$router->get( '/miembros/crear',         'Members\MemberController@create',  ['auth', 'role:members.create']);
$router->post('/miembros/crear',         'Members\MemberController@store',   ['auth', 'role:members.create', 'audit']);
$router->get( '/miembros/{id}',          'Members\MemberController@show',    ['auth', 'role:members.view']);
$router->get( '/miembros/{id}/editar',   'Members\MemberController@edit',    ['auth', 'role:members.edit']);
$router->post('/miembros/{id}/editar',   'Members\MemberController@update',  ['auth', 'role:members.edit', 'audit']);
$router->post('/miembros/{id}/eliminar', 'Members\MemberController@destroy', ['auth', 'role:members.delete', 'audit']);

// ─────────────────────────────────────────────────────────
// FAMILIAS
// ─────────────────────────────────────────────────────────
$router->get( '/familias',               'Members\FamilyController@index',  ['auth', 'role:families.view']);
$router->get( '/familias/crear',         'Members\FamilyController@create', ['auth', 'role:families.create']);
$router->post('/familias/crear',         'Members\FamilyController@store',  ['auth', 'role:families.create', 'audit']);
$router->get( '/familias/{id}',          'Members\FamilyController@show',   ['auth', 'role:families.view']);
$router->post('/familias/{id}/editar',   'Members\FamilyController@update', ['auth', 'role:families.edit', 'audit']);

// ─────────────────────────────────────────────────────────
// EVENTOS
// ─────────────────────────────────────────────────────────
$router->get( '/eventos/calendario-pdf', 'Events\EventController@exportCalendar', ['auth', 'role:events.view']);
$router->get( '/eventos',               'Events\EventController@index',   ['auth', 'role:events.view']);
$router->get( '/eventos/crear',         'Events\EventController@create',  ['auth', 'role:events.create']);
$router->post('/eventos/crear',         'Events\EventController@store',   ['auth', 'role:events.create', 'audit']);

// Tipos de Eventos
$router->get(   '/eventos/tipos',          'Events\EventTypeController@index',   ['auth', 'role:events.view']);
$router->post(  '/eventos/tipos',          'Events\EventTypeController@store',   ['auth', 'role:events.create', 'audit']);
$router->post(  '/eventos/tipos/{id}',     'Events\EventTypeController@update',  ['auth', 'role:events.edit', 'audit']);
$router->post(  '/eventos/tipos/{id}/eliminar', 'Events\EventTypeController@destroy', ['auth', 'role:events.delete', 'audit']);

// Disponibilidad de salón (AJAX) — antes de /eventos/{id}
$router->get( '/eventos/disponibilidad', 'Events\EventController@roomAvailability', ['auth', 'role:events.view']);

$router->get( '/eventos/{id}',          'Events\EventController@show',    ['auth', 'role:events.view']);
$router->get( '/eventos/{id}/editar',   'Events\EventController@edit',    ['auth', 'role:events.edit']);
$router->post('/eventos/{id}/editar',   'Events\EventController@update',  ['auth', 'role:events.edit', 'audit']);
$router->post('/eventos/{id}/eliminar', 'Events\EventController@destroy', ['auth', 'role:events.delete', 'audit']);

// Salones
$router->get( '/salones',               'Events\RoomController@index',   ['auth', 'role:events.view']);
$router->post('/salones',               'Events\RoomController@store',   ['auth', 'role:events.create', 'audit']);
$router->post('/salones/{id}',          'Events\RoomController@update',  ['auth', 'role:events.edit', 'audit']);
$router->post('/salones/{id}/eliminar', 'Events\RoomController@destroy', ['auth', 'role:events.delete', 'audit']);

// ─────────────────────────────────────────────────────────
// ASISTENCIA
// ─────────────────────────────────────────────────────────
$router->get( '/asistencia',                   'Events\AttendanceController@index',   ['auth', 'role:attendance.view']);
$router->get( '/asistencia/{id}/editar',       'Events\AttendanceController@edit',    ['auth', 'role:attendance.view']);
$router->post('/asistencia/{id}/editar',       'Events\AttendanceController@update',  ['auth', 'role:attendance.view']);
$router->post('/asistencia/{id}/eliminar',     'Events\AttendanceController@destroy', ['auth', 'role:attendance.view']);
$router->get( '/qr/asistencia/{uuid}',         'Events\AttendanceController@publicQr');
$router->post('/qr/asistencia/registrar',      'Events\AttendanceController@storeQr');
$router->get( '/qr/asistencia/{uuid}/buscar',  'Events\AttendanceController@searchMembers');

// ─────────────────────────────────────────────────────────
// MINISTERIOS
// ─────────────────────────────────────────────────────────
$router->get( '/ministerios',               'Events\MinistryController@index',  ['auth', 'role:ministries.view']);
$router->get( '/ministerios/crear',         'Events\MinistryController@create', ['auth', 'role:ministries.create']);
$router->post('/ministerios/crear',         'Events\MinistryController@store',  ['auth', 'role:ministries.create', 'audit']);
$router->get( '/ministerios/{id}',          'Events\MinistryController@show',   ['auth', 'role:ministries.view']);
$router->post('/ministerios/{id}/editar',   'Events\MinistryController@update', ['auth', 'role:ministries.edit', 'audit']);

// ─────────────────────────────────────────────────────────
// VOLUNTARIOS
// ─────────────────────────────────────────────────────────
$router->get( '/voluntarios',          'Events\VolunteerController@index',   ['auth', 'role:volunteers.view']);
$router->post('/voluntarios/asignar',  'Events\VolunteerController@schedule',['auth', 'role:volunteers.create']);
$router->post('/voluntarios/confirmar','Events\VolunteerController@confirm', ['auth', 'role:volunteers.create']);

// ─────────────────────────────────────────────────────────
// OFRENDAS
// ─────────────────────────────────────────────────────────
$router->get( '/ofrendas',                 'Finance\DonationController@index',   ['auth', 'role:donations.view']);
$router->get( '/ofrendas/crear',           'Finance\DonationController@create',  ['auth', 'role:donations.create']);
$router->post('/ofrendas/crear',           'Finance\DonationController@store',   ['auth', 'role:donations.create', 'audit']);
$router->get( '/ofrendas/categorias', 'Finance\DonationController@categories',     ['auth', 'role:donations.edit']);
$router->post('/ofrendas/categorias', 'Finance\DonationController@saveCategories', ['auth', 'role:donations.edit', 'audit']);
$router->get( '/ofrendas/{id}',            'Finance\DonationController@show',    ['auth', 'role:donations.view']);
$router->get( '/ofrendas/{id}/recibo',     'Finance\DonationController@receipt', ['auth', 'role:donations.view']);
$router->post('/ofrendas/{id}/aprobar',    'Finance\DonationController@approve', ['auth', 'role:donations.edit', 'audit']);
$router->post('/ofrendas/{id}/rechazar',   'Finance\DonationController@reject',  ['auth', 'role:donations.edit', 'audit']);

// ─────────────────────────────────────────────────────────
// CONSTRUCCIÓN
// ─────────────────────────────────────────────────────────
$router->get( '/construccion',                 'Finance\DonationController@index',   ['auth', 'role:donations.view']);
$router->get( '/construccion/crear',           'Finance\DonationController@create',  ['auth', 'role:donations.create']);
$router->post('/construccion/crear',           'Finance\DonationController@store',   ['auth', 'role:donations.create', 'audit']);
$router->get( '/construccion/categorias', 'Finance\DonationController@categories',     ['auth', 'role:donations.edit']);
$router->post('/construccion/categorias', 'Finance\DonationController@saveCategories', ['auth', 'role:donations.edit', 'audit']);
$router->get( '/construccion/{id}',            'Finance\DonationController@show',    ['auth', 'role:donations.view']);
$router->get( '/construccion/{id}/recibo',     'Finance\DonationController@receipt', ['auth', 'role:donations.view']);
$router->post('/construccion/{id}/aprobar',    'Finance\DonationController@approve', ['auth', 'role:donations.edit', 'audit']);
$router->post('/construccion/{id}/rechazar',   'Finance\DonationController@reject',  ['auth', 'role:donations.edit', 'audit']);

// ─────────────────────────────────────────────────────────
// DONACIONES Y OBRAS MISIONERAS
// ─────────────────────────────────────────────────────────
$router->get( '/donaciones',                 'Finance\DonationController@index',   ['auth', 'role:donations.view']);
$router->get( '/donaciones/crear',           'Finance\DonationController@create',  ['auth', 'role:donations.create']);
$router->post('/donaciones/crear',           'Finance\DonationController@store',   ['auth', 'role:donations.create', 'audit']);
$router->get( '/donaciones/categorias', 'Finance\DonationController@categories',     ['auth', 'role:donations.edit']);
$router->post('/donaciones/categorias', 'Finance\DonationController@saveCategories', ['auth', 'role:donations.edit', 'audit']);
$router->get( '/donaciones/{id}',            'Finance\DonationController@show',    ['auth', 'role:donations.view']);
$router->get( '/donaciones/{id}/recibo',     'Finance\DonationController@receipt', ['auth', 'role:donations.view']);
$router->post('/donaciones/{id}/aprobar',    'Finance\DonationController@approve', ['auth', 'role:donations.edit', 'audit']);
$router->post('/donaciones/{id}/rechazar',   'Finance\DonationController@reject',  ['auth', 'role:donations.edit', 'audit']);
$router->post('/donaciones/categorias/crear', 'Finance\DonationController@createCategory', ['auth', 'role:donations.create']);

// ─────────────────────────────────────────────────────────
// EGRESOS (GASTOS)
// ─────────────────────────────────────────────────────────
$router->get( '/egresos',                 'Finance\ExpenseController@index',   ['auth', 'role:finance.view']);
$router->get( '/egresos/crear',           'Finance\ExpenseController@create',  ['auth', 'role:finance.create']);
$router->post('/egresos/crear',           'Finance\ExpenseController@store',   ['auth', 'role:finance.create', 'audit']);
$router->post('/egresos/{id}/recibo', 'Finance\ExpenseController@attachReceipt', ['auth', 'role:finance.create', 'audit']);
$router->get( '/egresos/{id}/recibo', 'Finance\ExpenseController@receipt',       ['auth', 'role:finance.view']);
$router->post('/egresos/categorias/crear', 'Finance\ExpenseController@createCategory', ['auth', 'role:finance.create']);

// ─────────────────────────────────────────────────────────
// FINANZAS
// ─────────────────────────────────────────────────────────
$router->get( '/finanzas',        'Finance\FinanceController@index',  ['auth', 'role:finance.view']);
$router->get( '/finanzas/crear',  'Finance\FinanceController@create', ['auth', 'role:finance.create']);
$router->post('/finanzas/crear',  'Finance\FinanceController@store',  ['auth', 'role:finance.create', 'audit']);
$router->post('/finanzas/{id}/aprobar',   'Finance\FinanceController@approve', ['auth', 'role:finance.edit', 'audit']);
$router->post('/finanzas/{id}/rechazar',  'Finance\FinanceController@reject',  ['auth', 'role:finance.edit', 'audit']);
$router->get( '/finanzas/presupuesto', 'Finance\FinanceController@budget', ['auth', 'role:finance.view']);

// ─────────────────────────────────────────────────────────
// CONCILIACIONES BANCARIAS
// ─────────────────────────────────────────────────────────
$router->get( '/conciliaciones',          'Finance\ReconciliationController@index',   ['auth', 'role:finance.view']);
$router->get( '/conciliaciones/crear',    'Finance\ReconciliationController@create',  ['auth', 'role:finance.create']);
$router->post('/conciliaciones/crear',    'Finance\ReconciliationController@store',   ['auth', 'role:finance.create', 'audit']);
$router->post('/conciliaciones/calcular', 'Finance\ReconciliationController@calculate',['auth', 'role:finance.create']);
$router->get( '/conciliaciones/{id}',     'Finance\ReconciliationController@show',    ['auth', 'role:finance.view']);

// ─────────────────────────────────────────────────────────
// SEGUIMIENTO PASTORAL
// ─────────────────────────────────────────────────────────
$router->get( '/pastoral',                   'Pastoral\PastoralController@index',    ['auth', 'role:pastoral.view']);
$router->get( '/pastoral/pipeline',          'Pastoral\PastoralController@pipeline', ['auth', 'role:pastoral.view']);
$router->get( '/pastoral/crear',             'Pastoral\PastoralController@create',   ['auth', 'role:pastoral.create']);
$router->post('/pastoral/crear',             'Pastoral\PastoralController@store',    ['auth', 'role:pastoral.create', 'audit']);
$router->get( '/pastoral/{id}',              'Pastoral\PastoralController@show',     ['auth', 'role:pastoral.view']);
$router->post('/pastoral/{id}/actualizar',   'Pastoral\PastoralController@update',   ['auth', 'role:pastoral.edit', 'audit']);

// ─────────────────────────────────────────────────────────
// REPORTES Y EXPORTACIONES
// ─────────────────────────────────────────────────────────
$router->get('/reportes',                       'Reports\ReportController@index',    ['auth', 'role:reports.view']);
$router->get('/reportes/miembros',              'Reports\ReportController@members',  ['auth', 'role:reports.view']);
$router->get('/reportes/egresos',               'Reports\ReportController@expenses',  ['auth', 'role:reports.view']);
$router->get('/reportes/finanzas',              'Reports\ReportController@finances', ['auth', 'role:reports.view']);
$router->get('/reportes/asistencia',            'Reports\ReportController@attendance',['auth','role:reports.view']);
$router->get('/reportes/salones',               'Reports\ReportController@rooms',    ['auth', 'role:events.view']);
$router->get('/reportes/ingresos',              'Reports\ReportController@incomes',   ['auth', 'role:reports.view']);
$router->get('/reportes/exportar/{type}',       'Reports\ReportController@export',   ['auth', 'role:reports.export']);

// ─────────────────────────────────────────────────────────
// CONFIGURACIÓN DE LA IGLESIA
// ─────────────────────────────────────────────────────────
$router->get( '/configuracion',        'System\SettingsController@index',     ['auth', 'role:users.view']); // Solo accesos de admin en realidad, pero reutilizaremos el auth base y chequeamos en UI
$router->post('/configuracion',        'System\SettingsController@update',    ['auth', 'role:users.view', 'audit']);

// ─────────────────────────────────────────────────────────
// USUARIOS Y PERFIL
// ─────────────────────────────────────────────────────────
$router->get( '/usuarios',              'System\UserController@index',        ['auth', 'role:users.view']);
$router->get( '/usuarios/crear',        'System\UserController@create',       ['auth', 'role:users.create']);
$router->post('/usuarios/crear',        'System\UserController@store',        ['auth', 'role:users.create', 'audit']);
$router->get( '/usuarios/{id}/editar',  'System\UserController@edit',         ['auth', 'role:users.edit']);
$router->post('/usuarios/{id}/editar',  'System\UserController@update',       ['auth', 'role:users.edit', 'audit']);

$router->get( '/perfil',               'System\UserController@profile',       ['auth']);
$router->post('/perfil',               'System\UserController@updateProfile', ['auth']);
$router->get( '/perfil/mfa',           'System\UserController@mfaSetup',      ['auth']);
$router->post('/perfil/mfa',           'System\UserController@mfaEnable',     ['auth', 'audit']);

// AJAX Endpoint para polling de mensajes
$router->get('/api/messages/check', 'System\UserController@checkMessages', ['auth']);
$router->post('/api/users/force-logout', 'System\UserController@forceLogout', ['auth', 'role:users.edit', 'audit']);
$router->post('/api/users/send-message', 'System\UserController@sendMessage', ['auth', 'role:users.edit', 'audit']);
$router->get('/api/users/active-status', 'System\UserController@activeStatus', ['auth', 'role:users.view']);

// ─────────────────────────────────────────────────────────
// NOTIFICACIONES
// ─────────────────────────────────────────────────────────
$router->get( '/notificaciones',          'System\NotificationController@index',   ['auth']);
$router->post('/notificaciones/leer',     'System\NotificationController@markRead',['auth']);

