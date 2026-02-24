<?php
// --------------------
// CONFIG
// --------------------
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
require "../config/db.php"; // adjust ONLY if your db path changes

$error = "";

// --------------------
// DETECT ROLE FROM FOLDER
// --------------------
$currentDir = basename(__DIR__); // tenant OR landlord

if ($currentDir !== 'tenant' && $currentDir !== 'landlord') {
    die("Invalid login location");
}

$expectedRole = $currentDir; // role must match folder name

// --------------------
// HANDLE LOGIN
// --------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $login    = trim($_POST['login'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($login === '' || $password === '') {
        $error = "All fields are required";
    } else {

        $sql = "SELECT * FROM users
                WHERE (username = ? OR email = ?)
                AND role = ?
                LIMIT 1";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", $login, $login, $expectedRole);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($user = $result->fetch_assoc()) {

            if (password_verify($password, $user['password'])) {

                $_SESSION['user_id'] = $user['id'];
                $_SESSION['name']    = $user['name'];
                $_SESSION['role']    = $user['role'];

                header("Location: dashboard.php");
                exit;
            }
        }

        $error = "Invalid login credentials";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= ucfirst($expectedRole) ?> Login</title>
    <link rel="icon" type="image/png" sizes="64x64" href="../images/favicon.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body {
              background-color: #f4f4f4;
              font-family: Arial, sans-serif;
              display: flex;
              justify-content: center;
              align-items: center;
              height: 100vh;
              margin: 0;
              overflow: hidden;
        }
        .box {
                 background: #0F766E;
                 padding: 30px;
                 border-radius: 8px;
                 box-shadow: 0 0 10px rgba(0,0,0,0.1);
                 text-align: center;
                 width: 300px;
                 position: relative;
        }
        h2 {
            text-align: center;
            margin-bottom: 15px;
            color: #F59E0B;
        }
         input[type="text"],
         input[type="password"] {
           width: 100%;
           padding: 10px;
           margin-bottom: 15px;
           border-radius: 4px;
           border: 1px solid #ccc;
           font-size: 14px;
           box-sizing: border-box;
         }

        
         button {
           width: 100%;
           background: #F59E0B;
           color: white;
           border: none;
           border-radius: 4px;
           font-size: 16px;
           cursor: pointer;
           padding: 10px;
         }

           button:hover {
            background-color: #0090c0;
          }

        .error {
            color: red;
            text-align: center;
            margin-bottom: 10px;
            font-size: 14px;
        }
          .password-container {
      position: relative;
    }

    .password-container input {
      padding-right: 40px;
    }

    .password-container .toggle-password {
      position: absolute;
      right: 10px;
      top: 35%;
      transform: translateY(-50%);
      cursor: pointer;
      color: #666;
    }
    </style>
</head>
<body>

<div class="box">
    <h2><?= ucfirst($expectedRole) ?> Login</h2>

    <?php if ($error): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST">
        <input type="text" name="login" placeholder="Username or Email" required>
        <div class="password-container">
        <input type="password" id="password" name="password"  placeholder="Password" required>
         <i class="fas fa-eye toggle-password" id="togglePassword"></i>
    </div>
        <button type="submit">Login</button>
    </form>
</div>

<script>
  // Toggle password
  const passwordInput = document.getElementById("password");
  const togglePassword = document.getElementById("togglePassword");

  togglePassword.addEventListener("click", () => {
    const type = passwordInput.type === "password" ? "text" : "password";
    passwordInput.type = type;
    togglePassword.classList.toggle("fa-eye-slash");
  });


</script>

</body>
</html>

