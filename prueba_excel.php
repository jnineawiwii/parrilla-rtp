<?php
require __DIR__ . '/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Crear el libro
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Encabezados
$sheet->setCellValue('A1', 'Producto');
$sheet->setCellValue('B1', 'Precio');
$sheet->setCellValue('C1', 'Cantidad');

// Datos de ejemplo
$sheet->setCellValue('A2', 'Hamburguesa');
$sheet->setCellValue('B2', 85.50);
$sheet->setCellValue('C2', 10);

$sheet->setCellValue('A3', 'Tacos');
$sheet->setCellValue('B3', 45.00);
$sheet->setCellValue('C3', 20);

$sheet->setCellValue('A4', 'Refresco');
$sheet->setCellValue('B4', 25.00);
$sheet->setCellValue('C4', 15);

// Negritas en encabezados
$sheet->getStyle('A1:C1')->getFont()->setBold(true);

// Ajustar ancho de columnas automáticamente
foreach (range('A', 'C') as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

// Guardar el archivo
$writer = new Xlsx($spreadsheet);
$archivo = __DIR__ . '/reporte.xlsx';
$writer->save($archivo);

echo "✅ Excel generado correctamente en: " . $archivo;