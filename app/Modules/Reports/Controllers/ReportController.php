<?php
/**
 * ReportController.php — Reportes y Analítica
 * ---------------------------------------------
 * Exportación de reportes en PDF y Excel.
 *
 * @package App\Controllers
 */
namespace App\Modules\Reports\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Modules\Members\Models\Member;
use App\Modules\Finance\Models\Finance;
use App\Modules\Finance\Models\Donation;
use App\Modules\Events\Models\Event;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Chart\Chart;
use PhpOffice\PhpSpreadsheet\Chart\DataSeries;
use PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues;
use PhpOffice\PhpSpreadsheet\Chart\Legend;
use PhpOffice\PhpSpreadsheet\Chart\PlotArea;
use PhpOffice\PhpSpreadsheet\Chart\Title;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use Dompdf\Dompdf;
use Dompdf\Options;

class ReportController extends Controller
{
    public function index(Request $request, Response $response): void
    {
        $this->view('Reports::reports/index', ['title' => 'Reportes y Analítica']);
    }

    /**
     * Reporte de ocupación de salones por mes
     */
    public function rooms(Request $request, Response $response): void
    {
        $churchId = $this->churchId();
        $month    = max(1, min(12, (int)$request->get('month', date('n'))));
        $year     = (int)$request->get('year', date('Y')) ?: (int)date('Y');
        $from     = sprintf('%04d-%02d-01 00:00:00', $year, $month);
        $to       = date('Y-m-d H:i:s', strtotime($from . ' +1 month'));

        $schedule = [];
        foreach ((new \App\Modules\Events\Models\Event())->getRoomSchedule($churchId, $from, $to) as $ev) {
            $schedule[$ev['room_name']][] = $ev;
        }

        $this->view('Reports::reports/rooms', [
            'title'     => 'Ocupación de Salones',
            'month'     => $month,
            'year'      => $year,
            'occupancy' => (new \App\Modules\Events\Models\Room())->getOccupancy($churchId, $from, $to),
            'schedule'  => $schedule,
        ]);
    }

    public function members(Request $request, Response $response): void
    {
        $churchId    = $this->churchId();
        $memberModel = new Member();
        $stats       = $memberModel->getStatsByStatus($churchId);
        $growth      = $memberModel->getMonthlyGrowth($churchId);
        $inactive    = $memberModel->getInactive($churchId, 4);

        $this->view('Reports::reports/members', [
            'title'      => 'Reporte de Miembros',
            'report'     => 'members',
            'stats'      => $stats,
            'sectionCounts' => $memberModel->getSectionCounts($churchId),
            'growth'     => $growth,
            'inactive'   => $inactive,
        ]);
    }

    public function finances(Request $request, Response $response): void
    {
        $churchId     = $this->churchId();
        $financeModel = new Finance();
        $year         = (int)$request->get('year', date('Y'));
        $cashFlow     = $financeModel->getAnnualCashFlow($churchId, $year);
        $breakdown    = $financeModel->getMonthlyCategoryBreakdown($churchId, (int)date('n'), $year);

        $this->view('Reports::reports/finances', [
            'title'     => 'Reporte Financiero',
            'report'    => 'finances',
            'cashFlow'  => $cashFlow,
            'breakdown' => $breakdown,
            'year'      => $year,
        ]);
    }

    public function attendance(Request $request, Response $response): void
    {
        $eventModel = new Event();
        $weekly     = $eventModel->getWeeklyAttendance($this->churchId());
        $this->view('Reports::reports/attendance', [
            'title'   => 'Reporte de Asistencia',
            'report'  => 'attendance',
            'weekly'  => $weekly,
        ]);
    }

    public function expenses(Request $request, Response $response): void
    {
        $churchId = $this->churchId();
        $financeModel = new Finance();

        $filters = [
            'target_module' => $request->get('target_module', ''),
            'category_id'   => $request->get('category_id', ''),
            'year'          => $request->get('year', ''),
            'start_date'    => $request->get('start_date', ''),
            'end_date'      => $request->get('end_date', ''),
        ];

        $db = \App\Core\Database::getInstance();

        $where = ["f.church_id = ?", "f.type = 'egreso'", "f.status != 'anulado'"];
        $params = [$churchId];

        if (!empty($filters['target_module'])) {
            $where[] = "f.target_module = ?";
            $params[] = $filters['target_module'];
        }

        if (!empty($filters['category_id'])) {
            $where[] = "f.category_id = ?";
            $params[] = (int)$filters['category_id'];
        }

        if (!empty($filters['start_date'])) {
            $where[] = "f.transaction_date >= ?";
            $params[] = $filters['start_date'];
        }

        if (!empty($filters['end_date'])) {
            $where[] = "f.transaction_date <= ?";
            $params[] = $filters['end_date'];
        }

        if (!empty($filters['year']) && empty($filters['start_date']) && empty($filters['end_date'])) {
            $where[] = "f.period_year = ?";
            $params[] = (int)$filters['year'];
        }

        $whereStr = implode(" AND ", $where);

        $records = $db->query(
            "SELECT f.*, fc.name as category_name, ua.name as approved_by_name
             FROM finances f
             JOIN finance_categories fc ON fc.id = f.category_id
             LEFT JOIN users ua ON ua.id = f.approved_by
             WHERE {$whereStr}
             ORDER BY f.transaction_date DESC, f.id DESC",
            $params
        );

        $chartData = $db->query(
            "SELECT fc.name as label, SUM(f.amount) as value
             FROM finances f
             JOIN finance_categories fc ON fc.id = f.category_id
             WHERE {$whereStr}
             GROUP BY fc.name
             ORDER BY value DESC",
            $params
        );

        $categories = $financeModel->getCategories($churchId, 'egreso');

        $yearsResult = $db->query(
            "SELECT DISTINCT period_year as year FROM finances WHERE church_id = ? AND type = 'egreso' ORDER BY year DESC",
            [$churchId]
        );
        $years = array_map(fn($row) => (int)$row['year'], $yearsResult);
        if (empty($years)) {
            $years = [(int)date('Y')];
        }

        $this->view('Reports::reports/expenses', [
            'title'      => 'Reporte de Egresos',
            'report'     => 'expenses',
            'records'    => $records,
            'chartData'  => $chartData,
            'categories' => $categories,
            'years'      => $years,
            'filters'    => $filters,
        ]);
    }

    /**
     * Exportar reporte en Excel o PDF
     */
    public function incomes(Request $request, Response $response): void
    {
        $churchId = $this->churchId();
        $financeModel = new Finance();

        $filters = [
            'target_module' => $request->get('target_module', ''),
            'category_id'   => $request->get('category_id', ''),
            'year'          => $request->get('year', ''),
            'start_date'    => $request->get('start_date', ''),
            'end_date'      => $request->get('end_date', ''),
        ];

        $db = \App\Core\Database::getInstance();

        $where = ["f.church_id = ?", "f.type = 'ingreso'", "f.status != 'anulado'"];
        $params = [$churchId];

        if (!empty($filters['target_module'])) {
            $where[] = "f.target_module = ?";
            $params[] = $filters['target_module'];
        }

        if (!empty($filters['category_id'])) {
            $where[] = "f.category_id = ?";
            $params[] = (int)$filters['category_id'];
        }

        if (!empty($filters['start_date'])) {
            $where[] = "f.transaction_date >= ?";
            $params[] = $filters['start_date'];
        }

        if (!empty($filters['end_date'])) {
            $where[] = "f.transaction_date <= ?";
            $params[] = $filters['end_date'];
        }

        if (!empty($filters['year']) && empty($filters['start_date']) && empty($filters['end_date'])) {
            $where[] = "f.period_year = ?";
            $params[] = (int)$filters['year'];
        }

        $whereStr = implode(" AND ", $where);

        $records = $db->query(
            "SELECT f.*, fc.name as category_name, ua.name as approved_by_name
             FROM finances f
             JOIN finance_categories fc ON fc.id = f.category_id
             LEFT JOIN users ua ON ua.id = f.approved_by
             WHERE {$whereStr}
             ORDER BY f.transaction_date DESC, f.id DESC",
            $params
        );

        $chartData = $db->query(
            "SELECT fc.name as label, SUM(f.amount) as value
             FROM finances f
             JOIN finance_categories fc ON fc.id = f.category_id
             WHERE {$whereStr}
             GROUP BY fc.name
             ORDER BY value DESC",
            $params
        );

        $categories = $financeModel->getCategories($churchId, 'ingreso');

        $yearsResult = $db->query(
            "SELECT DISTINCT period_year as year FROM finances WHERE church_id = ? AND type = 'ingreso' ORDER BY year DESC",
            [$churchId]
        );
        $years = array_map(fn($row) => (int)$row['year'], $yearsResult);
        if (empty($years)) {
            $years = [(int)date('Y')];
        }

        $this->view('Reports::reports/incomes', [
            'title'      => 'Reporte de ingresos',
            'report'     => 'incomes',
            'records'    => $records,
            'chartData'  => $chartData,
            'categories' => $categories,
            'years'      => $years,
            'filters'    => $filters,
        ]);
    }

    /**
     * Exportar reporte en Excel o PDF
     */
    
    public function export(Request $request, Response $response): void
    {
        $type     = $request->param('type');
        $format   = $request->get('format', 'excel');
        $churchId = $this->churchId();

        switch ($type) {
            case 'miembros':
                $this->exportMembers($churchId, $format);
                break;
            case 'donaciones':
                $this->exportDonations($churchId, $format, $request);
                break;
            case 'finanzas':
                $this->exportFinances($churchId, $format);
                break;
            case 'egresos':
                $this->exportExpenses($churchId, $format, $request);
                break;
            case 'ingresos':
                $this->exportIncomes($churchId, $format, $request);
                break;
            default:
                Session::flash('error', 'Tipo de reporte no válido.');
                $response->redirect('/reportes');
        }
    }

    /**
     * Exportar reporte de egresos a Excel o PDF
     */
        private function buildExcelReport(int $churchId, array $records, string $type, array $filters, array $columns, callable $rowMapper): \PhpOffice\PhpSpreadsheet\Spreadsheet
    {
        $db = \App\Core\Database::getInstance();
        $church = $db->queryOne("SELECT name, logo FROM churches WHERE id = ?", [$churchId]);
        
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $sheet->setTitle($type);

        $startRow = 1;
        
        // Logo
        if (!empty($church['logo'])) {
            $logoPath = __DIR__ . '/../../../../../public/' . ltrim($church['logo'], '/');
            if (file_exists($logoPath)) {
                $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
                $drawing->setName('Logo');
                $drawing->setDescription('Logo');
                $drawing->setPath($logoPath);
                $drawing->setHeight(50);
                $drawing->setCoordinates('A1');
                $drawing->setWorksheet($sheet);
                $sheet->getRowDimension(1)->setRowHeight(60);
            }
        }
        
        // Church Name
        $sheet->setCellValue('C1', $church['name'] ?? 'Iglesia');
        $sheet->getStyle('C1')->getFont()->setBold(true)->setSize(16);
        
        // Filters Info
        $filtersDesc = [];
        if (!empty($filters['target_module'])) {
            $filtersDesc[] = 'Módulo: ' . match($filters['target_module']) {
                'ofrendas' => 'Ofrendas',
                'construccion' => 'Construcción',
                'donaciones' => 'Obras Misioneras y Donaciones',
                default => $filters['target_module']
            };
        }
        if (!empty($filters['category_id'])) {
            $catName = $db->queryOne("SELECT name FROM finance_categories WHERE id = ?", [(int)$filters['category_id']])['name'] ?? '';
            if ($catName) $filtersDesc[] = 'Categoría: ' . $catName;
        }
        if (!empty($filters['start_date']) || !empty($filters['end_date'])) {
            $filtersDesc[] = 'Rango: ' . ($filters['start_date'] ?: 'Inicio') . ' al ' . ($filters['end_date'] ?: 'Fin');
        } elseif (!empty($filters['year'])) {
            $filtersDesc[] = 'Año: ' . $filters['year'];
        } else {
            $filtersDesc[] = 'Todos los registros';
        }
        
        $filterString = implode(' | ', $filtersDesc);
        $sheet->setCellValue('A2', $filterString);
        $sheet->mergeCells('A2:H2');
        $sheet->getStyle('A2')->getFont()->setItalic(true);
        
        $startRow = 4;
        
        // Headers
        foreach ($columns as $col => $header) {
            $sheet->setCellValueByColumnAndRow($col + 1, $startRow, $header);
            $sheet->getColumnDimensionByColumn($col + 1)->setAutoSize(true);
            $sheet->getStyleByColumnAndRow($col + 1, $startRow)->getFont()->setBold(true);
        }
        
        $categoryTotals = [];
        
        // Data
        foreach ($records as $row => $r) {
            $values = $rowMapper($r, $row + 1);
            foreach ($values as $col => $val) {
                $sheet->setCellValueByColumnAndRow($col + 1, $startRow + $row + 1, $val);
            }
            
            $cat = $r['category_name'] ?? 'Otros';
            $amount = (float)($r['amount'] ?? 0);
            if (!isset($categoryTotals[$cat])) {
                $categoryTotals[$cat] = 0;
            }
            $categoryTotals[$cat] += $amount;
        }
        
        // Chart Sheet
        if (!empty($categoryTotals)) {
            $chartSheet = $spreadsheet->createSheet();
            $chartSheet->setTitle('Gráficas');
            
            // Add Header to Chart Sheet as requested ("en cada hoja que se cree")
            if (!empty($church['logo']) && file_exists($logoPath ?? '')) {
                $drawing2 = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
                $drawing2->setName('Logo');
                $drawing2->setDescription('Logo');
                $drawing2->setPath($logoPath);
                $drawing2->setHeight(50);
                $drawing2->setCoordinates('A1');
                $drawing2->setWorksheet($chartSheet);
                $chartSheet->getRowDimension(1)->setRowHeight(60);
            }
            $chartSheet->setCellValue('C1', $church['name'] ?? 'Iglesia');
            $chartSheet->getStyle('C1')->getFont()->setBold(true)->setSize(16);
            $chartSheet->setCellValue('A2', $filterString);
            $chartSheet->mergeCells('A2:H2');
            $chartSheet->getStyle('A2')->getFont()->setItalic(true);
            
            $startRowChart = 4;
            $chartSheet->setCellValue('A' . $startRowChart, 'Categoría');
            $chartSheet->setCellValue('B' . $startRowChart, 'Monto');
            $chartSheet->getStyle('A' . $startRowChart . ':B' . $startRowChart)->getFont()->setBold(true);
            
            $rRow = $startRowChart + 1;
            foreach ($categoryTotals as $c => $v) {
                $chartSheet->setCellValue('A' . $rRow, $c);
                $chartSheet->setCellValue('B' . $rRow, $v);
                $rRow++;
            }
            $chartSheet->getColumnDimension('A')->setAutoSize(true);
            
            $dataSeriesLabels = [
                new \PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues(\PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues::DATASERIES_TYPE_STRING, "'Gráficas'!\$B\$" . $startRowChart, null, 1),
            ];
            $xAxisTickValues = [
                new \PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues(\PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues::DATASERIES_TYPE_STRING, "'Gráficas'!\$A\$" . ($startRowChart + 1) . ":\$A\$" . ($rRow - 1), null, count($categoryTotals)),
            ];
            $dataSeriesValues = [
                new \PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues(\PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues::DATASERIES_TYPE_NUMBER, "'Gráficas'!\$B\$" . ($startRowChart + 1) . ":\$B\$" . ($rRow - 1), null, count($categoryTotals)),
            ];
            
            $series = new \PhpOffice\PhpSpreadsheet\Chart\DataSeries(
                \PhpOffice\PhpSpreadsheet\Chart\DataSeries::TYPE_BARCHART, 
                \PhpOffice\PhpSpreadsheet\Chart\DataSeries::GROUPING_STANDARD, 
                range(0, count($dataSeriesValues) - 1), 
                $dataSeriesLabels, 
                $xAxisTickValues, 
                $dataSeriesValues
            );
            $series->setPlotDirection(\PhpOffice\PhpSpreadsheet\Chart\DataSeries::DIRECTION_COL);
            
            $plotArea = new \PhpOffice\PhpSpreadsheet\Chart\PlotArea(null, [$series]);
            $legend = new \PhpOffice\PhpSpreadsheet\Chart\Legend(\PhpOffice\PhpSpreadsheet\Chart\Legend::POSITION_RIGHT, null, false);
            $title = new \PhpOffice\PhpSpreadsheet\Chart\Title('Distribución por Categoría');
            
            $chart = new \PhpOffice\PhpSpreadsheet\Chart\Chart(
                'chart1', 
                $title, 
                $legend, 
                $plotArea, 
                true, 
                0, 
                null, 
                null 
            );
            
            $chart->setTopLeftPosition('D2');
            $chart->setBottomRightPosition('M20');
            $chartSheet->addChart($chart);
        }
        
        $spreadsheet->setActiveSheetIndex(0);
        return $spreadsheet;
    }

    private function exportExpenses(int $churchId, string $format, Request $request): void
    {
        $db = \App\Core\Database::getInstance();

        $targetModule = $request->get('target_module', '');
        $categoryId   = $request->get('category_id', '');
        $year         = $request->get('year', '');
        $startDate    = $request->get('start_date', '');
        $endDate      = $request->get('end_date', '');

        $where = ["f.church_id = ?", "f.type = 'egreso'", "f.status != 'anulado'"];
        $params = [$churchId];

        if (!empty($targetModule)) {
            $where[] = "f.target_module = ?";
            $params[] = $targetModule;
        }

        if (!empty($categoryId)) {
            $where[] = "f.category_id = ?";
            $params[] = (int)$categoryId;
        }

        if (!empty($startDate)) {
            $where[] = "f.transaction_date >= ?";
            $params[] = $startDate;
        }

        if (!empty($endDate)) {
            $where[] = "f.transaction_date <= ?";
            $params[] = $endDate;
        }

        if (!empty($year) && empty($startDate) && empty($endDate)) {
            $where[] = "f.period_year = ?";
            $params[] = (int)$year;
        }

        $whereStr = implode(" AND ", $where);

        $records = $db->query(
            "SELECT f.*, fc.name as category_name, ua.name as approved_by_name
             FROM finances f
             JOIN finance_categories fc ON fc.id = f.category_id
             LEFT JOIN users ua ON ua.id = f.approved_by
             WHERE {$whereStr}
             ORDER BY f.transaction_date DESC, f.id DESC",
            $params
        );

                        if ($format === 'excel') {
            $headers = ['#', 'Fecha', 'Factura', 'RUC/Cédula', 'Proveedor', 'Detalle del Ingreso', 'Monto', 'Categoría', 'Corresponde a', 'Autorizado Por', 'Método Pago', 'Estado'];
            $filters = ['target_module' => $targetModule, 'category_id' => $categoryId, 'start_date' => $startDate, 'end_date' => $endDate, 'year' => $year];
            $spreadsheet = $this->buildExcelReport($churchId, $records, 'Ingresos', $filters, $headers, function($r, $rowNum) {
                $moduleLabel = match ($r['target_module'] ?? '') {
                    'ofrendas' => 'Ofrendas',
                    'construccion' => 'Construcción',
                    'donaciones' => 'Obras Misioneras y Donaciones',
                    default => 'N/A'
                };
                return [
                    $rowNum,
                    formatDate($r['transaction_date']),
                    $r['invoice_number'] ?? 'N/A',
                    $r['provider_ruc'] ?? 'N/A',
                    $r['provider_name'] ?? 'N/A',
                    $r['description'],
                    (float)$r['amount'],
                    $r['category_name'],
                    $moduleLabel,
                    $r['approved_by_name'] ?? 'N/A',
                    paymentMethodLabel($r['payment_method']),
                    ucfirst($r['status'])
                ];
            });

            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="reporte-ingresos-' . date('Y-m-d') . '.xlsx"');
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $writer->setIncludeCharts(true);
            $writer->save('php://output');
            exit;
        } elseif ($format === 'pdf') {
            $options = new Options();
            $options->set('isHtml5ParserEnabled', true);
            $options->set('isRemoteEnabled', true);
            $dompdf = new Dompdf($options);

            $filtersDesc = [];
            if (!empty($targetModule)) {
                $filtersDesc[] = 'Módulo: ' . match($targetModule) {
                    'ofrendas' => 'Ofrendas',
                    'construccion' => 'Construcción',
                    'donaciones' => 'Obras Misioneras y Donaciones',
                    default => $targetModule
                };
            }
            if (!empty($categoryId)) {
                $catName = $db->queryOne("SELECT name FROM finance_categories WHERE id = ?", [(int)$categoryId])['name'] ?? '';
                if ($catName) $filtersDesc[] = 'Categoría: ' . $catName;
            }
            if (!empty($startDate) || !empty($endDate)) {
                $filtersDesc[] = 'Rango: ' . ($startDate ? formatDate($startDate) : 'Inicio') . ' al ' . ($endDate ? formatDate($endDate) : 'Fin');
            } elseif (!empty($year)) {
                $filtersDesc[] = 'Año: ' . $year;
            } else {
                $filtersDesc[] = 'Todos los registros';
            }

            $html = \App\Core\View::make('reports/expenses_pdf', [
                'records'     => $records,
                'filtersDesc' => implode(' | ', $filtersDesc),
                'church_name' => $db->queryOne("SELECT name FROM churches WHERE id = ?", [$churchId])['name'] ?? 'Ekkles'
            ]);

            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'landscape');
            $dompdf->render();

            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="reporte-egresos-' . date('Y-m-d') . '.pdf"');
            echo $dompdf->output();
            exit;
        }
    }

    /**
     * Exportar miembros a Excel
     */
    private function exportIncomes(int $churchId, string $format, Request $request): void
    {
        $db = \App\Core\Database::getInstance();

        $targetModule = $request->get('target_module', '');
        $categoryId   = $request->get('category_id', '');
        $year         = $request->get('year', '');
        $startDate    = $request->get('start_date', '');
        $endDate      = $request->get('end_date', '');

        $where = ["f.church_id = ?", "f.type = 'ingreso'", "f.status != 'anulado'"];
        $params = [$churchId];

        if (!empty($targetModule)) {
            $where[] = "f.target_module = ?";
            $params[] = $targetModule;
        }

        if (!empty($categoryId)) {
            $where[] = "f.category_id = ?";
            $params[] = (int)$categoryId;
        }

        if (!empty($startDate)) {
            $where[] = "f.transaction_date >= ?";
            $params[] = $startDate;
        }

        if (!empty($endDate)) {
            $where[] = "f.transaction_date <= ?";
            $params[] = $endDate;
        }

        if (!empty($year) && empty($startDate) && empty($endDate)) {
            $where[] = "f.period_year = ?";
            $params[] = (int)$year;
        }

        $whereStr = implode(" AND ", $where);

        $records = $db->query(
            "SELECT f.*, fc.name as category_name, ua.name as approved_by_name
             FROM finances f
             JOIN finance_categories fc ON fc.id = f.category_id
             LEFT JOIN users ua ON ua.id = f.approved_by
             WHERE {$whereStr}
             ORDER BY f.transaction_date DESC, f.id DESC",
            $params
        );

        if ($format === 'excel') {
            $spreadsheet = new Spreadsheet();
            $sheet       = $spreadsheet->getActiveSheet();
            $sheet->setTitle('ingresos');

            $headers = ['#', 'Fecha', 'Factura', 'RUC/Cédula', 'Proveedor', 'Detalle del Gasto', 'Monto', 'Categoría', 'Corresponde a', 'Autorizado Por', 'Método Pago', 'Estado'];
            foreach ($headers as $col => $header) {
                $sheet->setCellValueByColumnAndRow($col + 1, 1, $header);
                $sheet->getColumnDimensionByColumn($col + 1)->setAutoSize(true);
                $sheet->getStyleByColumnAndRow($col + 1, 1)->getFont()->setBold(true);
            }

            foreach ($records as $row => $r) {
                $moduleLabel = match ($r['target_module'] ?? '') {
                    'ofrendas' => 'Ofrendas',
                    'construccion' => 'Construcción',
                    'donaciones' => 'Obras Misioneras y Donaciones',
                    default => 'N/A'
                };

                $sheet->setCellValueByColumnAndRow(1,  $row + 2, $row + 1);
                $sheet->setCellValueByColumnAndRow(2,  $row + 2, formatDate($r['transaction_date']));
                $sheet->setCellValueByColumnAndRow(3,  $row + 2, $r['invoice_number'] ?? 'N/A');
                $sheet->setCellValueByColumnAndRow(4,  $row + 2, $r['provider_ruc'] ?? 'N/A');
                $sheet->setCellValueByColumnAndRow(5,  $row + 2, $r['provider_name'] ?? 'N/A');
                $sheet->setCellValueByColumnAndRow(6,  $row + 2, $r['description']);
                $sheet->setCellValueByColumnAndRow(7,  $row + 2, (float)$r['amount']);
                $sheet->setCellValueByColumnAndRow(8,  $row + 2, $r['category_name']);
                $sheet->setCellValueByColumnAndRow(9,  $row + 2, $moduleLabel);
                $sheet->setCellValueByColumnAndRow(10, $row + 2, $r['approved_by_name'] ?? 'N/A');
                $sheet->setCellValueByColumnAndRow(11, $row + 2, paymentMethodLabel($r['payment_method']));
                $sheet->setCellValueByColumnAndRow(12, $row + 2, ucfirst($r['status']));
            }

            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="reporte-ingresos-' . date('Y-m-d') . '.xlsx"');
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
            exit;
        } elseif ($format === 'pdf') {
            $options = new Options();
            $options->set('isHtml5ParserEnabled', true);
            $options->set('isRemoteEnabled', true);
            $dompdf = new Dompdf($options);

            $filtersDesc = [];
            if (!empty($targetModule)) {
                $filtersDesc[] = 'Módulo: ' . match($targetModule) {
                    'ofrendas' => 'Ofrendas',
                    'construccion' => 'Construcción',
                    'donaciones' => 'Obras Misioneras y Donaciones',
                    default => $targetModule
                };
            }
            if (!empty($categoryId)) {
                $catName = $db->queryOne("SELECT name FROM finance_categories WHERE id = ?", [(int)$categoryId])['name'] ?? '';
                if ($catName) $filtersDesc[] = 'Categoría: ' . $catName;
            }
            if (!empty($startDate) || !empty($endDate)) {
                $filtersDesc[] = 'Rango: ' . ($startDate ? formatDate($startDate) : 'Inicio') . ' al ' . ($endDate ? formatDate($endDate) : 'Fin');
            } elseif (!empty($year)) {
                $filtersDesc[] = 'Año: ' . $year;
            } else {
                $filtersDesc[] = 'Todos los registros';
            }

            $html = \App\Core\View::make('reports/expenses_pdf', [
                'records'     => $records,
                'filtersDesc' => implode(' | ', $filtersDesc),
                'church_name' => $db->queryOne("SELECT name FROM churches WHERE id = ?", [$churchId])['name'] ?? 'Ekkles'
            ]);

            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'landscape');
            $dompdf->render();

            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="reporte-ingresos-' . date('Y-m-d') . '.pdf"');
            echo $dompdf->output();
            exit;
        }
    }

    /**
     * Exportar miembros a Excel
     */
    
    private function exportMembers(int $churchId, string $format): void
    {
        $memberModel = new Member();
        $result      = $memberModel->search($churchId, [], 1, 10000);
        $members     = $result['data'];

        if ($format === 'excel') {
            $spreadsheet = new Spreadsheet();
            $sheet       = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Miembros');

            // Cabeceras
            $headers = ['#', 'Código', 'Nombre', 'Apellido', 'Email', 'Teléfono', 'Estado', 'Ministerio', 'Fecha Membresía', 'Edad'];
            foreach ($headers as $col => $header) {
                $sheet->setCellValueByColumnAndRow($col + 1, 1, $header);
                $sheet->getColumnDimensionByColumn($col + 1)->setAutoSize(true);
                $sheet->getStyleByColumnAndRow($col + 1, 1)->getFont()->setBold(true);
            }

            // Datos
            foreach ($members as $row => $m) {
                $sheet->setCellValueByColumnAndRow(1,  $row + 2, $row + 1);
                $sheet->setCellValueByColumnAndRow(2,  $row + 2, $m['code']);
                $sheet->setCellValueByColumnAndRow(3,  $row + 2, $m['first_name']);
                $sheet->setCellValueByColumnAndRow(4,  $row + 2, $m['last_name']);
                $sheet->setCellValueByColumnAndRow(5,  $row + 2, $m['email'] ?? '');
                $sheet->setCellValueByColumnAndRow(6,  $row + 2, $m['phone'] ?? '');
                $sheet->setCellValueByColumnAndRow(7,  $row + 2, memberStatusLabel($m['status']));
                $sheet->setCellValueByColumnAndRow(8,  $row + 2, $m['ministry_name'] ?? '');
                $sheet->setCellValueByColumnAndRow(9,  $row + 2, formatDate($m['membership_date']));
                $sheet->setCellValueByColumnAndRow(10, $row + 2, $m['birth_date'] ? calcAge($m['birth_date']) : '');
            }

            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="miembros-' . date('Y-m-d') . '.xlsx"');
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
            exit;
        }
    }

    private function exportDonations(int $churchId, string $format, Request $request): void
    {
        $donationModel = new Donation();

        $typeParam = $request->get('type', '');
        // Si viene un array o una lista separada por comas, resolver
        if (is_string($typeParam) && strpos($typeParam, ',') !== false) {
            $typeFilter = explode(',', $typeParam);
        } else {
            $typeFilter = $typeParam;
        }

        $filters = [
            'type'   => $typeFilter,
            'month'  => $request->get('month', ''),
            'year'   => $request->get('year', ''),
            'search' => $request->get('search', ''),
        ];

        $result        = $donationModel->search($churchId, $filters, 1, 10000);
        $donations     = $result['data'];

        if ($format === 'excel') {
            $spreadsheet = new Spreadsheet();
            $sheet       = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Donaciones');

            $headers = ['#', 'Fecha', 'Recibo', 'RUC', 'Donante', 'Detalle', 'Monto', 'Método', 'Categoría'];
            foreach ($headers as $col => $h) {
                $sheet->setCellValueByColumnAndRow($col + 1, 1, $h);
                $sheet->getColumnDimensionByColumn($col + 1)->setAutoSize(true);
                $sheet->getStyleByColumnAndRow($col + 1, 1)->getFont()->setBold(true);
            }

            foreach ($donations as $row => $d) {
                $sheet->setCellValueByColumnAndRow(1, $row + 2, $row + 1);
                $sheet->setCellValueByColumnAndRow(2, $row + 2, formatDate($d['donation_date']));
                $sheet->setCellValueByColumnAndRow(3, $row + 2, $d['receipt_number']);
                $sheet->setCellValueByColumnAndRow(4, $row + 2, $d['ruc'] ?? '');
                $sheet->setCellValueByColumnAndRow(5, $row + 2, $d['is_anonymous'] ? 'Anónimo' : ($d['full_donor_name'] ?? $d['donor_name'] ?? ''));
                $sheet->setCellValueByColumnAndRow(6, $row + 2, $d['detail'] ?? '');
                $sheet->setCellValueByColumnAndRow(7, $row + 2, (float)$d['amount']);
                $sheet->setCellValueByColumnAndRow(8, $row + 2, paymentMethodLabel($d['payment_method']));
                $sheet->setCellValueByColumnAndRow(9, $row + 2, $d['category_name'] ?? donationTypeLabel($d['type']));
            }

            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="donaciones-' . date('Y-m-d') . '.xlsx"');
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
            exit;
        }
    }

    private function exportFinances(int $churchId, string $format): void
    {
        $financeModel = new Finance();
        $result       = $financeModel->search($churchId, [], 1, 10000);
        $transactions = $result['data'];

        if ($format === 'excel') {
            $spreadsheet = new Spreadsheet();
            $sheet       = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Transacciones');

            $headers = ['#', 'Tipo', 'Categoría', 'Descripción', 'Monto', 'Método', 'Fecha', 'Estado'];
            foreach ($headers as $col => $h) {
                $sheet->setCellValueByColumnAndRow($col + 1, 1, $h);
                $sheet->getColumnDimensionByColumn($col + 1)->setAutoSize(true);
                $sheet->getStyleByColumnAndRow($col + 1, 1)->getFont()->setBold(true);
            }

            foreach ($transactions as $row => $t) {
                $sheet->setCellValueByColumnAndRow(1, $row + 2, $row + 1);
                $sheet->setCellValueByColumnAndRow(2, $row + 2, strtoupper($t['type']));
                $sheet->setCellValueByColumnAndRow(3, $row + 2, $t['category_name']);
                $sheet->setCellValueByColumnAndRow(4, $row + 2, $t['description']);
                $sheet->setCellValueByColumnAndRow(5, $row + 2, (float)$t['amount']);
                $sheet->setCellValueByColumnAndRow(6, $row + 2, paymentMethodLabel($t['payment_method']));
                $sheet->setCellValueByColumnAndRow(7, $row + 2, formatDate($t['transaction_date']));
                $sheet->setCellValueByColumnAndRow(8, $row + 2, ucfirst($t['status']));
            }

            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="finanzas-' . date('Y-m-d') . '.xlsx"');
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
            exit;
        }
    }
}




