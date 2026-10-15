<?php
/**
 * Found Items Request Processor
 */

declare(strict_types=1);

define('APP_ROOT', dirname(__DIR__));
require_once APP_ROOT . '/config/database.php';
require_once APP_ROOT . '/includes/auth.php';
require_once APP_ROOT . '/includes/csrf.php';
require_once APP_ROOT . '/includes/functions.php';

require_login();
require_valid_csrf();

$action = $_POST['action'] ?? '';
$db = get_db_connection();
$user = current_user();

switch ($action) {
    case 'create':
        $itemName        = trim($_POST['item_name'] ?? '');
        $categoryId      = (int)($_POST['category_id'] ?? 0);
        $dateFound       = trim($_POST['date_found'] ?? '');
        $locationFound   = trim($_POST['location_found'] ?? '');
        $storageLocation = trim($_POST['storage_location'] ?? 'Vault');
        $description     = trim($_POST['description'] ?? '');
        $additionalNotes = trim($_POST['additional_notes'] ?? '');

        if ($itemName === '' || $categoryId <= 0 || $dateFound === '' || $locationFound === '' || $description === '') {
            set_flash('error', 'Please fill in all required fields marked with an asterisk.');
            header('Location: ' . BASE_URL . '/found_items/add.php');
            exit;
        }

        $imagePath = null;
        if (!empty($_FILES['item_image']['name'])) {
            $uploadRes = handle_image_upload($_FILES['item_image']);
            if (!$uploadRes['success']) {
                set_flash('error', $uploadRes['error']);
                header('Location: ' . BASE_URL . '/found_items/add.php');
                exit;
            }
            $imagePath = $uploadRes['path'];
        }

        try {
            $stmt = $db->prepare(
                "INSERT INTO found_items (item_name, category_id, description, date_found, location_found, storage_location, recorded_by_user_id, image_path, status, additional_notes, created_at)
                 VALUES (:item_name, :category_id, :description, :date_found, :location_found, :storage_location, :recorded_by, :image_path, 'Available', :additional_notes, NOW())"
            );
            $stmt->execute([
                'item_name'       => $itemName,
                'category_id'     => $categoryId,
                'description'     => $description,
                'date_found'      => $dateFound,
                'location_found'  => $locationFound,
                'storage_location'=> $storageLocation,
                'recorded_by'     => $user['id'],
                'image_path'      => $imagePath,
                'additional_notes'=> $additionalNotes ?: null
            ]);
            $newId = (int)$db->lastInsertId();

            log_activity('ADD_FOUND_ITEM', 'found_items', $newId, 'Received found item: ' . $itemName . ' stored at ' . $storageLocation);
            set_flash('success', 'Found property registered into custody with ID #' . $newId . '.');
            header('Location: ' . BASE_URL . '/found_items/view.php?id=' . $newId);
            exit;
        } catch (Exception $e) {
            error_log('Insert found item failed: ' . $e->getMessage());
            set_flash('error', 'Database failure while saving found item.');
            header('Location: ' . BASE_URL . '/found_items/add.php');
            exit;
        }
        break;

    case 'update':
        $id              = (int)($_POST['id'] ?? 0);
        $itemName        = trim($_POST['item_name'] ?? '');
        $categoryId      = (int)($_POST['category_id'] ?? 0);
        $dateFound       = trim($_POST['date_found'] ?? '');
        $locationFound   = trim($_POST['location_found'] ?? '');
        $storageLocation = trim($_POST['storage_location'] ?? '');
        $description     = trim($_POST['description'] ?? '');
        $status          = trim($_POST['status'] ?? 'Available');
        $additionalNotes = trim($_POST['additional_notes'] ?? '');

        if ($id <= 0 || $itemName === '' || $categoryId <= 0) {
            set_flash('error', 'Missing or invalid parameters.');
            header('Location: ' . BASE_URL . '/found_items/index.php');
            exit;
        }

        try {
            $imageSql = "";
            $params = [
                'id'              => $id,
                'item_name'       => $itemName,
                'category_id'     => $categoryId,
                'description'     => $description,
                'date_found'      => $dateFound,
                'location_found'  => $locationFound,
                'storage_location'=> $storageLocation,
                'status'          => $status,
                'additional_notes'=> $additionalNotes ?: null
            ];

            if (!empty($_FILES['item_image']['name'])) {
                $uploadRes = handle_image_upload($_FILES['item_image']);
                if ($uploadRes['success'] && $uploadRes['path']) {
                    $imageSql = ", image_path = :image_path";
                    $params['image_path'] = $uploadRes['path'];
                }
            }

            $stmt = $db->prepare(
                "UPDATE found_items 
                 SET item_name = :item_name, category_id = :category_id, description = :description,
                     date_found = :date_found, location_found = :location_found, storage_location = :storage_location,
                     status = :status, additional_notes = :additional_notes
                     $imageSql
                 WHERE id = :id"
            );
            $stmt->execute($params);

            log_activity('UPDATE_FOUND_ITEM', 'found_items', $id, 'Updated found item details and status to ' . $status);
            set_flash('success', 'Custody item record updated successfully.');
            header('Location: ' . BASE_URL . '/found_items/view.php?id=' . $id);
            exit;
        } catch (Exception $e) {
            error_log('Update found item failed: ' . $e->getMessage());
            set_flash('error', 'Database update failed.');
            header('Location: ' . BASE_URL . '/found_items/edit.php?id=' . $id);
            exit;
        }
        break;

    case 'update_status':
        $id     = (int)($_POST['id'] ?? 0);
        $status = trim($_POST['status'] ?? '');
        if ($id > 0 && $status !== '') {
            $stmt = $db->prepare("UPDATE found_items SET status = :status WHERE id = :id");
            $stmt->execute(['status' => $status, 'id' => $id]);
            log_activity('UPDATE_FOUND_STATUS', 'found_items', $id, 'Custody status updated to ' . $status);
            set_flash('success', 'Status updated to ' . $status . '.');
        }
        header('Location: ' . BASE_URL . '/found_items/view.php?id=' . $id);
        exit;

    case 'return_item':
        $id            = (int)($_POST['id'] ?? 0);
        $claimantNotes = trim($_POST['claimant_notes'] ?? '');

        if ($id > 0) {
            try {
                $db->beginTransaction();

                $stmt = $db->prepare(
                    "UPDATE found_items 
                     SET status = 'Returned', 
                         additional_notes = CONCAT(IFNULL(additional_notes, ''), '\n[RETURNED: ', :notes, ' by User #', :uid, ' on ', NOW(), ']')
                     WHERE id = :id"
                );
                $stmt->execute([
                    'notes' => $claimantNotes,
                    'uid'   => $user['id'],
                    'id'    => $id
                ]);

                // Also update any matching lost item to Claimed
                $stmt2 = $db->prepare("UPDATE lost_items SET status = 'Claimed' WHERE id IN (SELECT lost_item_id FROM item_matches WHERE found_item_id = :id)");
                $stmt2->execute(['id' => $id]);

                $db->commit();

                log_activity('RELEASE_FOUND_ITEM', 'found_items', $id, 'Item released to owner: ' . $claimantNotes);
                set_flash('success', 'Item successfully marked as Returned to owner.');
            } catch (Exception $e) {
                $db->rollBack();
                error_log('Return item failed: ' . $e->getMessage());
                set_flash('error', 'Failed to update return status: ' . $e->getMessage());
            }
        }
        header('Location: ' . BASE_URL . '/found_items/view.php?id=' . $id);
        exit;

    default:
        header('Location: ' . BASE_URL . '/found_items/index.php');
        exit;
}
