<?php include $_SERVER['DOCUMENT_ROOT'] . '/includes/mysql.php' ?>
<?php include $_SERVER['DOCUMENT_ROOT'] . '/includes/header.php' ?>
<?php
ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(-1);
?>

	<title>Clans of Macaria - Setup</title>
	<meta property="og:title" content="Clans of Macaria - Setup" />

	<meta name="description" content="Clans of Macaria setup page." />
	<meta property="og:description" content="Clans of Macaria setup page." />
</head>

<body>
<?php

switch (empty($_GET['setup']) ? '' : $_GET['setup']) {
	// no setup parameter
	case '':
?>
	<p>When you click on the setup link below, the system will clear out everything that's currently set up, delete all databases and data, and re-create the game system from scratch.</p>
	<p>YOU WILL LOSE ALL DATA!</p>
	<p>Please make sure you want to delete everything before clicking the SETUP link below.</p>
	<p><a href="?setup=setup">SETUP</a></p>
<?php
		break;

	// confirm - last chance
	case 'setup':
?>
	<p>THIS IS YOUR LAST CHANCE TO CONFIRM THAT YOU DEFINITELY WANT TO SET UP CLANS OF MACARIA!!</p>
	<p>YOU WILL LOSE ALL DATA AND EVERYTHING WILL BE SET UP AGAIN FROM SCRATCH!!</p>
	<p>Only click SETUP below if you definitely want to set up everything from scratch and are happy to loose all data.</p>
	<form action="." method="get">
		<p>Enter MySQL root user password: <input type="text" name="root"></p>
		<p>Choose Clans' database user password: <input type="text" name="clans"></p>
		<p><input type="hidden" name="setup" value="confirm"><input type="submit" name="submit" value="SETUP"></p>
	</form>
<?php
		break;

	// confirmed - so here we go - wipe everything and re-create it all
	case 'confirm':
?>
	<p>BOOM! HERE WE GO!!</p>
<?php
		$con = mysqli_connect('localhost', 'root', $_GET['root']);
		if (mysqli_connect_errno())
			echo '<p>Failed to connect to MySQL: ' . mysqli_connect_error() . '</p>';
		else {
			// Database
			dosql($con, 'DROP DATABASE clans_of_macaria', true);
			dosql($con, 'CREATE DATABASE clans_of_macaria', true);

			// Switch to new database
			if (dosql($con, 'USE clans_of_macaria', true)) {

				// Tables
				create_table($con, 'games', 'gameid BIGINT NOT NULL AUTO_INCREMENT, name VARCHAR(75) UNIQUE NOT NULL, PRIMARY KEY (gameid)', true);
				create_table($con, 'players', 'playerid BIGINT NOT NULL AUTO_INCREMENT, name VARCHAR(75) NOT NULL, username VARCHAR(25) UNIQUE NOT NULL, password CHAR(18) NOT NULL, PRIMARY KEY (playerid)', true);
				create_table($con, 'clan_types', 'clan_typeid BIGINT NOT NULL AUTO_INCREMENT, name VARCHAR(75) UNIQUE NOT NULL, image_url VARCHAR(255) NOT NULL, PRIMARY KEY (clan_typeid)', true);
				create_table($con, 'clans', 'gameid BIGINT NOT NULL, playerid BIGINT NOT NULL, clan_typeid BIGINT NOT NULL, FOREIGN KEY (gameid) REFERENCES games(gameid), FOREIGN KEY (playerid) REFERENCES players(playerid), FOREIGN KEY (clan_typeid) REFERENCES clan_types(clan_typeid)', true);
				create_table($con, 'tile_types', 'tile_typeid BIGINT NOT NULL AUTO_INCREMENT, name VARCHAR(75) UNIQUE NOT NULL, image_url VARCHAR(255) NOT NULL, PRIMARY KEY (tile_typeid)', true);
				create_table($con, 'tiles', 'tileid BIGINT NOT NULL AUTO_INCREMENT, gameid BIGINT NOT NULL, tile_typeid BIGINT NOT NULL, x BIGINT NOT NULL, y BIGINT NOT NULL, clan_typeid BIGINT, male_quantity BIGINT NOT NULL DEFAULT 0, female_quantity BIGINT NOT NULL DEFAULT 0, child_quantity BIGINT NOT NULL DEFAULT 0, PRIMARY KEY (tileid), FOREIGN KEY (gameid) REFERENCES games(gameid), FOREIGN KEY (tile_typeid) REFERENCES tile_types(tile_typeid), FOREIGN KEY (clan_typeid) REFERENCES clan_types(clan_typeid)', true);
				create_table($con, 'material_types', 'material_typeid BIGINT NOT NULL, name VARCHAR(75) UNIQUE NOT NULL, image_url VARCHAR(255) NOT NULL, PRIMARY KEY (material_typeid)', true);
				create_table($con, 'materials', 'tileid BIGINT NOT NULL, material_typeid BIGINT NOT NULL, quantity BIGINT, FOREIGN KEY (tileid) REFERENCES tiles(tileid), FOREIGN KEY (material_typeid) REFERENCES material_types(material_typeid)', true);
				create_table($con, 'tile_types_to_material_types', 'tile_typeid BIGINT NOT NULL, material_typeid BIGINT NOT NULL, new_quantity BIGINT NOT NULL, round_quantity BIGINT NOT NULL, FOREIGN KEY (tile_typeid) REFERENCES tile_types(tile_typeid), FOREIGN KEY (material_typeid) REFERENCES material_types(material_typeid)', true);

				// Summarize what we just created
				$sql = 'SHOW TABLES';
				$result = mysqli_query($con, $sql);
				if ($result) {
					echo '<p>Tables created:</p><ul>';
					while ($row = mysqli_fetch_row($result)) {
		                echo '<li><p>' . $row[0] . '</p><ul>';

						$sql = 'DESCRIBE ' . $row[0];
						$details = mysqli_query($con, $sql);
						if ($details) {
							while ($detail = mysqli_fetch_row($details))
				                echo '<li><p>' . $detail[0] . ' - ' . $detail[1] . ' - NULL=' . $detail[2] . ' - KEY=' . $detail[3] . ' - DEFAULT=' . $detail[4] . ' - ' . $detail[5] . '</p></li>';
						}

						echo '</ul></li>';
					}
        		    echo '</ul>';
				}
				else
					echo '<p>Error listing tables: ' . mysqli_error($con) . '</p>';

				// Drop and create clans database user
				dosql($con, 'DROP USER clans@localhost', true);
				dosql($con, 'CREATE USER clans@localhost IDENTIFIED BY \'' . str_replace('\'', "\\'", $_GET['clans']) . '\'', true);

				dosql($con, 'GRANT SELECT, INSERT, UPDATE, DELETE ON clans_of_macaria.* TO clans@localhost', true);

				dosql($con, 'FLUSH PRIVILEGES', true);

				// List database users
				$sql = 'SELECT CONCAT(User, \'@\', Host), PASSWORD FROM mysql.user'; // WHERE User = \'clans\'';
				$result = mysqli_query($con, $sql);
				if ($result) {
					echo '<p>Users in database:</p><ul>';
					while ($row = mysqli_fetch_row($result))
		                echo '<li><p>' . $row[0] . ' -- ' . $row[1] . '</p></li>';
        		    echo '</ul>';
				}
				else
					echo '<p>Error listing database users: ' . mysqli_error($con) . '</p>';

				// Write out clans database user's password
				if (file_put_contents('access.php', '<' . '?php $clans_of_macaria_password = \'' . str_replace('\'', "\\'", $_GET['clans']) . '\'; ?' . '>') > 0)
					echo '<p>Saving user clans\' password to ' . getcwd() . '/access.php successful.</p>';
				else
					echo '<p>Error writing clans password</p>';

				mysqli_close($con);

				// Test clans user
				$con = mysqli_connect('localhost', 'clans', $_GET['clans']);
				if (mysqli_connect_errno())
					echo '<p>Failed to connect to MySQL as clans: ' . mysqli_connect_error() . '</p>';
				else {
					echo '<p>Connected to MySQL as clans successfully.</p>';

					// List current user
					$sql = 'SELECT CURRENT_USER()';
					$result = mysqli_query($con, $sql);
					if ($result) {
						echo '<p>Current user:</p><ul>';
						while ($row = mysqli_fetch_row($result))
			                echo '<li><p>' . $row[0] . '</p></li>';
	        		    echo '</ul>';
					}
					else
						echo '<p>Error listing user info: ' . mysqli_error($con) . '</p>';

					// Switch to new database
					if (dosql($con, 'USE clans_of_macaria', true)) {
						// Insert test data into games table
						dosql($con, 'INSERT INTO games (name) VALUES (\'test\')', true);

						// List data in games table
						$sql = 'SELECT gameid, name FROM games';
						$result = mysqli_query($con, $sql);
						if ($result) {
							echo '<p>Content of table games:</p><ul>';
							while ($row = mysqli_fetch_row($result))
				                echo '<li><p>' . $row[0] . ', ' . $row[1] . '</p></li>';
		        		    echo '</ul>';
						}
						else
							echo '<p>Error listing table games: ' . mysqli_error($con) . '</p>';

						// Delete test data from games table
						dosql($con, 'DELETE FROM games WHERE name = \'test\'', true);
					}
				}
			}
		}

		mysqli_close($con);

		break;
}

?>
<?php include $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php' ?>