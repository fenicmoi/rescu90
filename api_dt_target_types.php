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

// Define columns that correspond to DataTables columns index
$columns = [
    0 => 't.id',
    1 => 't.type_name',
    2 => 't.marker_color',
    3 => 'usage_count'
];

$orderBy = isset($columns[$orderColumnIndex]) ? $columns[$orderColumnIndex] : 't.id';

// Base SQL
$sqlBase = "FROM target_types t LEFT JOIN target_locations l ON t.id = l.target_type_id";

// Where Clause for Search
$whereClause = "";
$params = [];
if (!empty($searchValue)) {
    $whereClause = "WHERE t.type_name LIKE ? OR t.marker_color LIKE ?";
    $searchWildcard = "%" . $searchValue . "%";
    $params = [$searchWildcard, $searchWildcard];
}

// Get Total Records (without search)
$stmtTotal = $pdo->query("SELECT COUNT(id) FROM target_types");
$recordsTotal = $stmtTotal->fetchColumn();

// Get Filtered Records (with search)
if (!empty($whereClause)) {
    $stmtFiltered = $pdo->prepare("SELECT COUNT(DISTINCT t.id) " . $sqlBase . " " . $whereClause);
    $stmtFiltered->execute($params);
    $recordsFiltered = $stmtFiltered->fetchColumn();
} else {
    $recordsFiltered = $recordsTotal;
}

// Get Data
$sqlData = "SELECT t.*, COUNT(l.id) as usage_count " . $sqlBase . " " . $whereClause . " GROUP BY t.id ORDER BY " . $orderBy . " " . $orderDir;

if ($length != -1) {
    $sqlData .= " LIMIT " . intval($length) . " OFFSET " . intval($start);
}

$stmtData = $pdo->prepare($sqlData);
$stmtData->execute($params);
$data = $stmtData->fetchAll(PDO::FETCH_ASSOC);

// Format Data for DataTables
$formattedData = [];
foreach ($data as $row) {
    $markerCol = '<div class="flex items-center gap-2">
                    <div class="w-6 h-6 rounded-full border border-gray-300 shadow-sm" style="background-color: ' . htmlspecialchars($row['marker_color']) . '"></div>
                    <span class="text-xs text-gray-500 font-mono">' . htmlspecialchars($row['marker_color']) . '</span>
                </div>';
                
    $usageBadge = '<span class="px-2.5 py-1 inline-flex text-xs leading-5 font-semibold rounded-full ' . ($row['usage_count'] > 0 ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800') . '">' . $row['usage_count'] . ' หมุด</span>';
    
    $actionCol = '<div class="text-right text-sm font-medium">
                    <a href="target_type_form.php?id='.$row['id'].'" class="text-blue-600 hover:text-blue-900 mr-3">✏️ แก้ไข</a>';
    if ($row['usage_count'] == 0) {
        $actionCol .= '<button onclick="deleteType('.$row['id'].', \''.htmlspecialchars($row['type_name']).'\')" class="text-red-600 hover:text-red-900">🗑️ ลบ</button>';
    } else {
        $actionCol .= '<span class="text-gray-300 cursor-not-allowed" title="ไม่สามารถลบได้เนื่องจากมีการใช้งานแล้ว">🗑️ ลบ</span>';
    }
    $actionCol .= '</div>';
    
    $formattedData[] = [
        '<span class="text-sm text-gray-500">' . $row['id'] . '</span>',
        '<div class="text-sm font-bold text-gray-900">' . htmlspecialchars($row['type_name']) . '</div>',
        $markerCol,
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
