<?php 
include('session.php'); 
include('dbcon.php'); 

if (isset($_GET['event_id'])) {
    $eventId = $_GET['event_id'];

    // The query calculates the total score divided by the number of judges for each sub-event
    $query = $conn->prepare("
        SELECT 
            c.contestant_ctr, 
            c.fullname, 
            se.subevent_id,
            se.event_name AS subevent_name,
            me.event_name AS main_event_name,

        SUM(sr.total_score - sr.deduction) / COUNT(j.judge_id) AS totalScore  -- Dividing by number of distinct judges for average score
        FROM sub_results sr
        INNER JOIN contestants c ON sr.contestant_id = c.contestant_id
        INNER JOIN sub_event se ON sr.subevent_id = se.subevent_id
        INNER JOIN main_event me ON se.mainevent_id = me.mainevent_id
        LEFT JOIN judges j ON j.subevent_id = se.subevent_id  
        WHERE sr.subevent_id = ?  -- Filter by subevent_id
        GROUP BY c.fullname, se.subevent_id, se.event_name, me.event_name
        ORDER BY totalScore DESC, c.contestant_ctr ASC
    ");
   // Execute the query
    $query->execute([$eventId]);
    $results = $query->fetchAll();

    if ($results) {
        $data = [];
        $totalScore = 0;
        $contestantCount = count($results);

        $i = 1;
        foreach ($results as $row) {
            $contestantName = $row['fullname'];
            $contestantNumber = $i;
            $averageScore = $row['totalScore'];  // Average score for the contestant

            // Store the contestant's data separately
            $data[] = [
                'contestantNumber' => $contestantNumber,
                'fullname' => $contestantName,
                'totalScore' => $averageScore
            ];

            // Add to total score (for overall average)
            $totalScore += $averageScore;
            $i++;
        }

        // Calculate the average score for all contestants in the event
        $averageScoreForEvent = ($contestantCount > 0) ? ($totalScore / $contestantCount) : 0;

        // Return the JSON-encoded response
        echo json_encode([
            'contestants' => $data,  // Contestants list with their totalScore (average score)
            'averageScore' => number_format($averageScoreForEvent, 2)  // Overall average score for the event
        ]);
    } else {
        echo json_encode(['error' => 'No contestants found for this event.']);
    }
}
?>