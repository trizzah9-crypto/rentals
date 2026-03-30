<?php
require ("../config/session.php");
require ("../config/db.php");


// Check if user is logged in AND role is landlord
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'landlord') {
    // redirect to landlord login if not allowed
    header("Location: login.php");
    exit;
}

// Now you can safely show landlord dashboard

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Rentals </title>
    <!-- Favicon icon -->
    <link rel="icon" type="image/png" sizes="64x64" href="../images/favicon.png">
    <link rel="stylesheet" href="../vendor/owl-carousel/css/owl.carousel.min.css">
        <!-- Datatable -->
    <link href="../vendor/datatables/css/jquery.dataTables.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../vendor/owl-carousel/css/owl.theme.default.min.css">
    <link href="../vendor/jqvmap/css/jqvmap.min.css" rel="stylesheet">
    <link href="../css/style.css" rel="stylesheet">
     <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
   


    <style>
        .dataTables_scrollBody {
             overflow-x: auto !important;
        }

        .dataTables_wrapper {
            overflow-x: auto;
        }

        .my-image {
            width: 100%;
            max-width: 90px; /* control max size */
            height: auto;
        }
    </style>

</head>

<body>

    <!--*******************
        Preloader start
    ********************-->
    <div id="preloader">
        <div class="sk-three-bounce">
            <div class="sk-child sk-bounce1"></div>
            <div class="sk-child sk-bounce2"></div>
            <div class="sk-child sk-bounce3"></div>
        </div>
    </div>
    <!--*******************
        Preloader end
    ********************-->


    <!--**********************************
        Main wrapper start
    ***********************************-->
    <div id="main-wrapper">

        <!--**********************************
            Nav header start
        ***********************************-->
        <div class="nav-header">
            <a href="dashboard.php" class="brand-logo">
                <img class="my-image" src="../images/logo.png" alt="">
                 
                <img class="brand-title" src="../images/logo-text.png" alt="">

            </a>

            <div class="nav-control">
                <div class="hamburger">
                    <span class="line"></span><span class="line"></span><span class="line"></span>
                </div>
            </div>
        </div>
        <!--**********************************
            Nav header end
        ***********************************-->

        <!--**********************************
            Header start
        ***********************************-->
        <div class="header">
            <div class="header-content">
                <nav class="navbar navbar-expand">
                    <div class="collapse navbar-collapse justify-content-between">
                        <div class="header-left">
                            <div class="search_bar dropdown">
                                <span class="search_icon p-3 c-pointer" data-toggle="dropdown">
                                    <i class="mdi mdi-magnify"></i>
                                </span>
                                <div class="dropdown-menu p-0 m-0">
                                    <form>
                                        <input class="form-control" type="search" placeholder="Search" aria-label="Search">
                                    </form>
                                </div>
                            </div>
                        </div>

                        <ul class="navbar-nav header-right">
                            <li class="nav-item dropdown notification_dropdown">
                                <a class="nav-link" href="#" role="button" data-toggle="dropdown">
                                    <i class="mdi mdi-bell"></i>
                                    <div class="pulse-css"></div>
                                </a>
                                <div class="dropdown-menu dropdown-menu-right">
                                    <ul class="list-unstyled">
                                        
                                    </ul>
                                    <a class="all-notification" href="#">See all notifications <i
                                            class="ti-arrow-right"></i></a>
                                </div>
                            </li>
                            <li class="nav-item dropdown header-profile">
                                <a class="nav-link" href="#" role="button" data-toggle="dropdown">
                                    <i class="mdi mdi-account"></i>
                                </a>
                                <div class="dropdown-menu dropdown-menu-right">
                                    <a href="./app-profile.html" class="dropdown-item">
                                        <i class="icon-user"></i>
                                        <span class="ml-2">Profile </span>
                                    </a>
                                    <a href="./email-inbox.html" class="dropdown-item">
                                        <i class="icon-envelope-open"></i>
                                        <span class="ml-2">Inbox </span>
                                    </a>
                                    <a href="../logout.php" class="dropdown-item">
                                        <i class="icon-key"></i>
                                        <span class="ml-2">Logout </span>
                                    </a>
                                </div>
                            </li>
                        </ul>
                    </div>
                </nav>
            </div>
        </div>
        <!--**********************************
            Header end ti-comment-alt
        ***********************************-->

        <!--**********************************
            Sidebar start
        ***********************************-->
        <div class="quixnav">
            <div class="quixnav-scroll">
                <ul class="metismenu" id="menu">
                    <!-- MAIN -->
                   <li class="nav-label first">Main</li>
                      <li>
                            <a href="dashboard.php">
                            <i class="icon-speedometer"></i>
                             <span class="nav-text">Dashboard</span>
                            </a>
                            <a href="dashboards.php">
                            <i class="icon-speedometer"></i>
                             <span class="nav-text">Dashboards</span>
                            </a>
                     </li>
                  
                       <!-- HOUSES -->
                <li class="nav-label">Property Management</li>
                <li>
                    <a class="has-arrow" href="javascript:void()">
                        <i class="icon-home"></i>
                        <span class="nav-text">Houses</span>
                    </a>
                    <ul>
                        <li>
                            <a href="house.php">
                                <i class="icon-plus"></i> Add House
                            </a>
                        </li>
                        <li>
                            <a href="house_list.php">
                                <i class="icon-list"></i> House List
                            </a>
                        </li>
                    </ul>
                  </li>

                                             <!-- TENANTS -->
                              <li class="nav-label">Tenant Management</li>
                              <li>
                                  <a class="has-arrow" href="javascript:void()">
                                      <i class="icon-people"></i>
                                      <span class="nav-text">Tenants</span>
                                  </a>
                                  <ul>
                                      <li>
                                          <a href="tenancies.php">
                                              <i class="icon-login"></i> Move In Tenant
                                          </a>
                                      </li>
                                      <li>
                                          <a href="m_tenant.php">
                                              <i class="icon-logout"></i> Move Out Tenant
                                          </a>
                                      </li>
                                  </ul>
                              </li>

                   



    <!-- PAYMENTS -->
                        <li class="nav-label">Payments & Billing</li>
                        <li>
                            <a href="approve_payments.php">
                                <i class="icon-credit-card"></i>
                                <span class="nav-text">Approve Payments</span>
                            </a>
                        </li>


                          <!-- UTILITIES -->
                          <li class="nav-label">Utilities</li>
                          <li>
                              <a href="water_readings.php">
                                  <i class="icon-drop"></i>
                                  <span class="nav-text">Water Readings</span>
                              </a>
                          </li>

                           <!-- SYSTEM / EXTRA -->
                               <li class="nav-label">System</li>
                               <li>
                                   <a href="test.php">
                                       <i class="icon-wrench"></i>
                                       <span class="nav-text">System Test</span>
                                   </a>
                               </li>
                           
                                 <!-- LOGOUT -->
                            <li class="nav-label">Account</li>
                            <li>
                                <a href="logout.php">
                                    <i class="icon-logout"></i>
                                    <span class="nav-text">Logout</span>
                                </a>
                            </li>

                     
                   
                </ul>
            </div>


        </div>
        <!--**********************************
            Sidebar end
        ***********************************-->

  