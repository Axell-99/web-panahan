<?php
include 'check_access.php';
requireAdmin();
// Check if panggil.php exists and includes proper database connection
if (!file_exists('panggil.php')) {
    die("Error: panggil.php file not found!");
}

include 'panggil.php';

// Check if database connection is properly established
// Assuming your panggil.php uses $conn or $connection variable
if (!isset($conn) && !isset($connection)) {
    die("Error: Database connection failed. Please check your panggil.php file.");
}

// Use the connection variable (adjust based on your panggil.php)
$db = isset($conn) ? $conn : $connection;

// Handle Excel Export
if (isset($_POST['export_excel'])) {
    $participants = json_decode($_POST['participants'], true);
    $categories = json_decode($_POST['categories'], true);
    $kegiatan = json_decode($_POST['kegiatan'], true);
    $selectedKegiatan = $_POST['selected_kegiatan'] ?? 'all';
    $selectedCategory = $_POST['selected_category'] ?? 'all';
    $isShuffled = $_POST['is_shuffled'] === '1';

    if (!empty($participants) && is_array($participants)) {
        // Create filename with timestamp
        $timestamp = date('Y-m-d_H-i-s');
        $filename = "Turnamen_Panahan_Bantalan_" . $timestamp . ".xlsx";

        // Set headers for Excel file download
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        // Excel XML content
        echo '<?xml version="1.0"?>
<?mso-application progid="Excel.Sheet"?>
<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"
 xmlns:o="urn:schemas-microsoft-com:office:office"
 xmlns:x="urn:schemas-microsoft-com:office:excel"
 xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"
 xmlns:html="http://www.w3.org/TR/REC-html40">
 <DocumentProperties xmlns="urn:schemas-microsoft-com:office:office">
  <Author>Turnamen Panahan System</Author>
  <Created>' . date('Y-m-d\TH:i:s\Z') . '</Created>
  <Company>Turnamen Panahan</Company>
  <Version>16.00</Version>
 </DocumentProperties>
 <ExcelWorkbook xmlns="urn:schemas-microsoft-com:office:excel">
  <WindowHeight>8640</WindowHeight>
  <WindowWidth>20250</WindowWidth>
  <WindowTopX>0</WindowTopX>
  <WindowTopY>0</WindowTopY>
  <ProtectStructure>False</ProtectStructure>
  <ProtectWindows>False</ProtectWindows>
 </ExcelWorkbook>
 <Styles>
  <Style ss:ID="Default" ss:Name="Normal">
   <Alignment ss:Vertical="Bottom"/>
   <Borders/>
   <Font ss:FontName="Calibri" x:Family="Swiss" ss:Size="11" ss:Color="#000000"/>
   <Interior/>
   <NumberFormat/>
   <Protection/>
  </Style>
  <Style ss:ID="Header">
   <Alignment ss:Horizontal="Center" ss:Vertical="Center"/>
   <Font ss:FontName="Calibri" x:Family="Swiss" ss:Size="12" ss:Color="#FFFFFF" ss:Bold="1"/>
   <Interior ss:Color="#4472C4" ss:Pattern="Solid"/>
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
   </Borders>
  </Style>
  <Style ss:ID="Data">
   <Alignment ss:Vertical="Center"/>
   <Font ss:FontName="Calibri" x:Family="Swiss" ss:Size="11"/>
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#D1D1D1"/>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#D1D1D1"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#D1D1D1"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#D1D1D1"/>
   </Borders>
  </Style>
  <Style ss:ID="BantalanNumber">
   <Alignment ss:Horizontal="Center" ss:Vertical="Center"/>
   <Font ss:FontName="Calibri" x:Family="Swiss" ss:Size="11" ss:Bold="1" ss:Color="#FFFFFF"/>
   <Interior ss:Color="#5B9BD5" ss:Pattern="Solid"/>
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
   </Borders>
  </Style>
  <Style ss:ID="BantalanLetter">
   <Alignment ss:Horizontal="Center" ss:Vertical="Center"/>
   <Font ss:FontName="Calibri" x:Family="Swiss" ss:Size="11" ss:Bold="1" ss:Color="#FFFFFF"/>
   <Interior ss:Color="#70AD47" ss:Pattern="Solid"/>
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
   </Borders>
  </Style>
  <Style ss:ID="Title">
   <Alignment ss:Horizontal="Center" ss:Vertical="Center"/>
   <Font ss:FontName="Calibri" x:Family="Swiss" ss:Size="16" ss:Bold="1"/>
  </Style>
  <Style ss:ID="Subtitle">
   <Alignment ss:Horizontal="Center" ss:Vertical="Center"/>
   <Font ss:FontName="Calibri" x:Family="Swiss" ss:Size="12"/>
  </Style>
 </Styles>
 <Worksheet ss:Name="Daftar Bantalan">
  <Table ss:ExpandedColumnCount="9" ss:ExpandedRowCount="' . (count($participants) + 10) . '" x:FullColumns="1" x:FullRows="1" ss:DefaultRowHeight="15">
   <Column ss:AutoFitWidth="0" ss:Width="40"/>
   <Column ss:AutoFitWidth="0" ss:Width="150"/>
   <Column ss:AutoFitWidth="0" ss:Width="120"/>
   <Column ss:AutoFitWidth="0" ss:Width="120"/>
   <Column ss:AutoFitWidth="0" ss:Width="100"/>
   <Column ss:AutoFitWidth="0" ss:Width="120"/>
   <Column ss:AutoFitWidth="0" ss:Width="120"/>
   <Column ss:AutoFitWidth="0" ss:Width="60"/>
   <Column ss:AutoFitWidth="0" ss:Width="60"/>
   
   <!-- Title -->
   <Row ss:Height="25">
    <Cell ss:MergeAcross="8" ss:StyleID="Title">
     <Data ss:Type="String">TURNAMEN PANAHAN - DAFTAR BANTALAN</Data>
    </Cell>
   </Row>
   
   <!-- Export Info -->
   <Row ss:Height="20">
    <Cell ss:MergeAcross="8" ss:StyleID="Subtitle">
     <Data ss:Type="String">Diekspor pada: ' . date('d/m/Y H:i:s') . '</Data>
    </Cell>
   </Row>';

        // Add filter info if any filters are applied
        if ($selectedKegiatan !== 'all' || $selectedCategory !== 'all') {
            echo '
   <Row ss:Height="20">
    <Cell ss:MergeAcross="8" ss:StyleID="Subtitle">
     <Data ss:Type="String">Filter: ';
            
            if ($selectedKegiatan !== 'all') {
                $kegiatanName = $kegiatan[$selectedKegiatan] ?? "Kegiatan $selectedKegiatan";
                echo "Kegiatan: $kegiatanName";
            }
            
            if ($selectedCategory !== 'all') {
                $categoryName = $categories[$selectedCategory] ?? "Kategori $selectedCategory";
                if ($selectedKegiatan !== 'all') {
                    echo " | ";
                }
                echo "Kategori: $categoryName";
            }
            
            echo '</Data>
    </Cell>
   </Row>';
        }

        if ($isShuffled) {
            echo '
   <Row ss:Height="20">
    <Cell ss:MergeAcross="8" ss:StyleID="Subtitle">
     <Data ss:Type="String">Catatan: Urutan peserta telah diacak</Data>
    </Cell>
   </Row>';
        }

        echo '
   
   <!-- Empty Row -->
   <Row ss:Height="15"/>
   
   <!-- Headers -->
   <Row ss:Height="20">
    <Cell ss:StyleID="Header"><Data ss:Type="String">No.</Data></Cell>
    <Cell ss:StyleID="Header"><Data ss:Type="String">Nama Peserta</Data></Cell>
    <Cell ss:StyleID="Header"><Data ss:Type="String">Nama Club</Data></Cell>
    <Cell ss:StyleID="Header"><Data ss:Type="String">Asal Kota</Data></Cell>
    <Cell ss:StyleID="Header"><Data ss:Type="String">Jenis Kelamin</Data></Cell>
    <Cell ss:StyleID="Header"><Data ss:Type="String">Kategori</Data></Cell>
    <Cell ss:StyleID="Header"><Data ss:Type="String">Kegiatan</Data></Cell>
    <Cell ss:StyleID="Header"><Data ss:Type="String">Bantalan No.</Data></Cell>
    <Cell ss:StyleID="Header"><Data ss:Type="String">Bantalan Huruf</Data></Cell>
   </Row>';

        // Add participant data
        foreach ($participants as $index => $participant) {
            $no = $index + 1;
            $nama = htmlspecialchars($participant['nama_peserta'] ?? '', ENT_XML1);
            $club = htmlspecialchars($participant['nama_club'] ?? '', ENT_XML1);
            $kota = htmlspecialchars($participant['asal_kota'] ?? '', ENT_XML1);
            $jenisKelamin = htmlspecialchars($participant['jenis_kelamin'] ?? '', ENT_XML1);
            $kategoriName = htmlspecialchars($categories[$participant['category_id']] ?? "Kategori {$participant['category_id']}", ENT_XML1);
            $kegiatanName = htmlspecialchars($kegiatan[$participant['kegiatan_id']] ?? "Kegiatan {$participant['kegiatan_id']}", ENT_XML1);
            $bantalanNo = $participant['randomNumber'];
            $bantalanHuruf = $participant['randomLetter'];
            
            echo '
   <Row>
    <Cell ss:StyleID="Data"><Data ss:Type="Number">' . $no . '</Data></Cell>
    <Cell ss:StyleID="Data"><Data ss:Type="String">' . $nama . '</Data></Cell>
    <Cell ss:StyleID="Data"><Data ss:Type="String">' . $club . '</Data></Cell>
    <Cell ss:StyleID="Data"><Data ss:Type="String">' . $kota . '</Data></Cell>
    <Cell ss:StyleID="Data"><Data ss:Type="String">' . $jenisKelamin . '</Data></Cell>
    <Cell ss:StyleID="Data"><Data ss:Type="String">' . $kategoriName . '</Data></Cell>
    <Cell ss:StyleID="Data"><Data ss:Type="String">' . $kegiatanName . '</Data></Cell>
    <Cell ss:StyleID="BantalanNumber"><Data ss:Type="Number">' . $bantalanNo . '</Data></Cell>
    <Cell ss:StyleID="BantalanLetter"><Data ss:Type="String">' . $bantalanHuruf . '</Data></Cell>
   </Row>';
        }

        echo '
  </Table>
 </Worksheet>
</Workbook>';
        exit;
    }
}

// Enhanced shuffle function - multiple shuffles for better randomization
function betterShuffle(&$array) {
    // Seed random number generator with current time
    mt_srand(microtime(true) * 1000000);
    
    // Shuffle multiple times for better randomization
    for ($i = 0; $i < 5; $i++) {
        shuffle($array);
        // Add small delay to change seed
        usleep(1000); // 1ms delay
        mt_srand(microtime(true) * 1000000);
    }
}

// Fetch data from database
$pesertaData = [];
$categoriesData = [];
$kegiatanData = [];

try {
    // Get peserta data
    $query = "SELECT * FROM peserta WHERE category_id IS NOT NULL AND kegiatan_id IS NOT NULL";
    $result = mysqli_query($db, $query);
    
    if (!$result) {
        throw new Exception("Error fetching peserta data: " . mysqli_error($db));
    }
    
    while ($row = mysqli_fetch_assoc($result)) {
        $pesertaData[] = $row;
    }

    // Get categories data
    $query = "SELECT * FROM categories";
    $result = mysqli_query($db, $query);
    
    if (!$result) {
        throw new Exception("Error fetching categories data: " . mysqli_error($db));
    }
    
    while ($row = mysqli_fetch_assoc($result)) {
        $categoriesData[$row['id']] = $row['nama_kategori'] ?? $row['name'] ?? "Kategori {$row['id']}";
    }

    // Get kegiatan data
    $query = "SELECT * FROM kegiatan";
    $result = mysqli_query($db, $query);
    
    if (!$result) {
        throw new Exception("Error fetching kegiatan data: " . mysqli_error($db));
    }
    
    while ($row = mysqli_fetch_assoc($result)) {
        $kegiatanData[$row['id']] = $row['nama_kegiatan'] ?? $row['name'] ?? "Kegiatan {$row['id']}";
    }
    
} catch(Exception $e) {
    die("Database Error: " . $e->getMessage());
}

// Filter logic
$selectedKegiatan = $_GET['kegiatan'] ?? 'all';
$selectedCategory = $_GET['kategori'] ?? 'all';
$isShuffled = isset($_GET['shuffle']) && $_GET['shuffle'] == '1';

// Filter participants
$filteredParticipants = $pesertaData;

if ($selectedKegiatan !== 'all') {
    $filteredParticipants = array_filter($filteredParticipants, function($p) use ($selectedKegiatan) {
        return $p['kegiatan_id'] == $selectedKegiatan;
    });
}

if ($selectedCategory !== 'all') {
    $filteredParticipants = array_filter($filteredParticipants, function($p) use ($selectedCategory) {
        return $p['category_id'] == $selectedCategory;
    });
}

// Reset array keys
$filteredParticipants = array_values($filteredParticipants);

// Enhanced shuffle if requested
if ($isShuffled) {
    betterShuffle($filteredParticipants);
}

// Assign bantalan (A, B, C pattern)
$letters = ['A', 'B', 'C'];
foreach ($filteredParticipants as $index => &$participant) {
    $participant['randomNumber'] = floor($index / 3) + 1;
    $participant['randomLetter'] = $letters[$index % 3];
}

// Get unique kegiatan and categories for filters
$availableKegiatan = array_unique(array_column($pesertaData, 'kegiatan_id'));
$availableCategories = array_unique(array_column(
    array_filter($pesertaData, function($p) use ($selectedKegiatan) {
        return $selectedKegiatan === 'all' || $p['kegiatan_id'] == $selectedKegiatan;
    }), 
    'category_id'
));
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Turnamen Panahan</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100">
    <div class="p-6">
        <div class="max-w-7xl mx-auto">
            <!-- Header -->
            <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center space-x-3">
                        <i class="fas fa-bullseye text-indigo-600 text-3xl"></i>
                        <h1 class="text-3xl font-bold text-gray-800">Turnamen Panahan</h1>
                    </div>
                    <div class="flex items-center space-x-4">
                        <!-- Export to Excel Button -->
                        <?php if (count($filteredParticipants) > 0): ?>
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="participants" value="<?php echo htmlspecialchars(json_encode($filteredParticipants)); ?>">
                            <input type="hidden" name="categories" value="<?php echo htmlspecialchars(json_encode($categoriesData)); ?>">
                            <input type="hidden" name="kegiatan" value="<?php echo htmlspecialchars(json_encode($kegiatanData)); ?>">
                            <input type="hidden" name="selected_kegiatan" value="<?php echo htmlspecialchars($selectedKegiatan); ?>">
                            <input type="hidden" name="selected_category" value="<?php echo htmlspecialchars($selectedCategory); ?>">
                            <input type="hidden" name="is_shuffled" value="<?php echo $isShuffled ? '1' : '0'; ?>">
                            <button type="submit" name="export_excel" class="bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded-lg transition-colors duration-200 flex items-center space-x-2 shadow-md hover:shadow-lg">
                                <i class="fas fa-file-excel"></i>
                                <span>Export Excel</span>
                            </button>
                        </form>
                        <?php endif; ?>
                        <!-- Back to Dashboard Button -->
                        <a href="dashboard.php" class="bg-gray-600 hover:bg-gray-700 text-white font-medium py-2 px-4 rounded-lg transition-colors duration-200 flex items-center space-x-2 shadow-md hover:shadow-lg">
                            <i class="fas fa-arrow-left"></i>
                            <span>Kembali ke Dashboard</span>
                        </a>
                        <!-- Participant Count -->
                        <div class="flex items-center space-x-2 text-sm text-gray-600">
                            <i class="fas fa-users"></i>
                            <span><?php echo count($filteredParticipants); ?> Peserta</span>
                        </div>
                    </div>
                </div>

                <!-- Filters -->
                <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Kegiatan</label>
                        <select name="kegiatan" onchange="this.form.submit()" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="all" <?php echo $selectedKegiatan === 'all' ? 'selected' : ''; ?>>Semua Kegiatan</option>
                            <?php foreach ($availableKegiatan as $kegiatanId): ?>
                                <option value="<?php echo $kegiatanId; ?>" <?php echo $selectedKegiatan == $kegiatanId ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($kegiatanData[$kegiatanId] ?? "Kegiatan $kegiatanId"); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Kategori</label>
                        <select name="kategori" onchange="this.form.submit()" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="all" <?php echo $selectedCategory === 'all' ? 'selected' : ''; ?>>Semua Kategori</option>
                            <?php foreach ($availableCategories as $categoryId): ?>
                                <option value="<?php echo $categoryId; ?>" <?php echo $selectedCategory == $categoryId ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($categoriesData[$categoryId] ?? "Kategori $categoryId"); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="flex items-end">
                        <button type="submit" name="shuffle" value="<?php echo $isShuffled ? '0' : '1'; ?>" 
                                class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2 px-4 rounded-lg transition-colors duration-200 flex items-center justify-center space-x-2">
                            <i class="fas fa-random"></i>
                            <span><?php echo $isShuffled ? 'Reset Urutan' : 'Acak Bantalan'; ?></span>
                        </button>
                    </div>

                    <div class="flex items-end">
                        <button type="button" onclick="window.location.href='<?php echo $_SERVER['PHP_SELF']; ?>'" 
                                class="w-full bg-gray-600 hover:bg-gray-700 text-white font-medium py-2 px-4 rounded-lg transition-colors duration-200 flex items-center justify-center space-x-2">
                            <i class="fas fa-sync-alt"></i>
                            <span>Refresh</span>
                        </button>
                    </div>

                    <!-- Hidden inputs to maintain other filters -->
                    <?php if ($selectedKegiatan !== 'all'): ?>
                        <input type="hidden" name="kegiatan" value="<?php echo htmlspecialchars($selectedKegiatan); ?>">
                    <?php endif; ?>
                    <?php if ($selectedCategory !== 'all'): ?>
                        <input type="hidden" name="kategori" value="<?php echo htmlspecialchars($selectedCategory); ?>">
                    <?php endif; ?>
                </form>
            </div>

            <!-- Table -->
            <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gradient-to-r from-indigo-600 to-blue-600 text-white">
                            <tr>
                                <th class="px-4 py-3 text-left text-sm font-semibold">No.</th>
                                <th class="px-4 py-3 text-left text-sm font-semibold">Nama Peserta</th>
                                <th class="px-4 py-3 text-left text-sm font-semibold">Nama Club</th>
                                <th class="px-4 py-3 text-left text-sm font-semibold">Asal Kota</th>
                                <th class="px-4 py-3 text-left text-sm font-semibold">Kategori</th>
                                <th class="px-4 py-3 text-left text-sm font-semibold">Kegiatan</th>
                                <th class="px-4 py-3 text-center text-sm font-semibold">Bantalan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php if (count($filteredParticipants) > 0): ?>
                                <?php foreach ($filteredParticipants as $index => $participant): ?>
                                    <tr class="<?php echo $index % 2 === 0 ? 'bg-white hover:bg-gray-50' : 'bg-gray-50 hover:bg-gray-100'; ?>">
                                        <td class="px-4 py-3 text-sm text-gray-900"><?php echo $index + 1; ?></td>
                                        <td class="px-4 py-3">
                                            <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($participant['nama_peserta'] ?? ''); ?></div>
                                            <div class="text-xs text-gray-500"><?php echo htmlspecialchars($participant['jenis_kelamin'] ?? ''); ?></div>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-900"><?php echo htmlspecialchars($participant['nama_club'] ?? ''); ?></td>
                                        <td class="px-4 py-3 text-sm text-gray-900"><?php echo htmlspecialchars($participant['asal_kota'] ?? ''); ?></td>
                                        <td class="px-4 py-3">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                <?php echo htmlspecialchars($categoriesData[$participant['category_id']] ?? "Kategori {$participant['category_id']}"); ?>
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-900">
                                            <?php echo htmlspecialchars($kegiatanData[$participant['kegiatan_id']] ?? "Kegiatan {$participant['kegiatan_id']}"); ?>
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="flex items-center justify-center space-x-1">
                                                <span class="bg-indigo-600 text-white text-sm font-bold px-3 py-2 rounded-lg shadow-md min-w-[40px] text-center">
                                                    <?php echo $participant['randomNumber']; ?>
                                                </span>
                                                <span class="bg-green-600 text-white text-sm font-bold px-3 py-2 rounded-lg shadow-md min-w-[40px] text-center">
                                                    <?php echo $participant['randomLetter']; ?>
                                                </span>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="px-4 py-8 text-center text-gray-500">
                                        <div class="flex flex-col items-center space-y-2">
                                            <i class="fas fa-users text-gray-300 text-5xl"></i>
                                            <span>Tidak ada peserta yang sesuai dengan filter</span>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Summary Card -->
            <?php if (count($filteredParticipants) > 0): ?>
                <div class="bg-white rounded-xl shadow-lg p-6 mt-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Ringkasan</h3>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div class="bg-blue-50 rounded-lg p-4">
                            <div class="text-2xl font-bold text-blue-600"><?php echo count($filteredParticipants); ?></div>
                            <div class="text-sm text-blue-800">Total Peserta</div>
                        </div>
                        <div class="bg-green-50 rounded-lg p-4">
                            <div class="text-2xl font-bold text-green-600">
                                <?php echo count(array_unique(array_column($filteredParticipants, 'category_id'))); ?>
                            </div>
                            <div class="text-sm text-green-800">Kategori Aktif</div>
                        </div>
                        <div class="bg-purple-50 rounded-lg p-4">
                            <div class="text-2xl font-bold text-purple-600">
                                <?php echo count(array_unique(array_column($filteredParticipants, 'asal_kota'))); ?>
                            </div>
                            <div class="text-sm text-purple-800">Kota Asal</div>
                        </div>
                        <div class="bg-orange-50 rounded-lg p-4">
                            <div class="text-2xl font-bold text-orange-600">
                                <?php echo ceil(count($filteredParticipants) / 3); ?>
                            </div>
                            <div class="text-sm text-orange-800">Total Bantalan</div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Instructions -->
            <div class="bg-white rounded-xl shadow-lg p-6 mt-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-3">Cara Penggunaan</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-600">
                    <div>
                        <h4 class="font-medium text-gray-800 mb-2">Sistem Bantalan:</h4>
                        <ul class="space-y-1">
                            <li>• Setiap bantalan terdiri dari 3 peserta (A, B, C)</li>
                            <li>• Bantalan 1: 1A, 1B, 1C</li>
                            <li>• Bantalan 2: 2A, 2B, 2C</li>
                            <li>• Dan seterusnya...</li>
                        </ul>
                    </div>
                    <div>
                        <h4 class="font-medium text-gray-800 mb-2">Fitur:</h4>
                        <ul class="space-y-1">
                            <li>• Filter berdasarkan kegiatan dan kategori</li>
                            <li>• Acak bantalan untuk pengundian (5x shuffle)</li>
                            <li>• Reset urutan ke posisi awal</li>
                            <li>• Export ke Excel dengan format lengkap</li>
                            <li>• Ringkasan statistik peserta</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>