<?php include '/srv/www/htdocs/clans-of-macaria/includes/header.php' ?>

	<title>Clans of Macaria - Setup</title>
	<meta property="og:title" content="Clans of Macaria - Setup" />

	<meta name="description" content="Clans of Macaria setup page." />
	<meta property="og:description" content="Clans of Macaria setup page." />
</head>

<body>
<?php

switch ($_GET['setup']) {
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
		<p>Enter database root user password: <input type="text" name="root"></p>
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
			echo "Failed to connect to MySQL: " . mysqli_connect_error();
		else {
			$sql="DROP USER clans@localhost";
			if (mysqli_query($con, $sql))
				echo "User clans deleted successfully";
			else
				echo "Error deleting user clans: " . mysqli_error($con);

			$sql="CREATE USER clans@localhost IDENTIFIED BY 'honeypot'";
			if (mysqli_query($con, $sql))
				echo "User clans created successfully";
			else
				echo "Error creating user clans: " . mysqli_error($con);

			$sql="DROP DATABASE clans_of_macaria";
			if (mysqli_query($con, $sql))
				echo "Database clans_of_macaria deleted successfully";
			else
				echo "Error deleting database: " . mysqli_error($con);

			$sql="CREATE DATABASE clans_of_macaria";
			if (mysqli_query($con, $sql))
				echo "Database clans_of_macaria created successfully";
			else
				echo "Error creating database: " . mysqli_error($con);

			$sql="GRANT ALL PRIVILEGES ON clans_of_macaria.* TO clans@localhost";
			if (mysqli_query($con, $sql))
				echo "User clans given full access to database clans_of_macaria successfully";
			else
				echo "Error giving full access: " . mysqli_error($con);

			$sql="FLUSH PRIVILEGES";
			if (mysqli_query($con, $sql))
				echo "Flushed privileges successfully";
			else
				echo "Error flushing privileges: " . mysqli_error($con);
		}

		mysql_close();

		$con = mysqli_connect('localhost', 'clans', 'honeypot');
		if (mysqli_connect_errno())
			echo "Failed to connect to MySQL: " . mysqli_connect_error();
		else {
			$sql="CREATE TABLE games (name VARCHAR(255))";
			if (mysqli_query($con, $sql))
				echo "Table games created successfully";
			else
				echo "Error creating table: " . mysqli_error($con);
		}

		break;
}

?>
<?php include '/srv/www/htdocs/clans-of-macaria/includes/footer.php' ?>