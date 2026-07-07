<?php

namespace App\Exports;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class CidExport
{
    protected $cids;

    public function __construct($cids)
    {
        $this->cids = $cids;
    }

    public function download(string $filename)
    {
        $spreadsheet = new Spreadsheet();
        
        $groupedByVendor = $this->groupByVendor($this->cids);
        
        $sheetIndex = 0;
        foreach ($groupedByVendor as $vendorName => $items) {
            $sheet = ($sheetIndex === 0) ? $spreadsheet->getActiveSheet() : $spreadsheet->createSheet();
            $sheetName = $this->sanitizeSheetName($vendorName);
            $sheet->setTitle($sheetName);
            
            $this->fillSheet($sheet, $vendorName, $items);
            $sheetIndex++;
        }
        
        $spreadsheet->setActiveSheetIndex(0);
        $writer = new Xlsx($spreadsheet);
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        
        $writer->save('php://output');
        exit;
    }

    protected function groupByVendor($cids): array
    {
        $grouped = [];
        foreach ($cids as $cid) {
            $vendor = $cid->vendor_name ?: 'Unknown';
            if (!isset($grouped[$vendor])) $grouped[$vendor] = [];
            $grouped[$vendor][] = $cid;
        }
        return $grouped;
    }

    protected function sanitizeSheetName(string $name): string
    {
        $name = preg_replace('/[\/\\\?\*\[\]:]+/', '', $name);
        return mb_substr($name, 0, 31) ?: 'Sheet';
    }

    protected function fillSheet($sheet, string $vendorName, array $items)
    {
        $sheet->setCellValue('A1', 'DAFTAR MASTER CID - ' . strtoupper($vendorName));
        $sheet->mergeCells('A1:G1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        
        $period = date('F Y'); // e.g., July 2026
        $sheet->setCellValue('A2', 'Periode Export: ' . $period);
        $sheet->mergeCells('A2:G2');
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A2')->getFont()->setItalic(true);
        
        $headers = ['No', 'CID', 'CID IS', 'Pelanggan', 'Service', 'SLA Target', 'Status'];
        $col = 'A';
        foreach ($headers as $h) {
            $sheet->setCellValue($col . '3', $h);
            $sheet->getStyle($col . '3')->getFont()->setBold(true);
            $sheet->getStyle($col . '3')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFD3D3D3');
            $sheet->getStyle($col . '3')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            $col++;
        }
        
        $row = 4;
        $no = 1;
        foreach ($items as $cid) {
            $isDismantled = (bool) $cid->is_dismantled;
            
            $sheet->setCellValue('A' . $row, $no++);
            $sheet->setCellValue('B' . $row, $cid->cid);
            $sheet->setCellValue('C' . $row, $cid->cid_is);
            $sheet->setCellValue('D' . $row, $cid->customer_name);
            $sheet->setCellValue('E' . $row, $cid->service);
            $sheet->setCellValue('F' . $row, $cid->sla_percentage . '%');
            $sheet->setCellValue('G' . $row, $isDismantled ? __('cids.status_dismantled') : __('cids.status_active'));
            
            for ($c = 'A'; $c <= 'G'; $c++) {
                $style = $sheet->getStyle($c . $row);
                $style->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                
                if ($isDismantled) {
                    $style->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFFFCCCC'); // Light Red
                }
            }
            $row++;
        }
        
        foreach (range('A', 'G') as $col) $sheet->getColumnDimension($col)->setAutoSize(true);
    }
}
