<?php

namespace Controllers;

use Model\Registro;
use Model\Usuario;
use Model\Evento;
use Model\Paquete; // Asegúrate de importar el modelo Paquete si no está incluido
use MVC\Router;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class DashboardController {

    public static function index(Router $router)
    {
        // Obtener últimos registros 
        $registros = Registro::get(5);
        foreach ($registros as $registro) {
            $registro->usuario = Usuario::find($registro->usuario_id);
            $registro->paquete = Paquete::find($registro->paquete_id); // Obtener el paquete asociado
        }

        // Calcular los ingresos
        $virtuales = Registro::total('paquete_id', 2);
        $presenciales = Registro::total('paquete_id', 1);
        $ingresos = ($virtuales * 8) + ($presenciales * 16);
        $ingresoensoles = $ingresos * 3.75;

        // Obtener eventos con más y menos lugares disponibles
        $menos_disponibles = Evento::ordenarLimite('disponibles', 'ASC', 5);
        $mas_disponibles = Evento::ordenarLimite('disponibles', 'DESC', 5);

        // Renderizar la vista del panel de administración
        $router->render('admin/dashboard/index', [
            'titulo' => 'Panel de Administración',
            'registros' => $registros,
            'ingresos' => $ingresos,
            'ingresoensoles' => $ingresoensoles,
            'menos_disponibles' => $menos_disponibles,
            'mas_disponibles' => $mas_disponibles
        ]);
    }

    public static function exportarExcel()
    {
        // Obtener los datos necesarios
        $registros = Registro::get(60);
        foreach ($registros as $registro) {
            $registro->usuario = Usuario::find($registro->usuario_id);
            $registro->paquete = Paquete::find($registro->paquete_id); // Obtener el paquete asociado
        }
    
        // Calcular los ingresos
        $virtuales = Registro::total('paquete_id', 2);
        $presenciales = Registro::total('paquete_id', 1);
        $ingresos = ($virtuales * 8) + ($presenciales * 16);
        $ingresoensoles = $ingresos * 3.75;
    
        // Obtener todos los eventos para más y menos lugares disponibles
        $todos_menos_disponibles = Evento::ordenarLimite('disponibles', 'ASC', 10);
        $todos_mas_disponibles = Evento::ordenarLimite('disponibles', 'DESC', 10);
        // Crear una instancia de Spreadsheet
        $spreadsheet = new Spreadsheet();
        
           // Estilo para celdas con fondo celeste
    $styleTitulo = [
        'fill' => [
            'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
            'startColor' => [
                'rgb' => '92CDDC',
            ],
        ],
        'font' => [
            'bold' => true,
        ],

        'alignment' => [
            'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            'wrapText' => true, // Ajuste de texto activado para todas las celdas de datos
            
        ],
        
    ];

    
        // Hoja de Usuarios Registrados
        $sheetUsuarios = $spreadsheet->createSheet();
        $sheetUsuarios->setTitle('Usuarios Registrados');
        $sheetUsuarios->setCellValue('B2', 'Usuarios Registrados');
        $sheetUsuarios->setCellValue('C2', 'Plan'); // Nueva columna para el Plan
        $sheetUsuarios->getStyle('B2:C2')->applyFromArray($styleTitulo); // Aplicar estilo a las celdas del título
        $sheetUsuarios->getColumnDimension('B')->setWidth(30); // Ajustar ancho de la columna B
        $sheetUsuarios->getColumnDimension('C')->setWidth(20); // Ajustar ancho de la columna C
        $row = 3; // Iniciar en la tercera fila
        foreach ($registros as $registro) {
            $sheetUsuarios->setCellValue('B' . $row, $registro->usuario->nombre . ' ' . $registro->usuario->apellido);
            $sheetUsuarios->setCellValue('C' . $row, $registro->paquete->nombre); // Mostrar el nombre del plan
            $row++;
        }
     // Aplicar bordes a la tabla de Usuarios Registrados
     $sheetUsuarios->getStyle('B2:C' . ($row - 1))->applyFromArray([
        'borders' => [
            'allBorders' => [
                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                'color' => ['rgb' => '000000'],
            ],
        ],
    ]);

        // Hoja de Ingresos
        $sheetIngresos = $spreadsheet->createSheet();
        $sheetIngresos->setTitle('Ingresos');
        $sheetIngresos->setCellValue('B2', 'Ingresos en Dólares');
        $sheetIngresos->setCellValue('C2', '$' . $ingresos);
        $sheetIngresos->setCellValue('B3', 'Ingresos en Soles');
        $sheetIngresos->setCellValue('C3', 'S/' . $ingresoensoles);
        $sheetIngresos->getStyle('B2:B3')->applyFromArray($styleTitulo); // Aplicar estilo a las celdas del título
        $sheetIngresos->getColumnDimension('B')->setWidth(15); // Ajustar ancho de la columna B
        $sheetIngresos->getColumnDimension('C')->setWidth(10); // Ajustar ancho de la columna C

          // Aplicar bordes a la tabla de Ingresos
    $sheetIngresos->getStyle('B2:C3')->applyFromArray([
        'borders' => [
            'allBorders' => [
                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                'color' => ['rgb' => '000000'],
            ],
        ],
    ]);

       // Hoja de Eventos con Menos Lugares Disponibles
$sheetMenosDisponibles = $spreadsheet->createSheet();
$sheetMenosDisponibles->setTitle('Eventos Menos Disponibles');
$sheetMenosDisponibles->setCellValue('B2', 'Eventos');
$sheetMenosDisponibles->setCellValue('C2', 'Lugares Disponibles');
$sheetMenosDisponibles->setCellValue('D2', 'Ganancia'); // Nueva columna
$sheetMenosDisponibles->getStyle('B2:D2')->applyFromArray($styleTitulo); // Aplicar estilo a las celdas del título
$sheetMenosDisponibles->getColumnDimension('B')->setWidth(30); // Ajustar ancho de la columna B
$sheetMenosDisponibles->getColumnDimension('C')->setWidth(20); // Ajustar ancho de la columna C
$sheetMenosDisponibles->getColumnDimension('D')->setWidth(12); // Ajustar ancho de la nueva columna D

$row = 3; // Iniciar en la tercera fila
foreach ($todos_menos_disponibles as $evento) {
    $sheetMenosDisponibles->setCellValue('B' . $row, $evento->nombre);
    $sheetMenosDisponibles->setCellValue('C' . $row, $evento->disponibles . ' Disponibles');

    // Calcular el nuevo valor
    $nuevoCalculo = (50 - $evento->disponibles) * 30;
    $sheetMenosDisponibles->setCellValue('D' . $row, '' . $nuevoCalculo); // Ajustar formato según sea necesario
    
    $sheetMenosDisponibles->getStyle('B' . $row . ':D' . $row)->applyFromArray([
        'alignment' => [
            'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            'wrapText' => true,
        ],
    ]);
    $row++;
}

// Aplicar bordes a la tabla de Eventos Menos Disponibles
$sheetMenosDisponibles->getStyle('B2:D' . ($row - 1))->applyFromArray([
    'borders' => [
        'allBorders' => [
            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
            'color' => ['rgb' => '000000'],
        ],
    ],
]);
        // Aplicar bordes a la tabla de Eventos Menos Disponibles
    $sheetMenosDisponibles->getStyle('B2:C' . ($row - 1))->applyFromArray([
        'borders' => [
            'allBorders' => [
                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                'color' => ['rgb' => '000000'],
            ],
        ],
    ]);
    
        // Hoja de Eventos con Más Lugares Disponibles
        $sheetMasDisponibles = $spreadsheet->createSheet();
        $sheetMasDisponibles->setTitle('Eventos Más Disponibles');
        $row = 3; // Iniciar en la tercera fila
        foreach ($todos_mas_disponibles as $evento) {
            $sheetMasDisponibles->setCellValue('B2', 'Eventos');
            $sheetMasDisponibles->setCellValue('B' . $row, $evento->nombre);
            $sheetMasDisponibles->setCellValue('C2', 'Lugares Disponibles');
            $sheetMasDisponibles->setCellValue('C' . $row, $evento->disponibles . ' Disponibles');
            $sheetMasDisponibles->getStyle('B2:C2')->applyFromArray($styleTitulo); // Aplicar estilo a la celda del título
            $sheetMasDisponibles->getColumnDimension('B')->setWidth(30); // Ajustar ancho de la columna B
            $sheetMasDisponibles->getColumnDimension('C')->setWidth(20); // Ajustar ancho de la columna C
            $row++;
        }

        // Aplicar bordes a la tabla de Eventos Más Disponibles
    $sheetMasDisponibles->getStyle('B2:C' . ($row - 1))->applyFromArray([
        'borders' => [
            'allBorders' => [
                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                'color' => ['rgb' => '000000'],
            ],
        ],
    ]);
    
        // Configurar el Writer para Xlsx (Excel 2007 y posteriores)
        $writer = new Xlsx($spreadsheet);
    
        // Configurar cabeceras para la descarga del archivo
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="indicadores_dashboard.xlsx"');
        header('Cache-Control: max-age=0');
    
        // Guardar el archivo Excel en la salida (output)
        $writer->save('php://output');
        exit;
    }
}
