<?php
require_once 'db_config.php';
require_once 'auth.php';

header('Content-Type: application/json');

// Only Admins can manage menus
if ($user_role_id != 1) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method == 'GET') {
    try {
        $stmt = $pdo->query("SELECT * FROM menus ORDER BY parent_id ASC, order_num ASC");
        $menus = $stmt->fetchAll();
        echo json_encode(['status' => 'success', 'data' => $menus]);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit;
}

if ($method == 'POST') {
    $action = $_POST['action'] ?? '';

    try {
        if ($action == 'add') {
            $menu_type = $_POST['menu_type'] ?? 'backend';
            $title = $_POST['title'] ?? '';
            $url = $_POST['url'] ?? '#';
            $icon = $_POST['icon'] ?? '';
            $css_class = $_POST['css_class'] ?? '';
            $parent_id = !empty($_POST['parent_id']) ? $_POST['parent_id'] : null;
            $order_num = (int)($_POST['order_num'] ?? 0);
            
            // Handle allowed_roles array
            $allowed_roles = null;
            if (!empty($_POST['allowed_roles']) && is_array($_POST['allowed_roles'])) {
                $allowed_roles = json_encode(array_map('intval', $_POST['allowed_roles']));
            }

            $stmt = $pdo->prepare("INSERT INTO menus (menu_type, title, url, icon, css_class, parent_id, order_num, allowed_roles) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$menu_type, $title, $url, $icon, $css_class, $parent_id, $order_num, $allowed_roles]);
            echo json_encode(['status' => 'success', 'message' => 'Menu added successfully']);
        } 
        elseif ($action == 'edit') {
            $id = $_POST['id'] ?? 0;
            $title = $_POST['title'] ?? '';
            $url = $_POST['url'] ?? '#';
            $icon = $_POST['icon'] ?? '';
            $css_class = $_POST['css_class'] ?? '';
            $parent_id = !empty($_POST['parent_id']) ? $_POST['parent_id'] : null;
            $order_num = (int)($_POST['order_num'] ?? 0);
            $is_active = isset($_POST['is_active']) ? (int)$_POST['is_active'] : 1;
            
            $allowed_roles = null;
            if (!empty($_POST['allowed_roles']) && is_array($_POST['allowed_roles'])) {
                $allowed_roles = json_encode(array_map('intval', $_POST['allowed_roles']));
            }

            $stmt = $pdo->prepare("UPDATE menus SET title=?, url=?, icon=?, css_class=?, parent_id=?, order_num=?, allowed_roles=?, is_active=? WHERE id=?");
            $stmt->execute([$title, $url, $icon, $css_class, $parent_id, $order_num, $allowed_roles, $is_active, $id]);
            echo json_encode(['status' => 'success', 'message' => 'Menu updated successfully']);
        }
        elseif ($action == 'delete') {
            $id = $_POST['id'] ?? 0;
            $stmt = $pdo->prepare("DELETE FROM menus WHERE id=?");
            $stmt->execute([$id]);
            echo json_encode(['status' => 'success', 'message' => 'Menu deleted successfully']);
        }
        elseif ($action == 'reorder') {
            $order_data = json_decode($_POST['order_data'], true);
            // format: [{id: 1, order: 1}, {id: 2, order: 2}]
            if (is_array($order_data)) {
                $pdo->beginTransaction();
                $stmt = $pdo->prepare("UPDATE menus SET order_num = ? WHERE id = ?");
                foreach ($order_data as $item) {
                    $stmt->execute([$item['order'], $item['id']]);
                }
                $pdo->commit();
                echo json_encode(['status' => 'success', 'message' => 'Order updated']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Invalid data format']);
            }
        }
        else {
            echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
        }
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}
?>
