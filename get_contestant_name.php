<?php
include('session.php');
include('dbcon.php');

if (isset($_GET['event_id'])) {
    $eventId = $_GET['event_id'];

    // Query to fetch contestant names and their numbers for the selected event
    $query = $conn->prepare("
        SELECT 
            c.contestant_ctr, 
            c.fullname
        FROM contestants c
        INNER JOIN sub_results sr ON c.contestant_id = sr.contestant_id
        WHERE sr.subevent_id = ?  -- Filter by subevent_id
    ");

    // Execute the query
    $query->execute([$eventId]);
    $results = $query->fetchAll();

    if ($results) {
        $contestants = [];
        
        // Loop through the results and store contestant info
        foreach ($results as $row) {
            $contestants[] = [
                'contestantNumber' => $row['contestant_ctr'],
                'fullname' => $row['fullname']
            ];
        }

        // Return the contestant data as JSON
        echo json_encode([
            'contestants' => $contestants
        ]);
    } else {
        echo json_encode(['error' => 'No contestants found for this event.']);
    }
}
?>
