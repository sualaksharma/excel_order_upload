<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            text-align: center;
        }

        .container {
            background-color: #ffffff;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 300px;
            margin: 0 auto;
            padding: 20px;
            margin-top: 50px;
        }

        h1 {
            color: #333;
        }

        label {
            display: block;
            margin-bottom: 10px;
        }

        input[type="text"],
        input[type="password"] {
            width: calc(100% - 20px); /* Adjust the width to account for the margin */
            padding: 10px;
            margin-bottom: 10px;
            margin-right: 10px; /* Add margin to the right side */
            border: 1px solid #ccc;
            border-radius: 3px;
        }

        input[type="submit"] {
            background-color: #007BFF;
            color: #fff;
            padding: 10px 15px;
            border: none;
            border-radius: 3px;
            cursor: pointer;
        }

        input[type="submit"]:hover {
            background-color: #0056b3;
        }

        .error-message {
            color: red;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Login</h1>
        
        <?php
        // Check if the user is already logged in
        session_start();
        if (isset($_SESSION['user_authenticated']) && $_SESSION['user_authenticated'] === true) {
            header('Location: reports.php');
            exit();
        }

        // Check if the user submitted the login form
        if (isset($_POST['username']) && isset($_POST['password'])) {
            $username = $_POST['username'];
            $password = $_POST['password'];

            // Check if the provided credentials are correct
            if ($username === 'sualak_sharma' && $password === 'billionaire@291100') {
                // Authentication successful, set session variable
                $_SESSION['user_authenticated'] = true;

                // Redirect to the main page
                header('Location: reports.php');
                exit();
            } else {
                // Authentication failed, display an error message
                echo "<p class='error-message'>Invalid credentials. Please try again.</p>";
            }
        }
        ?>

        <form method="POST" action="">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required>
            
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>

            <input type="submit" value="Login">
        </form>
    </div>
</body>
</html>
