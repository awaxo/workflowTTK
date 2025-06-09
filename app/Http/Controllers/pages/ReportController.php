<?php

namespace App\Http\Controllers\pages;

use App\Exports\ReportExport;
use App\Http\Controllers\Controller;
use App\Models\ChemicalPathogenicFactor;
use Illuminate\Support\Facades\Auth;
use Modules\EmployeeRecruitment\App\Models\RecruitmentWorkflow;
use App\Models\User;
use Maatwebsite\Excel\Facades\Excel;

/*
 * ReportController handles the generation and export of various reports,
 * including job advertisement statistics, chemical workers, and carcinogenic workers.
 */
class ReportController extends Controller
{
    /**
     * Display reports page
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Get available years from recruitment workflows
        $availableYears = RecruitmentWorkflow::selectRaw('YEAR(created_at) as year')
            ->groupBy('year')
            ->orderBy('year', 'desc')
            ->pluck('year')
            ->toArray();

        // Define report types
        $reportTypes = [
            'job_advertisement_statistics' => 'Álláshirdetési statisztika',
            'chemical_workers' => 'Vegyi anyaggal dolgozók',
            'carcinogenic_workers' => 'Rákkeltő anyaggal dolgozók'
        ];

        return view('content.pages.reports', compact('availableYears', 'reportTypes'));
    }

    /**
     * Generate report data based on type and year
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function generateReport()
    {
        $user = User::find(Auth::id());
        if (!$user->hasRole('adminisztrator')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request = request();
        $year = $request->input('year');
        $reportType = $request->input('report_type');

        if (!$year || !$reportType) {
            return response()->json(['error' => 'Year and report type are required'], 400);
        }

        $data = $this->getReportData($reportType, $year);

        return response()->json([
            'success' => true,
            'data' => $data,
            'report_type' => $reportType,
            'year' => $year
        ]);
    }

    /**
     * Export report data to Excel
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function exportReport()
    {
        $user = User::find(Auth::id());
        if (!$user->hasRole('adminisztrator')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request = request();
        $year = $request->input('year');
        $reportType = $request->input('report_type');

        if (!$year || !$reportType) {
            return response()->json(['error' => 'Year and report type are required'], 400);
        }

        $data = $this->getReportData($reportType, $year);
        
        return $this->generateExcelExport($data, $reportType, $year);
    }

    /**
     * Get report data based on type and year
     *
     * @param string $reportType
     * @param int $year
     * @return array
     */
    private function getReportData($reportType, $year)
    {
        switch ($reportType) {
            case 'job_advertisement_statistics':
                return $this->getJobAdvertisementStatistics($year);
            case 'chemical_workers':
                return $this->getChemicalWorkers($year);
            case 'carcinogenic_workers':
                return $this->getCarcinogenicWorkers($year);
            default:
                return [];
        }
    }

    /**
     * Get job advertisement statistics
     *
     * @param int $year
     * @return array
     */
    private function getJobAdvertisementStatistics($year)
    {
        $completedWorkflows = RecruitmentWorkflow::where('state', 'completed')
            ->whereYear('created_at', $year)
            ->get();

        $totalCompleted = $completedWorkflows->count();
        $withJobAd = $completedWorkflows->where('job_ad_exists', true)->count();
        $totalFemaleApplicants = $completedWorkflows->sum('applicants_female_count');
        $totalMaleApplicants = $completedWorkflows->sum('applicants_male_count');

        return [
            'total_completed' => $totalCompleted,
            'with_job_ad' => $withJobAd,
            'female_applicants' => $totalFemaleApplicants,
            'male_applicants' => $totalMaleApplicants
        ];
    }

    /**
     * Get chemical workers data
     *
     * @param int $year
     * @return array
     */
    private function getChemicalWorkers($year)
    {
        $workers = RecruitmentWorkflow::whereYear('created_at', $year)
            ->whereNotNull('medical_eligibility_data')
            ->get()
            ->filter(function ($workflow) {
                $medicalData = json_decode($workflow->medical_eligibility_data, true);
                return isset($medicalData['chemicals_exposure']) && 
                       in_array($medicalData['chemicals_exposure'], ['resz', 'egesz']);
            });

        $result = [];
        foreach ($workers as $worker) {
            $medicalData = json_decode($worker->medical_eligibility_data, true);
            
            // Get chemical pathogenic factors
            $chemicalIds = $medicalData['chemical_hazards_exposure'] ?? [];
            $chemicals = ChemicalPathogenicFactor::whereIn('id', $chemicalIds)
                ->where('deleted', 0)
                ->pluck('factor')
                ->toArray();

            $result[] = [
                'name' => $worker->name,
                'chemicals' => $chemicals,
                'exposure_level' => $medicalData['chemicals_exposure']
            ];
        }

        return $result;
    }

    /**
     * Get carcinogenic workers data
     *
     * @param int $year
     * @return array
     */
    private function getCarcinogenicWorkers($year)
    {
        $workers = RecruitmentWorkflow::whereYear('created_at', $year)
            ->whereNotNull('medical_eligibility_data')
            ->get()
            ->filter(function ($workflow) {
                $medicalData = json_decode($workflow->medical_eligibility_data, true);
                return isset($medicalData['carcinogenic_substances_exposure']) && 
                       in_array($medicalData['carcinogenic_substances_exposure'], ['resz', 'egesz']);
            });

        $result = [];
        foreach ($workers as $worker) {
            $medicalData = json_decode($worker->medical_eligibility_data, true);
            
            $result[] = [
                'name' => $worker->name,
                'substances' => $medicalData['planned_carcinogenic_substances_list'] ?? '',
                'exposure_level' => $medicalData['carcinogenic_substances_exposure']
            ];
        }

        return $result;
    }

    /**
     * Generate Excel export
     *
     * @param array $data
     * @param string $reportType
     * @param int $year
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    private function generateExcelExport($data, $reportType, $year)
    {
        // Prepare data and headers based on report type
        $exportData = $this->prepareExportData($data, $reportType);
        $headers = $this->getExportHeaders($reportType);
        
        // Generate filename
        $reportTypeNames = [
            'job_advertisement_statistics' => 'allashirdetesi_statisztika',
            'chemical_workers' => 'vegyi_anyaggal_dolgozok',
            'carcinogenic_workers' => 'rakkelte_anyaggal_dolgozok'
        ];
        
        $filename = 'riport_' . ($reportTypeNames[$reportType] ?? $reportType) . '_' . $year . '_' . date('Y-m-d_H-i-s') . '.xlsx';
        
        // Create and download Excel file
        return Excel::download(new ReportExport($exportData, $headers, $reportType, $year), $filename);
    }

    /**
     * Prepare export data based on report type
     *
     * @param array $data
     * @param string $reportType
     * @return array
     */
    private function prepareExportData($data, $reportType)
    {
        switch ($reportType) {
            case 'job_advertisement_statistics':
                return [
                    ['Összes lezárt ügy száma', $data['total_completed']],
                    ['Álláshirdetéssel történt felvétel', $data['with_job_ad']],
                    ['Álláshirdetésre jelentkező nők száma', $data['female_applicants']],
                    ['Álláshirdetésre jelentkező férfiak száma', $data['male_applicants']]
                ];
                
            case 'chemical_workers':
                return array_map(function($worker) {
                    return [
                        $worker['name'],
                        $this->getExposureLevelText($worker['exposure_level']),
                        implode('; ', $worker['chemicals'])
                    ];
                }, $data);
                
            case 'carcinogenic_workers':
                return array_map(function($worker) {
                    return [
                        $worker['name'],
                        $this->getExposureLevelText($worker['exposure_level']),
                        $worker['substances']
                    ];
                }, $data);
                
            default:
                return [];
        }
    }

    /**
     * Get export headers based on report type
     *
     * @param string $reportType
     * @return array
     */
    private function getExportHeaders($reportType)
    {
        switch ($reportType) {
            case 'job_advertisement_statistics':
                return ['Mutató', 'Érték'];
                
            case 'chemical_workers':
                return ['Név', 'Kitettség szintje', 'Kémiai kóroki tényezők'];
                
            case 'carcinogenic_workers':
                return ['Név', 'Kitettség szintje', 'Rákkeltő anyagok'];
                
            default:
                return [];
        }
    }

    /**
     * Get human readable exposure level text
     *
     * @param string $level
     * @return string
     */
    private function getExposureLevelText($level)
    {
        $levelMap = [
            'resz' => 'Munkaidő részében',
            'egesz' => 'Munkaidő egészében',
            'nincs' => 'Nincs kitettség'
        ];
        return $levelMap[$level] ?? $level;
    }
}