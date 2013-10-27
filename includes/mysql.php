<?php
function create_table($con, $table, $fields) {
	if (mysqli_query($con, 'CREATE TABLE ' . $table . ' (' . $fields . ')'))
		echo '<p>Table ' . $table . ' created successfully</p>';
	else
		echo '<p>Error creating table ' . $table . ': ' . mysqli_error($con) . '</p>';
}
?>