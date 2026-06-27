<?php
require_once 'auth.php';
requireRole([1, 2]); // Admins and Governor

require_once 'db_config.php';

// DataTables request parameters
$draw = isset($_POST['draw']) ? intval($_POST['draw']) : 1;
$start = isset($_POST['start']) ? intval($_POST['start']) : 0;
$length = isset($_POST['length']) ? intval($_POST['length']) : 10;
$searchValue = isset($_POST['search']['value']) ? $_POST['search']['value'] : '';

// Ordering
$orderColumnIndex = isset($_POST['order'][0]['column']) ? intval($_POST['order'][0]['column']) : 0;
$orderDir = isset($_POST['order'][0]['dir']) && $_POST['order'][0]['dir'] === 'asc' ? 'ASC' : 'DESC';

$columns = [
    0 => 'c.id',
    1 => 'c.type_name',
    2 => 'usage_count'
];

$orderBy = isset($columns[$orderColumnIndex]) ? $columns[$orderColumnIndex] : 'c.id';

// Base SQL
$sqlBase = "FROM camera_types c LEFT JOIN cctv_locations l ON c.id = l.camera_type_id";

// Where Clause for Search
$whereClause = "";
$params = [];
if (!empty($searchValue)) {
    $whereClause = "WHERE c.type_name LIKE ?";
    $searchWildcard = "%" . $searchValue . "%";
    $params = [$searchWildcard];
}

// Get Total Records (without search)
$stmtTotal = $pdo->query("SELECT COUNT(id) FROM camera_types");
$recordsTotal = $stmtTotal->fetchColumn();

// Get Filtered Records (with search)
if (!empty($whereClause)) {
    $stmtFiltered = $pdo->prepare("SELECT COUNT(DISTINCT c.id) " . $sqlBase . " " . $whereClause);
    $stmtFiltered->execute($params);
    $recordsFiltered = $stmtFiltered->fetchColumn();
} else {
    $recordsFiltered = $recordsTotal;
}

// Get Data
$sqlData = "SELECT c.*, COUNT(l.id) as usage_count " . $sqlBase . " " . $whereClause . " GROUP BY c.id ORDER BY " . $orderBy . " " . $orderDir;

if ($length != -1) {
    $sqlData .= " LIMIT " . intval($length) . " OFFSET " . intval($start);
}

$stmtData = $pdo->prepare($sqlData);
$stmtData->execute($params);
$data = $stmtData->fetchAll(PDO::FETCH_ASSOC);

// Format Data for DataTables
$formattedData = [];
foreach ($data as $row) {
    $usageBadge = '<span class="px-2.5 py-1 inline-flex text-xs leading-5 font-semibold rounded-full ' . ($row['usage_count'] > 0 ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800') . '">' . $row['usage_count'] . ' ตัว</span>';
    
    $actionCol = '<div class="text-right text-sm font-medium">
                    <a href="camera_type_form.php?id='.$row['id'].'" class="text-blue-600 hover:text-blue-900 mr-3">✏️ แก้ไข</a>';
    if ($row['usage_count'] == 0) {
        $actionCol .= '<button onclick="deleteType('.$row['id'].', \''.htmlspecialchars($row['type_name']).'\')" class="text-red-600 hover:text-red-900">🗑️ ลบ</button>';
    } else {
        $actionCol .= '<span class="text-gray-300 cursor-not-allowed" title="ไม่สามารถลบได้เนื่องจากมีการใช้งานแล้ว">🗑️ ลบ</span>';
    }
    $actionCol .= '</div>';
    
    $formattedData[] = [
        '<span class="text-sm text-gray-500">' . $row['id'] . '</span>',
        '<div class="text-sm font-bold text-gray-900">' . htmlspecialchars($row['type_name']) . '</div>',
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
