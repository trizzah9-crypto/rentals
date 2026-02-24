<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>

<style>

     * {
      box-sizing: border-box;
    }

    body {
      margin: 0;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      background: #eef2f3;
      padding: 20px;
    }

    /* MAIN WRAPPER */
    .wrapper {
      display: flex;
      width: 90%;
      max-width: 1000px;
      height: 520px;
      background: #ffffff;
      border-radius: 18px;
      box-shadow: 0 15px 40px rgba(0,0,0,0.12);
      overflow: hidden;
      animation: fadeSlide 0.7s ease forwards;
    }

    @keyframes fadeSlide {
      0% { opacity:0; transform: translateY(40px); }
      100% { opacity:1; transform: translateY(0); }
    }

  
    /* RIGHT SIDE (FORM) */
    .right {
      width: 50%;
      padding: 40px;
      display: flex;
      flex-direction: column;
      justify-content: center;
      text-align: center;
    }

    form {
      width: 100%;
      text-align: center;
    }

    .logo {
      width: 120px;
      margin-bottom: 20px;
      filter: drop-shadow(0 0 3px rgba(0,0,0,0.15));
    }

    h2 {
      margin-bottom: 26px;
      color: #11998e;
      font-weight: 700;
      letter-spacing: 1px;
    }

    input[type="email"],
    input[type="password"] {
      width: 100%;
      padding: 12px 14px;
      margin-bottom: 18px;
      border-radius: 10px;
      border: 1.5px solid #d3d3d3;
      font-size: 15px;
      transition: all 0.3s ease;
    }

    input:focus {
      border-color: #11998e;
      box-shadow: 0 0 8px rgba(17,153,142,0.4);
      background-color: #e5f7f2;
    }

    .password-container {
      position: relative;
      width: 100%;
    }

    .password-container input {
      width: 100%;
      padding-right: 45px;
    }

    .toggle-password {
      position: absolute;
      right: 12px;
      top: 50%;
      transform: translateY(-50%);
      cursor: pointer;
      color: #777;
      font-size: 18px;
    }

    button {
      width: 100%;
      background: linear-gradient(45deg, #38ef7d, #11998e);
      color: white;
      border: none;
      border-radius: 12px;
      font-size: 17px;
      font-weight: 700;
      cursor: pointer;
      padding: 14px 0;
      box-shadow: 0 8px 18px rgba(56, 239, 125, 0.5);
      transition: 0.25s ease;
      margin-top: 10px;
    }

    button:hover {
      transform: scale(1.05);
      box-shadow: 0 12px 25px rgba(0,0,0,0.15);
    }

    #errorMsg {
      color: #c62828;
      font-weight: 600;
      min-height: 20px;
    }

    p#noAccount {
      margin-top: 16px;
      font-size: 14px;
      color: #555;
    }

    p#noAccount a {
      color: #11998e;
      font-weight: 600;
      text-decoration: none;
    }

    @media (max-width: 850px) {
      .wrapper {
        flex-direction: column;
        height: auto;
      }
      .left, .right {
        width: 100%;
      }
      .left {
        padding: 40px 25px;
      }
    }
        </style>

</head>
<body>

<?php if (isset($_GET['expired'])): ?>
<div class="alert alert-warning text-center">
    Session expired due to inactivity. Please log in again.
</div>
<?php endif; ?>


        <div class="wrapper">

        
<h3>Login</h3>

<div class="right">
<form  class="login" id="loginForm">
    <input type="email" id="email" placeholder="Email" required><br><br>
    <div class="password-container">
    <input type="password" id="password" placeholder="Password" required>
     <i class="fas fa-eye toggle-password" id="togglePassword"></i>
    <br><br>
    </div>
    <button type="submit">Login</button>

</form>
</div>
</div>
<script src="assets/js/auth.js"></script>
  
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
  // Toggle password
  const togglePassword = document.getElementById("togglePassword");

  togglePassword.addEventListener("click", () => {
    const type = passwordInput.type === "password" ? "text" : "password";
    passwordInput.type = type;
    togglePassword.classList.toggle("fa-eye-slash");
  });


</script>

</body>
</html>
