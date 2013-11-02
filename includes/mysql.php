<?php
function dosql($con, $sql, $echo) {
	$rv = mysqli_query($con, $sql);

	if (!is_null($echo) && $echo) {
		$sql = explode(' ', $sql, 2);

		$object_type = '';
		$object_name = '';
		switch (strtoupper($sql[0])) {
			case 'DROP':
			case 'CREATE':
				$object_type = explode(' ', $sql[1], 3);
				$object_name = $object_type[1];
				$object_type = strtolower($object_type[0]);
				break;

			case 'GRANT':
				$object_type = 'privileges';
				$object_name = explode(' ON ', $sql[1], 3);
				$object_name[1] = explode(' ', $object_name[1], 2);
				$object_name[1] = $object_name[1][0];
				$object_name = $object_name[0] . ' on ' . $object_name[1];
				break;

			case 'FLUSH':
				$object_type = strtolower($sql[1]);
				$object_name = '';
				break;

			case 'INSERT':
				$object_type = 'records into';
				$object_name = explode(' ', $sql[1], 3);
				$object_name = $object_name[1];
				break;

			case 'DELETE':
				$object_type = 'records from';
				$object_name = explode(' ', $sql[1], 3);
				$object_name = $object_name[1];
				break;

			case 'USE':
				$object_type = 'database';
				$object_name = $sql[1];
				break;
		}

		$command = strtolower($sql[0]);
		switch (strtoupper($sql[0])) {
			case 'DROP':
				$command = 'dropping';
				break;

			case 'CREATE':
				$command = 'creating';
				break;

			case 'GRANT':
				$command = 'Granting';
				break;

			case 'FLUSH':
				$command = 'flushing';
				break;

			case 'INSERT':
				$command = 'inserting';
				break;

			case 'DELETE':
				$command = 'deleting';
				break;

			case 'USE':
				$command = 'switching to';
				break;
		}
		
		if ($rv)
			echo '<p>' . strtoupper(substr($command, 0, 1)) . substr($command, 1) . ' ' . $object_type . ' ' . $object_name . ' successful.</p>';
		else
			echo '<p>Error ' . $command . ' ' . $object_type . ' ' . $object_name . ': ' . mysqli_error($con) . '</p>';
	}

	return $rv;
}

function listsql($con, $sql, $echo) {
	$table = explode(' FROM ', $sql, 2);
	$table = explode(' ', $table[1] . ' ', 2);
	$table = str_replace('mysql.', '', strtolower($table[0]));

	$result = dosql($con, $sql, false);

	if ($result) {
		echo '<p>Records in ' . $table . ':</p><ul>';
		while ($row = mysqli_fetch_row($result)) {
			echo '<li><p>';
			for ($col = 0; $col < count($row); $col++)
				echo ($col > 0 ? ' -- ' : '') . $row[$col];
			echo '</p></li>';
		}
		echo '</ul>';
	}
	else
		echo '<p>Error listing records in ' . $table . ': ' . mysqli_error($con) . '</p>';
}

function create_table($con, $table, $fields, $echo) {
	dosql($con, 'CREATE TABLE ' . $table . ' (' . $fields . ')', $echo);
}
?>