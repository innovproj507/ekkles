<?php
/**
 * DashboardController.php — Dashboard Ejecutivo
 * -----------------------------------------------
 * Recopila KPIs de todos los módulos para el panel principal.
 *
 * @package App\Controllers
 */

namespace App\Modules\Dashboard\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Modules\Members\Models\Member;
use App\Modules\Events\Models\Event;
use App\Modules\Finance\Models\Donation;
use App\Modules\Finance\Models\Finance;
use App\Modules\Pastoral\Models\PastoralFollow;
use App\Modules\System\Models\Notification;

class DashboardController extends Controller
{
    public function index(Request $request, Response $response): void
    {
        $this->requirePermission('dashboard.view');

        $churchId = $this->churchId();

        $memberModel   = new Member();
        $eventModel    = new Event();
        $donationModel = new Donation();
        $financeModel  = new Finance();
        $pastoralModel = new PastoralFollow();

        // ── KPIs de Miembros ──
        $memberStats = $memberModel->getStatsByStatus($churchId);
        $statsMap    = [];
        foreach ($memberStats as $s) {
            $statsMap[$s['status']] = (int)$s['total'];
        }

        $activeMembers   = $statsMap['activo'] ?? 0;
        $inactiveMembers = $statsMap['inactivo'] ?? 0;
        $visitors        = $statsMap['visitante'] ?? 0;
        $newBelievers    = $statsMap['nuevo_creyente'] ?? 0;
        $totalMembers    = array_sum($statsMap);
        $sectionCounts   = $memberModel->getSectionCounts($churchId);

        // ── Miembros inactivos (4+ semanas) ──
        $inactiveAlert = count($memberModel->getInactive($churchId, 4));

        // ── Cumpleaños hoy ──
        $birthdaysToday = $memberModel->getBirthdaysToday($churchId);

        // ── Crecimiento mensual (últimos 12 meses) ──
        $monthlyGrowth = $memberModel->getMonthlyGrowth($churchId);

        // ── Próximos eventos ──
        $upcomingEvents = $eventModel->getUpcoming($churchId, 5);

        // ── Asistencia semanal (últimas 8 semanas) ──
        $weeklyAttendance = $eventModel->getWeeklyAttendance($churchId);

        // ── Finanzas del mes ──
        $financeSummary = $financeModel->getCurrentMonthSummary($churchId);
        // Ofrendas del mes en curso (cuenta Iglesia: diezmos + ofrendas) y balance de esa cuenta
        $offeringTypes  = ['diezmo', 'ofrenda_general', 'ofrenda_especial'];
        $offeringsMonth = $donationModel->getPeriodTotal($churchId, $offeringTypes, (int)date('Y'), (int)date('n'));
        $offeringsBalance = $donationModel->getBalance($churchId, $offeringTypes, 'ofrendas', date('Y-m-d'));
        $monthNames = [1 => 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
        $currentMonthName = $monthNames[(int)date('n')];

        // ── Flujo de caja anual ──
        $cashFlow = $financeModel->getAnnualCashFlow($churchId, (int)date('Y'));

        // ── CRM Pastoral ──
        $pipelineSummary = $pastoralModel->getPipelineSummary($churchId);
        $overdueFollowups= count($pastoralModel->getOverdue($churchId));

        $this->view('Dashboard::dashboard/index', [
            'title'            => 'Dashboard Ejecutivo',
            'activeMembers'    => $activeMembers,
            'inactiveMembers'  => $inactiveMembers,
            'visitors'         => $visitors,
            'newBelievers'     => $newBelievers,
            'totalMembers'     => $totalMembers,
            'sectionCounts'    => $sectionCounts,
            'inactiveAlert'    => $inactiveAlert,
            'birthdaysToday'   => $birthdaysToday,
            'monthlyGrowth'    => $monthlyGrowth,
            'upcomingEvents'   => $upcomingEvents,
            'weeklyAttendance' => $weeklyAttendance,
            'financeSummary'   => $financeSummary,
            'offeringsMonth'   => $offeringsMonth,
            'offeringsBalance' => $offeringsBalance,
            'currentMonthName' => $currentMonthName,
            'cashFlow'         => $cashFlow,
            'pipelineSummary'  => $pipelineSummary,
            'overdueFollowups' => $overdueFollowups,
        ]);
    }
}


