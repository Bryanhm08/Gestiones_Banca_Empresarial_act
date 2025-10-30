<?php

namespace App\Exports;

use App\Models\Liberacion;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Carbon\Carbon;

class LiberacionExport implements FromArray, WithDrawings, WithEvents, WithTitle
{
    protected Liberacion $lib;
    protected string $usuario;

    // Fila en la que comenzará la cabecera de la tabla
    protected int $tableHeaderRow = 7;

    public function __construct(Liberacion $lib, ?string $usuario = null)
    {
        $this->lib     = $lib->fresh(); // por si cambio en memoria
        $this->usuario = $usuario ?? (auth()->user()->name ?? '—');
    }

    public function title(): string
    {
        return 'Liberación ' . $this->lib->id;
    }

    /**
     * Estructura de datos para la tabla:
     * - Filas 1-6: encabezado visual (logo/títulos/metadata)
     * - Fila 7: headings de la tabla
     * - Filas 8..N: datos
     */
    public function array(): array
    {
        $cols = $this->lib->columns ?? [];
        $headings = array_map(fn($c) => $c['label'], $cols);
        $headings[] = 'Estatus';

        $rows = [];
        foreach (($this->lib->rows ?? []) as $row) {
            $line = [];
            foreach ($cols as $c) {
                $line[] = $row['values'][$c['id']] ?? '';
            }
            $line[] = ($row['status'] ?? 'pendiente');
            $rows[] = $line;
        }

        // Construimos la “matriz” completa, dejando las primeras filas para header
        $matrix = [];

        // (1) Filas de cabecera visual
        // F1: se usa para logo y merge del título
        $matrix[0][0] = null; // reservado al logo
        // F2: TÍTULO (columna A mergeada hasta última)
        $matrix[1][0] = 'REPORTE DE LIBERACIONES';
        // F3: Subtítulo (fecha/hora y usuario)
        $matrix[2][0] = 'Generado: ' . Carbon::now()->format('d/m/Y H:i') . '  ·  Usuario: ' . $this->usuario;
        // F4: Info de la liberación
        $nombre = $this->lib->nombre ?: ('Liberación #' . $this->lib->id);
        $matrix[3][0] = 'Cuadro: ' . $nombre;
        // F5: En blanco (espacio)
        $matrix[4][0] = '';
        // F6: En blanco (espacio)
        $matrix[5][0] = '';

        // (2) Fila de headings (F7)
        $matrix[$this->tableHeaderRow - 1] = $headings;

        // (3) Datos desde F8
        foreach ($rows as $r) {
            $matrix[] = $r;
        }

        return $matrix;
    }

    /**
     * Inserta el logo en la esquina superior izquierda.
     * Coloca tu logo en public/img/logo-chn.png (o cambia la ruta aquí).
     */
    public function drawings()
    {
        $logoPath = public_path('img/logo-chn.png'); // <-- ajusta si tu logo está en otra ruta

        if (!is_file($logoPath)) {
            return []; // si no hay logo, no pasa nada
        }

        $d = new Drawing();
        $d->setName('Logo CHN');
        $d->setDescription('Logo CHN');
        $d->setPath($logoPath);
        $d->setHeight(42);       // alto en px aproximado
        $d->setCoordinates('A1'); // posición
        $d->setOffsetX(0);
        $d->setOffsetY(2);

        return [$d];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // ==== Determinar tabla ====
                $colsCount = count($this->lib->columns ?? []) + 1; // + Estatus
                if ($colsCount < 1) $colsCount = 1;

                $lastCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colsCount);
                $lastRow = max($this->tableHeaderRow, $this->tableHeaderRow + count($this->lib->rows ?? []));
                $tableRange = "A{$this->tableHeaderRow}:{$lastCol}{$lastRow}";
                $headersRange = "A{$this->tableHeaderRow}:{$lastCol}{$this->tableHeaderRow}";

                // ==== Page setup & márgenes ====
                $pageSetup = $sheet->getPageSetup();
                $pageSetup->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);
                $pageSetup->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);
                $pageSetup->setFitToWidth(1)->setFitToHeight(0);

                $margins = $sheet->getPageMargins();
                $margins->setTop(0.4)->setRight(0.3)->setLeft(0.3)->setBottom(0.6);

                // Encabezado/ pie de página
                $headerFooter = $sheet->getHeaderFooter();
                $headerFooter->setOddFooter('&LCHN · SGBE&R&Página &P de &N');

                // Repetir fila de encabezados al imprimir
                $sheet->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd($this->tableHeaderRow, $this->tableHeaderRow);

                // ==== Merge & estilos de encabezado (Título / Subtítulo / Info) ====
                // Merge título/subtítulo/info hasta la última columna
                $sheet->mergeCells("A2:{$lastCol}2");
                $sheet->mergeCells("A3:{$lastCol}3");
                $sheet->mergeCells("A4:{$lastCol}4");

                // Estilo título
                $sheet->getStyle("A2")->applyFromArray([
                    'font' => ['bold' => true, 'size' => 16],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                // Subtítulo
                $sheet->getStyle("A3")->applyFromArray([
                    'font' => ['size' => 10, 'color' => ['rgb' => '666666']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                // Info
                $sheet->getStyle("A4")->applyFromArray([
                    'font' => ['size' => 11],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                // ==== Encabezados de tabla ====
                $sheet->getStyle($headersRange)->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '1F4E79'], // azul corporativo
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical'   => Alignment::VERTICAL_CENTER,
                        'wrapText'   => true,
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => 'D9D9D9'],
                        ],
                    ],
                ]);
                // Altura de fila para headers
                $sheet->getRowDimension($this->tableHeaderRow)->setRowHeight(22);

                // ==== Datos: bordes y alineaciones ====
                if ($lastRow >= $this->tableHeaderRow + 1) {
                    $dataRange = "A" . ($this->tableHeaderRow + 1) . ":{$lastCol}{$lastRow}";
                    $sheet->getStyle($dataRange)->applyFromArray([
                        'alignment' => [
                            'horizontal' => Alignment::HORIZONTAL_LEFT,
                            'vertical'   => Alignment::VERTICAL_CENTER,
                            'wrapText'   => true,
                        ],
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                                'color' => ['rgb' => 'E6E6E6'],
                            ],
                        ],
                    ]);
                }

                // ==== Ancho de columnas: autofit básico ====
                for ($i = 1; $i <= $colsCount; $i++) {
                    $column = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i);
                    $sheet->getColumnDimension($column)->setAutoSize(true);
                }

                // ==== Autofiltro ====
                $sheet->setAutoFilter($tableRange);

                // ==== Colorear la columna de Estatus (última) según valor ====
                $statusColLetter = $lastCol;
                for ($row = $this->tableHeaderRow + 1; $row <= $lastRow; $row++) {
                    $cell = $statusColLetter . $row;
                    $val  = strtoupper((string)$sheet->getCell($cell)->getValue());
                    if (str_contains($val, 'LIBERADO')) {
                        $sheet->getStyle($cell)->getFill()->setFillType(Fill::FILL_SOLID)
                              ->getStartColor()->setRGB('D9F2E6'); // verde suave
                    } else {
                        // pendiente
                        $sheet->getStyle($cell)->getFill()->setFillType(Fill::FILL_SOLID)
                              ->getStartColor()->setRGB('FFF2CC'); // amarillo suave
                    }
                    $sheet->getStyle($cell)->getFont()->setBold(true);
                    $sheet->getStyle($cell)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                }

                // Margen superior para que el logo no colisione
                $sheet->getRowDimension(1)->setRowHeight(30);
            },
        ];
    }
}
