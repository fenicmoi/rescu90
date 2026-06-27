<?php
require_once 'auth.php';
requireRole([1]); // Only Super Admin

require_once 'db_config.php';

// DataTables request parameters
$draw = isset($_POST['draw']) ? intval($_POST['draw']) : 1;
$start = isset($_POST['start']) ? intval($_POST['start']) : 0;
$length = isset($_POST['length']) ? intval($_POST['length']) : 10;
$searchValue = isset($_POST['search']['value']) ? $_POST['search']['value'] : '';

// Ordering
$orderColumnIndex = isset($_POST['order'][0]['column']) ? intval($_POST['order'][0]['column']) : 0;
$orderDir = isset($_POST['order'][0]['dir']) && $_POST['order'][0]['dir'] === 'asc' ? 'ASC' : 'DESC';

// Define columns that correspond to DataTables columns index
$columns = [
    0 => 'ps.id',
    1 => 'ps.station_code',
    2 => 'ps.station_name',
    3 => 'd.name_th',
    4 => 'usage_count'
];

$orderBy = isset($columns[$orderColumnIndex]) ? $columns[$orderColumnIndex] : 'ps.district_id ASC, ps.id ASC';

// Base SQL
$sqlBase = "FROM police_stations ps
            LEFT JOIN districts d ON ps.district_id = d.id
            LEFT JOIN cctv_locations c ON ps.id = c.police_station_id";

// Where Clause for Search
$whereClause = "";
$params = [];
if (!empty($searchValue)) {
    $whereClause = "WHERE ps.station_code LIKE ? OR ps.station_name LIKE ? OR d.name_th LIKE ?";
    $searchWildcard = "%" . $searchValue . "%";
    $params = [$searchWildcard, $searchWildcard, $searchWildcard];
}

// Get Total Records (without search)
$stmtTotal = $pdo->query("SELECT COUNT(id) FROM police_stations");
$recordsTotal = $stmtTotal->fetchColumn();

// Get Filtered Records (with search)
if (!empty($whereClause)) {
    $stmtFiltered = $pdo->prepare("SELECT COUNT(DISTINCT ps.id) " . $sqlBase . " " . $whereClause);
    $stmtFiltered->execute($params);
    $recordsFiltered = $stmtFiltered->fetchColumn();
} else {
    $recordsFiltered = $recordsTotal;
}

// Get Data
$sqlData = "SELECT ps.*, d.name_th as district_name, COUNT(c.id) as usage_count " . $sqlBase . " " . $whereClause . " GROUP BY ps.id ORDER BY " . $orderBy;
// Fix ordering direction except if ordering by usage_count (which aliases)
if ($orderBy != 'ps.district_id ASC, ps.id ASC') {
    $sqlData .= " " . $orderDir;
}

if ($length != -1) {
    $sqlData .= " LIMIT " . intval($length) . " OFFSET " . intval($start);
}

$stmtData = $pdo->prepare($sqlData);
$stmtData->execute($params);
$data = $stmtData->fetchAll(PDO::FETCH_ASSOC);

// Format Data for DataTables
$formattedData = [];
foreach ($data as $row) {
    // Usage count
    $usageBadge = '<span class="px-2.5 py-1 inline-flex text-xs leading-5 font-semibold rounded-full ' . ($row['usage_count'] > 0 ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800') . '">' . $row['usage_count'] . ' ตัว</span>';
    
    // Actions
    $actionCol = '<div class="text-right text-sm font-medium">
                    <a href="police_station_form.php?id='.$row['id'].'" class="text-blue-600 hover:text-blue-900 mr-3">✏️ แก้ไข</a>';
    if ($row['usage_count'] == 0) {
        $actionCol .= '<button onclick="deleteStation('.$row['id'].', \''.htmlspecialchars($row['station_name']).'\')" class="text-red-600 hover:text-red-900">🗑️ ลบ</button>';
    } else {
        $actionCol .= '<span class="text-gray-300 cursor-not-allowed" title="ไม่สามารถลบได้เนื่องจากมีการใช้งานแล้ว">🗑️ ลบ</span>';
    }
    $actionCol .= '</div>';
    
    $formattedData[] = [
        '<span class="text-sm text-gray-500">' . $row['id'] . '</span>',
        '<span class="text-sm text-gray-900 font-mono">' . htmlspecialchars($row['station_code'] ?? '-') . '</span>',
        '<div class="text-sm font-bold text-gray-900">' . htmlspecialchars($row['station_name']) . '</div>',
        '<span class="text-sm text-gray-600">' . htmlspecialchars($row['district_name']) . '</span>',
        '<div class="text-center">' . $usageBadge . '</div>',
        $actionCol
    ];
}

// Return JSON
echo json_encode([
    "draw" => $draw,
    "recordsTotal" => intval($recordsTotal),
    "recordsFiltered" => intval($recordsFiltered),
    "data" => $formattedData
]);
