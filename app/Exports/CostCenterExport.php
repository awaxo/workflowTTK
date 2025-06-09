<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Collection;

/**
 * Class CostCenterExport
 * 
 * This class handles the export of cost center data to an Excel file.
 * It implements the FromCollection, WithHeadings, and WithStyles interfaces
 * to define the data source, column headers, and styling for the exported sheet.
 */
class CostCenterExport implements FromCollection, WithHeadings, WithStyles
{
    protected $data;
    protected $headers;

    /**
     * Constructor
     * 
     * @param array $data Export data
     * @param array $headers Column headers
     */
    public function __construct($data, $headers)
    {
        $this->data = $data;
        $this->headers = $headers;
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
     * Worksheet styling
     * 
     * @param \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet
     */
    public function styles(Worksheet $sheet)
    {
        // Fejléc stílusok beállítása
        $sheet->getStyle('A1:' . $this->getLastColumnLetter($sheet) . '1')->applyFromArray([
            'font' => [
                'bold' => true,
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => [
                    'rgb' => 'E9ECEF',
                ],
            ],
        ]);
        
        // Automatikus oszlopszélesség beállítása minden oszlopra
        foreach (range('A', $this->getLastColumnLetter($sheet)) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
    }
    
    /**
     * Get the letter of the last column
     * 
     * @param \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet
     * @return string
     */
    private function getLastColumnLetter(Worksheet $sheet)
    {
        return \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($this->headers));
    }
}