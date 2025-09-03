<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class GenericReportExport implements
    FromArray,
    WithHeadings,
    ShouldAutoSize,
    WithColumnFormatting,
    WithStyles,
    WithEvents,
    WithCustomStartCell,
    WithTitle,
    WithDrawings
{
    protected string $title;
    protected array $headings;
    protected array $rows;
    protected array $formats;

    public function __construct(string $title, array $headings, array $rows, array $formats = [])
    {
        $this->title = $title;
        $this->headings = $headings;
        $this->rows = $rows;
        $this->formats = $formats;
    }

    public function title(): string
    {
        return 'Reporte';
    }

    public function array(): array
    {
        return $this->rows;
    }

    public function headings(): array
    {
        return $this->headings;
    }

    public function startCell(): string
    {
        return 'A5';
    }

    public function columnFormats(): array
    {
        return $this->formats + [];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            4 => ['font' => ['bold' => true]],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                $cols = count($this->headings);
                $lastCol = Coordinate::stringFromColumnIndex($cols);
                $sheet->getRowDimension(1)->setRowHeight(26);
                $sheet->getRowDimension(2)->setRowHeight(26);
                $sheet->getRowDimension(3)->setRowHeight(26);
                $sheet->mergeCells("A4:{$lastCol}4");
                $sheet->setCellValue('A4', $this->title);
                $sheet->getStyle('A4')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => '0B3A5B']],
                    'alignment' => ['horizontal' => 'left', 'vertical' => 'center'],
                ]);
                $headerRange = "A5:{$lastCol}5";
                $sheet->getStyle($headerRange)->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '0B3A5B']],
                    'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
                    'borders' => ['allBorders' => ['borderStyle' => 'thin', 'color' => ['rgb' => '0B3A5B']]],
                ]);
                $sheet->setAutoFilter($headerRange);
                $sheet->freezePane('A6');
                $rowCount = count($this->rows);
                if ($rowCount > 0) {
                    $dataStart = 6;
                    $dataEnd = $dataStart + $rowCount - 1;

                    for ($r = $dataStart; $r <= $dataEnd; $r++) {
                        if ($r % 2 === 0) {
                            $sheet->getStyle("A{$r}:{$lastCol}{$r}")->applyFromArray([
                                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'F8FAFC']],
                            ]);
                        }
                    }
                    $sheet->getStyle("A{$dataStart}:{$lastCol}{$dataEnd}")->applyFromArray([
                        'borders' => ['allBorders' => ['borderStyle' => 'hair', 'color' => ['rgb' => 'DDE3EA']]],
                    ]);
                }
            },
        ];
    }
    public function drawings()
    {
        $path = public_path('/images/logo2.png');
        if (!file_exists($path)) {
            return [];
        }
        $drawing = new Drawing();
        $drawing->setName('CHN');
        $drawing->setDescription('CHN');
        $drawing->setPath($path);
        $drawing->setHeight(100);
        $drawing->setCoordinates('A1');
        $drawing->setOffsetX(0);
        $drawing->setOffsetY(0);
        return [$drawing];
    }
}
