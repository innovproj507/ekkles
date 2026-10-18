<?php
/**
 * FinanceController.php — Gestión Financiera
 * -------------------------------------------
 * Ingresos, egresos, presupuesto y flujo de caja.
 * Ninguna transacción se elimina físicamente.
 *
 * @package App\Controllers
 */
namespace App\Modules\Finance\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Helpers\Validator;
use App\Modules\System\Models\AuditLog;
use App\Modules\System\Models\Notification;
use App\Modules\Finance\Models\Finance;

class FinanceController extends Controller
{
    private Finance $financeModel;

    public function __construct()
    {
        $this->financeModel = new Finance();
    }

    public function index(Request $request, Response $response): void
    {
        $churchId = $this->churchId();
        $filters  = [
            'type'        => $request->get('type', ''),
            'month'       => $request->get('month', (int)date('n')),
            'year'        => $request->get('year', (int)date('Y')),
            'category_id' => $request->get('category_id', ''),
            'target_module' => $request->get('target_module', ''),
            'search'      => $request->get('search', ''),
            'status'      => $request->get('status', ''),
        ];
        $page = max(1, (int)$request->get('page', 1));

        $pagination  = $this->financeModel->search($churchId, $filters, $page);
        $summary     = $this->financeModel->getCurrentMonthSummary($churchId);
        $categories  = $this->financeModel->getCategories($churchId);
        $cashFlow    = $this->financeModel->getAnnualCashFlow($churchId, (int)date('Y'));

        $db = \App\Core\Database::getInstance();
        $pendingCount = (int)$db->queryOne("SELECT COUNT(id) as total FROM finances WHERE church_id = ? AND status = 'pendiente'", [$churchId])['total'];

        $this->view('Finance::finance/index', [
            'title'       => 'Finanzas',
            'transactions'=> $pagination['data'],
            'pagination'  => $pagination,
            'filters'     => $filters,
            'summary'     => $summary,
            'categories'  => $categories,
            'cashFlow'    => $cashFlow,
            'pendingCount'=> $pendingCount,
        ]);
    }

    public function create(Request $request, Response $response): void
    {
        $categories = $this->financeModel->getCategories($this->churchId());
        $this->view('Finance::finance/create', [
            'title'      => 'Registrar Transacción',
            'categories' => $categories,
            'old'        => Session::getFlash('old') ?? [],
            'errors'     => Session::getFlash('errors') ?? [],
        ]);
    }

    public function store(Request $request, Response $response): void
    {
        $this->requireCsrf($request);
        $data = $request->all();

        $validator = Validator::make($data, [
            'type'             => 'required|in:ingreso,egreso',
            'category_id'      => 'required|numeric',
            'description'      => 'required|min:3',
            'amount'           => 'required|numeric|positive',
            'transaction_date' => 'required|date',
        ]);

        if ($validator->fails()) {
            Session::flash('errors', $validator->errors());
            Session::flash('old', $data);
            $response->redirect('/finanzas/crear');
            return;
        }

        $status = Auth::roleLevel() >= 7 ? 'aprobado' : 'pendiente';
        $approvedBy = Auth::roleLevel() >= 7 ? Auth::id() : null;

        $date   = $data['transaction_date'];
        $transId = $this->financeModel->createTransaction([
            'church_id'        => $this->churchId(),
            'type'             => $data['type'],
            'category_id'      => (int)$data['category_id'],
            'description'      => trim($data['description']),
            'amount'           => (float)$data['amount'],
            'currency'         => 'USD',
            'payment_method'   => $data['payment_method'] ?? 'efectivo',
            'reference'        => $data['reference'] ?? null,
            'transaction_date' => $date,
            'period_month'     => (int)date('n', strtotime($date)),
            'period_year'      => (int)date('Y', strtotime($date)),
            'notes'            => $data['notes'] ?? null,
            'created_by'       => Auth::id(),
            'status'           => $status,
            'approved_by'      => $approvedBy,
        ]);

        if ($status === 'pendiente') {
            $this->notifyAdminsPendingApproval($this->churchId(), 'Transacción Financiera Pendiente', "El usuario " . Auth::user()['name'] . " ha registrado un(a) {$data['type']} por $" . number_format((float)$data['amount'], 2) . " que requiere aprobación.", [
                'action_url' => '/finanzas',
                'action_label' => 'Revisar y Aprobar'
            ]);
            $this->redirectWithSuccess('/finanzas', 'Transacción registrada exitosamente. Se ha notificado al Administrador para su aprobación.');
            return;
        }

        AuditLog::record('create', 'finance', null, 'finances', $transId, null, [
            'type'   => $data['type'],
            'amount' => $data['amount'],
        ]);

        $this->redirectWithSuccess('/finanzas', 'Transacción registrada exitosamente.');
    }

    public function budget(Request $request, Response $response): void
    {
        $churchId   = $this->churchId();
        $year       = (int)$request->get('year', date('Y'));
        $categories = $this->financeModel->getCategories($churchId);
        $cashFlow   = $this->financeModel->getAnnualCashFlow($churchId, $year);

        $this->view('Finance::finance/budget', [
            'title'      => 'Presupuesto',
            'categories' => $categories,
            'cashFlow'   => $cashFlow,
            'year'       => $year,
        ]);
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
     * Aprobar transacción (Solo Administradores nivel 7+)
     */
    public function approve(Request $request, Response $response): void
    {
        $this->requireCsrf($request);
        if (Auth::roleLevel() < 7) {
            $this->redirectWithError('/finanzas', 'No tienes permisos para aprobar.');
            return;
        }
        $id = (int)$request->param('id');
        $this->financeModel->update($id, [
            'status' => 'aprobado',
            'approved_by' => Auth::id()
        ]);
        AuditLog::record('approve', 'finance', null, 'finances', $id);
        $this->redirectWithSuccess('/finanzas', 'Transacción aprobada correctamente.');
    }

    /**
     * Rechazar transacción (Solo Administradores nivel 7+)
     */
    public function reject(Request $request, Response $response): void
    {
        $this->requireCsrf($request);
        if (Auth::roleLevel() < 7) {
            $this->redirectWithError('/finanzas', 'No tienes permisos para rechazar.');
            return;
        }
        $id = (int)$request->param('id');
        $this->financeModel->update($id, [
            'status' => 'rechazado',
            'approved_by' => Auth::id()
        ]);
        AuditLog::record('reject', 'finance', null, 'finances', $id);
        $this->redirectWithSuccess('/finanzas', 'Transacción rechazada.');
    }
}


