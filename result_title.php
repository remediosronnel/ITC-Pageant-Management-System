<!DOCTYPE html>
<html lang="en">

<?php
error_reporting(0);

include('header2.php');
include('session.php');

$active_sub_event = $_GET['event_id'];

function ordinal($i)
{
    $l = substr($i, -1);
    $s = substr($i, -2, -1);

    return $i . (
        (($l == 1 && $s == 1) ||
            ($l == 2 && $s == 1) ||
            ($l == 3 && $s == 1) ||
            $l > 3 ||
            $l == 0) ? 'th' : (($l == 3) ? 'rd' : (($l == 2) ? 'nd' : 'st')));
}

// Get sub_event details
$s_event_query = $conn->query("SELECT * FROM sub_event WHERE subevent_id='$active_sub_event'") or die($conn->errorInfo()[2]);
while ($s_event_row = $s_event_query->fetch()) {
    $MEidxx = $s_event_row['mainevent_id'];

    // Get main event details
    $event_query = $conn->query("SELECT * FROM main_event WHERE mainevent_id='$MEidxx'") or die($conn->errorInfo()[2]);
    while ($event_row = $event_query->fetch()) {

        // Fetch contestant results for sub_event
        $o_result_query = $conn->query("SELECT DISTINCT contestant_id FROM sub_results WHERE mainevent_id='$MEidxx' AND subevent_id='$active_sub_event'") or die($conn->errorInfo()[2]);
        while ($o_result_row = $o_result_query->fetch()) {

            $contestant_id = $o_result_row['contestant_id'];

            // Initialize average score calculation
            $tot_score = 0;
            $tot_deduction = 0;
            $judge_count = 0;

            // Calculate total score and deductions for each contestant
            $tot_score_query = $conn->query("SELECT total_score, deduction FROM sub_results WHERE contestant_id='$contestant_id'") or die($conn->errorInfo()[2]);
            while ($tot_score_row = $tot_score_query->fetch()) {
                $tot_score += $tot_score_row['total_score'];
                $tot_deduction += $tot_score_row['deduction'];
                $judge_count++;
            }

            // Compute exact (non-rounded) average score with deduction
            $average_score = ($tot_score - $tot_deduction) / $judge_count;

            // Check if rank exists in rank_system table, then insert or update
            $rsChecker = $conn->query("SELECT * FROM rank_system WHERE subevent_id='$active_sub_event' AND contestant_id='$contestant_id'") or die($conn->errorInfo()[2]);
            if ($rsChecker->rowCount() > 0) {
                $conn->query("UPDATE rank_system SET total_rank='$average_score' WHERE subevent_id='$active_sub_event' AND contestant_id='$contestant_id'");
            } else {
                $conn->query("INSERT INTO rank_system(subevent_id, contestant_id, total_rank) VALUES('$active_sub_event', '$contestant_id', '$average_score')");
            }
        }

        // Fetch contestants and rank them by average score (deductions included)
        $rsPlacer = $conn->query("
            SELECT contestant_id, AVG(total_score - deduction) AS avg_score 
            FROM sub_results 
            WHERE subevent_id='$active_sub_event' 
            GROUP BY contestant_id 
            ORDER BY avg_score DESC
        ") or die($conn->errorInfo()[2]);

        // Update place_title field in sub_results based on rank
        $rspCtr = 0;
        while ($rsp_row = $rsPlacer->fetch()) {
            $rspCtr++;
            $rsp_contestant_id = $rsp_row['contestant_id'];
            $place_title = ordinal($rspCtr);

            $conn->query("UPDATE sub_results SET place_title='$place_title' WHERE contestant_id='$rsp_contestant_id'");
        }
    }
}
?>

<body data-spy="scroll" data-target=".bs-docs-sidebar">

<div class="container">
    <div class="row">
        <div class="span12">

            <?php
            // Get sub_event details again to display on the page
            $s_event_query = $conn->query("SELECT * FROM sub_event WHERE subevent_id='$active_sub_event'") or die(mysql_error());
            while ($s_event_row = $s_event_query->fetch()) {
                $MEidxx = $s_event_row['mainevent_id'];

                // Get main event details again
                $event_query = $conn->query("SELECT * FROM main_event WHERE mainevent_id='$MEidxx'") or die(mysql_error());
                while ($event_row = $event_query->fetch()) {
            ?>

            <center>
                <?php include('doc_header.php'); ?>

                <table>
                    <tr><td align="center"><h3><?php echo $event_row['event_name']; ?></h3></td></tr>
                    <tr><td align="center"><h4><?php echo $s_event_row['event_name']; ?></h4></td></tr>
                    <tr><td align="center"><h4>Participant's Placing Results</h4></td></tr>
                </table>
            </center>

            <table class="table table-bordered">
                <thead>
                    <th>Participant</th>
                    <th>Summary of Scores</th>
                    <th>Participant's Placing</th>
                </thead>
                <tbody>

                <?php
                // ✅ Fetch contestants ordered by true average score (deductions included)
                $o_result_query = $conn->query("
                    SELECT contestant_id,
                           AVG(total_score - deduction) AS final_avg
                    FROM sub_results
                    WHERE mainevent_id='$MEidxx' 
                      AND subevent_id='$active_sub_event'
                    GROUP BY contestant_id
                    ORDER BY final_avg DESC
                ") or die($conn->errorInfo()[2]);

                while ($o_result_row = $o_result_query->fetch()) {
                    $contestant_id = $o_result_row['contestant_id'];
                ?>
                    <tr>
                        <td><h5>
                            <?php
                            // Fetch contestant name
                            $cname_query = $conn->query("SELECT * FROM contestants WHERE contestant_id='$contestant_id'") or die($conn->errorInfo()[2]);
                            while ($cname_row = $cname_query->fetch()) {
                                $contXXname = $cname_row['contestant_ctr'] . ". " . $cname_row['fullname'];
                            }
                            echo $contXXname;
                            ?>
                        </h5></td>

                        <td>
                            <table class="table table-bordered">
                                <tr>
                                    <th>Judge</th>
                                    <th>Score</th>
                                    <th>Rank</th>
                                </tr>

                                <?php
                                $divz = 0;
                                $totx_score = 0;
                                $rank_score = 0;
                                $totx_deduct = 0;

                                $tot_score_query = $conn->query("SELECT * FROM sub_results WHERE contestant_id='$contestant_id'") or die($conn->errorInfo()[2]);
                                while ($tot_score_row = $tot_score_query->fetch()) {
                                    $divz++;
                                    $place_title = $tot_score_row['place_title'];
                                }

                                // Fetch total score for each judge
                                $tot_score_query = $conn->query("SELECT judge_id, total_score, deduction, rank FROM sub_results WHERE contestant_id='$contestant_id' ORDER BY judge_id") or die($conn->errorInfo()[2]);
                                while ($tot_score_row = $tot_score_query->fetch()) {
                                    $totx_score += $tot_score_row['total_score'];
                                    $rank_score += (int)$tot_score_row['rank'];
                                    $totx_deduct = $tot_score_row['deduction'];

                                    $total_score = $totx_score - $totx_deduct;
                                ?>
                                    <tr>
                                        <td style="width: 50% !important;">
                                            <?php
                                            $jx_id = $tot_score_row['judge_id'];
                                            $jname_query = $conn->query("SELECT * FROM judges WHERE judge_id='$jx_id'") or die($conn->errorInfo()[2]);
                                            $jname_row = $jname_query->fetch();
                                            echo $jname_row['fullname'];
                                            ?>
                                        </td>
                                        <td style="width: 25% !important;"><?php echo ($tot_score_row['total_score'] - $tot_score_row['deduction']); ?><?php echo " (-" . $tot_score_row['deduction'] . ")"; ?></td>
                                        <td style="width: 25% !important;"><?php echo (int)$tot_score_row['rank']; ?></td>
                                    </tr>
                                <?php } ?>

                                <tr>
                                    <td></td>
                                    <td><b>Ave: <?php echo number_format((($totx_score/$divz) - $totx_deduct), 2); ?></b></td>
                                    <td><b>Sum: <?php echo $rank_score; ?></b></td>
                                </tr>

                            </table>
                        </td>

                        <td style="width: 17%!important;">
                            <center>
                                <h3>
                                    <?php
                                    $pt_result_query = $conn->query("SELECT * FROM sub_results WHERE contestant_id='$contestant_id'") or die($conn->errorInfo()[2]);
                                    $pt_result_row = $pt_result_query->fetch();
                                    echo $pt_result_row['place_title'];
                                    ?>
                                </h3>
                                <hr />
                                <?php echo $contXXname; ?>
                            </center>
                        </td>
                    </tr>

                <?php } ?>

                </tbody>
            </table>

            <?php } } ?>

        </div>
    </div>
</div>

<?php include('footer.php'); ?>

<!-- JavaScript -->
<script src="assets/js/jquery.js"></script>
<script src="assets/js/bootstrap-transition.js"></script>
<script src="assets/js/bootstrap-alert.js"></script>
<script src="assets/js/bootstrap-modal.js"></script>
<script src="assets/js/bootstrap-dropdown.js"></script>
<script src="assets/js/bootstrap-scrollspy.js"></script>
<script src="assets/js/bootstrap-tab.js"></script>
<script src="assets/js/bootstrap-tooltip.js"></script>
<script src="assets/js/bootstrap-popover.js"></script>
<script src="assets/js/bootstrap-button.js"></script>
<script src="assets/js/bootstrap-collapse.js"></script>
<script src="assets/js/bootstrap-carousel.js"></script>
<script src="assets/js/bootstrap-typeahead.js"></script>
<script src="assets/js/bootstrap-affix.js"></script>
<script src="assets/js/holder/holder.js"></script>
<script src="assets/js/google-code-prettify/prettify.js"></script>
<script src="assets/js/application.js"></script>

</body>
</html>
