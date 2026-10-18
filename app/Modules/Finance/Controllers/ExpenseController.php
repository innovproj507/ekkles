<?php
/**
 * ExpenseController.php — Gestión de Egresos (Gastos) con Auditoría
 * -------------------------------------------------------------------
 * Registro y control detallado de egresos con autorización.
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
use App\Helpers\Upload;
use App\Helpers\Validator;
use App\Modules\System\Models\AuditLog;
use App\Modules\Finance\Models\Finance;
use App\Modules\System\Models\User;

class ExpenseController extends Controller
{
    private Finance $financeModel;

    public function __construct()
    {
        $this->financeModel = new Finance();
    }

    /**
     * Listado de Egresos con filtros y KPIs
     */
    public function index(Request $request, Response $response): void
    {
        $churchId = $this->churchId();
        $page = max(1, (int)$request->get('page', 1));

        $filters = [
            'type'          => 'egreso', // Forzamos tipo egreso
            'target_module' => $request->get('target_module', ''),
            'month'         => $request->get('month', (int)date('n')),
            'year'          => $request->get('year', (int)date('Y')),
            'category_id'   => $request->get('category_id', ''),
            'receipt'       => $request->get('receipt', ''),
            'search'        => $request->get('search', ''),
        ];

        // Buscar transacciones de egreso
        $pagination = $this->financeModel->search($churchId, $filters, $page);

        // Sumatorias según la procedencia y el periodo filtrados
        $modules = ['ofrendas' => 'Ofrendas', 'construccion' => 'Construcción', 'donaciones' => 'Obras Misioneras'];
        $module  = isset($modules[$filters['target_module']]) ? $filters['target_module'] : null;
        $year    = (int)$filters['year'] ?: (int)date('Y');
        $month   = (int)$filters['month'] ?: null;
        $scope   = $module ? ' · ' . $modules[$module] : '';

        $period      = $this->financeModel->getExpenseTotals($churchId, $module, $year, $month);
        $yearly      = $this->financeModel->getExpenseTotals($churchId, $module, $year);
        $accumulated = $this->financeModel->getExpenseTotals($churchId, $module);

        $summary = [
            'period_label'      => 'Egresos' . $scope . ' · ' . ($month ? monthName($month) . ' ' . $year : 'Año ' . $year),
            'period'            => $period['total'],
            'period_count'      => $period['count'],
            'yearly_label'      => 'Egresos' . $scope . ' · Año ' . $year,
            'yearly'            => $yearly['total'],
            'accumulated_label' => 'Total Acumulado' . $scope,
            'accumulated'       => $accumulated['total'],
        ];

        // Obtener solo categorías de tipo egreso para los filtros
        $categories = $this->financeModel->getCategories($churchId, 'egreso');

        $this->view('Finance::expenses/index', [
            'title'        => 'Egresos',
            'transactions' => $pagination['data'],
            'pagination'   => $pagination,
            'filters'      => $filters,
            'summary'      => $summary,
            'categories'   => $categories,
        ]);
    }

    /**
     * Mostrar formulario para registrar un egreso
     */
    public function create(Request $request, Response $response): void
    {
        $churchId = $this->churchId();
        
        // Categorías de egresos
        $categories = $this->financeModel->getCategories($churchId, 'egreso');
        
        // Usuarios para la asignación de la aprobación
        $userModel = new User();
        $users = $userModel->getByChurch($churchId);

        $this->view('Finance::expenses/create', [
            'title'      => 'Registrar Egreso',
            'categories' => $categories,
            'users'      => $users,
            'old'        => Session::getFlash('old') ?? [],
            'errors'     => Session::getFlash('errors') ?? [],
        ]);
    }

    /**
     * Almacenar un nuevo egreso
     */
    public function store(Request $request, Response $response): void
    {
        $this->requireCsrf($request);
        $churchId = $this->churchId();
        $data     = $request->all();

        $validator = Validator::make($data, [
            'amount'           => 'required|numeric|positive',
            'category_id'      => 'required|numeric',
            'target_module'    => 'required|in:ofrendas,construccion,donaciones',
            'transaction_date' => 'required|date',
            'description'      => 'required|min:3',
            'approved_by'      => 'required|numeric',
            'invoice_number'   => 'required|min:2',
            'provider_name'    => 'required|min:2',
        ]);

        if ($validator->fails()) {
            Session::flash('errors', $validator->errors());
            Session::flash('old', $data);
            $response->redirect('/egresos/crear');
            return;
        }

        $date = $data['transaction_date'];

        // Recibo (opcional): se guarda ordenado por la fecha del egreso
        $attachments = null;
        if (!empty($_FILES['receipt']['name'])) {
            $upload = (new Upload())->uploadReceipt($_FILES['receipt'], $date, $data['provider_name'] . ' ' . $data['invoice_number']);
            if (!$upload['success']) {
                Session::flash('errors', [['receipt' => [$upload['error']]]]);
                Session::flash('old', $data);
                $response->redirect('/egresos/crear');
                return;
            }
            $attachments = json_encode([$this->attachmentEntry($upload)], JSON_UNESCAPED_UNICODE);
        }

        $transId = $this->financeModel->createTransaction([
            'church_id'        => $churchId,
            'type'             => 'egreso',
            'category_id'      => (int)$data['category_id'],
            'target_module'    => $data['target_module'],
            'description'      => trim($data['description']),
            'amount'           => (float)$data['amount'],
            'currency'         => 'USD',
            'payment_method'   => $data['payment_method'] ?? 'efectivo',
            'reference'        => !empty($data['reference']) ? trim($data['reference']) : null,
            'invoice_number'   => trim($data['invoice_number']),
            'provider_ruc'     => !empty($data['provider_ruc']) ? trim($data['provider_ruc']) : null,
            'provider_name'    => trim($data['provider_name']),
            'transaction_date' => $date,
            'period_month'     => (int)date('n', strtotime($date)),
            'period_year'      => (int)date('Y', strtotime($date)),
            'notes'            => !empty($data['notes']) ? trim($data['notes']) : null,
            'attachments'      => $attachments,
            'created_by'       => Auth::id(),
            'approved_by'      => (int)$data['approved_by'],
            'status'           => 'aprobado',
        ]);

        AuditLog::record('create', 'finance', null, 'finances', $transId, null, [
            'type'           => 'egreso',
            'target_module'  => $data['target_module'],
            'amount'         => $data['amount'],
            'invoice_number' => $data['invoice_number'],
            'provider_name'  => $data['provider_name'],
            'approved_by'    => $data['approved_by']
        ]);

        $this->redirectWithSuccess('/egresos', 'Egreso registrado y autorizado exitosamente.');
    }

    /**
     * Adjuntar un recibo a un egreso existente
     */
    public function attachReceipt(Request $request, Response $response): void
    {
        $this->requireCsrf($request);
        $expense = $this->findOwnExpense((int)$request->param('id'));
        $back    = (string)($request->post('back') ?: '/egresos');
        if (!str_starts_with($back, '/egresos')) {
            $back = '/egresos';
        }

        if (!$expense) {
            $this->redirectWithError($back, 'Egreso no encontrado.');
            return;
        }
        if (empty($_FILES['receipt']['name'])) {
            $this->redirectWithError($back, 'Seleccione el archivo del recibo.');
            return;
        }

        $upload = (new Upload())->uploadReceipt(
            $_FILES['receipt'], $expense['transaction_date'], ($expense['provider_name'] ?? '') . ' ' . ($expense['invoice_number'] ?? '')
        );
        if (!$upload['success']) {
            $this->redirectWithError($back, $upload['error']);
            return;
        }

        $attachments   = json_decode((string)$expense['attachments'], true) ?: [];
        $attachments[] = $this->attachmentEntry($upload);
        $this->financeModel->update((int)$expense['id'], ['attachments' => json_encode($attachments, JSON_UNESCAPED_UNICODE)]);
        AuditLog::record('update', 'finance', null, 'finances', (int)$expense['id'], null, ['recibo_adjuntado' => $upload['path']]);

        $this->redirectWithSuccess($back, 'Recibo adjuntado al egreso del ' . formatDate($expense['transaction_date']) . '.');
    }

    /**
     * Ver o descargar un recibo de un egreso (?n = índice del adjunto)
     */
    public function receipt(Request $request, Response $response): void
    {
        $expense = $this->findOwnExpense((int)$request->param('id'));
        $attachments = $expense ? (json_decode((string)$expense['attachments'], true) ?: []) : [];
        $entry = $attachments[(int)$request->get('n', 0)] ?? null;
        $path  = $entry ? Upload::receiptAbsolutePath($entry['path']) : null;

        if (!$path) {
            $response->abort(404, 'Recibo no encontrado.');
            return;
        }

        header('Content-Type: ' . (mime_content_type($path) ?: 'application/octet-stream'));
        header('Content-Length: ' . filesize($path));
        header('Content-Disposition: inline; filename="' . basename($path) . '"');
        header('X-Content-Type-Options: nosniff');
        readfile($path);
        exit;
    }

    /** Egreso de la iglesia actual o null */
    private function findOwnExpense(int $id): ?array
    {
        $row = \App\Core\Database::getInstance()->queryOne(
            "SELECT * FROM finances WHERE id = ? AND church_id = ? AND type = 'egreso'",
            [$id, $this->churchId()]
        );
        return $row ?: null;
    }

    /** Datos del recibo que se guardan en finances.attachments */
    private function attachmentEntry(array $upload): array
    {
        return [
            'path'        => $upload['path'],
            'name'        => $upload['name'],
            'size'        => $upload['size'],
            'uploaded_at' => date('Y-m-d H:i:s'),
            'uploaded_by' => Auth::id(),
        ];
    }

    /**
     * Crear una nueva categoría de egresos vía AJAX
     */
    public function createCategory(Request $request, Response $response): void
    {
        header('Content-Type: application/json');

        if (!$this->verifyCsrf($request)) {
            $this->json(['success' => false, 'error' => 'Token CSRF inválido. Por favor intente de nuevo.'], 403);
            return;
        }

        $targetModule = trim($request->post('target_module') ?? $request->get('target_module') ?? '');
        if (empty($targetModule) || !in_array($targetModule, ['ofrendas', 'construccion', 'donaciones'])) {
            $this->json(['success' => false, 'error' => 'El módulo de destino es requerido e inválido.'], 400);
            return;
        }

        $churchId = $this->churchId();
        $name = trim($request->post('name') ?? $request->get('name') ?? '');
        if (empty($name)) {
            $this->json(['success' => false, 'error' => 'El nombre de la categoría es requerido.'], 400);
            return;
        }

        $db = \App\Core\Database::getInstance();
        
        // Verificar si la categoría ya existe para egresos en el mismo módulo
        $existing = $db->queryOne(
            "SELECT id FROM finance_categories WHERE church_id = ? AND LOWER(name) = ? AND type = 'egreso' AND target_module = ? AND is_active = 1",
            [$churchId, mb_strtolower($name), $targetModule]
        );

        if ($existing) {
            $this->json(['success' => false, 'error' => 'Ya existe una categoría de egresos con este nombre en este módulo.'], 400);
            return;
        }

        // Crear la categoría
        $db->execute(
            "INSERT INTO finance_categories (church_id, name, type, target_module, code, is_active) VALUES (?, ?, 'egreso', ?, NULL, 1)",
            [$churchId, $name, $targetModule]
        );
        $newId = (int)$db->lastInsertId();

        AuditLog::record('create', 'finance', null, 'finance_categories', $newId, null, ['name' => $name, 'type' => 'egreso', 'target_module' => $targetModule]);

        $this->json([
            'success'  => true,
            'category' => [
                'id'            => $newId,
                'name'          => $name,
                'target_module' => $targetModule
            ]
        ]);
    }
}


