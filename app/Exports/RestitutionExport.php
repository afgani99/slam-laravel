<?php

namespace App\Exports;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class RestitutionExport
{
    protected array $data;
    protected string $period;
    protected int $year;
    protected int $month;

    public function __construct(array $data, int $year, int $month, string $period)
    {
        $this->data = $data;
        $this->year = $year;
        $this->month = $month;
        $this->period = $period;
    }

    public function download(string $filename)
    {
        $spreadsheet = new Spreadsheet();
        
        // Group by vendor
        $groupedByVendor = $this->groupByVendor($this->data);
        
        $sheetIndex = 0;
        foreach ($groupedByVendor as $vendorName => $items) {
            if ($sheetIndex === 0) {
                $sheet = $spreadsheet->getActiveSheet();
            } else {
                $sheet = $spreadsheet->createSheet();
            }
            
            // Sanitize sheet name (max 31 chars, no special chars)
            $sheetName = $this->sanitizeSheetName($vendorName);
            $sheet->setTitle($sheetName);
            
            $this->fillSheet($sheet, $vendorName, $items);
            $sheetIndex++;
        }
        
        // Set first sheet as active
        $spreadsheet->setActiveSheetIndex(0);
        
        $writer = new Xlsx($spreadsheet);
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        
        $writer->save('php://output');
        exit;
    }

    protected function groupByVendor(array $data): array
    {
        $grouped = [];
        foreach ($data as $item) {
            $vendor = $item['vendor_name'] ?: 'Unknown';
            if (!isset($grouped[$vendor])) {
                $grouped[$vendor] = [];
            }
            $grouped[$vendor][] = $item;
        }
        return $grouped;
    }

    protected function sanitizeSheetName(string $name): string
    {
        // Remove invalid characters
        $name = preg_replace('/[\/\\\?\*\[\]:]+/', '', $name);
        // Limit to 31 characters
        if (mb_strlen($name) > 31) {
            $name = mb_substr($name, 0, 31);
        }
        return $name ?: 'Sheet';
    }

    protected function fillSheet($sheet, string $vendorName, array $items)
    {
        $row = 1;
        
        // Title
        $sheet->setCellValue('A' . $row, 'LAPORAN RESTITUSI SLA');
        $sheet->mergeCells('A' . $row . ':I' . $row);
        $sheet->getStyle('A' . $row)->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $row++;
        
        // Vendor
        $sheet->setCellValue('A' . $row, 'Vendor: ' . $vendorName);
        $sheet->mergeCells('A' . $row . ':I' . $row);
        $sheet->getStyle('A' . $row)->getFont()->setBold(true)->setSize(12);
        $row++;
        
        // Period
        $sheet->setCellValue('A' . $row, 'Periode: ' . $this->period);
        $sheet->mergeCells('A' . $row . ':I' . $row);
        $sheet->getStyle('A' . $row)->getFont()->setItalic(true);
        $row++;
        
        $row++; // Empty row
        
        // Header
        $headers = ['No', 'CID', 'Pelanggan', 'Service', 'Target SLA', 'SLA Tercapai', 'Total Downtime', 'Total Pending', 'Downtime Efektif'];
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . $row, $header);
            $sheet->getStyle($col . $row)->getFont()->setBold(true);
            $sheet->getStyle($col . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFD3D3D3');
            $sheet->getStyle($col . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle($col . $row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            $col++;
        }
        $row++;
        
        // Data rows
        $no = 1;
        foreach ($items as $item) {
            $sheet->setCellValue('A' . $row, $no);
            $sheet->setCellValue('B' . $row, $item['cid']);
            $sheet->setCellValue('C' . $row, $item['customer_name']);
            $sheet->setCellValue('D' . $row, $item['service']);
            $sheet->setCellValue('E' . $row, number_format($item['sla_target'], 2) . '%');
            $sheet->setCellValue('F' . $row, number_format($item['sla_achieved'], 2) . '%');
            $sheet->setCellValue('G' . $row, number_format($item['total_downtime']) . ' menit');
            $sheet->setCellValue('H' . $row, number_format($item['total_pending']) . ' menit');
            $sheet->setCellValue('I' . $row, number_format($item['effective_downtime']) . ' menit');
            
            // Borders
            for ($c = 'A'; $c <= 'I'; $c++) {
                $sheet->getStyle($c . $row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            }
            
            // Alignment
            $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('E' . $row . ':I' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            
            $row++;
            $no++;
        }
        
        $row++; // Empty row
        
        // Detail tiket kendala section
        $sheet->setCellValue('A' . $row, 'RINCIAN TIKET KENDALA');
        $sheet->mergeCells('A' . $row . ':I' . $row);
        $sheet->getStyle('A' . $row)->getFont()->setBold(true)->setSize(11);
        $sheet->getStyle('A' . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFF4B084');
        $row++;
        
        foreach ($items as $item) {
            $sheet->setCellValue('A' . $row, 'CID: ' . $item['cid'] . ' - ' . $item['customer_name']);
            $sheet->mergeCells('A' . $row . ':I' . $row);
            $sheet->getStyle('A' . $row)->getFont()->setBold(true);
            $sheet->getStyle('A' . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFEEEEEE');
            $row++;
            
            // Get tickets for this CID
            $tickets = $this->getTicketsForCid($item['id']);
            
            if (!empty($tickets)) {
                // Ticket headers
                $ticketHeaders = ['No', 'Ticket #', 'Case Type', 'Mulai', 'Selesai', 'Durasi', 'Pending', 'Efektif', 'RFO'];
                $col = 'A';
                foreach ($ticketHeaders as $header) {
                    $sheet->setCellValue($col . $row, $header);
                    $sheet->getStyle($col . $row)->getFont()->setBold(true)->setSize(9);
                    $sheet->getStyle($col . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFE0E0E0');
                    $sheet->getStyle($col . $row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                    $col++;
                }
                $row++;
                
                $ticketNo = 1;
                foreach ($tickets as $ticket) {
                    $sheet->setCellValue('A' . $row, $ticketNo);
                    $sheet->setCellValue('B' . $row, $ticket['ticket_number']);
                    $sheet->setCellValue('C' . $row, $ticket['case_type']);
                    $sheet->setCellValue('D' . $row, $ticket['started_at']);
                    $sheet->setCellValue('E' . $row, $ticket['finished_at']);
                    $sheet->setCellValue('F' . $row, $ticket['duration'] . ' mnt');
                    $sheet->setCellValue('G' . $row, $ticket['pending'] . ' mnt');
                    $sheet->setCellValue('H' . $row, $ticket['effective'] . ' mnt');
                    $sheet->setCellValue('I' . $row, $ticket['rfo_action'] ?: '-');
                    
                    // Borders
                    for ($c = 'A'; $c <= 'I'; $c++) {
                        $sheet->getStyle($c . $row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                        $sheet->getStyle($c . $row)->getFont()->setSize(9);
                    }
                    
                    $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    
                    $row++;
                    $ticketNo++;
                }
                
                $row++; // Empty row after each CID's tickets
            } else {
                $sheet->setCellValue('A' . $row, 'Tidak ada data tiket');
                $sheet->mergeCells('A' . $row . ':I' . $row);
                $sheet->getStyle('A' . $row)->getFont()->setItalic(true);
                $row++;
                $row++;
            }
        }
        
        // Auto-size columns
        foreach (range('A', 'I') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
    }

    protected function getTicketsForCid(int $cidId): array
    {
        $tickets = \App\Models\Ticket::where('cid_id', $cidId)
            ->where('status', \App\Models\Ticket::STATUS_CLOSED)
            ->where('case_type', \App\Models\Ticket::CASE_LINK_DOWN)
            ->whereBetween('started_at', [
                now()->year($this->year)->month($this->month)->startOfMonth(),
                now()->year($this->year)->month($this->month)->endOfMonth()
            ])
            ->with('pendingIntervals')
            ->get();
        
        $result = [];
        foreach ($tickets as $ticket) {
            $duration = $ticket->started_at->diffInMinutes($ticket->finished_at);
            $pending = $ticket->pendingIntervals()
                ->whereNotNull('ended_at')
                ->get()
                ->sum(fn($interval) => $interval->started_at->diffInMinutes($interval->ended_at));
            $effective = max(0, $duration - $pending);
            
            $result[] = [
                'ticket_number' => $ticket->ticket_number,
                'case_type' => $ticket->case_type,
                'started_at' => $ticket->started_at->format('d/m/Y H:i'),
                'finished_at' => $ticket->finished_at->format('d/m/Y H:i'),
                'duration' => number_format($duration),
                'pending' => number_format($pending),
                'effective' => number_format($effective),
                'rfo_action' => $ticket->rfo_action,
            ];
        }
        
        return $result;
    }
}
