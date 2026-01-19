<?php
require_once 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

// إنشاء جدول بيانات جديد
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// تعيين اسم الورقة
$sheet->setTitle('قالب استيراد المنتجات');

// إعداد العناوين
$headers = [
    'A1' => 'منتج',
    'B1' => 'السعر',
    'C1' => 'الكمية',
    'D1' => 'الباركود',
    'E1' => 'الفئة',
    'F1' => 'سعر التكلفة',
    'G1' => 'الصورة'
];

// إضافة العناوين
foreach ($headers as $cell => $header) {
    $sheet->setCellValue($cell, $header);
}

// إضافة بيانات عينة
$sampleData = [
    ['منتج إلكتروني - هاتف ذكي', 899.99, 50, '123456789012', 'إلكترونيات', 750.00, 'uploads/phone.jpg'],
    ['قميص قطني أبيض', 45.50, 100, '987654321098', 'ملابس', 25.00, 'uploads/shirt.jpg'],
    ['كتاب برمجة PHP', 85.00, 30, '456789123456', 'كتب', 60.00, 'uploads/book.jpg'],
    ['شامبو شعر', 15.75, 200, '789123456789', 'عناية شخصية', 10.00, 'uploads/shampoo.jpg'],
    ['سجادة غرفة معيشة', 299.99, 15, '321654987321', 'أثاث منزلي', 200.00, 'uploads/carpet.jpg'],
    ['قلم حبر أزرق', 3.50, 500, '654987321654', 'قرطاسية', 1.50, 'uploads/pen.jpg'],
    ['سماعات بلوتوث', 149.99, 25, '147258369147', 'إلكترونيات', 120.00, 'uploads/headphones.jpg'],
    ['حقيبة ظهر مدرسية', 89.99, 40, '963852741963', 'حقائب', 65.00, 'uploads/backpack.jpg'],
    ['قهوة عربية مطحونة', 12.99, 80, '852741963852', 'مواد غذائية', 8.50, 'uploads/coffee.jpg'],
    ['مصباح مكتبي LED', 34.99, 60, '741963852741', 'إضاءة', 22.00, 'uploads/lamp.jpg']
];

// إضافة البيانات العينة
$row = 2;
foreach ($sampleData as $data) {
    $sheet->setCellValue('A' . $row, $data[0]);
    $sheet->setCellValue('B' . $row, $data[1]);
    $sheet->setCellValue('C' . $row, $data[2]);
    $sheet->setCellValue('D' . $row, $data[3]);
    $sheet->setCellValue('E' . $row, $data[4]);
    $sheet->setCellValue('F' . $row, $data[5]);
    $sheet->setCellValue('G' . $row, $data[6]);
    $row++;
}

// تصميم العناوين
$headerStyle = [
    'font' => [
        'bold' => true,
        'color' => ['rgb' => 'FFFFFF'],
        'size' => 12,
    ],
    'fill' => [
        'fillType' => Fill::FILL_SOLID,
        'startColor' => ['rgb' => '4F46E5'], // لون أزرق داكن
    ],
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_CENTER,
        'vertical' => Alignment::VERTICAL_CENTER,
    ],
    'borders' => [
        'allBorders' => [
            'borderStyle' => Border::BORDER_THIN,
            'color' => ['rgb' => 'FFFFFF'],
        ],
    ],
];

$sheet->getStyle('A1:G1')->applyFromArray($headerStyle);

// تصميم البيانات
$dataStyle = [
    'borders' => [
        'allBorders' => [
            'borderStyle' => Border::BORDER_THIN,
            'color' => ['rgb' => 'E5E7EB'],
        ],
    ],
    'alignment' => [
        'vertical' => Alignment::VERTICAL_CENTER,
    ],
];

$sheet->getStyle('A2:G' . ($row - 1))->applyFromArray($dataStyle);

// تصميم خاص للأعمدة العددية
$numericStyle = [
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_RIGHT,
    ],
];

$sheet->getStyle('B2:C' . ($row - 1))->applyFromArray($numericStyle);
$sheet->getStyle('F2:F' . ($row - 1))->applyFromArray($numericStyle);

// تعيين عرض الأعمدة
$sheet->getColumnDimension('A')->setWidth(25); // Name
$sheet->getColumnDimension('B')->setWidth(12); // Price
$sheet->getColumnDimension('C')->setWidth(12); // Quantity
$sheet->getColumnDimension('D')->setWidth(15); // Barcode
$sheet->getColumnDimension('E')->setWidth(15); // Category
$sheet->getColumnDimension('F')->setWidth(12); // Cost Price
$sheet->getColumnDimension('G')->setWidth(20); // Image

// تعيين ارتفاع الصفوف
$sheet->getRowDimension(1)->setRowHeight(25);
for ($i = 2; $i < $row; $i++) {
    $sheet->getRowDimension($i)->setRowHeight(18);
}

// إنشاء الكاتب وحفظ الملف
$writer = new Xlsx($spreadsheet);

// إعداد headers للتحميل
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="قالب_استيراد_المنتجات.xlsx"');
header('Cache-Control: max-age=0');

// حفظ الملف إلى output
$writer->save('php://output');
?></content>
<parameter name="filePath">c:\xampp\htdocs\smart_shop\generate_excel_template.php