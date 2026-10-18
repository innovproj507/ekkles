<?php
/**
 * DonationController.php — Gestión de Donaciones, Ofrendas y Construcción
 * -------------------------------------------------------------------------
 * Ofrendas, ofrendas generales, construcción y donaciones con generación de recibos PDF.
 *
 * @package App\Controllers
 */

namespace App\Modules\Finance\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Helpers\Validator;
use App\Modules\System\Models\AuditLog;
use App\Modules\System\Models\Notification;
use App\Modules\System\Models\User;
use App\Modules\Finance\Models\Donation;
use App\Modules\Members\Models\Member;
use Dompdf\Dompdf;
use Dompdf\Options;

class DonationController extends Controller
{
    private Donation $donationModel;

    public function __construct()
    {
        $this->donationModel = new Donation();
    }

    /**
     * Helper para obtener el contexto de la petición según la URL
     */
    private function getContext(Request $request): array
    {
        $uri = $request->uri();
        if (str_starts_with($uri, '/ofrendas')) {
            return [
                'context'      => 'ofrendas',
                'title'        => 'Ofrendas',
                'basePath'     => '/ofrendas',
                'allowedTypes' => ['ofrenda_general', 'ofrenda_especial', 'diezmo'],
            ];
        } elseif (str_starts_with($uri, '/construccion')) {
            return [
                'context'      => 'construccion',
                'title'        => 'Construcción',
                'basePath'     => '/construccion',
                'allowedTypes' => ['construccion'],
            ];
        } else {
            return [
                'context'      => 'donaciones',
                'title'        => 'Obras Misioneras y Donaciones',
                'basePath'     => '/donaciones',
                'allowedTypes' => ['donacion', 'misiones', 'proyecto', 'promesa'],
            ];
        }
    }

    public function index(Request $request, Response $response): void
    {
        $churchId = $this->churchId();
        $page     = max(1, (int)$request->get('page', 1));
        
        $contextData = $this->getContext($request);
        $context = $contextData['context'];
        $title = $contextData['title'];
        $basePath = $contextData['basePath'];
        $allowedTypes = $contextData['allowedTypes'];

        $selectedType = $request->get('type', '');
        if (!empty($selectedType) && in_array($selectedType, $allowedTypes)) {
            $typeFilter = $selectedType;
        } else {
            $typeFilter = $allowedTypes;
        }

        // Mes vacío ('') = todos los meses del año seleccionado
        $month = $request->get('month', (int)date('n'));
        $month = ($month === '' || (int)$month === 0) ? '' : (int)$month;
        $year  = (int)$request->get('year', (int)date('Y')) ?: (int)date('Y');

        $filters  = [
            'type'   => $typeFilter,
            'month'  => $month,
            'year'   => $year,
            'search' => $request->get('search', ''),
            'status' => $request->get('status', ''),
        ];

        $pagination = $this->donationModel->search($churchId, $filters, $page);

        // Sumatorias del módulo (o del tipo filtrado): periodo filtrado, año y acumulado
        $periodMonth = $month === '' ? null : $month;
        // Cierre del periodo: fin del mes (o del año); si el periodo está en curso, hoy
        $periodEnd   = $periodMonth ? date('Y-m-t', mktime(0, 0, 0, $periodMonth, 1, $year)) : "{$year}-12-31";
        $balanceDate = min($periodEnd, date('Y-m-d'));
        $period      = $periodMonth ? monthName($periodMonth) . ' ' . $year : 'Año ' . $year;
        $label       = is_string($typeFilter)
            ? donationTypeLabel($typeFilter)
            : ['ofrendas' => 'Ofrendas', 'construccion' => 'Construcción', 'donaciones' => 'Donaciones'][$context];

        $summary = [
            'card1_label' => "{$label} · {$period}",
            'card1_val'   => $this->donationModel->getPeriodTotal($churchId, $typeFilter, $year, $periodMonth),
            'card2_label' => "{$label} · Año {$year}",
            'card2_val'   => $this->donationModel->getPeriodTotal($churchId, $typeFilter, $year),
            // Balance de la cuenta (ingresos − egresos) al cierre del periodo filtrado
            'card3_label' => 'Balance · al ' . date('d/m/Y', strtotime($balanceDate)),
            'card3_val'   => $this->donationModel->getBalance($churchId, $allowedTypes, $context, $balanceDate),
        ];

        $topDonors  = $this->donationModel->getTopDonors($churchId, (int)date('n'), (int)date('Y'), 5);

        // Para evitar problemas en el filtro tipo en la vista, guardamos el tipo seleccionado original (o vacío)
        $filters['selected_type'] = $selectedType;

        $db = \App\Core\Database::getInstance();
        $pendingCount = (int)$db->queryOne(
            "SELECT COUNT(id) as total FROM donations WHERE church_id = ? AND status = 'pendiente' AND type IN (" . implode(',', array_fill(0, count($allowedTypes), '?')) . ")",
            array_merge([$churchId], $allowedTypes)
        )['total'];

        $this->view('Finance::donations/index', [
            'title'        => $title,
            'donations'    => $pagination['data'],
            'pagination'   => $pagination,
            'filters'      => $filters,
            'summary'      => $summary,
            'topDonors'    => $topDonors,
            'basePath'     => $basePath,
            'allowedTypes' => $allowedTypes,
            'pendingCount' => $pendingCount,
        ]);
    }

    public function create(Request $request, Response $response): void
    {
        $contextData = $this->getContext($request);
        $churchId = $this->churchId();
        $members = (new Member())->getForSelect($churchId);

        $financeModel = new \App\Modules\Finance\Models\Finance();

        // Filtrar categorías por módulo directamente con target_module
        $context = $contextData['context']; // 'ofrendas' | 'construccion' | 'donaciones'
        $categories = $financeModel->getCategories($churchId, 'ingreso', $context);

        $this->view('Finance::donations/create', [
            'title'        => 'Registrar ' . $contextData['title'],
            'members'      => $members,
            'categories'   => $categories,
            'groups'       => $financeModel->getCategoryGroups($churchId, 'ingreso'),
            'old'          => Session::getFlash('old') ?? [],
            'errors'       => Session::getFlash('errors') ?? [],
            'basePath'     => $contextData['basePath'],
            'allowedTypes' => $contextData['allowedTypes'],
        ]);
    }


    public function store(Request $request, Response $response): void
    {
        $this->requireCsrf($request);
        $churchId = $this->churchId();
        $data     = $request->all();

        $contextData = $this->getContext($request);
        $basePath = $contextData['basePath'];

        $validator = Validator::make($data, [
            'amount'        => 'required|numeric|positive',
            'category_id'   => 'required|numeric',
            'donation_date' => 'required|date',
            'detail'        => 'required|min:3',
        ]);

        if ($validator->fails()) {
            Session::flash('errors', $validator->errors());
            Session::flash('old', $data);
            $response->redirect($basePath . '/crear');
            return;
        }

        // Validar si el número de recibo es manual y ya existe
        $receiptNumber = !empty($data['receipt_number']) ? trim($data['receipt_number']) : null;
        if ($receiptNumber) {
            $db = \App\Core\Database::getInstance();
            $exists = $db->queryOne(
                "SELECT id FROM donations WHERE receipt_number = ? AND church_id = ?",
                [$receiptNumber, $churchId]
            );
            if ($exists) {
                Session::flash('errors', [['receipt_number' => ['El número de recibo ya ha sido registrado.']]]);
                Session::flash('old', $data);
                $response->redirect($basePath . '/crear');
                return;
            }
        }

        // Cargar y mapear la categoría seleccionada
        $categoryId = (int)$data['category_id'];
        $db = \App\Core\Database::getInstance();
        $category = $db->queryOne(
            "SELECT * FROM finance_categories WHERE id = ? AND church_id = ? AND is_active = 1",
            [$categoryId, $churchId]
        );

        if (!$category) {
            Session::flash('errors', [['category_id' => ['La categoría seleccionada no es válida.']]]);
            Session::flash('old', $data);
            $response->redirect($basePath . '/crear');
            return;
        }

        // Determinar type para compatibilidad con enums del sistema anterior
        $code = $category['code'] ?? '';
        $type = 'donacion';
        if ($code === 'ING-001') {
            $type = 'diezmo';
        } elseif ($code === 'ING-002') {
            $type = 'ofrenda_general';
        } elseif ($code === 'ING-003') {
            $type = 'ofrenda_especial';
        } elseif ($code === 'ING-007') {
            $type = 'construccion';
        } elseif ($code === 'ING-006') {
            $type = 'misiones';
        } elseif ($code === 'ING-004') {
            $type = 'donacion';
        } elseif ($code === 'ING-005') {
            $type = 'proyecto';
        } else {
            $catName = mb_strtolower($category['name']);
            if (strpos($catName, 'diezmo') !== false) {
                $type = 'diezmo';
            } elseif (strpos($catName, 'ofrenda') !== false) {
                $type = 'ofrenda_general';
            } elseif (strpos($catName, 'construcción') !== false || strpos($catName, 'construccion') !== false) {
                $type = 'construccion';
            } elseif (strpos($catName, 'misión') !== false || strpos($catName, 'mision') !== false) {
                $type = 'misiones';
            }
        }

        $status = Auth::roleLevel() >= 7 ? 'aprobado' : 'pendiente';
        $approvedBy = Auth::roleLevel() >= 7 ? Auth::id() : null;

        $donationId = $this->donationModel->createDonation([
            'church_id'      => $churchId,
            'member_id'      => !empty($data['member_id']) ? (int)$data['member_id'] : null,
            'donor_name'     => $data['donor_name'] ?? null,
            'ruc'            => !empty($data['ruc']) ? trim($data['ruc']) : null,
            'type'           => $type,
            'detail'         => trim($data['detail']),
            'category_id'    => $categoryId,
            'donation_date'  => $data['donation_date'],
            'amount'         => (float)$data['amount'],
            'currency'       => 'USD',
            'payment_method' => $data['payment_method'] ?? 'efectivo',
            'reference'      => $data['reference'] ?? null,
            'notes'          => $data['notes'] ?? null,
            'is_anonymous'   => isset($data['is_anonymous']) ? 1 : 0,
            'created_by'     => Auth::id(),
            'receipt_number' => $receiptNumber,
            'status'         => $status,
            'approved_by'    => $approvedBy,
        ], $churchId);

        if ($status === 'pendiente') {
            $actionUrl = $context === 'donaciones' ? '/donaciones' : ($context === 'construccion' ? '/construccion' : '/ofrendas');
            $this->notifyAdminsPendingApproval($churchId, 'Donación / Ofrenda Pendiente', "El usuario " . Auth::user()['name'] . " ha registrado un ingreso por $" . number_format((float)$data['amount'], 2) . " que requiere aprobación.", [
                'action_url' => $actionUrl,
                'action_label' => 'Revisar y Aprobar'
            ]);
            $this->redirectWithSuccess($basePath, 'Registro guardado exitosamente. Se ha notificado al Administrador para su aprobación.');
            return;
        }

        AuditLog::record('create', 'donations', null, 'donations', $donationId, null, [
            'amount' => $data['amount'],
            'type'   => $type,
        ]);

        $this->redirectWithSuccess($basePath, 'Registro guardado exitosamente.');
    }

    public function show(Request $request, Response $response): void
    {
        $id = (int)$request->param('id');
        $contextData = $this->getContext($request);
        $basePath = $contextData['basePath'];
        $response->redirect($basePath . '/' . $id . '/recibo');
    }

    /**
     * Generar recibo PDF de donación
     */
    public function receipt(Request $request, Response $response): void
    {
        $id       = (int)$request->param('id');
        $donation = $this->donationModel->find($id);

        $contextData = $this->getContext($request);
        $basePath = $contextData['basePath'];

        if (!$donation) {
            Session::flash('error', 'Registro no encontrado.');
            $response->redirect($basePath);
            return;
        }

        // Obtener datos del donante
        $memberModel  = new Member();
        $member = $donation['member_id'] ? $memberModel->find($donation['member_id']) : null;

        // Obtener datos de la iglesia para el logo
        $db = \App\Core\Database::getInstance();
        $church = $db->queryOne("SELECT * FROM churches WHERE id = ?", [$churchId]);

        $logoBase64 = null;
        if (!empty($church['logo'])) {
            $logoPath = dirname(__DIR__, 4) . '/public/uploads/' . $church['logo'];
            if (file_exists($logoPath)) {
                $logoData = file_get_contents($logoPath);
                $type = pathinfo($logoPath, PATHINFO_EXTENSION);
                $logoBase64 = 'data:image/' . $type . ';base64,' . base64_encode($logoData);
            }
        }

        // Generar HTML del recibo
        $html = View::make('donations/receipt_pdf', [
            'donation'   => $donation,
            'member'     => $member,
            'church'     => $church,
            'logoBase64' => $logoBase64
        ]);

        // Configurar Dompdf
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', false);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A5', 'portrait');
        $dompdf->render();

        // Marcar recibo como enviado
        $this->donationModel->update($id, ['receipt_sent' => 1]);
        AuditLog::record('receipt_generated', 'donations', null, 'donations', $id);

        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="recibo-' . $donation['receipt_number'] . '.pdf"');
        echo $dompdf->output();
        exit;
    }

    /**
     * Crear una nueva categoría de ingresos vía AJAX
     */
    public function createCategory(Request $request, Response $response): void
    {
        // Enviar cabecera JSON
        header('Content-Type: application/json');

        if (!$this->verifyCsrf($request)) {
            $this->json(['success' => false, 'error' => 'Token CSRF inválido. Por favor intente de nuevo.'], 403);
            return;
        }

        $churchId = $this->churchId();
        $name  = trim($request->post('name') ?? $request->get('name') ?? '');
        $group = trim((string)$request->post('group_name', '')) ?: null;
        // La cuenta llega desde el formulario (la ruta es única para las tres cuentas)
        $module = (string)$request->post('module', '');
        if (!in_array($module, ['ofrendas', 'construccion', 'donaciones'], true)) {
            $module = 'donaciones';
        }

        if (empty($name)) {
            $this->json(['success' => false, 'error' => 'El nombre de la categoría es requerido.'], 400);
            return;
        }

        $db = \App\Core\Database::getInstance();

        // Verificar si la categoría ya existe en esta cuenta (activa y de tipo ingreso)
        $existing = $db->queryOne(
            "SELECT id FROM finance_categories WHERE church_id = ? AND LOWER(name) = ? AND type = 'ingreso' AND target_module = ? AND is_active = 1",
            [$churchId, mb_strtolower($name), $module]
        );

        if ($existing) {
            $this->json(['success' => false, 'error' => 'Ya existe una categoría de ingresos con este nombre.'], 400);
            return;
        }

        // Crear la categoría
        $db->execute(
            "INSERT INTO finance_categories (church_id, name, group_name, type, target_module, code, is_active) VALUES (?, ?, ?, 'ingreso', ?, NULL, 1)",
            [$churchId, $name, $group, $module]
        );
        $newId = (int)$db->lastInsertId();

        AuditLog::record('create', 'finance', null, 'finance_categories', $newId, null, ['name' => $name, 'type' => 'ingreso', 'target_module' => $module, 'group_name' => $group]);

        $this->json([
            'success'  => true,
            'category' => [
                'id'         => $newId,
                'name'       => $name,
                'group_name' => $group,
            ]
        ]);
    }

    /**
     * Clasificación de las categorías de ingreso de la cuenta en grupos
     */
    public function categories(Request $request, Response $response): void
    {
        $contextData = $this->getContext($request);
        $financeModel = new \App\Modules\Finance\Models\Finance();

        $this->view('Finance::donations/categories', [
            'title'      => 'Categorías · ' . $contextData['title'],
            'basePath'   => $contextData['basePath'],
            'categories' => $financeModel->getCategories($this->churchId(), 'ingreso', $contextData['context']),
            'groups'     => $financeModel->getCategoryGroups($this->churchId(), 'ingreso'),
        ]);
    }

    /**
     * Guardar el grupo asignado a cada categoría de la cuenta
     */
    public function saveCategories(Request $request, Response $response): void
    {
        $this->requireCsrf($request);
        $contextData = $this->getContext($request);
        $churchId = $this->churchId();
        $groups   = (array)$request->post('group', []);

        $db = \App\Core\Database::getInstance();
        $changed = 0;
        foreach ($groups as $categoryId => $group) {
            $group = trim((string)$group);
            $changed += $db->execute(
                "UPDATE finance_categories SET group_name = ?
                 WHERE id = ? AND church_id = ? AND type = 'ingreso' AND target_module = ?",
                [$group !== '' ? mb_substr($group, 0, 80) : null, (int)$categoryId, $churchId, $contextData['context']]
            );
        }

        AuditLog::record('update', 'finance', null, 'finance_categories', null, null, ['grupos_actualizados' => $changed, 'cuenta' => $contextData['context']]);
        $this->redirectWithSuccess($contextData['basePath'] . '/categorias', "Clasificación guardada ({$changed} categorías actualizadas).");
    }

    /**
     * Enviar notificación interna a los administradores
     */
    private function notifyAdminsPendingApproval(int $churchId, string $title, string $body, array $data = []): void
    {
        $db = \App\Core\Database::getInstance();
        $admins = $db->query(
            "SELECT id FROM users WHERE church_id = ? AND role IN ('super_admin', 'ministro_principal', 'administrador') AND is_active = 1",
            [$churchId]
        );
        $notifModel = new Notification();
        foreach ($admins as $admin) {
            $notifModel->notify($churchId, (int)$admin['id'], $title, $body, $data);
        }
    }

    /**
     * Aprobar donación (Solo Administradores nivel 7+)
     */
    public function approve(Request $request, Response $response): void
    {
        $this->requireCsrf($request);
        if (Auth::roleLevel() < 7) {
            $this->redirectWithError($_SERVER['HTTP_REFERER'] ?? '/donaciones', 'No tienes permisos para aprobar.');
            return;
        }
        $id = (int)$request->param('id');
        $this->donationModel->update($id, [
            'status' => 'aprobado',
            'approved_by' => Auth::id()
        ]);
        AuditLog::record('approve', 'donations', null, 'donations', $id);
        $this->redirectWithSuccess($_SERVER['HTTP_REFERER'] ?? '/donaciones', 'Transacción aprobada correctamente.');
    }

    /**
     * Rechazar donación (Solo Administradores nivel 7+)
     */
    public function reject(Request $request, Response $response): void
    {
        $this->requireCsrf($request);
        if (Auth::roleLevel() < 7) {
            $this->redirectWithError($_SERVER['HTTP_REFERER'] ?? '/donaciones', 'No tienes permisos para rechazar.');
            return;
        }
        $id = (int)$request->param('id');
        $this->donationModel->update($id, [
            'status' => 'rechazado',
            'approved_by' => Auth::id()
        ]);
        AuditLog::record('reject', 'donations', null, 'donations', $id);
        $this->redirectWithSuccess($_SERVER['HTTP_REFERER'] ?? '/donaciones', 'Transacción rechazada.');
    }
}


