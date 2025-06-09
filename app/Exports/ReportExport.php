<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Collection;

/**
 * Class ReportExport
 * 
 * This class handles the export of various reports to an Excel file.
 * It implements the FromCollection, WithHeadings, WithStyles, and WithTitle interfaces
 * to define the data source, column headers, styling, and title for the exported sheet.
 */
class ReportExport implements FromCollection, WithHeadings, WithStyles, WithTitle
{
    protected $data;
    protected $headers;
    protected $reportType;
    protected $year;

    /**
     * Constructor
     * 
     * @param array $data Export data
     * @param array $headers Column headers
     * @param string $reportType Report type identifier
     * @param int $year Report year
     */
    public function __construct($data, $headers, $reportType, $year)
    {
        $this->data = $data;
        $this->headers = $headers;
        $this->reportType = $reportType;
        $this->year = $year;
    }

    /**
     * Data to be exported
     * 
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return new Collection($this->data);
    }

    /**
     * Column headers
     * 
     * @return array
     */
    public function headings(): array
    {
        return $this->headers;
    }

    /**
     * Worksheet title
     * 
     * @return string
     */
    public function title(): string
    {
        $titles = [
            'job_advertisement_statistics' => 'Álláshirdetési statisztika',
            'chemical_workers' => 'Vegyi anyaggal dolgozók',
            'carcinogenic_workers' => 'Rákkeltő anyaggal dolgozók'
        ];

        return ($titles[$this->reportType] ?? 'Riport') . ' - ' . $this->year;
    }

    /**
     * Worksheet styling
     * 
     * @param \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet
     */
    public function styles(Worksheet $sheet)
    {
        // Get the last column letter
        $lastColumn = $this->getLastColumnLetter();
        
        // Header row styling
        $sheet->getStyle('A1:' . $lastColumn . '1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 12,
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => [
                    'rgb' => 'E9ECEF',
                ],
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
        ]);

        // Data rows styling
        if (count($this->data) > 0) {
            $dataRange = 'A2:' . $lastColumn . (count($this->data) + 1);
            $sheet->getStyle($dataRange)->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    ],
                ],
                'alignment' => [
                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP,
                    'wrapText' => true,
                ],
            ]);
        }
        
        // Auto-size columns
        foreach (range('A', $lastColumn) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Set minimum width for text columns to improve readability
        if ($this->reportType === 'chemical_workers' || $this->reportType === 'carcinogenic_workers') {
            $sheet->getColumnDimension('A')->setWidth(25); // Name column
            if ($this->reportType === 'chemical_workers') {
                $sheet->getColumnDimension('C')->setWidth(40); // Chemical factors column
            } else {
                $sheet->getColumnDimension('C')->setWidth(40); // Carcinogenic substances column
            }
        }

        // Set row height for better readability
        $sheet->getDefaultRowDimension()->setRowHeight(20);
        $sheet->getRowDimension(1)->setRowHeight(25); // Header row
    }
    
    /**
     * Get the letter of the last column
     * 
     * @return string
     */
    private function getLastColumnLetter()
    {
        return \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($this->headers));
    }
}