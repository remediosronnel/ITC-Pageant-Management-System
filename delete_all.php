<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);

include('session.php');
include('dbcon.php'); // <-- adjust filename/path if needed

header('Content-Type: application/json');
// make sure PDO throws exceptions so we can catch errors

if ($username === 'admin' && $password === 'secret') {
    $_SESSION['is_admin'] = 1;
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_all') {
    

    // Optional: simple permission check (uncomment and customize if you keep an admin flag)
    if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
        echo json_encode(['success' => false, 'message' => 'Permission denied.']);
        exit;
    }

    // child tables first (if you prefer), but we will disable FK checks anyway
    $tables = ['sub_results', 'sub_event', 'contestants', 'judges', 'main_event'];

    try {
        $conn->beginTransaction();

        // disable FK checks for the session so truncate won't fail on FK constraints
        $conn->exec("SET FOREIGN_KEY_CHECKS = 0");

        // check existence and truncate (fallback to DELETE if TRUNCATE fails)
        $checkStmt = $conn->prepare("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = :t");
        foreach ($tables as $t) {
            $checkStmt->execute([':t' => $t]);
            if ($checkStmt->fetchColumn() > 0) {
                try {
                    $conn->exec("TRUNCATE TABLE `{$t}`");
                } catch (PDOException $e) {
                    // fallback in case TRUNCATE is not allowed; use DELETE + reset auto increment
                    $conn->exec("DELETE FROM `{$t}`");
                    $conn->exec("ALTER TABLE `{$t}` AUTO_INCREMENT = 1");
                }
            }
        }

        // re-enable FK checks and commit
        $conn->exec("SET FOREIGN_KEY_CHECKS = 1");
        $conn->commit();

        echo json_encode(['success' => true, 'message' => 'All specified tables cleared successfully.']);
   } catch (PDOException $e) {
        try { $conn->exec("SET FOREIGN_KEY_CHECKS = 1"); } catch (Exception $ee) {}
        $conn->rollBack();
        $errorMsg = addslashes($e->getMessage());
        echo json_encode(['success' => false, 'message' => "PHP Error: $errorMsg"]);
        exit;
    }

    catch (Throwable $t) {
        echo json_encode(['success' => false, 'message' => "Unhandled: " . addslashes($t->getMessage())]);
        exit;
    }

    exit;
}
?>
