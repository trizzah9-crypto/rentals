<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>SaaS Tenant Dashboard</title>

<style>
*{margin:0;padding:0;box-sizing:border-box;}
body{font-family:'Segoe UI',sans-serif;background:#f4f7fb;color:#2d3748;}

.container{display:flex;min-height:100vh;}

.sidebar{
    width:230px;
    background:#1e293b;
    color:#fff;
    padding:25px 20px;
}

.sidebar h2{
    margin-bottom:30px;
    font-size:20px;
}

.sidebar a{
    display:block;
    color:#cbd5e1;
    text-decoration:none;
    padding:10px 12px;
    border-radius:6px;
    margin-bottom:8px;
    transition:0.2s;
}

.sidebar a:hover{
    background:#334155;
    color:#fff;
}
.sidebar .active{background:#2563eb;color:#fff;}

.main{
    flex:1;
    padding:30px;
}

.topbar{
    display:flex;
    justify-content:space-between;
    margin-bottom:25px;
}

.card{
    background:#fff;
    border-radius:12px;
    padding:20px;
    box-shadow:0 8px 20px rgba(0,0,0,0.05);
    margin-bottom:20px;
    transition:0.3s;
}

.card:hover{
    transform:translateY(-4px);
}

.grid{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(200px,1fr));
    gap:20px;
}

.metric h4{
    font-size:13px;
    color:#718096;
}

.metric p{
    font-size:22px;
    font-weight:700;
    margin-top:5px;
}

.progress-bar{
    background:#e2e8f0;
    border-radius:50px;
    height:14px;
    overflow:hidden;
    margin-top:10px;
}

.progress-fill{
    height:100%;
    background:#2563eb;
    width:0%;
    transition:width 1.5s ease;
}

.status-good{
    background:#e6f4ea;
    color:#198754;
    padding:12px;
    border-radius:8px;
    margin-bottom:15px;
}

.status-late{
    background:#fdeaea;
    color:#dc3545;
    padding:12px;
    border-radius:8px;
    margin-bottom:15px;
}

button{
    padding:10px 15px;
    border:none;
    border-radius:6px;
    cursor:pointer;
}

.btn-primary{
    background:#2563eb;
    color:#fff;
}

.btn-danger{
    background:#dc3545;
    color:#fff;
}
</style>
</head>
<body>


<div class="container">

<div class="sidebar">
    <h2>Tenant Panel</h2>
    <a href="dashboard.php" class="active">Dashboard</a>
    <a href="payments.php">Make Payment</a>
    <a href="rent_history.php">Rent History</a>
    <a href="tenancy.php">My Tenancy</a>
    <form method="POST" action="logout.php">
        <button class="btn-danger" style="margin-top:15px;width:100%;">Logout</button>
    </form>
</div>
