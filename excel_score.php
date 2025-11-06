<?php
require 'vendor/vendor/autoload.php';
include 'panggil.php'; 

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;

$category = mysqli_query($conn, "SELECT * FROM `categories` WHERE id = ".$_GET['category_id']);
$category_fetch = mysqli_fetch_assoc($category);

$kegiatan = mysqli_query($conn, "SELECT * FROM `kegiatan` WHERE id = ".$_GET['kegiatan_id']);
$kegiatan_fetch = mysqli_fetch_assoc($kegiatan);

$scoreboard = mysqli_query($conn, "SELECT * FROM `score_boards` WHERE id = ".$_GET['scoreboard']);
$scoreboard_fetch = mysqli_fetch_assoc($scoreboard);

$peserta_query_value = mysqli_query($conn, "
    SELECT 
        p.id AS peserta_id,
        p.nama_peserta,
        p.jenis_kelamin,
        p.kegiatan_id,
        p.category_id,
        COALESCE(SUM(
            CASE 
                WHEN s.score = 'm' THEN 0
                WHEN s.score = 'x' THEN 10
                ELSE CAST(s.score AS UNSIGNED)
            END
        ), 0) AS total_score,
        COALESCE(SUM(CASE WHEN s.score = 'x' THEN 1 ELSE 0 END), 0) AS jumlah_x
    FROM peserta p
    LEFT JOIN score s 
        ON p.id = s.peserta_id 
        WHERE s.kegiatan_id = ".$_GET['kegiatan_id']."
        AND s.category_id = ".$_GET['category_id']."
        AND s.score_board_id = ".$_GET['scoreboard']."
    GROUP BY p.id, p.nama_peserta
    ORDER BY total_score DESC, jumlah_x DESC;
");

$peserta = [];

while($b = mysqli_fetch_array($peserta_query_value)) {
    $peserta[] = $b;
}

$total_score_peserta = [];

// // siapkan data (array of arrays)
// $rows = [
//     ['Nama','Umur','Kota'],
//     ['Budi', 30, 'Jakarta'],
//     ['Siti', 27, 'Bandung'],
// ];

// buat spreadsheet
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Training');



$row = 1; // posisi baris awal di Excel
$no_rank = 1;
$total_score_peserta_index = 0;

$sheet->getStyle('A1')->getFont()->setSize(16)->setBold(true);
$sheet->setCellValue("A{$row}", $category_fetch['name']);
$row++;
$sheet->setCellValue("A{$row}", $kegiatan_fetch['nama_kegiatan']);
$row++;
$row++;
foreach ($peserta as $p) {
    // Judul rank
    $total_score_peserta[] = ['nama' => $p['nama_peserta']];

    $sheet->getStyle("A{$row}")->getFont()->setSize(14)->setBold(true);
    $sheet->setCellValue("A{$row}", "Rank#{$no_rank} {$p['nama_peserta']}");
    $row++;

    // Header tabel
    $col = 'A';
    $sheet->setCellValue($col.$row, 'Rambahan'); $col++;
    for ($a = 1; $a <= $scoreboard_fetch['jumlah_anak_panah']; $a++) {
        $sheet->setCellValue($col.$row, "Shot $a");
        $col++;
    }
    // 
    $sheet->setCellValue($col.$row, 'Total'); $col++;
    $sheet->setCellValue($col.$row, 'End');
    $sheet->getStyle('A'.$row.':'.$col.$row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle('A'.$row.':'.$col.$row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
      ->getStartColor()->setARGB('CCCACA'); // HEX: kuning
    $sheet->getStyle('A'.$row.':'.$col.$row)->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN)->getColor()->setARGB(Color::COLOR_BLACK);
    
    $row++;
    // $sheet->getStyle('A1')->getFont()->setSize(16)->setBold(true);

    // Data tiap sesi
    $end_value_total = [];
    for ($s = 1; $s <= $scoreboard_fetch['jumlah_sesi']; $s++) {
        $col = 'A';
        $sheet->setCellValue($col.$row, $s); 
        $col++;

        $total_score = 0;

        for ($a = 1; $a <= $scoreboard_fetch['jumlah_anak_panah']; $a++) {
            $score_query = mysqli_query($conn, "SELECT * FROM score 
                WHERE category_id=".$_GET['category_id']."
                AND kegiatan_id =".$_GET['kegiatan_id']."
                AND score_board_id=".$_GET['scoreboard']."
                AND peserta_id=".$p['peserta_id']."
                AND session=".$s."
                AND arrow=".$a);
            
            $score_fetch = mysqli_fetch_assoc($score_query);
            $score_value = 0;
            if ($score_fetch) {
                if ($score_fetch['score'] == "x") {
                    $score_value = 10;
                } elseif ($score_fetch['score'] == "m") {
                    $score_value = 0;
                } else {
                    $score_value = $score_fetch['score'] ?? 0;
                }
            }
            $total_score += (int)$score_value;

            // tulis nilai score (aslinya X/M/angka)
            $sheet->setCellValue($col.$row, $score_fetch['score'] ?? "m");
            $col++;
        }

        // tulis total
        $sheet->setCellValue($col.$row, $total_score);
        $col++;

        // hitung end value
        $total_score_peserta[$total_score_peserta_index] += ['rambahan_'.$s => $total_score];
        $end_value = 0;
        if (empty($end_value_total)) {
            $end_value = $total_score;
            $end_value_total[] = $total_score;
        } else {
            $end_value = array_sum($end_value_total) + $total_score;
            $end_value_total[] = $total_score;
        }
        $sheet->setCellValue($col.$row, $end_value);
        $sheet->getStyle('A'.$row.':'.$col.$row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A'.$row.':'.$col.$row)->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN)->getColor()->setARGB(Color::COLOR_BLACK);

        $row++;
    }

    $row += 2; // kasih spasi antar peserta
    $no_rank++;
    $total_score_peserta_index++;
}


// Buat worksheet kedua
$spreadsheet->createSheet();
$spreadsheet->setActiveSheetIndex(1);
$sheet2 = $spreadsheet->getActiveSheet();
$sheet2->setTitle('Rekap Total');

// Header
$row = 1;
$col = 'A';
$sheet2->getStyle("A{$row}")->getFont()->setSize(16)->setBold(true);
$sheet2->setCellValue("A{$row}", $category_fetch['name']);
$row++;
$sheet2->setCellValue("A{$row}", $kegiatan_fetch['nama_kegiatan']);
$row++;
$row++;

$sheet2->setCellValue($col.$row, 'No'); $col++;
$sheet2->setCellValue($col.$row, 'Nama'); $col++;

// Rambahan headers
for ($a = 1; $a <= $scoreboard_fetch['jumlah_sesi']; $a++) {
    $sheet2->setCellValue($col.$row, "Rambahan $a");
    $col++;
}

// Kolom total
$sheet2->setCellValue($col.$row, 'Total');
$row++;

// Data peserta
foreach ($total_score_peserta as $i_tsp => $tsp) {
    $col = 'A';
    $sheet2->getStyle($col.$row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
    $sheet2->setCellValue($col.$row, $i_tsp + 1); $col++;
    $sheet2->setCellValue($col.$row, $tsp['nama']); $col++;

    $total_tsp = 0;
    for ($a = 1; $a <= $scoreboard_fetch['jumlah_sesi']; $a++) {
        $nilai = $tsp['rambahan_'.$a] ?? 0;
        $sheet2->getStyle($col.$row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet2->setCellValue($col.$row, $nilai);
        $total_tsp += $nilai;
        $col++;
    }

    // tulis total
    $sheet2->getStyle($col.$row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
    $sheet2->setCellValue($col.$row, $total_tsp);
    $row++;
}

// Contoh styling: header bold + background abu
$lastCol = chr(ord('A') + $scoreboard_fetch['jumlah_sesi'] + 2);
$headerRange = "A4:{$lastCol}4";

$sheet2->getStyle($headerRange)->getFont()->setBold(true);
$sheet2->getStyle($headerRange)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
$sheet2->getStyle($headerRange)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
       ->getStartColor()->setARGB('DDDDDD');

// Border seluruh tabel
$tableRange = "A4:{$lastCol}".($row-1);
$sheet2->getStyle($tableRange)->getBorders()->getAllBorders()
       ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);






// header untuk download
$filename = "export_score_board_" . date('Ymd_His') . ".xlsx";

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header("Content-Disposition: attachment;filename={$filename}");
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
