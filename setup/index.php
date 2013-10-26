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
	case null:
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
			$sql = 'DROP DATABASE clans_of_macaria';
			if (mysqli_query($con, $sql))
				echo '<p>Database clans_of_macaria deleted successfully</p>';
			else
				echo '<p>Error deleting database: ' . mysqli_error($con) . '</p>';

			$sql = 'CREATE DATABASE clans_of_macaria';
			if (mysqli_query($con, $sql))
				echo '<p>Database clans_of_macaria created successfully</p>';
			else
				echo '<p>Error creating database: ' . mysqli_error($con) . '</p>';

			// Switch to new database
			$sql = 'USE clans_of_macaria';
			if (mysqli_query($con, $sql)) {
				echo '<p>Database clans_of_macaria selected successfully</p>';

				// Tables
				$sql = 'CREATE TABLE games (gameid BIGINT NOT NULL AUTO_INCREMENT, name VARCHAR(75) UNIQUE NOT NULL, PRIMARY KEY (gameid))';
				if (mysqli_query($con, $sql))
					echo '<p>Table games created successfully</p>';
				else
					echo '<p>Error creating table games: ' . mysqli_error($con) . '</p>';

				$sql = 'CREATE TABLE players (playerid BIGINT NOT NULL AUTO_INCREMENT, name VARCHAR(75) NOT NULL, username VARCHAR(25) UNIQUE NOT NULL, password CHAR(18) NOT NULL, PRIMARY KEY (playerid))';
				if (mysqli_query($con, $sql))
					echo '<p>Table players created successfully</p>';
				else
					echo '<p>Error creating table players: ' . mysqli_error($con) . '</p>';

				$sql = 'CREATE TABLE clan_types (clan_typeid BIGINT NOT NULL AUTO_INCREMENT, name VARCHAR(75) UNIQUE NOT NULL, image_url VARCHAR(255) NOT NULL, PRIMARY KEY (clan_typeid))';
				if (mysqli_query($con, $sql))
					echo '<p>Table clan_types created successfully</p>';
				else
					echo '<p>Error creating table clan_types: ' . mysqli_error($con) . '</p>';

				$sql = 'CREATE TABLE games_to_players_to_clan_types (gameid BIGINT NOT NULL, playerid BIGINT NOT NULL, clan_typeid BIGINT NOT NULL, FOREIGN KEY (gameid) REFERENCES games(gameid), FOREIGN KEY (playerid) REFERENCES players(playerid), FOREIGN KEY (clan_typeid) REFERENCES clan_types(clan_typeid))';
				if (mysqli_query($con, $sql))
					echo '<p>Table games_to_players_to_clan_types created successfully</p>';
				else
					echo '<p>Error creating table games_to_players_to_clan_types: ' . mysqli_error($con) . '</p>';

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

				// Write out clans database user's password
				if (file_put_contents('access.php', '<' . '?php $clans_of_macaria_password = \'' . str_replace('\'', "\\'", $_GET['clans']) . '\'; ?' . '>') > 0)
					echo '<p>User clans password saved to ' . getcwd() . '/access.php</p>';
				else
					echo '<p>Error writing clans password</p>';

				// Create clans database user
				$sql = 'CREATE USER clans@clans_of_macaria IDENTIFIED BY \'' . str_replace('\'', "\\'", $_GET['clans']) . '\'';
				if (mysqli_query($con, $sql))
					echo '<p>User clans created successfully</p>';
				else
					echo '<p>Error creating user clans: ' . mysqli_error($con) . '</p>';

				$sql = 'GRANT SELECT, INSERT, UPDATE, DELETE ON clans_of_macaria.* TO clans';
				if (mysqli_query($con, $sql))
					echo '<p>User clans given SELECT, INSERT, UPDATE and DELETE access to database clans_of_macaria successfully</p>';
				else
					echo '<p>Error giving full access: ' . mysqli_error($con) . '</p>';

				$sql = 'FLUSH PRIVILEGES';
				if (mysqli_query($con, $sql))
					echo '<p>Flushed privileges successfully</p>';
				else
					echo '<p>Error flushing privileges: ' . mysqli_error($con) . '</p>';

				$conclans = mysqli_connect('localhost', 'clans', $_GET['clans']);
				if (mysqli_connect_errno())
					echo '<p>Failed to connect to MySQL: ' . mysqli_connect_error() . '</p>';
				else {
					echo '<p>User clans connected successfully</p>';
	
					mysqli_close($conclans);
				}
			}
			else
				echo '<p>Error selecting database clans_of_macaria: ' . mysqli_error($con) . '</p>';
		}

		mysqli_close($con);

		break;
}

?>
<?php include $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php' ?>