<?php include "secret.php"; ?>

<?php
/*
mod == 1    ввод новых записей                      (modify.php)
mod == 2    редактирование записей                  (index.php -> modify.php)
mod == 3    изменение статуса                       (modify.php)
mod == 4    фильтр для вывода данных                (index.php)
*/
?>

<html>
<head>
  <title>arkusha.ru - todo</title>
  <meta charset="utf-8">
  <link rel="stylesheet" href="style.css">
</head>
<body background="bg1.png">

<a href=""><img src="todo_s.png"></a><br>

<?php
// LOGIN CHECK
$loggedin = false;
if (isset($_COOKIE['secret'])) {
    if ($_COOKIE['secret'] == md5($page_password)) {
        $loggedin = true;
    } else {
		setcookie("secret", "", time()-3600);
	}
} else {
    if (isset($_POST["code"])) {
        $code = $_POST["code"];
        if ($code == $page_password) {
            setcookie("secret", md5($page_password), time()+86400*90);
			setcookie("url", $_SERVER['REQUEST_URI'], time()+86400*90);
            $loggedin = true;
        }
    }
}
// LOGIN FORM
if ($loggedin == false) {
    print "<center><form method=post>Пароль: <input name=code type=password><input type=submit></form></center>";
}
?>

<?php
if ($loggedin == true) {
    
    // FILTER
    if (isset($_POST["mod"]) && $_POST["mod"] == 4) {
        if (isset($_POST["filter_deleted"]) && $_POST["filter_deleted"] == "on") {setcookie("filter_deleted", "show", time()+86400*90); $filter_deleted = "show";} else {setcookie("filter_deleted", "hide", time()+86400*90); $filter_deleted = "hide";}
        if (isset($_POST["filter_opened"]) && $_POST["filter_opened"] == "on") {setcookie("filter_opened", "show", time()+86400*90); $filter_opened = "show";} else {setcookie("filter_opened", "hide", time()+86400*90); $filter_opened = "hide";}
        if (isset($_POST["filter_closed"]) && $_POST["filter_closed"] == "on") {setcookie("filter_closed", "show", time()+86400*90); $filter_closed = "show";} else {setcookie("filter_closed", "hide", time()+86400*90); $filter_closed = "hide";}
        if (isset($_POST["filter_priority0"]) && $_POST["filter_priority0"] == "on") {setcookie("filter_priority0", "show", time()+86400*90); $filter_priority0 = "show";} else {setcookie("filter_priority0", "hide", time()+86400*90); $filter_priority0 = "hide";}
        if (isset($_POST["filter_priority1"]) && $_POST["filter_priority1"] == "on") {setcookie("filter_priority1", "show", time()+86400*90); $filter_priority1 = "show";} else {setcookie("filter_priority1", "hide", time()+86400*90); $filter_priority1 = "hide";}
        if (isset($_POST["filter_priority2"]) && $_POST["filter_priority2"] == "on") {setcookie("filter_priority2", "show", time()+86400*90); $filter_priority2 = "show";} else {setcookie("filter_priority2", "hide", time()+86400*90); $filter_priority2 = "hide";}
        if (isset($_POST["filter_priority3"]) && $_POST["filter_priority3"] == "on") {setcookie("filter_priority3", "show", time()+86400*90); $filter_priority3 = "show";} else {setcookie("filter_priority3", "hide", time()+86400*90); $filter_priority3 = "hide";}
        if (isset($_POST["filter_priority4"]) && $_POST["filter_priority4"] == "on") {setcookie("filter_priority4", "show", time()+86400*90); $filter_priority4 = "show";} else {setcookie("filter_priority4", "hide", time()+86400*90); $filter_priority4 = "hide";}
    } else {
        if (!isset($filter_deleted)) {if(!isset($_COOKIE['filter_deleted'])) {$filter_deleted = "show";} else {$filter_deleted = $_COOKIE['filter_deleted'];}}
        if (!isset($filter_opened)) {if(!isset($_COOKIE['filter_opened'])) {$filter_opened = "show";} else {$filter_opened = $_COOKIE['filter_opened'];}}
        if (!isset($filter_closed)) {if(!isset($_COOKIE['filter_closed'])) {$filter_closed = "show";} else {$filter_closed = $_COOKIE['filter_closed'];}}
        if (!isset($filter_priority0)) {if(!isset($_COOKIE['filter_priority0'])) {$filter_priority0 = "show";} else {$filter_priority0 = $_COOKIE['filter_priority0'];}}
        if (!isset($filter_priority1)) {if(!isset($_COOKIE['filter_priority1'])) {$filter_priority1 = "show";} else {$filter_priority1 = $_COOKIE['filter_priority1'];}}
        if (!isset($filter_priority2)) {if(!isset($_COOKIE['filter_priority2'])) {$filter_priority2 = "show";} else {$filter_priority2 = $_COOKIE['filter_priority2'];}}
        if (!isset($filter_priority3)) {if(!isset($_COOKIE['filter_priority3'])) {$filter_priority3 = "show";} else {$filter_priority3 = $_COOKIE['filter_priority3'];}}
        if (!isset($filter_priority4)) {if(!isset($_COOKIE['filter_priority4'])) {$filter_priority4 = "show";} else {$filter_priority4 = $_COOKIE['filter_priority4'];}}
    }
    
    // START QUERY
    $db = pg_connect("host=localhost port=5432 dbname=" . $db_name . " user=" . $db_user . " password=" . $db_password);

    if ($db != false) {
$query =
"SELECT
    task_id,
    task_priority,
    task_status,
    status_description,
    task_description,
    task_comment,
    to_char(task_date_open, 'DD.MM.YYYY HH24:MI'),
    to_char(task_date_deadline, 'DD.MM.YYYY HH24:MI'),
    to_char(task_date_close, 'DD.MM.YYYY HH24:MI'),
    (
	CASE WHEN task_date_deadline IS NOT NULL AND task_status = 2 THEN (
        CONCAT(
            EXTRACT(day FROM task_date_deadline-current_timestamp),
            'д ',
            EXTRACT(hour FROM task_date_deadline-current_timestamp),
            'ч ')
    ) END
    ) as time_left,
    (
	CASE WHEN task_date_deadline IS NOT NULL AND task_status = 2 THEN (
        EXTRACT(day FROM task_date_deadline-current_timestamp)
    ) END
    ) as days_left
FROM ta_task, ta_status
WHERE status_id = task_status ";
        if (isset($filter_deleted) && $filter_deleted == "hide") {$query = $query . "AND task_status != 1 ";}
        if (isset($filter_opened) && $filter_opened == "hide") {$query = $query . "AND task_status != 2 ";}
        if (isset($filter_closed) && $filter_closed == "hide") {$query = $query . "AND task_status != 3 ";}
        if (isset($filter_priority0) && $filter_priority0 == "hide") {$query = $query . "AND task_priority != 0 ";}
        if (isset($filter_priority1) && $filter_priority1 == "hide") {$query = $query . "AND task_priority != 1 ";}
        if (isset($filter_priority2) && $filter_priority2 == "hide") {$query = $query . "AND task_priority != 2 ";}
        if (isset($filter_priority3) && $filter_priority3 == "hide") {$query = $query . "AND task_priority != 3 ";}
        if (isset($filter_priority4) && $filter_priority4 == "hide") {$query = $query . "AND task_priority != 4 ";}
        $query = $query . "ORDER BY task_priority DESC, task_id";
        //print "<font color=grey><i>" . $query . "</i></font>";
        $result = pg_query($db, $query);
        if ($result != false) {

            // MAIN TABLE
            print "<form action=modify.php method=post id=modify_status_form><input type=hidden name=mod value=3><input type=hidden name=task_id id=task_id><input type=hidden name=task_status id=task_status></form>\n";
            print "<form method=post id=modify_task_form><input type=hidden name=mod value=2><input type=hidden name=task_id id=task_id2></form>\n";
            print "<table width=100%>\n";
            print "<tr><td colspan=12><button onclick=document.cookie='secret=';document.location.reload() style='float: right;'>Выйти</button></td></tr>";
            
            print "<tr bgcolor=#bbbbbb class=brow><td class=bb></td><td></td><td align=center class=bl colspan=2><b>Открыта</b><td align=center class=bl><b>Дедлайн</b></td><td align=center class=bl><b>Осталось</b></td><td align=center class=bl colspan=2><b>Закрыта</b></td><td class=bl></td><td><b>Описание</b></td><td class=bl><b>Комментарий</b></td><td class=bb></td></tr>\n";
            while ($row = pg_fetch_row($result)) {
                // COLOR by STATUS
                switch ($row[2]) {
                    case 1: $row_color = "#bbbbbb"; break;
                    case 2: $row_color = "#cceecc"; break;
                    case 3: $row_color = "#f7eeee"; break;
                }
                // COLOR by DEADLINE
                if (!is_null($row[10]) and $row[10] < 4) {$row_color = "#f7f777";}
                if (!is_null($row[10]) and $row[10] < 2) {$row_color = "#f77777";}
                print "<tr bgcolor=" . $row_color ." class=brow>";

                // BUTTON (DELETE)
                print "<td align=center width=30 class=bb><img src=delete.png title=Удалить onclick=document.getElementById('task_id').value=" . $row[0] . ";document.getElementById('task_status').value=1;document.getElementById('modify_status_form').submit()></td>";
                // ID
                //print "<td align=center width=20>" . $row[0] . "</td>";
                // STATUS
                print "<td align=center width=80>";
                switch ($row[2]) {
                    case 1: print "<b><font color=#ffffff>" . $row[3] . "</font></b>"; break;
                    case 2: print "<b><font color=#101010>" . $row[3] . "</font></b>"; break;
                    case 3: print "<b><font color=#101010>" . $row[3] . "</font></b>"; break;
                }
                print "</td>";
                // DATE OPEN
                print "<td align=center width=120 class=bl>" . $row[6] . "</td>";
                // BUTTONS (OPEN)
                print "<td align=center width=30><img src=undo.png title=Открыть onclick=document.getElementById('task_id').value=" . $row[0] . ";document.getElementById('task_status').value=2;document.getElementById('modify_status_form').submit()></td>";
                // DATE DEADLINE
                print "<td align=center width=120 class=bl>" . $row[7] . "</td>";
                // DATE LEFT
                print "<td align=center class=bl><b>" . $row[9] . "</b></td>";
                
                // BUTTONS (CLOSE)
                print "<td align=center width=30 class=bl><img src=yes.png title=Закрыть onclick=document.getElementById('task_id').value=" . $row[0] . ";document.getElementById('task_status').value=3;document.getElementById('modify_status_form').submit()></td>";
                // DATE CLOSE
                print "<td align=center width=120>" . $row[8] . "</td>";
                // PRIORITY
                print "<td align=center width=20 class=bl>";
                switch ($row[1]) {
                    case 1: print "<img src=low.png title='Приоритет 1'>"; break;
                    case 2: print "<img src=med.png title='Приоритет 2'>"; break;
                    case 3: print "<img src=high.png title='Приоритет 3'>"; break;
                    case 4: print "<img src=highest.png title='Приоритет 4'>"; break;
                }
                print "</td>";
                // DESCRIPTION
                print "<td><pre>" . $row[4] . "</pre></td>";
                // COMMENT
                print "<td width=250 class=bl><pre>" . $row[5] . "</pre></td>";
                // BUTTONS (EDIT)
                print "<td align=center width=30 class=bb><img src=modify.png title=Редактировать onclick=document.getElementById('task_id2').value=" . $row[0] . ";document.getElementById('modify_task_form').submit()></td>";

                print "</tr>\n";
            }
			
			// FILTER BUTTON BAR
			$button_color_on = "#cceecc";
			$button_color_off = "#bbbbbb";
			print "<tr><td colspan=12>";
			print "<button id=filter_button8 onclick='toggle_filter(8);' style='float: right; background-color: "; if ($filter_priority4 == "show") {print $button_color_on;} else {print $button_color_off;}; print "'>4</button>\n";
			print "<button id=filter_button7 onclick='toggle_filter(7);' style='float: right; background-color: "; if ($filter_priority3 == "show") {print $button_color_on;} else {print $button_color_off;}; print "'>3</button>\n";
			print "<button id=filter_button6 onclick='toggle_filter(6);' style='float: right; background-color: "; if ($filter_priority2 == "show") {print $button_color_on;} else {print $button_color_off;}; print "'>2</button>\n";
			print "<button id=filter_button5 onclick='toggle_filter(5);' style='float: right; background-color: "; if ($filter_priority1 == "show") {print $button_color_on;} else {print $button_color_off;}; print "'>1</button>\n";
			print "<button id=filter_button4 onclick='toggle_filter(4);' style='float: right; background-color: "; if ($filter_priority0 == "show") {print $button_color_on;} else {print $button_color_off;}; print "'>0</button>\n";
			print "<button id=filter_button3 onclick='toggle_filter(3);' style='float: right; background-color: "; if ($filter_deleted == "show") {print $button_color_on;} else {print $button_color_off;}; print "'>Удаленные</button>\n";
			print "<button id=filter_button2 onclick='toggle_filter(2);' style='float: right; background-color: "; if ($filter_closed == "show") {print $button_color_on;} else {print $button_color_off;}; print "'>Закрытые</button>\n";
			print "<button id=filter_button1 onclick='toggle_filter(1);' style='float: right; background-color: "; if ($filter_opened == "show") {print $button_color_on;} else {print $button_color_off;}; print "'>Открытые</button>\n";
			print "<button onclick=document.getElementById('modify_filter_form_hidden').submit() style='float: right;'>Фильтровать</button>\n";
            print "</td></tr>\n";

			print "</table>\n";
        }



        // TASK INPUT
        if (isset($_POST["mod"]) && $_POST["mod"] == 2) {
            $task_id = $_POST["task_id"];
            $query = "SELECT task_priority, task_description, task_comment, to_char(task_date_deadline, 'YYYY-MM-DD'), to_char(task_date_deadline, 'HH24:MI') FROM ta_task WHERE task_id =" . $task_id;
            $result = pg_query($db, $query);
            if ($result != false) {
                $row = pg_fetch_row($result);
                print "<b><u>ИЗМЕНИТЬ ЗАПИСЬ #" . $task_id . "</u></b><br><br>\n";
                if ($row[3] != "") {
                    $date = $row[3];
                    $checked = " checked";
                } else {
                    $date = date("Y-m-d");
                }
                if ($row[4] != "") {
                    $time = $row[4];
                } else {
                    $time = "12:00";
                }
                print "<form action=modify.php method=post>";
                print "<input type=hidden name=mod value=2>";
                print "<input type=hidden name=task_id value=" . $task_id . ">";
                print "<select name=task_priority>";
                for ($i = 0; $i <=4; $i++) {
                    if ($i == $row[0]) {
                        print "<option value=" . $i . " selected>" . $i . "</option>";
                    } else {
                        print "<option value=" . $i . ">" . $i . "</option>";
                    }
                }
                print "</select> приоритет ";
                print "<input type=date name=task_date_deadline value=" . $date . ">";
                print "<input type=time name=task_time_deadline value='" . $time . "'>";
                print "<input type=checkbox name=enable_deadline" . $checked . "> дедлайн?<br>";
                print "<textarea bgcolor=#eeeeee rows=6 cols=100 name=task_description placeholder=Задача>" . $row[1] . "</textarea><br>";
                print "<textarea bgcolor=#eeeeee rows=4 cols=100 name=task_comment placeholder=Комментарий>" . $row[2] . "</textarea><br>";
                print "<input type=submit value=записать>";
                print "</form>\n";
            }
        } else {
            print "<b><u>НОВАЯ ЗАПИСЬ</u></b><br><br>\n";
            print "<form action=modify.php method=post>";
            print "<input type=hidden name=mod value=1>";
            print "<select name=task_priority>";
            for ($i = 0; $i <=4; $i++) {
                print "<option value=" . $i . ">" . $i . "</option>";
            }
            print "</select> приоритет ";
            print "<input type=date name=task_date_deadline value=" . date("Y-m-d") . ">";
            print "<input type=time name=task_time_deadline value='12:00'>";
            print "<input type=checkbox name=enable_deadline> дедлайн?<br>";
            print "<textarea rows=6 cols=100 name=task_description placeholder=Задача></textarea><br>";
            print "<textarea rows=4 cols=100 name=task_comment placeholder=Комментарий></textarea><br>";
            print "<input type=submit class=button value=Записать>";
            print "</form>\n";
        }
		

        pg_close($db);
    }
}
?>

<form method="post" id="modify_filter_form_hidden">
<input type="hidden" name="mod" value="4">
<input type="hidden" id="filter1" name="filter_opened" value="<?php if ($filter_opened == "show") {print "on";} else {print "off";}?>">
<input type="hidden" id="filter2" name="filter_closed" value="<?php if ($filter_closed == "show") {print "on";} else {print "off";}?>">
<input type="hidden" id="filter3" name="filter_deleted" value="<?php if ($filter_deleted == "show") {print "on";} else {print "off";}?>">
<input type="hidden" id="filter4" name="filter_priority0" value="<?php if ($filter_priority0 == "show") {print "on";} else {print "off";}?>">
<input type="hidden" id="filter5" name="filter_priority1" value="<?php if ($filter_priority1 == "show") {print "on";} else {print "off";}?>">
<input type="hidden" id="filter6" name="filter_priority2" value="<?php if ($filter_priority2 == "show") {print "on";} else {print "off";}?>">
<input type="hidden" id="filter7" name="filter_priority3" value="<?php if ($filter_priority3 == "show") {print "on";} else {print "off";}?>">
<input type="hidden" id="filter8" name="filter_priority4" value="<?php if ($filter_priority4 == "show") {print "on";} else {print "off";}?>">
</form>




<script>
function toggle_filter(i) {
  if (document.getElementById(''.concat("filter", i)).value == "on") {
	document.getElementById(''.concat("filter_button", i)).style["background-color"] = "<?php print $button_color_off ?>";
	document.getElementById(''.concat("filter", i)).value = "off";
  } else {
	document.getElementById(''.concat("filter_button", i)).style["background-color"] = "<?php print $button_color_on ?>";
	document.getElementById(''.concat("filter", i)).value = "on";
  }
}
</script>

</body>
</html>