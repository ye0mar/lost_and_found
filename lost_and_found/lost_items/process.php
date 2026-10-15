<?php
/**
 * Lost Items Request Processor
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
        $itemName     = trim($_POST['item_name'] ?? '');
        $categoryId   = (int)($_POST['category_id'] ?? 0);
        $dateLost     = trim($_POST['date_lost'] ?? '');
        $locationLost = trim($_POST['location_lost'] ?? '');
        $description  = trim($_POST['description'] ?? '');
        $contactName  = trim($_POST['contact_name'] ?? '');
        $contactPhone = trim($_POST['contact_phone'] ?? '');
        $contactEmail = trim($_POST['contact_email'] ?? '');

        if ($itemName === '' || $categoryId <= 0 || $dateLost === '' || $locationLost === '' || $description === '') {
            set_flash('error', 'Please fill in all required fields marked with an asterisk.');
            header('Location: ' . BASE_URL . '/lost_items/report.php');
            exit;
        }

        // Handle Image Upload
        $imagePath = null;
        if (!empty($_FILES['item_image']['name'])) {
            $uploadRes = handle_image_upload($_FILES['item_image']);
            if (!$uploadRes['success']) {
                set_flash('error', $uploadRes['error']);
                header('Location: ' . BASE_URL . '/lost_items/report.php');
                exit;
            }
            $imagePath = $uploadRes['path'];
        }

        try {
            $stmt = $db->prepare(
                "INSERT INTO lost_items (item_name, category_id, description, date_lost, location_lost, reported_by_user_id, contact_name, contact_phone, contact_email, image_path, status, created_at)
                 VALUES (:item_name, :category_id, :description, :date_lost, :location_lost, :reported_by, :contact_name, :contact_phone, :contact_email, :image_path, 'Reported', NOW())"
            );
            $stmt->execute([
                'item_name'    => $itemName,
                'category_id'  => $categoryId,
                'description'  => $description,
                'date_lost'    => $dateLost,
                'location_lost'=> $locationLost,
                'reported_by'  => $user['id'],
                'contact_name' => $contactName ?: null,
                'contact_phone'=> $contactPhone ?: null,
                'contact_email'=> $contactEmail ?: null,
                'image_path'   => $imagePath
            ]);
            $newId = (int)$db->lastInsertId();

            log_activity('REPORT_LOST_ITEM', 'lost_items', $newId, 'Reported lost item: ' . $itemName);
            set_flash('success', 'Lost item report successfully filed with ID #' . $newId . '.');
            header('Location: ' . BASE_URL . '/lost_items/view.php?id=' . $newId);
            exit;
        } catch (Exception $e) {
            error_log('Insert lost item failed: ' . $e->getMessage());
            set_flash('error', 'Database failure while saving lost item record.');
            header('Location: ' . BASE_URL . '/lost_items/report.php');
            exit;
        }
        break;

    case 'update':
        $id           = (int)($_POST['id'] ?? 0);
        $itemName     = trim($_POST['item_name'] ?? '');
        $categoryId   = (int)($_POST['category_id'] ?? 0);
        $dateLost     = trim($_POST['date_lost'] ?? '');
        $locationLost = trim($_POST['location_lost'] ?? '');
        $description  = trim($_POST['description'] ?? '');
        $status       = trim($_POST['status'] ?? 'Reported');
        $contactName  = trim($_POST['contact_name'] ?? '');
        $contactPhone = trim($_POST['contact_phone'] ?? '');
        $contactEmail = trim($_POST['contact_email'] ?? '');

        if ($id <= 0 || $itemName === '' || $categoryId <= 0) {
            set_flash('error', 'Missing or invalid parameters.');
            header('Location: ' . BASE_URL . '/lost_items/index.php');
            exit;
        }

        try {
            $imageSql = "";
            $params = [
                'id'           => $id,
                'item_name'    => $itemName,
                'category_id'  => $categoryId,
                'description'  => $description,
                'date_lost'    => $dateLost,
                'location_lost'=> $locationLost,
                'status'       => $status,
                'contact_name' => $contactName ?: null,
                'contact_phone'=> $contactPhone ?: null,
                'contact_email'=> $contactEmail ?: null
            ];

            if (!empty($_FILES['item_image']['name'])) {
                $uploadRes = handle_image_upload($_FILES['item_image']);
                if ($uploadRes['success'] && $uploadRes['path']) {
                    $imageSql = ", image_path = :image_path";
                    $params['image_path'] = $uploadRes['path'];
                }
            }

            $stmt = $db->prepare(
                "UPDATE lost_items 
                 SET item_name = :item_name, category_id = :category_id, description = :description,
                     date_lost = :date_lost, location_lost = :location_lost, status = :status,
                     contact_name = :contact_name, contact_phone = :contact_phone, contact_email = :contact_email
                     $imageSql
                 WHERE id = :id"
            );
            $stmt->execute($params);

            log_activity('UPDATE_LOST_ITEM', 'lost_items', $id, 'Updated details and status to ' . $status);
            set_flash('success', 'Lost item record updated successfully.');
            header('Location: ' . BASE_URL . '/lost_items/view.php?id=' . $id);
            exit;
        } catch (Exception $e) {
            error_log('Update lost item failed: ' . $e->getMessage());
            set_flash('error', 'Database update failed.');
            header('Location: ' . BASE_URL . '/lost_items/edit.php?id=' . $id);
            exit;
        }
        break;

    case 'update_status':
        $id     = (int)($_POST['id'] ?? 0);
        $status = trim($_POST['status'] ?? '');
        if ($id > 0 && $status !== '') {
            $stmt = $db->prepare("UPDATE lost_items SET status = :status WHERE id = :id");
            $stmt->execute(['status' => $status, 'id' => $id]);
            log_activity('UPDATE_LOST_STATUS', 'lost_items', $id, 'Status updated to ' . $status);
            set_flash('success', 'Status updated to ' . $status . '.');
        }
        header('Location: ' . BASE_URL . '/lost_items/view.php?id=' . $id);
        exit;

    case 'confirm_match':
        $lostId  = (int)($_POST['lost_item_id'] ?? 0);
        $foundId = (int)($_POST['found_item_id'] ?? 0);
        $score   = (float)($_POST['match_score'] ?? 0);

        if ($lostId > 0 && $foundId > 0) {
            try {
                $db->beginTransaction();

                // 1. Insert match record
                $stmt = $db->prepare(
                    "INSERT INTO item_matches (lost_item_id, found_item_id, match_score, matched_by_user_id, status, notes, created_at)
                     VALUES (:lost, :found, :score, :user, 'Confirmed', 'Matched by staff review', NOW())"
                );
                $stmt->execute([
                    'lost'  => $lostId,
                    'found' => $foundId,
                    'score' => $score,
                    'user'  => $user['id']
                ]);

                // 2. Update statuses of both items
                $stmt1 = $db->prepare("UPDATE lost_items SET status = 'Matched' WHERE id = :id");
                $stmt1->execute(['id' => $lostId]);

                $stmt2 = $db->prepare("UPDATE found_items SET status = 'Matched' WHERE id = :id");
                $stmt2->execute(['id' => $foundId]);

                $db->commit();

                log_activity('CONFIRM_ITEM_MATCH', 'item_matches', $lostId, "Associated Lost Item #$lostId with Found Item #$foundId (Score: $score%)");
                set_flash('success', 'Match confirmed! Both items updated to Matched status.');
            } catch (Exception $e) {
                $db->rollBack();
                error_log('Match confirmation failed: ' . $e->getMessage());
                set_flash('error', 'Failed to confirm item match: ' . $e->getMessage());
            }
        }
        header('Location: ' . BASE_URL . '/lost_items/view.php?id=' . $lostId);
        exit;

    default:
        header('Location: ' . BASE_URL . '/lost_items/index.php');
        exit;
}
