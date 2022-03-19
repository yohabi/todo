<?php include "secret.php"; ?>

<?php
$loggedin = false;
if (isset($_COOKIE['secret'])) {
	if ($_COOKIE['secret'] == md5($page_password)) {
		$loggedin = true;
	}
}

if ($loggedin == true) {
	if (isset($_POST["mod"])) {
		$mod = $_POST["mod"];
		switch ($mod) {
			// ADD LINE
			case 1:
				$task_priority = $_POST["task_priority"];
				$task_description = str_replace("'", "", $_POST["task_description"]);
				$task_comment = str_replace("'", "", $_POST["task_comment"]);
				$enable_deadline = $_POST["enable_deadline"];

				$db = pg_connect("host=localhost port=5432 dbname=" . $db_name . " user=" . $db_user . " password=" . $db_password);
				if ($db != false) {
					if ($enable_deadline == "on") {
						$task_date_deadline = $_POST["task_date_deadline"];
						$task_time_deadline = $_POST["task_time_deadline"];
						$query = "INSERT INTO ta_task (task_priority, task_description, task_comment, task_date_deadline) VALUES (" . $task_priority . ", '" . $task_description . "', '" . $task_comment . "', to_timestamp('" . $task_date_deadline . " " . $task_time_deadline . "', 'YYYY-MM-DD HH24:MI'));";
					} else {
						$query = "INSERT INTO ta_task (task_priority, task_description, task_comment) VALUES (" . $task_priority . ", '" . $task_description . "', '" . $task_comment . "');";
					}
					print $query;
					$result = pg_query($db, $query);
					pg_close($db);
				}
				break;

			// EDIT TASK
			case 2:
				$task_id = $_POST["task_id"];
				$task_priority = $_POST["task_priority"];
				$task_description = str_replace("'", "", $_POST["task_description"]);
				$task_comment = str_replace("'", "", $_POST["task_comment"]);
				$enable_deadline = $_POST["enable_deadline"];

				$db = pg_connect("host=localhost port=5432 dbname=" . $db_name . " user=" . $db_user . " password=" . $db_password);
				if ($db != false) {
					if ($enable_deadline == "on") {
						$task_date_deadline = $_POST["task_date_deadline"];
						$task_time_deadline = $_POST["task_time_deadline"];
						$query = "UPDATE ta_task SET task_priority=" . $task_priority . ", task_description='" . $task_description . "', task_comment='" . $task_comment . "', task_date_deadline=to_timestamp('" . $task_date_deadline . " " . $task_time_deadline . "', 'YYYY-MM-DD HH24:MI') WHERE task_id=" . $task_id;
					} else {
						$query = "UPDATE ta_task SET task_priority=" . $task_priority . ", task_description='" . $task_description . "', task_comment='" . $task_comment . "' WHERE task_id=" . $task_id;
					}
					print $query;
					$result = pg_query($db, $query);
					pg_close($db);
				}
				break;

			// STATUS CHANGE
			case 3:
				$task_id = $_POST["task_id"];
				$task_status = $_POST["task_status"];
			
				$db = pg_connect("host=localhost port=5432 dbname=" . $db_name . " user=" . $db_user . " password=" . $db_password);
				if ($db != false) {
					switch ($task_status) {
						case 1:
							$query = "UPDATE ta_task SET task_status=" . $task_status . " WHERE task_id=" . $task_id;
							break;
						case 2:
							$query = "UPDATE ta_task SET task_status=" . $task_status . ", task_date_close=NULL WHERE task_id=" . $task_id;
							break;
						case 3:
							$query = "UPDATE ta_task SET task_status=" . $task_status . ", task_date_close=" . current_timestamp . " WHERE task_id=" . $task_id;
							break;
					}
					$result = pg_query($db, $query);
					pg_close($db);
				}
				break;

		}
	}
}

header("HTTP/1.1 301 Moved Permanently"); 
header("Location: " . $_COOKIE['url']); 
exit();
?>