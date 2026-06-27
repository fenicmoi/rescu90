<?php
require_once 'auth.php';
requireRole([1, 2, 3, 4]); // Admins and Officers

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
    0 => 'c.id',
    1 => 'c.station_id',
    2 => 'c.affiliation',
    3 => 't.type_name',
    4 => 'c.location_name'
];

$orderBy = isset($columns[$orderColumnIndex]) ? $columns[$orderColumnIndex] : 'c.id';

// Base SQL
$sqlBase = "FROM cctv_locations c LEFT JOIN police_stations p ON c.police_station_id = p.id LEFT JOIN camera_types t ON c.camera_type_id = t.id";

// Where Clause for Search
$whereClause = "";
$params = [];
if (!empty($searchValue)) {
    $whereClause = "WHERE c.station_id LIKE ? OR c.affiliation LIKE ? OR p.station_name LIKE ? OR t.type_name LIKE ? OR c.location_name LIKE ?";
    $searchWildcard = "%" . $searchValue . "%";
    $params = [$searchWildcard, $searchWildcard, $searchWildcard, $searchWildcard, $searchWildcard];
}

// Get Total Records (without search)
$stmtTotal = $pdo->query("SELECT COUNT(c.id) FROM cctv_locations c");
$recordsTotal = $stmtTotal->fetchColumn();

// Get Filtered Records (with search)
if (!empty($whereClause)) {
    $stmtFiltered = $pdo->prepare("SELECT COUNT(c.id) " . $sqlBase . " " . $whereClause);
    $stmtFiltered->execute($params);
    $recordsFiltered = $stmtFiltered->fetchColumn();
} else {
    $recordsFiltered = $recordsTotal;
}

// Get Data
$sqlData = "SELECT c.*, p.station_name as police_station_name, t.type_name as camera_type " . $sqlBase . " " . $whereClause . " ORDER BY " . $orderBy . " " . $orderDir;

if ($length != -1) {
    $sqlData .= " LIMIT " . intval($length) . " OFFSET " . intval($start);
}

$stmtData = $pdo->prepare($sqlData);
$stmtData->execute($params);
$data = $stmtData->fetchAll(PDO::FETCH_ASSOC);

// Format Data for DataTables
$formattedData = [];
foreach ($data as $row) {
    $policeStation = $row['police_station_name'] ? htmlspecialchars($row['police_station_name']) : '-';
    $affiliation = htmlspecialchars($row['affiliation']);
    
    // Combining Affiliation and Police Station
    $affiliationCol = $affiliation . '<br><span class="text-xs text-gray-400">' . $policeStation . '</span>';
    
    // Action buttons
    $actionCol = '
        <div class="text-center text-sm font-medium">
            <a href="cctv_form.php?id='.$row['id'].'" class="text-blue-600 hover:text-blue-900 mr-3">✏️ แก้ไข</a>
            <button onclick="deleteCctv('.$row['id'].')" class="text-red-600 hover:text-red-900">🗑️ ลบ</button>
        </div>
    ';
    
    $formattedData[] = [
        '<span class="text-sm text-gray-500">' . $row['id'] . '</span>',
        '<span class="text-sm font-medium text-gray-900">' . htmlspecialchars($row['station_id']) . '</span>',
        '<span class="text-sm text-gray-600">' . $affiliationCol . '</span>',
        '<span class="text-sm text-gray-600">' . htmlspecialchars($row['camera_type']) . '</span>',
        '<span class="text-sm text-gray-600">' . htmlspecialchars($row['location_name']) . '</span>',
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
