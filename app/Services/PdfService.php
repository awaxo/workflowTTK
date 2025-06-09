<?php

namespace App\Services;

use Mpdf\Mpdf;
use Mpdf\Config\ConfigVariables;
use Mpdf\Config\FontVariables;
use Mpdf\Output\Destination;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

/**
 * PDF generation service using mPDF library
 * 
 * This service provides methods to create and configure mPDF instances,
 * set headers and footers, and generate PDF documents from Blade views.
 */
class PdfService
{
    /**
     * Create new MPDF instance with default configuration
     *
     * @param array $config Additional configuration
     * @return Mpdf
     */
    public function createMpdf(array $config = []): Mpdf
    {
        // Create temp directory if it doesn't exist
        $tempDir = storage_path('app/mpdf');
        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0755, true);
        }
        
        // Get default font directories
        $defaultConfig = (new ConfigVariables())->getDefaults();
        $fontDirs = $defaultConfig['fontDir'];
        
        // Get default font data
        $defaultFontConfig = (new FontVariables())->getDefaults();
        $fontData = $defaultFontConfig['fontdata'];
        
        // Default configuration
        $defaultConfig = [
            'mode' => 'utf-8',
            'format' => 'A4',
            'margin_left' => 15,
            'margin_right' => 15,
            'margin_top' => 25,
            'margin_bottom' => 25,
            'margin_header' => 5,
            'margin_footer' => 10,
            'tempDir' => $tempDir,
            'fontDir' => array_merge($fontDirs, [
                resource_path('fonts'),
            ]),
            'fontdata' => $fontData + [
                'dejavusans' => [
                    'R' => 'DejaVuSans.ttf',
                    'B' => 'DejaVuSans-Bold.ttf',
                    'I' => 'DejaVuSans-Oblique.ttf',
                    'BI' => 'DejaVuSans-BoldOblique.ttf',
                ]
            ],
            'default_font' => 'dejavusans',
            'showImageErrors' => true,
        ];
        
        // Merge default and custom config
        $finalConfig = array_merge($defaultConfig, $config);
        
        // Return new MPDF instance
        return new Mpdf($finalConfig);
    }
    
    /**
     * Set standard TTK header
     * 
     * @param Mpdf $mpdf MPDF instance
     * @param array $options Optional parameters for header customization
     * @return void
     */
    public function setTtkHeader(Mpdf $mpdf, array $options = []): void
    {
        $headerHtml = '
        <div>
            <table style="width: 100%; table-layout: fixed; border-collapse: collapse;">
                <tr>
                    <td style="width: 30%; vertical-align: middle;">
                        <img src="' . public_path('assets/img/logo/header-1.jpg') . '" alt="Logo" style="max-width: 100%; max-height: 50px; height: auto;">
                        <img src="' . public_path('assets/img/logo/header-2.jpg') . '" alt="Logo" style="max-width: 100%; max-height: 50px; height: auto;">
                    </td>
                    <td style="width: 45%; vertical-align: middle;">
                        <div style="font-size: 0.6em; color: blue; text-align: left; margin-right: 10px;">HUN-REN TERMÉSZETTUDOMÁNYI KUTATÓKÖZPONT</div>
                        <div style="font-size: 0.65em; color: #999; text-align: left; margin-right: 10px;">1117 BUDAPEST, MAGYAR TUDÓSOK KÖRÚTJA 2.</div>
                    </td>
                    <td style="width: 25%; vertical-align: middle;">
                        <div style="font-size: 0.6em; color: blue; text-align: left; margin-right: 10px;">LEVÉLCÍM: 1519 BUDAPEST, PF. 286.</div>
                        <div style="font-size: 0.65em; color: #999; text-align: left; margin-right: 10px;">www.ttk.hu</div>
                    </td>
                </tr>
            </table>
            
            <div style="border-bottom: 1px solid #cccccc; margin-top: 0; padding-bottom: 5px;"></div>
        </div>
        ';
        
        $mpdf->DefHTMLHeaderByName('ttkheader', $headerHtml);
        $mpdf->SetHTMLHeaderByName('ttkheader', true);
    }
    
    /**
     * Set standard footer with document info and page numbers
     * 
     * @param Mpdf $mpdf MPDF instance
     * @param string $title Document title
     * @param string $id Document ID
     * @param string $year Document year
     * @param string|null $date Current date (formatted)
     * @return void
     */
    public function setStandardFooter(Mpdf $mpdf, string $title, string $id, string $year, ?string $date = null): void
    {
        // Use current date if not provided
        if ($date === null) {
            $date = date('Y. m. d. H:i:s');
        }
        
        // Create footer text
        $footerText = $title . ' (' . $date . '). Ügy szám: ID ' . $id . '/' . $year;
        
        // Footer HTML
        $footerHtml = '
        <table width="100%" style="font-size: 9pt; border-top: 1px solid #cccccc; padding-top: 3mm;">
            <tr>
                <td width="75%" align="left">' . $footerText . '</td>
                <td width="25%" align="right">{PAGENO}/{nbpg}</td>
            </tr>
        </table>
        ';
        
        $mpdf->DefHTMLFooterByName('ttkfooter', $footerHtml);
        $mpdf->SetHTMLFooterByName('ttkfooter', true);
    }
    
    /**
     * Generate PDF document from a view
     * 
     * @param string $view Blade view name
     * @param array $data View data
     * @param array $pdfConfig PDF configuration
     * @param array $documentInfo Document metadata
     * @param bool $setStandardHeader Whether to set the standard TTK header
     * @param array $footerInfo Footer information (if null, footer will not be set)
     * @return Mpdf
     */
    public function generatePdf(
        string $view, 
        array $data = [], 
        array $pdfConfig = [],
        array $documentInfo = [],
        bool $setStandardHeader = false,
        ?array $footerInfo = null
    ): Mpdf {
        // Create MPDF instance with adjusted configuration
        $mpdf = $this->createMpdf($pdfConfig);
        
        // Set document metadata if provided
        if (isset($documentInfo['title'])) {
            $mpdf->SetTitle($documentInfo['title']);
        }
        
        if (isset($documentInfo['author'])) {
            $mpdf->SetAuthor($documentInfo['author']);
        }
        
        if (isset($documentInfo['creator'])) {
            $mpdf->SetCreator($documentInfo['creator']);
        }
        
        // Set standard header if requested
        if ($setStandardHeader) {
            $this->setTtkHeader($mpdf);
        }
        
        // Set standard footer if info provided
        if ($footerInfo !== null && 
            isset($footerInfo['title']) && 
            isset($footerInfo['id']) && 
            isset($footerInfo['year'])) {
            $this->setStandardFooter(
                $mpdf,
                $footerInfo['title'],
                $footerInfo['id'],
                $footerInfo['year'],
                $footerInfo['date'] ?? null
            );
        }
        
        // Get HTML content from view
        $html = view($view, $data)->render();
        
        // Write HTML to PDF
        $mpdf->WriteHTML($html);
        
        return $mpdf;
    }
    
    /**
     * Return PDF as download response
     *
     * @param Mpdf $mpdf MPDF instance
     * @param string $filename Filename for download
     * @return Response
     */
    public function downloadPdf(Mpdf $mpdf, string $filename): Response
    {
        return response($mpdf->Output('', Destination::INLINE))
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }
}