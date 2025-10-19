<?php
include 'dbcon.php';

if (isset($_GET['mainevent_id'])) {
    $mainevent_id = intval($_GET['mainevent_id']);

    // ✅ Step 1: Count unique judges by name (avoid duplicates)
    $judgeSql = "
        SELECT COUNT(DISTINCT j.judge_id) AS total_judges
        FROM judges j
        INNER JOIN sub_event se 
            ON j.subevent_id = se.subevent_id
        INNER JOIN sub_results sr 
            ON sr.judge_id = j.judge_id 
            AND sr.subevent_id = se.subevent_id
        WHERE se.mainevent_id = ?
    ";

    $stmt = $conn->prepare($judgeSql);
    $stmt->execute([$mainevent_id]);
    $judgeCount = (int)$stmt->fetchColumn();

    // Prevent division by zero
    if ($judgeCount == 0) $judgeCount = 1;

    // ✅ Step 2: Compute contestant totals and averages based on fullname (merged across all subevents)
  $query = "
    SELECT
        c.contestant_id,
        c.fullname AS contestant_name,
        ROUND(SUM(COALESCE(sr.total_score, 0) - COALESCE(sr.deduction, 0)), 2) AS total_score_sum,
        ROUND(SUM(COALESCE(sr.total_score, 0) - COALESCE(sr.deduction, 0)) / ?, 2) AS average_score
    FROM sub_results sr
    INNER JOIN contestants c ON sr.contestant_id = c.contestant_id
    WHERE sr.mainevent_id = ?
    GROUP BY c.fullname
    ORDER BY average_score DESC
";


    $stmt2 = $conn->prepare($query);
    $stmt2->execute([$judgeCount, $mainevent_id]);
    $results = $stmt2->fetchAll(PDO::FETCH_ASSOC);

    header('Content-Type: application/json');
    echo json_encode([
        'results' => $results,
        'total_judges' => $judgeCount
    ]);
}
?>
