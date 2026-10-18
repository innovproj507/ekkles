<?php
/**
 * ReconciliationController.php — Controlador de Conciliaciones Bancarias
 * ----------------------------------------------------------------------
 * Flujo de trabajo para conciliar cuentas y cerrar periodos.
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
use App\Modules\Finance\Models\Reconciliation;

class ReconciliationController extends Controller
{
    private Reconciliation $reconModel;

    public function __construct()
    {
        $this->reconModel = new Reconciliation();
    }

    /**
     * Listado de conciliaciones
     */
    public function index(Request $request, Response $response): void
    {
        $churchId = $this->churchId();
        $page = max(1, (int)$request->get('page', 1));

        $filters = [
            'account_name' => $request->get('account_name', ''),
            'status'       => $request->get('status', ''),
        ];

        $pagination = $this->reconModel->search($churchId, $filters, $page);

        $this->view('Finance::reconciliations/index', [
            'title'      => 'Conciliaciones Bancarias',
            'records'    => $pagination['data'],
            'pagination' => $pagination,
            'filters'    => $filters,
        ]);
    }

    /**
     * Formulario para nueva conciliación (Paso 1: Fechas y Cuenta)
     * O (Paso 2: Calcular saldos y capturar saldo real) si es AJAX.
     */
    public function create(Request $request, Response $response): void
    {
        $churchId = $this->churchId();

        $this->view('Finance::reconciliations/create', [
            'title'  => 'Nueva Conciliación',
            'old'    => Session::getFlash('old') ?? [],
            'errors' => Session::getFlash('errors') ?? [],
        ]);
    }

    /**
     * Calcular saldos para una cuenta y rango de fechas (AJAX)
     */
    public function calculate(Request $request, Response $response): void
    {
        header('Content-Type: application/json');

        if (!$this->verifyCsrf($request)) {
            $this->json(['success' => false, 'error' => 'Token CSRF inválido.'], 403);
            return;
        }

        $accountName = trim($request->post('account_name') ?? '');
        $startDate   = trim($request->post('period_start') ?? '');
        $endDate     = trim($request->post('period_end') ?? '');

        if (!in_array($accountName, ['ofrendas', 'construccion', 'misiones']) || !$startDate || !$endDate) {
            $this->json(['success' => false, 'error' => 'Parámetros incompletos o inválidos.'], 400);
            return;
        }

        if (strtotime($startDate) > strtotime($endDate)) {
            $this->json(['success' => false, 'error' => 'La fecha de inicio debe ser anterior a la de fin.'], 400);
            return;
        }

        $churchId = $this->churchId();

        $startingBalance = $this->reconModel->getStartingBalance($churchId, $accountName, $startDate);
        $totalIncome     = $this->reconModel->getIncomeInPeriod($churchId, $accountName, $startDate, $endDate);
        $totalExpense    = $this->reconModel->getExpenseInPeriod($churchId, $accountName, $startDate, $endDate);
        
        $expectedBalance = $startingBalance + $totalIncome - $totalExpense;

        $this->json([
            'success'          => true,
            'starting_balance' => round($startingBalance, 2),
            'total_income'     => round($totalIncome, 2),
            'total_expense'    => round($totalExpense, 2),
            'expected_balance' => round($expectedBalance, 2),
        ]);
    }

    /**
     * Guardar o Cerrar conciliación
     */
    public function store(Request $request, Response $response): void
    {
        $this->requireCsrf($request);
        $churchId = $this->churchId();
        $data     = $request->all();

        $validator = Validator::make($data, [
            'account_name'     => 'required|in:ofrendas,construccion,misiones',
            'period_start'     => 'required|date',
            'period_end'       => 'required|date',
            'actual_balance'   => 'required|numeric',
            'starting_balance' => 'required|numeric',
            'total_income'     => 'required|numeric',
            'total_expense'    => 'required|numeric',
            'expected_balance' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            Session::flash('errors', $validator->errors());
            Session::flash('old', $data);
            $response->redirect('/conciliaciones/crear');
            return;
        }

        $actualBalance = (float)$data['actual_balance'];
        $expectedBalance = (float)$data['expected_balance'];
        $difference = round($expectedBalance - $actualBalance, 2);

        $action = $data['action'] ?? 'draft';

        if ($action === 'close') {
            // Verificar si hay diferencia
            if (abs($difference) > 0.01) {
                Session::flash('errors', [['La diferencia debe ser 0 para poder cerrar la conciliación. Diferencia actual: $' . $difference]]);
                Session::flash('old', $data);
                $response->redirect('/conciliaciones/crear');
                return;
            }

            $id = $this->reconModel->closeReconciliation([
                'church_id'        => $churchId,
                'account_name'     => $data['account_name'],
                'period_start'     => $data['period_start'],
                'period_end'       => $data['period_end'],
                'starting_balance' => $data['starting_balance'],
                'total_income'     => $data['total_income'],
                'total_expense'    => $data['total_expense'],
                'expected_balance' => $expectedBalance,
                'actual_balance'   => $actualBalance,
                'difference'       => $difference,
                'notes'            => trim($data['notes'] ?? ''),
            ], Auth::id());

            AuditLog::record('close', 'finance', null, 'reconciliations', $id, null, ['account' => $data['account_name'], 'balance' => $actualBalance]);
            $this->redirectWithSuccess('/conciliaciones/' . $id, 'Conciliación cerrada exitosamente.');

        } else {
            // Guardar borrador
            $id = $this->reconModel->create([
                'church_id'        => $churchId,
                'account_name'     => $data['account_name'],
                'period_start'     => $data['period_start'],
                'period_end'       => $data['period_end'],
                'starting_balance' => $data['starting_balance'],
                'total_income'     => $data['total_income'],
                'total_expense'    => $data['total_expense'],
                'expected_balance' => $expectedBalance,
                'actual_balance'   => $actualBalance,
                'difference'       => $difference,
                'status'           => 'borrador',
                'notes'            => trim($data['notes'] ?? ''),
            ]);

            AuditLog::record('create', 'finance', null, 'reconciliations', $id, null, ['account' => $data['account_name'], 'status' => 'borrador']);
            $this->redirectWithSuccess('/conciliaciones/' . $id, 'Borrador de conciliación guardado.');
        }
    }

    /**
     * Ver detalles
     */
    public function show(Request $request, Response $response): void
    {
        $id = (int)$request->param('id');
        $recon = $this->reconModel->find($id);

        if (!$recon || $recon['church_id'] != $this->churchId()) {
            $response->redirect('/conciliaciones');
            return;
        }

        $this->view('Finance::reconciliations/show', [
            'title' => 'Detalle de Conciliación',
            'recon' => $recon,
        ]);
    }
}
