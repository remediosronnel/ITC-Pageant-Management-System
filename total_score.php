<!DOCTYPE html>
<html lang="en">

<?php
include('header2.php');
include('session.php');
?>

<head>
    <style>
        
        .summary-btn {
            margin-left: 10px;  /* Space between the event name and the button */
            padding: 5px 10px;
            border-radius: 5px;
            cursor: pointer;
        }

        .nav-tabs {
            margin-bottom: 10px;
        }

        .nav-tabs li {
            display: inline-block;
        }

        .nav-tabs li a {
            padding: 10px 15px;
            background-color: #ddd;
            color: #444;
            text-decoration: none;
            cursor: pointer;
            border-radius: 5px 5px 0 0;
        }

        .nav-tabs li.active a {
            background-color: red;
            color: white;
        }

        button.event-button {
            padding: 10px 20px;
            margin: 5px;
            background-color: #ddd;
            border: none;
            cursor: pointer;
            border-radius: 5px;
        }

        button.selected {
            background-color: red;
            color: white;
        }

        #average-score {
            margin-top: 20px;
            font-weight: bold;
        }

        #contestant-scores {
            margin-top: 20px;
            width: 100%;
            border-collapse: collapse;
        }

        #contestant-scores th,
        #contestant-scores td {
            padding: 8px;
            border: 1px solid #ddd;
            text-align: center;
        }

        #contestant-scores th {
            background-color: #030101FF;
        }

        #contestant-list {
            display: none;
        }
    </style>

<script>
    
let selectedEvents = [];
let contestantsData = {};  // Store contestants by their unique name to avoid duplicates
let totalScore = 0;  // Variable to accumulate the total score across subevents

// Function to toggle event selection
function toggleEvent(button, eventId) {
    const index = selectedEvents.indexOf(eventId);

    if (index > -1) {
        selectedEvents.splice(index, 1);
        button.classList.remove('selected');
    } else {
        selectedEvents.push(eventId);
        button.classList.add('selected');
    }

    calculateAverageScore();
    displayContestants();
}

// Function to calculate average score for each contestant
function calculateAverageScore() {
    totalScore = 0; // Reset the total score before the new calculation
    contestantsData = {};  // Reset the contestants data object

    // Reset the table for average scores before calculation
    document.getElementById('contestant-scores').innerHTML = '';

    // Make sure we fetch the data for all selected sub-events
    selectedEvents.forEach(subeventId => {  // Pass the subevent_id
        // Fetch contestant scores for the selected sub-event
        fetch(`get_event_scores.php?event_id=${subeventId}`)
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    console.log(data.error); // Log error if no data is found
                } else {
                    // Loop through the data to populate the table
                    data.contestants.forEach(contestant => {
                        // Ensure we only add the contestant once and update the total score across all events
                        if (!contestantsData[contestant.fullname]) {
                            contestantsData[contestant.fullname] = {
                                contestantNumber: contestant.contestantNumber,
                                totalScore: 0,
                                scoreCount: 0, // Track how many events we are adding score from
                                fullname: contestant.fullname
                            };
                        }

                        // Add the score from this sub-event to the contestant's total score
                        contestantsData[contestant.fullname].totalScore += parseFloat(contestant.totalScore);
                        contestantsData[contestant.fullname].scoreCount += 1; // Increase the event count for this contestant
                    });

                    // Add this subevent's total score to the overall totalScore
                    totalScore += data.averageScore;  // Assuming `data.averageScore` returns the score per subevent
                }

                // After processing all subevents, calculate the final average score
                const averageScore = totalScore / selectedEvents.length; // Divide by the number of selected subevents
                document.getElementById("average-score").innerText = `Average Score: ${averageScore.toFixed(2)}`;

                // Display the contestants in the table
                displayContestantScores();
            })
            .catch(error => {
                console.error('Error fetching data:', error);
            });
    });
}

// Function to display the contestant scores (average) in a table
function displayContestantScores() {
    const table = document.getElementById('contestant-scores');

    // Loop through the contestantsData to display their names and average scores
    for (let fullname in contestantsData) {
        const contestant = contestantsData[fullname];

        const row = table.insertRow();
        row.insertCell(0).textContent = contestant.contestantNumber; // Contestant number
        row.insertCell(1).textContent = contestant.fullname; // Contestant name
        
        // Calculate and display the average score (totalScore divided by the number of events)
        const averageScore = contestant.totalScore / selectedEvents.length; // Divide by the number of sub-events selected
        row.insertCell(2).textContent = averageScore.toFixed(2); // Average score across selected events
    }
}

// Function to display the contestants list if there are selected events
function displayContestants() {
    const contestantList = document.getElementById('contestant-list');

    // Check if the list is already visible
    if (contestantList.style.display !== 'none') {
        // If the list is already visible, hide it
        contestantList.style.display = 'none';
    }

    // Display the list if there are selected events
    if (selectedEvents.length > 0) {
        // Show the new list
        contestantList.style.display = 'block';
    } else {
        // Hide the list if no event is selected
        contestantList.style.display = 'none';
    }
}


// Function to toggle the visibility of sub-events under each main event
function toggleTab(eventId) {
    let allPanels = document.querySelectorAll('.sub-event-panel');
    allPanels.forEach(panel => panel.style.display = 'none');

    let selectedPanel = document.getElementById('panel-' + eventId);
    selectedPanel.style.display = 'block';
}



</script>

</head>

<body data-spy="scroll" data-target=".bs-docs-sidebar">

    <!-- Navbar -->
    <div class="navbar navbar-inverse navbar-fixed-top">
        <div class="navbar-inner">
            <div class="container">
                <button type="button" class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a class="brand" href="#"><img src="uploads/<?php echo $company_logo; ?>" width="23" height="23" />&nbsp;
                    <font size="3">Pageant Management System</font></a>
                <div class="nav-collapse collapse">
                    <ul class="nav">
                        <?php if ($tabname == "") { ?>
                            <li><a href="selection.php">User Selection</a></li>
                            <li><a href="home.php">List of Events</a></li>
                            <li class="active"><a href="score_sheets.php"><strong>SCORE SHEETS</strong></a></li>
                            <li><a href="rev_main_event.php">Data Reviews</a></li>
                            <li class="dropdown">
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">My
                                    Account <span class="caret"></span></a>
                                <ul class="dropdown-menu" role="menu">
                                    <li><a target="_blank" href="edit_organizer.php">Settings</a></li>
                                    <li><a href="logout.php">Logout <?php echo $name; ?></a></li>
                                </ul>
                            </li>
                        <?php } else { ?>
                            <li> <a href="logout.php">Tabulator: <b><?php echo $tabname; ?></b> - <i>logout</i></a></li>
                        <?php } ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Subhead -->
    <?php if ($tabname == "") { ?>
        <header class="jumbotron subhead" id="overview">
            <div class="container">
                <h1>Score Sheets</h1>
                <p class="lead">Pageant Management System</p>
            </div>
        </header>
    <?php } else { ?>
        <header class="jumbotrontabulator subhead" id="overview">
            <div class="container">
                <h1>Score Sheets</h1>
                <p class="lead">Pageant Management System - Tabulator's Panel</p>
            </div>
        </header>
    <?php } ?>

    <div class="container">
        <div class="row">
            <div class="span12">
                <br />
                <div class="col-md-12">
                    <ul class="breadcrumb">
                        <li><a href="<?= ($tabname == "") ? "selection.php" : "#" ?>">User Selection</a> / </li>
                        <li><a href="<?= ($tabname == "") ? "home.php" : "#" ?>">List of Events</a> / </li>
                        <li><a href="score_sheets.php">Score Sheets</a> / </li>
                        <li><a href="total_score.php">Total Score</a> / </li>
                    </ul>
                </div>

                <section id="download-bootstrap">
                    <!-- Main Event Tabs -->
                    <ul class="nav nav-tabs">
                        <?php
                        // Fetch main events and display as tabs
                        $sy_query = $conn->query("SELECT * FROM main_event WHERE organizer_id='$session_id' AND status='activated'") or die(mysql_error());
                        while ($sy_row = $sy_query->fetch()) {
                            $MEidxxx = $sy_row['mainevent_id'];
                        echo "
                            <li style='display: inline-block; margin-right: 15px;'>
                                <a href='#' onclick='toggleTab({$sy_row['mainevent_id']})' 
                                style='display: inline-block; padding-right: 10px;'>{$sy_row['event_name']}</a>
                                <button class='btn btn-info' 
                                        onclick='showSummary({$sy_row['mainevent_id']})'
                                        title='Summary'>Summary</button>
                            </li>";

                        }
                        ?>
                    </ul>

                    <!-- Sub-event panels for each main event -->
                    <?php
                    $sy_query = $conn->query("SELECT * FROM main_event WHERE organizer_id='$session_id' AND status='activated'") or die(mysql_error());
                    while ($sy_row = $sy_query->fetch()) {
                        $MEidxxx = $sy_row['mainevent_id'];
                        $s_event_query = $conn->query("SELECT * FROM sub_event WHERE mainevent_id='$MEidxxx'") or die(mysql_error());
                        echo "<div class='sub-event-panel' id='panel-{$sy_row['mainevent_id']}' style='display: none;'>";
                        while ($s_event_row = $s_event_query->fetch()) {
                            echo "<button class='event-button' onclick='toggleEvent(this, {$s_event_row['subevent_id']})'>{$s_event_row['event_name']}</button>";
                        }
                        echo "</div>";
                    }
                    ?>

                    <!-- Average Score -->
                    

                   
                </section>

                 <!-- Contestant Scores Table -->
                   

            </div>
        </div>

           
<!-- Average Score -->
 <div id="ranking-display" style=" margin-top: 20px;"></div>
        <div id="ranking-results" ></div>
        <div id="average-score" style="display:none; text-align: center;"></div>
        

        <!-- Contestant Ranking Display -->
        
            <h3>Contestant Ranking</h3>
            <table id="ranking-table" style="width: 100%; border-collapse: collapse; text-align: center;">
                <thead>
                    <tr>
                        <th>Rank</th>
                        <th>Contestant Name</th>
                        <th>Average Score</th>
                    </tr>
                </thead>
                <tbody>

                 <div id="contestant-list">
                        <table id="contestant-scores">
                            <thead>
                                <tr>
                                    <th>Rank</th>
                                    <th>Contestant Name</th>
                                    <th>Average Score</th>
                                </tr>
                            </thead>
                            <tbody>
                                
                            </tbody>
                        </table>
                    </div>

                </tbody>


            </table>
        </div>




    </div>

    <?php include('footer.php'); ?>

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


<script>
function showSummary(mainevent_id) {
    // Redirect to the new page with mainevent_id as GET parameter
    window.location.href = 'summary_ranking.php?mainevent_id=' + mainevent_id;
} </script>

<script>
let summaryVisible = {}; // track which event's summary is visible

function showSummary(mainevent_id) {
    const rankingDiv = document.getElementById("ranking-results");

    // Toggle logic: if visible, hide and stop
    if (summaryVisible[mainevent_id]) {
        rankingDiv.style.display = "none";
        summaryVisible[mainevent_id] = false;
        return;
    }

    // Otherwise, show and fetch data
    fetch("summary_ranking.php?mainevent_id=" + mainevent_id)
      .then(response => response.json())
      .then(data => {
          const results = data.results;
          const totalJudges = data.total_judges;

          const contestantTotals = {};
          const contestantNames = {};

          results.forEach(row => {
              const id = row.contestant_id;
              const score = parseFloat(row.total_score_sum || row.total_score || 0);
              const name = row.contestant_name;

              if (!contestantTotals[id]) {
                  contestantTotals[id] = 0;
                  contestantNames[id] = name;
              }
              contestantTotals[id] += score;
          });

          const contestantAverages = [];
          for (const id in contestantTotals) {
              const avg = contestantTotals[id] / totalJudges;
              contestantAverages.push({
                  id: id,
                  name: contestantNames[id],
                  total: contestantTotals[id],
                  average: avg
              });
          }

          contestantAverages.sort((a, b) => b.average - a.average);

          let html = `
              <h3 style="margin-bottom:10px;">Ranking Summary</h3>
              <table border="1" cellpadding="8" cellspacing="0" style="border-collapse:collapse;width:80%;max-width:800px;">
                  <tr style="background:#007bff;color:white;text-align:center;">
                      <th>Rank</th>
                      <th>Contestant</th>
                      <th>Total Score</th>
                      <th>Average Score</th>
                  </tr>
          `;

          contestantAverages.forEach((c, index) => {
              html += `
                  <tr style="text-align:center;">
                      <td>${index + 1}</td>
                      <td>${c.name}</td>
                      <td>${c.total.toFixed(2)}</td>
                      <td>${c.average.toFixed(2)}</td>
                  </tr>
              `;
          });

          html += "</table>";

          rankingDiv.innerHTML = html;
          rankingDiv.style.display = "block";
          summaryVisible[mainevent_id] = true; // mark as visible
      })
      .catch(error => console.error('Error fetching summary data:', error));
}
</script>

    
    
</body>

</html>
