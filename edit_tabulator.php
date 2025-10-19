<!DOCTYPE html>
<html lang="en">

<?php 
include('header.php');
include('session.php');
?>

<body>
<!-- Navbar ================================================== -->
<div class="navbar navbar-inverse navbar-fixed-top">
  <div class="navbar-inner">
    <div class="container">
      <!-- Navbar content -->
    </div>
  </div>
</div>

<header class="jumbotron subhead" id="overview" style="margin: 0; padding: 0;">
  <div class="container" style="display: flex; align-items: center; justify-content: space-between; margin: 0; padding: 0;">
    <div style="margin: 0; padding: 0;">
      <h1 style="margin: 0; padding-left: 100px;">Settings - Tabulator</h1>
      <p class="lead" style="margin: 0; padding-left: 100px;">Pageant Management System</p>
    </div>
    <div style="flex: 1; text-align: right; margin: 0; padding: 0;">
      <img src="img/ms_itcnoback.png" alt="Left Logo" style="width: 180px; margin: 0; padding: 0;">
    </div>
  </div>
</header>

<div class="container">
  <div class="col-lg-3"></div>
  <div class="col-lg-6">
    <a href="edit_organizer.php" class="btn btn-primary"><strong>ORGANIZER SETTINGS &raquo;</strong></a>
    <hr />

    <div class="panel panel-danger">
      <div class="panel-heading">
        <h3 class="panel-title"><strong>Tabulator Settings Panel</strong></h3>
      </div>

      <div class="panel-body">
        <form method="POST" enctype="multipart/form-data">
          <?php 
          $query = $conn->query("SELECT * FROM organizer WHERE org_id='$session_id' AND access='Tabulator'") or die(mysql_error());
          if($query->rowCount() > 0) {
            while ($row = $query->fetch()) {
          ?>
            <table align="center">
              <tr><td colspan="5"><strong>Basic Information</strong><hr /></td></tr>
              <tr>
                <td>Firstname:
                  <input type="text" name="fname" class="form-control" placeholder="Firstname" value="<?php echo $row['fname']; ?>" required autofocus>
                </td>
                <td>&nbsp;</td>
                <td>Middlename:
                  <input type="text" name="mname" class="form-control" placeholder="Middlename" value="<?php echo $row['mname']; ?>" required autofocus>
                </td>
                <td>&nbsp;</td>
                <td>Lastname:
                  <input type="text" name="lname" class="form-control" placeholder="Lastname" value="<?php echo $row['lname']; ?>" required autofocus>
                </td>
              </tr>

              <tr><td colspan="5">&nbsp;</td></tr>
              <tr><td colspan="5"><strong>Account Security</strong><hr /></td></tr>
              <tr>
                <td>Username:
                  <input type="text" name="username" class="form-control" placeholder="Username" value="<?php echo $row['username']; ?>" required autofocus>
                </td>
                <td>&nbsp;</td>
                <td>New Password:
                  <input id="password" type="password" name="passwordx" class="form-control" placeholder="New Password" value="<?php echo $row['password']; ?>" required autofocus>
                </td>
                <td>&nbsp;</td>
                <td>Re-type Password:
                  <input id="confirm_password" type="password" name="password2x" class="form-control" placeholder="Re-type Password" value="<?php echo $row['password']; ?>" required autofocus>
                </td>
              </tr>
              <tr>
                <td colspan="4"></td>
                <td><span id='message'></span></td>
              </tr>

              <tr><td colspan="5">&nbsp;</td></tr>
              <tr><td colspan="5"><strong>Confirmation</strong><hr /></td></tr>
              <tr>
                <td colspan="5">Tabulator Current Password:
                  <input type="password" name="tab_password" class="form-control" placeholder="Tabulator Current Password" required autofocus>
                </td>
              </tr>
              <tr><td colspan="5">&nbsp;</td></tr>
              <tr>
                <td colspan="5">Organizer Current Password:
                  <input type="password" name="org_password" class="form-control" placeholder="Organizer Current Password" required autofocus>
                </td>
              </tr>
            </table>

            <div class="col-lg-12">
              <hr />
              <div class="btn-group pull-right">
                <a href="home.php" type="button" class="btn btn-default">Cancel</a>
                <button name="update" type="submit" class="btn btn-success">Update</button>
              </div>
            </div> 
          </form>

          <?php 
            }
          } else {
          ?>
            <!-- If no record found for Tabulator, show form to create new Tabulator -->
            <form method="POST">
              <table align="center">
                <tr><td colspan="5"><strong>Basic Information</strong><hr /></td></tr>
                <tr>
                  <td>Firstname:
                    <input type="text" name="fname" class="form-control" placeholder="Firstname" required autofocus>
                  </td>
                  <td>&nbsp;</td>
                  <td>Middlename:
                    <input type="text" name="mname" class="form-control" placeholder="Middlename" required autofocus>
                  </td>
                  <td>&nbsp;</td>
                  <td>Lastname:
                    <input type="text" name="lname" class="form-control" placeholder="Lastname" required autofocus>
                  </td>
                </tr>
                <tr><td colspan="5">&nbsp;</td></tr>
                <tr><td colspan="5"><strong>Account Security</strong><hr /></td></tr>
                <tr>
                  <td>Username:
                    <input type="text" name="username" class="form-control" placeholder="Username" required autofocus>
                  </td>
                  <td>&nbsp;</td>
                  <td>Password:
                    <input id="password" type="password" name="passwordx" class="form-control" placeholder="Password" required autofocus>
                  </td>
                  <td>&nbsp;</td>
                  <td>Re-type Password:
                    <input id="confirm_password" type="password" name="password2" class="form-control" placeholder="Re-type Password" required autofocus>
                  </td>
                </tr>
                <tr><td colspan="4"></td><td><span id='message'></span></td></tr> 
                <tr><td colspan="5">&nbsp;</td></tr>
                <tr><td colspan="5"><strong>Confirmation</strong><hr /></td></tr>
                <tr>
                  <td colspan="5">Organizer Password:
                    <input type="password" name="org_password" class="form-control" placeholder="Password" required autofocus>
                  </td>
                </tr>
              </table>
              <br />
              <div class="btn-group pull-right">
                <a href="edit_organizer.php" type="button" class="btn btn-default">CANCEL</a>
                <button name="add_tabulator" type="submit" class="btn btn-primary">ADD</button>
              </div>
            </form>
          <?php } ?>

        </div><!-- end panel body -->
      </div> <!-- end panel -->
    </div><!-- end col-6 -->

    <div class="col-lg-3"></div>
  </div> <!-- end container -->

  <?php include('footer.php'); ?>

  <script src="javascript/jquery1102.min.js"></script>
  <script src="../assets/js/ie10-viewport-bug-workaround.js"></script>

  <script>
  $('#password, #confirm_password').on('keyup', function () {
    if ($('#password').val() == $('#confirm_password').val()) {
      $('#message').html('Matching').css('color', 'green');
    } else 
      $('#message').html('Not Matching').css('color', 'red');
  });
  </script>

</body>
</html>

<?php
if(isset($_POST['update'])) {
  $fname = $_POST['fname']; 
  $mname = $_POST['mname'];  
  $lname = $_POST['lname'];  
  $username = $_POST['username'];  
  $password = $_POST['passwordx'];  
  $password2 = $_POST['password2x'];  
  $org_password = $_POST['org_password'];

  // Validate password match
  if($password != $password2) {
    echo "<script>alert('Password mismatch!');</script>";
    exit;
  }

  // Check the organizer password for validation
  $org_query = $conn->query("SELECT * FROM organizer WHERE password='$org_password' AND access='Organizer'");
  if($org_query->rowCount() == 0) {
    echo "<script>alert('Invalid Organizer password!');</script>";
    exit;
  }

  // Update or create new Tabulator
  $tab_query = $conn->query("SELECT * FROM organizer WHERE org_id='$session_id' AND access='Tabulator'");
  if($tab_query->rowCount() > 0) {
    // Update existing Tabulator
    $sql = "UPDATE organizer SET 
            fname='$fname', mname='$mname', lname='$lname', username='$username', password='$password' 
            WHERE org_id='$session_id' AND access='Tabulator'";
  } else {
    // Add new Tabulator
    $sql = "INSERT INTO organizer (fname, mname, lname, username, password, org_id, access, status) 
            VALUES ('$fname', '$mname', '$lname', '$username', '$password', '$session_id', 'Tabulator', 'offline')";
  }

  if($conn->query($sql)) {
    echo "<script>alert('Tabulator updated successfully'); window.location='edit_tabulator.php';</script>";
  } else {
    echo "<script>alert('Failed to update Tabulator');</script>";
  }
}
?>
