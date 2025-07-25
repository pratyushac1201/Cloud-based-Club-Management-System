<?php
date_default_timezone_set('Asia/Kolkata'); // Set timezone to IST
require_once('dbconfig.php');

global $con;

// Add these configuration variables at the top of the file after the global $con declaration
$email_config = array(
    'smtp_host' => 'smtp.gmail.com',
    'smtp_port' => 465,
    'smtp_username' => 'namithar2004@gmail.com',
    'smtp_password' => 'dlyy msnr rkbq fpxj',
    'from_email' => 'namithar2004@gmail.com',
    'from_name' => 'Club Manager'
);

// Add this function at the top (after $email_config)
function send_brevo_email($to_email, $to_name, $subject, $html_content, $text_content = '') {
    $apiKey = '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';
    $data = [
        'sender' => ['name' => 'Club Manager', 'email' => 'your_verified_sender@yourdomain.com'],
        'to' => [['email' => $to_email, 'name' => $to_name]],
        'subject' => $subject,
        'htmlContent' => $html_content,
        'textContent' => $text_content ?: strip_tags($html_content)
    ];
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://api.brevo.com/v3/smtp/email');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'accept: application/json',
        'api-key: ' . $apiKey,
        'content-type: application/json'
    ]);
    $response = curl_exec($ch);
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($httpcode >= 200 && $httpcode < 300) {
        return true;
    } else {
        error_log('Brevo email error: ' . $response);
        return false;
    }
}

// Helper function to format dates in IST
function formatDateIST($date) {
    return date('jS M Y H:i', strtotime($date));
}

// Helper function to get current date/time in IST
function getCurrentDateTimeIST() {
    return date('Y-m-d H:i:s');
}

/*******************************
 * function for login into panel.
 *******************************/

function login()
{
	global $con;
	if (isset($_POST['submit'])) 
	{
		
		$username = $_POST['username'];
		$username = stripslashes($username);
		$password = $_POST['password'];
		$password = stripslashes($password);

		$query = "SELECT * from userinfo where username ='$username' AND password ='$password'";
		$result = mysqli_query($con,$query);
		$rows = mysqli_affected_rows($con);

		if($rows == 1)
		{
			$_SESSION['username'] = $username;
			while($row = mysqli_fetch_assoc($result))
			{
				$last_login = $row['currunt_login'];

				// Use PHP IST time instead of SQL NOW()
				$currunt_login = getCurrentDateTimeIST();
				$query = "UPDATE userinfo SET last_login='$last_login', currunt_login='$currunt_login' WHERE username='$username'";
				mysqli_query($con,$query);
			}

			echo '<div class="text-center alert bg-success col-md-offset-4 col-md-4" role="alert"><span>Welcome back, <b>'.$_SESSION['username'].'</b>!</span></div>';
			echo '<script>setTimeout(function () { window.location.href = "home.php";}, 1000);</script>';
		}
		else
		{
			echo '<div class="text-center alert bg-danger col-md-offset-4 col-md-4" role="alert"><span>Sorry <b>'.$username.'</b>, Try Again!</span></div>';
		}	
	}

	return false;
}


/*******************************
 * to check for authorized user.
 *******************************/

function check_session()
{
	if( !isset($_SESSION["username"]) )
	{
    	header("location:index.php");
    	exit();
	}	
    return false;
}

/*******************************
 * load all data of the session user.
 *******************************/

function get_member_data($session_name)
{
	global $con;
	$query = "SELECT * FROM userinfo WHERE username='$session_name'";
	$result = mysqli_query($con,$query);
	$rows = mysqli_affected_rows($con);
	
	if($rows == 1)
	{
		$row = mysqli_fetch_assoc($result);
	}
	else
		echo 'error while retriving data';
	return $row;
}

/*******************************
 * to load all required user data for user settings.
 *******************************/

function user_setting($user_id)
{
	global $con;
	$user_id = $user_id;
	$query = "SELECT * FROM userinfo where id='$user_id'";
	$result = mysqli_query($con,$query);
	$rows = mysqli_affected_rows($con);
	
	if($rows == 1)
	{
		$row = mysqli_fetch_assoc($result);
	}
	else
		echo 'error while retriving data';
	return $row;	
}

/*******************************
 * updates settings panel of user.
 *******************************/

function update_settings($id)
{
	global $con;

	$query = "SELECT * FROM userinfo where id='$id'";
		$result = mysqli_query($con,$query);
		$rows = mysqli_affected_rows($con);
	
		if($rows == 1)
		{
			while($row = mysqli_fetch_assoc($result))
			{
				$table_pwd = $row['password'];
			}
		}
		else
		{
			echo 'error while retriving table_pwd';
		}
		

	if (isset($_POST['update_settings'])) 
	{
		$name = $_POST['name'];
		$name = stripslashes($name);
		$old_pwd = $_POST['old_pwd'];
		$old_pwd = stripslashes($old_pwd);
		$new_pwd = $_POST['new_pwd'];
		$new_pwd = stripslashes($new_pwd);

		if(!empty($_POST['old_pwd']) && !empty($_POST['new_pwd']))
		{
			if($old_pwd == $table_pwd)
			{
				$query = "UPDATE userinfo SET name='$name', password='$new_pwd' WHERE id='$id'";
				mysqli_query($con,$query);
				$rows = mysqli_affected_rows($con);
				if($rows == 1)
				{
					echo '<div class="text-center alert bg-success col-md-offset-4 col-md-4" role="alert"><span>Details updated!</span></div>';
					echo '<script>setTimeout(function () { window.location.href = "home.php";}, 1000);</script>';
				}
				else
				{
					echo '<div class="text-center alert bg-danger col-md-offset-4 col-md-4" role="alert"><span>problem while updating name and password</span></div>';
					
				}
			}
			else
			{
				echo '<div class="text-center alert bg-danger col-md-offset-4 col-md-4" role="alert"><span>check your old password and try again</span></div>';
			}
			
		}
		else
		{
			$query = "UPDATE userinfo SET name='$name' WHERE id='$id'";
			mysqli_query($con,$query);
			$rows = mysqli_affected_rows($con);
			if($rows == 1)
			{
				echo '<div class="text-center alert bg-success col-md-offset-4 col-md-4" role="alert"><span>Details updated!</span></div>';
				echo '<script>setTimeout(function () { window.location.href = "home.php";}, 1000);</script>';

			}
			else
			{
				echo '<div class="text-center alert bg-danger col-md-offset-4 col-md-4" role="alert"><span>problem while updating details</span></div>';
				
			}
		}
		
	}

	return false;
}

/*******************************
 * calculate count of all members.
 *******************************/

function get_all_status()
{
	global $con;
	$query = "SELECT * FROM userinfo";
	$result = mysqli_query($con,$query);
	$rows = mysqli_affected_rows($con);
	return $rows;
}

/*******************************
 * calculate count of all members.
 *******************************/

function get_all_posts()
{
	global $con;
	$query = "SELECT * FROM blog_posts";
	$result = mysqli_query($con,$query);
	$rows = mysqli_affected_rows($con);
	return $rows;
}

/*******************************
 * calculate count of CORE members
 *******************************/

function get_vip_status()
{
	global $con;
	$query = "SELECT * FROM userinfo where role NOT LIKE 'Member'";
	$result = mysqli_query($con,$query);
	$rows = mysqli_affected_rows($con);
	return $rows;
}

/*******************************
 * calculate total sessions.
 *******************************/

function total_sessions()
{
	global $con;
	$query = "SELECT * FROM sessions";
	$result = mysqli_query($con,$query);
	$rows = mysqli_affected_rows($con);
	return $rows;
}

/*******************************
 * calculate complete sessions
 *******************************/

function completed_sessions()
{
	global $con;
	$query = "SELECT * FROM sessions";
	$result = mysqli_query($con,$query);
	$rows = mysqli_affected_rows($con);
	$completed_sessions = 0;
	if($rows == 0)
	{
		$completed_sessions = 0;
	}
	else
	{
		while($row = mysqli_fetch_assoc($result))
		{
			if(time() >= strtotime($row['session_date']))
			{
				$completed_sessions++;
			}
		}
	}
	return $completed_sessions;
}

/*******************************
 * retrive all member data in table format.
 *******************************/

function all_member_table($role)
{
	global $con;
	$role = $role;
	$query = "SELECT * FROM userinfo";
	$result = mysqli_query($con,$query);
	$rows = mysqli_affected_rows($con);
	?>
	<table class="table table-hover table-responsive">
			<tr class="alert-info">
				<th><h4>Id</h4></th>
				<th><h4>Name</h4></th>
				<th><h4>Username</h4></th>
				<th><h4>Email</h4></th>
				<th><h4>Role</h4></th>
				<th><h4>Action</h4></th>
			</tr>
	<?php
	while ($row = mysqli_fetch_assoc($result))
		{
			if(empty($row['email']))
			{
				$row['email'] = '-';
			}
			echo '<tr>
				<td>'.$row['id'].'</td>
				<td>'.$row['name'].'</td>
				<td>'.$row['username'].'</td>
				<td>'.$row['email'].'</td>
				<td>'.$row['role'].'</td>
				<td>';
				
				if($role == "President")
				{
					echo '<a href="edit_member.php?mem_id='.$row['id'].'">Edit</a> | <a href="delete_member.php?mem_id='.$row['id'].'">Remove</a>';
				}
				else
				{
					echo '-';
				}
			
echo '</td></tr>';
		}
	echo '</table>';
	return false;
}

/*******************************
 * Add new member.
 *******************************/

function add_member($role)
{
	global $con;
	$role = $role;

	if (isset($_POST['add_member'])) 
	{
		$name = $_POST['name'];
		$name = stripslashes($name);
		$email = $_POST['email'];
		$email = stripslashes($email);
		$username = $_POST['username'];
		$username = stripslashes($username);
		$password = $_POST['password'];
		$password = stripslashes($password);
		$pic = 'imgs/user.png';

		if($role == 'President')
		{
			$select_role = $_POST["role"];
		}
		else
		{
			$select_role = "-";
		}

		$query = "INSERT into userinfo (name, email, username, password, role, pic) VALUES ('$name', '$email', '$username', '$password', '$select_role', '$pic')";
		$result = mysqli_query($con,$query);
		$rows = mysqli_affected_rows($con);
		if($rows == 1)
		{
			$subject = 'Welcome to Club Manager';
			$html = "Dear $name,<br><br>Welcome to Club Manager! Your account has been successfully created.<br><br>Your login details:<br>Username: $username<br>Password: $password<br><br>You can login at: http://namithar.free.nf<br><br>Best regards,<br>Club Manager Team";
			$text = "Dear $name,\n\nWelcome to Club Manager! Your account has been successfully created.\n\nYour login details:\nUsername: $username\nPassword: $password\n\nYou can login at: http://namithar.free.nf\n\nBest regards,\nClub Manager Team";
			if (send_brevo_email($email, $name, $subject, $html, $text)) {
				echo '<div class="text-center alert alert-success col-md-offset-4 col-md-4"><p><b>Success! Member Added and Welcome Email Sent</b></p></div>';
			} else {
				echo '<div class="text-center alert alert-warning col-md-offset-4 col-md-4"><p><b>Member added but email notification failed</b></p></div>';
			}
			echo '<script>setTimeout(function () { window.location.href = "manage_members.php";}, 1000);</script>';
		}
		else
		{
			echo '<div class="text-center alert alert-danger col-md-offset-4 col-md-4" role="alert"><span>error while adding member, try again</span></div>';
		}
	}

	return false;
}

/*******************************
 * edit member infrmation.
 *******************************/

function edit_member($role,$mem_id)
{
	global $con;
	$role = $role;
	$mem_id = $mem_id;

	if (isset($_POST['edit_member']))
	{
		$edit_name = $_POST['name'];
		$edit_name = stripslashes($edit_name);
		$edit_email = $_POST['email'];
		$edit_email = stripslashes($edit_email);
		$edit_username = $_POST['username'];
		$edit_username = stripslashes($edit_username);
		
		if($role == 'President')
		{
			$edit_select_role = $_POST['role'];
		}
		else
		{
			$edit_select_role = "";
		}

		if(empty($edit_select_role))
		{
			$query = "UPDATE userinfo SET name='$edit_name', email='$edit_email', username='$edit_username' WHERE id='$mem_id'";
		}
		else
		{
			$query = "UPDATE userinfo SET name='$edit_name', email='$edit_email', username='$edit_username', role='$edit_select_role' WHERE id='$mem_id'";
		}
		
		$result = mysqli_query($con,$query);
		$rows = mysqli_affected_rows($con);
		if($rows == 1)
		{
			echo '<div class="text-center alert alert-success col-md-offset-4 col-md-4" role="alert"><span>Success! info updated</span></div>';
			echo '<script>setTimeout(function () { window.location.href = "manage_members.php";}, 1000);</script>';
		}
		else
		{
			echo '<div class="text-center alert alert-danger col-md-offset-4 col-md-4" role="alert"><span>error while updating info, try again</span></div>';
		}
	}

	return false;
}

/*******************************
 * delete member record.
 *******************************/

function delete_member($mem_id,$role)
{
	global $con;
	$mem_id = $mem_id;
	$role = $role;

	if(isset($_POST['yes']))
	{
		$query = "DELETE from userinfo where id='$mem_id'";
		$result = mysqli_query($con,$query);
		$rows = mysqli_affected_rows($con);
		echo mysqli_error($con);
		if($rows == 1)
		{
			echo '<div class="text-center alert alert-success col-md-offset-4 col-md-4" role="alert"><span>Success! Member removed</span></div>';
			echo '<script>setTimeout(function () { window.location.href = "manage_members.php";}, 1000);</script>';
		}
		else
		{
			echo '<div class="text-center alert alert-danger col-md-offset-4 col-md-4" role="alert"><span>error while removing member, try again</span></div>';
		}
	}
	
	return false;
}

/*******************************
 * forgot password function.
 *******************************/

function forgot()
{
	global $con;
	global $email_config;
	$otp = mt_rand(111111, 999999);
	if(isset($_POST['send_code']))
	{
		$email = $_POST['email'];
		$query = "SELECT * from userinfo where email='$email'";
		$result = mysqli_query($con,$query);
		$rows = mysqli_affected_rows($con);
		if($rows == 1)
		{
			$query = "UPDATE userinfo SET otp='$otp' where email='$email'";
			$result = mysqli_query($con,$query);
			$rows = mysqli_affected_rows($con);
			if($rows == 1)
			{
				$subject = 'Club - Password Reset Code';
				$html = "Your password reset code is: <b>$otp</b>";
				$text = "Your password reset code is: $otp";
				if (send_brevo_email($email, $email, $subject, $html, $text)) {
					echo '<div class="text-center alert alert-success col-md-offset-4 col-md-4"><p><b>Password reset code sent to '.$email.' check your mailbox</b></p></div>';
				} else {
					echo '<div class="text-center alert alert-warning col-md-offset-4 col-md-4"><p><b>Failed to send password reset code</b></p></div>';
				}
			}
			else
			{
				echo '<div class="text-center alert alert-danger col-md-offset-4 col-md-4" role="alert"><span>error in generating otp</span></div>';
			}
		}
		else
		{
			echo '<div class="text-center alert alert-danger col-md-offset-4 col-md-4" role="alert"><span>invalid email! try again</span></div>';
		}
	}
	return false;
}

/*******************************
 * show all session and events.
 *******************************/

function show_events($role)
{
	global $con;
	$query = "SELECT * FROM sessions ORDER by session_date ASC";
	$result = mysqli_query($con,$query);
	$rows = mysqli_affected_rows($con);

	if($rows == 0)
	{
		echo '<div class="text-center alert alert-info col-md-offset-4 col-md-4" role="alert"><span>no events scheduled yet!</span></div>';
	}
	
	while($row = mysqli_fetch_assoc($result))
	{
		if(time() >= strtotime($row['session_date']))
		{
			$choose_css = "panel-red";
		}
		else
		{
			$choose_css = "panel-teal";
		}
		?>
			
		<div class="col-md-4">
			<div class="panel <?php echo $choose_css; ?>">
				<div class="panel-heading dark-overlay"><?php echo $row['session_name']; ?></div>
					<div class="panel-body">
						<p>
						<b>Date:</b> <small><?php echo formatDateIST($row['session_date']); ?></small><br>
						<?php echo $row['session_details']; ?>
						</p>
					</div>
					<?php
						if($role == 'President')
		        		{
		        			echo '<div class="panel-footer"><a class="btn btn-primary btn-sm" href="edit_event.php?event_id='.$row['session_id'].'">Edit</a> <a class="btn btn-danger btn-sm pull-right" href="delete_event.php?event_id='.$row['session_id'].'">Delete</a></div>';
		        		}
					?>
			</div>
		</div>
	<?php
	}

	return false;
}

/*******************************
 * events in table format.
 *******************************/

function all_events_table($role)
{
	$role = $role;

	if($role == "President" || $role == "Technical")
	{
		global $con;
		$query = "SELECT * FROM sessions";
		$result = mysqli_query($con,$query);
		$rows = mysqli_affected_rows($con);
		if($rows == 0)
		{
			echo '<div class="col-md-offset-3 col-md-5 alert alert-warning text-center"><b>no event scheduled, schedule event first!</b></div>';
			exit();
		}
		?>
		<table class="table manage-member-panel table-hover table-responsive">
				<tr class="alert-info">
					<th><h4>Id</h4></th>
					<th><h4>Event Title</h4></th>
					<th><h4>Description</h4></th>
					<th><h4>Date</h4></th>
					<th><h4>Action</h4></th>
				</tr>
		<?php
		while ($row = mysqli_fetch_assoc($result))
			{
				echo '<tr>
					<td>'.$row['session_id'].'</td>
					<td>'.$row['session_name'].'</td>
					<td>'.$row['session_details'].'</td>
					<td>'.$row['session_date'].'</td>
					<td><a href="edit_event.php?event_id='.$row['session_id'].'">Edit</a>';
					echo ' | <a href="delete_event.php?event_id='.$row['session_id'].'">Remove</a></td></tr>';
			}
		echo '</table>';
		}
	return false;
}

/*******************************
 * add new event.
 *******************************/

function add_event()
{
	global $con;
	if (isset($_POST['add_event'])) 
	{
		$name = $_POST['name'];
		$name = stripslashes($name);
		$description = $_POST['description'];
		$description = stripslashes($description);
		$date = date('Y-m-d H:i:s', strtotime($_POST['date']));

		$query = "INSERT into sessions (session_name, session_details, session_date) VALUES ('$name', '$description', '$date')";
		$result = mysqli_query($con,$query);
		$rows = mysqli_affected_rows($con);
		if($rows == 1)
		{
			echo '<div class="text-center alert alert-success col-md-offset-4 col-md-4" role="alert"><span>Success! event Added</span></div>';
			echo '<script>setTimeout(function () { window.location.href = "schedule.php";}, 1000);</script>';
		}
		else
		{
			echo '<div class="text-center alert alert-danger col-md-offset-4 col-md-4" role="alert"><span>error while adding event, try again</span></div>';
		}
	}

	return false;
}

/*******************************
 * delete event.
 *******************************/

function delete_event($event_id,$role)
{
	global $con;
	$event_id = $event_id;
	$role = $role;

	if(isset($_POST['yes']))
	{
		$query = "DELETE from sessions where session_id='$event_id'";
		$result = mysqli_query($con,$query);
		$rows = mysqli_affected_rows($con);
		echo mysqli_error($con);
		if($rows == 1)
		{
			echo '<div class="text-center alert alert-success col-md-offset-4 col-md-4" role="alert"><span>Success! Event removed</span></div>';
			echo '<script>setTimeout(function () { window.location.href = "schedule.php";}, 1000);</script>';
		}
		else
		{
			echo '<div class="text-center alert alert-danger col-md-offset-4 col-md-4" role="alert"><span>error while removing session, try again</span></div>';
		}
	}
	
	return false;
}

/*******************************
 * edit event information.
 *******************************/

function edit_event($event_id,$role)
{
	global $con;
	$role = $role;
	$event_id = $event_id;

	if (isset($_POST['edit_event']))
	{
		$name = $_POST['name'];
		$name = stripslashes($name);
		$description = $_POST['description'];
		$description = stripslashes($description);
		// Convert HTML5 datetime-local input to IST format
		$date = date('Y-m-d H:i:s', strtotime($_POST['date']));
		
		$query = "UPDATE sessions SET session_name='$name', session_details='$description', session_date='$date' WHERE session_id='$event_id'";
		$result = mysqli_query($con,$query);
		$rows = mysqli_affected_rows($con);
		if($rows == 1)
		{
			echo '<div class="text-center alert alert-success col-md-offset-4 col-md-4" role="alert"><span>Success! info updated</span></div>';
			echo '<script>setTimeout(function () { window.location.href = "schedule.php";}, 1000);</script>';
		}
		else
		{
			echo '<div class="text-center alert alert-danger col-md-offset-4 col-md-4" role="alert"><span>error while updating info, try again</span></div>';
		}
	}

	return false;
}

/*******************************
 * show present and absent members attendance
 *******************************/

function attendance($session_id,$role)
{
	global $con;
	$session_id = $session_id;

	$query = "SELECT * from attendance where session_id='$session_id'";
	$result = mysqli_query($con,$query);
	$rows = mysqli_affected_rows($con);

	$key = str_rot13($session_id);

	if($rows == 1)
	{
		$attendance_row = mysqli_fetch_assoc($result);
		$string_ids = unserialize($attendance_row['id_array']);
		$marked_by = $attendance_row['marked_by'];
		$marked_at = $attendance_row['marked_at'];
		?>
		<div class="row">
			<div class="col-md-5">
				<table class="table table-responsive">
				<tr class="success"><th>ID</th><th>Present Members Name</th></tr>
		<?php
		if (empty($string_ids)) {
			echo '<tr class="success"><td colspan="2">No one is present.</td></tr>';
		} else {
			foreach($string_ids as $uid) {
				$q = "SELECT * FROM userinfo where id='$uid'";
				$r = mysqli_query($con,$q);
				if($r && mysqli_num_rows($r) > 0) {
					$user = mysqli_fetch_assoc($r);
					echo '<tr class="success"><td>'.$user['id'].'</td><td>'.$user['name'].'</td></tr>';
				}
			}
		}
		?>
				</table>
			</div>
			<div class="col-md-5">
				<table class="table table-responsive">
					<tr class="danger"><th>ID</th><th>Absent Members Name</th></tr>
					<?php
					$all_id_array = array();
					$q = "SELECT id, name FROM userinfo";
					$r = mysqli_query($con,$q);
					while ($user = mysqli_fetch_assoc($r)) {
						$all_id_array[$user['id']] = $user['name'];
					}
					$absent_array = array_diff(array_keys($all_id_array), (array)$string_ids);
					if (empty($absent_array)) {
						echo '<tr class="danger"><td colspan="2">Everyone is present. Nice!</td></tr>';
					} else {
						foreach($absent_array as $uid) {
							echo '<tr class="danger"><td>'.$uid.'</td><td>'.$all_id_array[$uid].'</td></tr>';
						}
					}
					?>
				</table>
			</div>
		</div>
		<div class="row">
			<div class="col-md-10">
				<p><b>Marked by:</b> <?php echo htmlspecialchars($marked_by); ?> &nbsp; <b>Marked at:</b> <?php echo formatDateIST($marked_at); ?></p>
				<?php if($role == "President" || $role == "Technical") {
					echo '<a href="manage_attendance.php?key='.$key.'" class="btn btn-warning">Update Attendance for this Session</a>';
				} ?>
			</div>
		</div>
		<?php
	}
	else
	{
		if($role == "President" || $role == "Technical")
		{
			echo '<br><div class="text-center"><a href="manage_attendance.php?key='.$key.'" class="btn btn-primary">Fill Attendance for this Session</a></div>';
		}
		else
		{
			echo '<div class="text-center alert alert-info col-md-offset-4 col-md-4" role="alert"><span>Attendance is not updated for this session, Please contact your Technical Head or President for attendance!</span></div>';
		}
	}
	return false;
}

/*******************************
 * submit or update attendance in database.
 *******************************/

function do_attendance($key)
{
	global $con;
	global $session_name;
	if(isset($_POST['submit_attendance']))
	{
		$string_ids = isset($_POST['checkbx']) ? serialize($_POST['checkbx']) : serialize([]);
		$marked_by = $session_name;
		$marked_at = getCurrentDateTimeIST();
		$query = "SELECT session_id FROM attendance WHERE session_id='$key'";
		$result = mysqli_query($con,$query);
		$rows = mysqli_affected_rows($con);
		if($rows == 1)
		{
			// Update existing attendance
			$query = "UPDATE attendance SET id_array='$string_ids', marked_by='$marked_by', marked_at='$marked_at' WHERE session_id='$key'";
			$result = mysqli_query($con,$query);
			echo mysqli_error($con);
			if(mysqli_affected_rows($con) == 1) {
				echo '<div class="text-center alert alert-success col-md-offset-4 col-md-4" role="alert"><span>Success! Attendance updated!</span></div>';
			} else {
				echo '<div class="text-center alert alert-warning col-md-offset-4 col-md-4" role="alert"><span>No changes made to attendance.</span></div>';
			}
		}
		else
		{
			// Insert new attendance
			$query = "INSERT into attendance (session_id, id_array, marked_by, marked_at) VALUES ('$key', '$string_ids', '$marked_by', '$marked_at')";
			$result = mysqli_query($con,$query);
			echo mysqli_error($con);
			if(mysqli_affected_rows($con) == 1) {
				echo '<div class="text-center alert alert-success col-md-offset-4 col-md-4" role="alert"><span>Success! Attendance saved!</span></div>';
			} else {
				echo '<div class="text-center alert alert-danger col-md-offset-4 col-md-4" role="alert"><span>Error while saving attendance, try again</span></div>';
			}
		}
		echo '<script>setTimeout(function () { window.location.href = "attendance.php";}, 1000);</script>';
		exit();
	}
	return false;
}

/*******************************
 * Display Notice board.
 *******************************/

function show_notice($role)
{
	global $con;
	$query = "SELECT * FROM notice ORDER by date DESC";
	$result = mysqli_query($con,$query);
	$rows = mysqli_affected_rows($con);

	if($rows == 0)
	{
		echo '<div class="text-center alert alert-info col-md-offset-4 col-md-4" role="alert"><span>no notice posted yet!</span></div>';
		exit();
	}
	
	$select = 1;
	while($row = mysqli_fetch_assoc($result))
	{
		if($select%2 == 1)
		{
			$css = 'panel-teal';
		}
		else
		{
			$css = 'panel-orange';
		}
		?>

		<div class="col-md-4">
			<div class="panel <?php echo $css; ?>">
			<div class="panel-heading dark-overlay"><?php echo $row['title']; ?></div>
				<div class="panel-body">
					<p>
					<b>Date:</b> <small><?php echo formatDateIST($row['date']); ?></small><br>
					<?php echo $row['description']; ?>
					</p>
				</div>
				<?php
					if($role == 'President')
	        		{
	        			echo '<div class="panel-footer"><a class="btn btn-primary btn-sm" href="edit_notice.php?notice_id='.$row['notice_id'].'">Edit</a> <a class="btn btn-danger btn-sm pull-right" href="delete_notice.php?notice_id='.$row['notice_id'].'">Delete</a></div>';
	        		}
				?>
			</div>
		</div>
		<?php
		$select++;
	}

	return false;
}

/*******************************
 * Add new Notice.
 *******************************/

function add_notice()
{
	global $con;
	if (isset($_POST['add_notice'])) 
	{
		$name = $_POST['name'];
		$name = stripslashes($name);
		$description = $_POST['description'];
		$description = stripslashes($description);
		$date = date('Y-m-d H:i:s', strtotime($_POST['date']));

		$query = "INSERT into notice (title, description, date) VALUES ('$name', '$description', '$date')";
		$result = mysqli_query($con,$query);
		$rows = mysqli_affected_rows($con);
		if($rows == 1)
		{
			echo '<div class="text-center alert alert-success bg-success col-md-offset-4 col-md-4" role="alert" style="color: #fff;"></b>Success! Notice Added</b></div>';
			echo '<script>setTimeout(function () { window.location.href = "notice.php";}, 1000);</script>';
		}
		else
		{
			echo '<div class="text-center alert alert-success bg-success col-md-offset-4 col-md-4" role="alert" style="color: #fff;"><b>error while adding notice</b></div>';
		}
	}

	return false;
}

/*******************************
 * delete notice.
 *******************************/

function delete_notice($notice_id,$role)
{
	global $con;
	$notice_id = $notice_id;
	$role = $role;

	if(isset($_POST['yes']))
	{
		$query = "DELETE from notice where notice_id='$notice_id'";
		$result = mysqli_query($con,$query);
		$rows = mysqli_affected_rows($con);
		echo mysqli_error($con);
		if($rows == 1)
		{
			echo '<div class="text-center alert alert-success col-md-offset-4 col-md-4" role="alert"><span>Success! Notice removed</span></div>';
			echo '<script>setTimeout(function () { window.location.href = "notice.php";}, 1000);</script>';
		}
		else
		{
			echo '<div class="text-center alert alert-danger col-md-offset-4 col-md-4" role="alert"><span>error while removing notice, try again</span></div>';
		}
	}
	
	return false;
}

/*******************************
 * edit notice information.
 *******************************/

function edit_notice($notice_id,$role)
{
	global $con;
	$role = $role;

	if (isset($_POST['edit_notice']))
	{
		$name = $_POST['name'];
		$name = stripslashes($name);
		$description = $_POST['description'];
		$description = stripslashes($description);
		// Convert HTML5 datetime-local input to IST format
		$date = date('Y-m-d H:i:s', strtotime($_POST['date']));
		
		$query = "UPDATE notice SET title='$name', description='$description', date='$date' WHERE notice_id='$notice_id'";
		$result = mysqli_query($con,$query);
		$rows = mysqli_affected_rows($con);
		if($rows == 1)
		{
			echo '<div class="text-center alert alert-success bg-success col-md-offset-4 col-md-4" role="alert" style="color: #fff;"></b>Success! Notice Edited</b></div>';
			echo '<script>setTimeout(function () { window.location.href = "notice.php";}, 1000);</script>';
		}
		else
		{
			echo '<div class="text-center alert alert-danger bg-danger col-md-offset-4 col-md-4" role="alert" style="color: #fff;"></b>error while editing notice</b></div>';
		}
	}

	return false;
}

/*******************************
 * starter for every page.
 *******************************/

function starter($id,$name,$role,$pic,$last_login,$total_members,$core_members,$total_sessions,$completed_sessions)
{
	?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Club Manager - Dashboard</title>
<link rel='shortcut icon' href='favicon.ico' type='image/x-icon'/ >
<link href="css/pace-theme-corner-indicator.css" rel="stylesheet">
<script src="js/pace.min.js"></script>
<script>pace.start();</script>
<link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
<link href="css/styles.css" rel="stylesheet">
<script src="https://use.fontawesome.com/c250a4b18e.js"></script>
<link href="https://fonts.googleapis.com/css?family=Open+Sans" rel="stylesheet">
</head>
<body>
	<nav class="navbar navbar-inverse navbar-fixed-top" role="navigation">
		<div class="container-fluid">
			<div class="navbar-header">
				<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#sidebar-collapse">
					<span class="sr-only">Toggle navigation</span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
				</button>
				<b><a class="navbar-brand" href="home.php"><span>Club</span>Manager</a></b>
				<ul class="user-menu">
					<li class="dropdown pull-right">
						<a class="dropdown-toggle" data-toggle="dropdown"><img src="<?php echo $pic; ?>" class="img-responsive img-circle img-thumbnail" height="35px" width="35px"> <b id="mobhide"><?php echo $name; ?></b> <div class="btn btn-xs btn-info" id="mobhide"><?php echo $role; ?></div><span class="caret"></span></a>

						<ul class="dropdown-menu" role="menu">
							<li><a href="update_pic.php"><i class="fa fa-user" aria-hidden="true"></i> Change Profile Pic</a></li>
							<li><a href="user_settings.php?user_id=<?php echo $id; ?>"><i class="fa fa-cog" aria-hidden="true"></i> Settings</a></li>
							<li><a href="logout.php"><i class="fa fa-sign-out" aria-hidden="true"></i> Logout</a></li>
						</ul>
					</li>
				</ul>
			</div>			
		</div><!-- /.container-fluid -->
	</nav><br>
		<div id="sidebar-collapse" class="col-sm-3 col-lg-2 sidebar">
		<form role="search" action="search.php" method="post">
			<div class="form-group">
				<input type="text" name="term" class="form-control" placeholder="Search" required>
			</div>
		</form>
		<ul class="nav menu">
			<li><a href="home.php"><i class="fa fa-tachometer" aria-hidden="true"></i>
 <b>Dashboard</b></a></li>

			<li><a href="blog-home.php"><i class="fa fa-pencil-square-o" aria-hidden="true"></i> <b>Blog</b></a></li>

			<li><a href="notice.php"><i class="fa fa-sticky-note-o" aria-hidden="true"></i> <b>Club Notice</b></a></li>

			<li><a href="attendance.php"><i class="fa fa-line-chart" aria-hidden="true"></i> <b>Attendance</b></a></li>

			<?php if($role == 'President'){
				echo '<li><a href="manage_members.php"><i class="fa fa-users" aria-hidden="true"></i> <b>Members</b></a></li>';
			} ?>
			
			<li><a href="schedule.php"><i class="fa fa-calendar" aria-hidden="true"></i> <b>Sessions</b></a></li>

			<li role="presentation" class="divider"></li>
			<li><a style="color: #000;"><i class="fa fa-clock-o" aria-hidden="true"></i> <b>last login</b><br><?php echo $last_login; ?></a></li>
			<li role="presentation" class="divider"></li>
		</ul>
		<div class="text-center" style="margin-top: 95px; color: #000;"></div>
	</div><!--/.sidebar-->
	
	<div class="col-sm-9 col-sm-offset-3 col-lg-10 col-lg-offset-2 main">
	<?php
	return false;
}

function at_bottom()
{
	?>
	</div>	<!--/.main-->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
<link rel="stylesheet" type="text/css" href="css/DateTimePicker.min.css" />
<script type="text/javascript" src="js/DateTimePicker.min.js"></script>
<!-- include summernote css/js-->
<link href="http://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.2/summernote.css" rel="stylesheet">
<script src="http://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.2/summernote.js"></script>
	<script>
		$(document).ready(function()
		{
		    $("#dtBox").DateTimePicker();
			$('.menu').on("click",".menu",function(e){ 
  			e.preventDefault(); // cancel click
  			var page = $(this).attr('href');   
  			$('.menu').load(page);
			});
			$('#content').summernote({
    			height: 350,
   			 });
		});
	</script>
	<script>
		
		!function ($) {
		    $(document).on("click","ul.nav li.parent > a > span.icon", function(){          
		        $(this).find('em:first').toggleClass("glyphicon-minus");      
		    }); 
		    $(".sidebar span.icon").find('em:first').addClass("glyphicon-plus");
		}(window.jQuery);

		$(window).on('resize', function () {
		  if ($(window).width() > 768) $('#sidebar-collapse').collapse('show')
		})
		$(window).on('resize', function () {
		  if ($(window).width() <= 767) $('#sidebar-collapse').collapse('hide')
		})
	</script>
</body>
</html>
	<?php
	return false;
}

/**********************************************************************************
*****************************   Blog functions    *********************************
**********************************************************************************/

function show_posts($role,$session_name)
{
	global $con;
	$query = "SELECT * FROM blog_posts ORDER BY id DESC";
	$result = mysqli_query($con,$query);

	if(mysqli_num_rows($result) > 0)
	{
		$select = 1;
		while($row = mysqli_fetch_assoc($result))
		{
			if($select%2 == 1)
			{
				$css = 'panel-primary';
			}
			else
			{
				$css = 'panel-info';
			}
			?>

			<div class="col-lg-5">
				<div class="panel <?php echo $css; ?>">
				<div class="panel-heading">
				<?php echo $row['postTitle']; ?>
				</div>
				<div class="panel-body">
				<p>Posted by <b><?php echo $row['auther']; ?></b> on <b><?php echo formatDateIST($row['post_date']); ?></b> in 
				<a href="viewbycat.php?cat=<?php echo $row['catinfo']; ?>"><?php echo $row['catinfo']; ?></a>
					<br><br>
			    <p><?php echo $row['description']; ?></p>
			    </div>               
			    <div class="panel-footer">
			    <?php
			    	if($session_name == $row['auther'] || $role == 'President')
			    	{?>
			    		<a class="btn btn-warning" href="edit-post.php?id=<?php echo $row['id']; ?>&title=<?php echo $row['postTitle']; ?>">Edit</a>
			    		<a class="btn btn-danger" href="delete-post.php?id=<?php echo $row['id']; ?>&title=<?php echo $row['postTitle']; ?>">Delete</a> 
			    	<?php }
			   	?>
			    <a class="btn btn-primary" href="viewpost.php?id=<?php echo $row['id']; ?>&title=<?php echo $row['postTitle']; ?>">Read More</a>      
			    </div></div></div>
			    <?php
			    $select++;
		}

	}
	else
	{
		echo '<div class="alert bg-warning text-center col-md-offset-4 col-md-4 col-sm-12"><span><h4>no posts found, visit after sometime!</h4></span></div>';
	}
	return false;
}

function new_post()
{
	global $con;

	$auther = $_SESSION['username'];

	if(isset($_POST['publish'])) 
	{

		$postTitle = $_POST['postTitle'];
		$postTitle = stripslashes($postTitle);
		$postTitle = mysqli_real_escape_string($con,$postTitle);

		$description = $_POST['description'];
		$description = stripslashes($description);
		$description = mysqli_real_escape_string($con,$description);

		$content = $_POST['content'];
		$content = stripslashes($content);
		$content = mysqli_real_escape_string($con,$content);

		$catvalue = $_POST['cats'];
		$catvalue = stripslashes($catvalue);

		$query = "INSERT INTO blog_posts (id, postTitle, description, content, post_date, auther, catinfo) VALUES (NULL, '$postTitle', '$description', '$content', NOW(), '$auther','$catvalue')";
		mysqli_query($con,$query);
		
		$rows = mysqli_affected_rows($con);

		if($rows == 1)
		{
			echo '<div class="text-center alert alert-success col-md-offset-4 col-md-4"><p><b>Success! Post Published</b></p></div>';
			echo '<script>setTimeout(function () { window.location.href = "blog-home.php";}, 1000);</script>';
		}
		else
		{
			echo '<div class="text-center alert alert-danger col-md-offset-4 col-md-4"><p><b>Sorry, error while publishing post, try again</b></p></div>';	
		}

	}

	return false;
}

function edit_post($post_id)
{
	global $con;
	if (isset($_POST['update'])) 
	{
		$postTitle = $_POST['postTitle'];
		$postTitle = stripslashes($postTitle);
		$postTitle = mysqli_real_escape_string($con,$postTitle);

		$description = $_POST['description'];
		$description = stripslashes($description);
		$description = mysqli_real_escape_string($con,$description);

		$content = $_POST['content'];
		$content = stripslashes($content);
		$content = mysqli_real_escape_string($con,$content);

		$catvalue = $_POST['cats'];
		$catvalue = stripslashes($catvalue);

		$query = "UPDATE blog_posts SET postTitle='$postTitle',description='$description',content='$content',catinfo='$catvalue' WHERE id='$post_id'";

		mysqli_query($con,$query);

		$rows = mysqli_affected_rows($con);

			if($rows == 1)
			{
				echo '<div class="text-center alert alert-success col-md-offset-4 col-md-4"><p><b>Success! Post Updated</b></p></div>';
				echo '<script>setTimeout(function () { window.location.href = "blog-home.php";}, 1000);</script>';
			}
			else
			{
				echo '<div class="text-center alert alert-danger col-md-offset-4 col-md-4"><p><b>Error, post updating failed, try again</b></p></div>';
				
			}
	}
	return false;
}

function delete_post($post_id)
{
	global $con;

	if(isset($_POST['yes']))
	{
		$query = "DELETE FROM blog_posts WHERE id='$post_id'";
		mysqli_query($con,$query);
		$rows = mysqli_affected_rows($con);
		if($rows == 1)
		{
			echo '<div class="text-center alert alert-success col-md-offset-4 col-md-4"><p><b>Success! Post Deleted</b></p></div>';
				echo '<script>setTimeout(function () { window.location.href = "blog-home.php";}, 1000);</script>';
		}
		else
		{
			echo '<div class="text-center alert alert-danger col-md-offset-4 col-md-4"><p><b>Error, post updating failed, try again</b></p></div>';
		}
	}
	return false;
}

function show_home_posts()
{
	global $con;

	$query = "SELECT * FROM blog_posts ORDER BY id DESC LIMIT 0,5";
	$result = mysqli_query($con,$query);

	if(mysqli_num_rows($result) > 0)
	{
		$select = 1;
		while($row = mysqli_fetch_assoc($result))
		{
			if($select%2 == 1)
			{
				$css = 'panel-teal';
			}
			else
			{
				$css = 'panel-orange';
			}
			?>

			<div class="col-lg-4">
				<div class="panel <?php echo $css; ?>">
				<div class="panel-body">
				<a href="viewpost.php?id=<?php echo $row['id']; ?>&title=<?php echo $row['postTitle']; ?>" style="color: #fff;">
				<h3 style="color: #fff;"><?php echo $row['postTitle']; ?></h3>
				<a href="viewpost.php?id=<?php echo $row['id']; ?>&title=<?php echo $row['postTitle']; ?>" style="color: #fff;">
				<p>Posted by <b><?php echo $row['auther']; ?></b> on <b><?php echo formatDateIST($row['post_date']); ?></b> in 
				<b><a style="color: #fff;" href="viewbycat.php?cat=<?php echo $row['catinfo']; ?>"><?php echo $row['catinfo']; ?></a></b></p>
			    </a>
			    </a>
			    </div>               
			    </div>
			</div>
			    <?php
			    $select++;
		}

	}
	else
	{
		echo '<div class="alert bg-warning text-center col-md-offset-4 col-md-4 col-sm-12"><span><h4>no posts found, visit after sometime!</h4></span></div>';
	}

	return false;
}
?>