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
    0 => 'u.id',
    1 => 'u.name',
    2 => 'u.phone',
    3 => 'u.username',
    4 => 'd.name_th',
    5 => 'r.role_name',
    6 => 'u.created_at'
];

$orderBy = isset($columns[$orderColumnIndex]) ? $columns[$orderColumnIndex] : 'u.id';

// Base SQL
$sqlBase = "FROM users u JOIN roles r ON u.role_id = r.id LEFT JOIN districts d ON u.district_id = d.id";

// Where Clause for Search
$whereClause = "";
$params = [];
if (!empty($searchValue)) {
    $whereClause = "WHERE u.name LIKE ? OR u.username LIKE ? OR u.position LIKE ? OR u.agency LIKE ? OR r.role_name LIKE ? OR d.name_th LIKE ?";
    $searchWildcard = "%" . $searchValue . "%";
    $params = [$searchWildcard, $searchWildcard, $searchWildcard, $searchWildcard, $searchWildcard, $searchWildcard];
}

// Get Total Records (without search)
$stmtTotal = $pdo->query("SELECT COUNT(u.id) FROM users u");
$recordsTotal = $stmtTotal->fetchColumn();

// Get Filtered Records (with search)
if (!empty($whereClause)) {
    $stmtFiltered = $pdo->prepare("SELECT COUNT(u.id) " . $sqlBase . " " . $whereClause);
    $stmtFiltered->execute($params);
    $recordsFiltered = $stmtFiltered->fetchColumn();
} else {
    $recordsFiltered = $recordsTotal;
}

// Get Data
$sqlData = "SELECT u.id, u.username, u.name, u.agency, u.position, u.phone, r.role_name, u.district_id, d.name_th as district_name, u.created_at " . $sqlBase . " " . $whereClause . " ORDER BY " . $orderBy . " " . $orderDir;

if ($length != -1) {
    $sqlData .= " LIMIT " . intval($length) . " OFFSET " . intval($start);
}

$stmtData = $pdo->prepare($sqlData);
$stmtData->execute($params);
$data = $stmtData->fetchAll(PDO::FETCH_ASSOC);

// Format Data for DataTables
$formattedData = [];
foreach ($data as $row) {
    // Name & Position
    $positionStr = $row['position'] ?? '';
    $agencyStr = !empty($row['agency']) ? ' (' . htmlspecialchars($row['agency']) . ')' : '';
    $nameCol = '<div class="text-sm font-medium text-gray-900">' . htmlspecialchars($row['name']) . '</div>
                <div class="text-xs text-gray-500">' . htmlspecialchars($positionStr) . $agencyStr . '</div>';

    // Phone
    $phoneCol = '<div class="text-sm text-gray-900">' . htmlspecialchars($row['phone'] ?? '-') . '</div>';

    // Username
    $userCol = '<div class="text-sm text-gray-500">' . htmlspecialchars($row['username']) . '</div>';

    // District
    $districtCol = '<div class="text-sm text-gray-500">' . ($row['district_name'] ? 'อ.' . htmlspecialchars($row['district_name']) : '<span class="text-gray-300">ไม่ระบุ / ทั้งจังหวัด</span>') . '</div>';

    // Role
    $roleClass = 'bg-gray-100 text-gray-800';
    if ($row['role_name'] == 'Admin') $roleClass = 'bg-purple-100 text-purple-800';
    if ($row['role_name'] == 'Governor') $roleClass = 'bg-blue-100 text-blue-800';
    if ($row['role_name'] == 'District Chief') $roleClass = 'bg-green-100 text-green-800';
    if ($row['role_name'] == 'Officer') $roleClass = 'bg-yellow-100 text-yellow-800';
    
    $roleCol = '<span class="px-2.5 py-1 inline-flex text-xs leading-5 font-semibold rounded-full ' . $roleClass . '">' . htmlspecialchars($row['role_name']) . '</span>';

    // Date
    $dateCol = date('d M Y, H:i', strtotime($row['created_at']));

    // Action buttons
    $isSuperAdmin = ($row['id'] == 1); // Protect ID 1
    
    $actionCol = '<div class="text-right text-sm font-medium">';
    if (!$isSuperAdmin || $_SESSION['user_id'] == 1) {
        $actionCol .= '<a href="user_form.php?id='.$row['id'].'" class="text-blue-600 hover:text-blue-900 mr-3">✏️ แก้ไข</a>';
    }
    
    if (!$isSuperAdmin) {
        $actionCol .= '<button onclick="deleteUser('.$row['id'].', \''.htmlspecialchars($row['username']).'\')" class="text-red-600 hover:text-red-900">🗑️ ลบ</button>';
    } elseif ($_SESSION['user_id'] != 1) {
        $actionCol .= '<span class="text-gray-400 cursor-not-allowed" title="ไม่สามารถลบ Super Admin ได้">🔒 สงวนสิทธิ์</span>';
    }
    $actionCol .= '</div>';
    
    $formattedData[] = [
        '<span class="text-sm text-gray-500">' . $row['id'] . '</span>',
        $nameCol,
        $phoneCol,
        $userCol,
        $districtCol,
        $roleCol,
        '<span class="text-sm text-gray-500">' . $dateCol . '</span>',
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
