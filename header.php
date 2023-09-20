<!DOCTYPE html>

<html lang="en">



<head>



    <meta charset="utf-8">

    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <meta name="description" content="">

    <meta name="author" content="">



    <title>Stock Management</title>



    <!-- Bootstrap core CSS -->

    <link href="css/font-awesome.min.css" rel="stylesheet">

    <link href="css/bootstrap/bootstrap.min.css" rel="stylesheet">

    <link href="css/theme.css" rel="stylesheet">



    <!-- Bootstrap core JavaScript -->

    <script src="js/jquery.min.js"></script>

    <script src="js/bootstrap/bootstrap.bundle.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


</head>



<body>



    <!-- Navigation -->

    <nav id="header" class="navbar navbar-expand-lg navbar-light bg-light">

        <div class="container">

            <a class="navbar-brand" href="#">

                <img src="images/logo-3.png" height="75px">

            </a>

            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarResponsive" aria-controls="navbarResponsive" aria-expanded="false" aria-label="Toggle navigation">

                <span class="navbar-toggler-icon"></span>

            </button>

            <div class="collapse navbar-collapse" id="navbarResponsive">

                <ul class="navbar-nav ml-auto">

                    <li class="nav-item <?php echo (stripos($_SERVER["SCRIPT_NAME"], "/index.php") > 0) ? "active" : ""; ?>">

                        <a class="nav-link" href="index.php"><i class="fa fa-home"></i> Home

                        </a>

                    </li>

                    <li class="nav-item <?php echo (stripos($_SERVER["SCRIPT_NAME"], "/clients.php") > 0) ? "active" : ""; ?>">

                        <a class="nav-link" href="clients.php"><i class="fa fa-users"></i> Clients</a>

                    </li>

                    <li class="nav-item <?php echo (stripos($_SERVER["SCRIPT_NAME"], "/complete_item.php") > 0) ? "active" : ""; ?>">

                        <a class="nav-link" href="complete_item.php"><i class="fa fa-archive" aria-hidden="true"></i> Complete Item</a>

                    </li>

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="" id="navbarDropdownMenuLink1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fa fa-list"></i> Sales
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="navbarDropdownMenuLink1">
                            <li class="nav-item <?php echo (stripos($_SERVER["SCRIPT_NAME"], "/sales.php") > 0) ? "active" : ""; ?>">
                                <a class="nav-link" href="sales.php"><i class="fa fa-bar-chart"></i> Sales</a>
                            </li>
                            <li class="nav-item <?php echo (stripos($_SERVER["SCRIPT_NAME"], "/view_invoice_list.php") > 0) ? "active" : ""; ?>">
                                <a class="nav-link" href="view_invoice_list.php"><i class="fa fa-list"></i> Invoice List</a>
                            </li>
                        </ul>

                    </li>

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="" id="navbarDropdownMenuLink2" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fa fa-archive"></i> Stocks
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="navbarDropdownMenuLink2">
                            <li class="nav-item <?php echo (stripos($_SERVER["SCRIPT_NAME"], "/stocks.php") > 0) ? "active" : ""; ?>">
                                <a class="nav-link" href="stocks.php"><i class="fa fa-archive"></i> Stocks</a>
                            </li>
                            <li class="nav-item <?php echo (stripos($_SERVER["SCRIPT_NAME"], "/category.php") > 0) ? "active" : ""; ?>">
                                <a class="nav-link" href="category.php"><i class="fa fa-archive"></i> Category</a>
                            </li>
                        </ul>

                    </li>

                    <li class="nav-item <?php echo (stripos($_SERVER["SCRIPT_NAME"], "/pumps.php") > 0) ? "active" : ""; ?>">

                        <a class="nav-link" href="pumps.php"><i class="fa fa-archive" aria-hidden="true"></i> Pumps</a>

                    </li>

                    <li class="nav-item <?php echo (stripos($_SERVER["SCRIPT_NAME"], "/settings.php") > 0) ? "active" : ""; ?>">

                        <a class="nav-link" href="settings.php"><i class="fa fa-gears"></i> Setting</a>

                    </li>

                    <li class="nav-item">

                        <a class="nav-link" href="logout.php"><i class="fa fa-sign-out"></i> Logout</a>

                    </li>

                </ul>

            </div>

        </div>

    </nav>

    <div id="wrap">

        <div class="container mt-3">

            <?php
            $qry = "SELECT * FROM `total_stock` WHERE stock < alert_limit";

            $stmt = $db1->prepare($qry);

            $stmt->setFetchMode(PDO::FETCH_ASSOC);

            $stmt->execute();

            if ($stmt->rowCount()) {
            ?>

                <div class="stock_ticker">
                    <div class="title">
                        <h5>Out Of Stock</h5>
                    </div>
                    <div class="news">
                        <marquee class="news-content">
                            <?php
                            echo "<ul>";
                            while ($row = $stmt->fetch()) {
                                echo "<li>" . $row['parts_name'] . "</li>";
                            }
                            echo "</ul>";
                            ?>
                        </marquee>
                    </div>
                </div>

            <?php } ?>

        </div>