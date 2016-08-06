<?php
require_once("utils/main.php");
clear();

$errors = array();
$userid = $_SESSION['id'];
$profile = new Profile($userid);
if ($profile->isBlock()) {
    session_destroy();
    clear();
    exit;
}
?>
<!doctype html>
<html>
    <head>
        <meta charset="utf-8">
        <title>AfricoBank</title>
        <link rel="stylesheet" href="/bootstrap.css" />
        <link rel="shortcut icon" href="/africo.png" >
        <link rel="stylesheet" href="http://cdn.datatables.net/1.10.5/css/jquery.dataTables.css" />
        <script  src="//code.jquery.com/jquery-1.11.1.min.js" type="text/javascript"></script>
        <script  src="//cdn.datatables.net/1.10.5/js/jquery.dataTables.min.js" type="text/javascript"></script>
        <!--<script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>-->
        <script type="text/javascript">
            $(window).load(function () {
                $(".loader").fadeOut("slow");
            })
        </script>

        <style style="text/css">
            body{margin:0px auto;
                 font-family:verdana;
                 font-size:12px;
                 #background-color:#333;
                 #color:#FFF;
            }
            #main{
                width:800px;
                margin:0 auto;
                height:auto;
            }

            #topbar{
                width:auto;
                height:auto;
            }

            #content{
                width:800px;
                height:auto;
                padding-left:49px;
                padding-top:15px;
            }

            #footer{
                padding-top:50px;

            }

            #menu{
                height:auto;


            }
            #links {
                padding-top:20px;
                width:auto;
                height:30px;
                padding-left:0px;
            }
            #menu li{
                display:inline;
                margin-left:20px;
                float:left;
            }
            #menu li a{
                display:block;
            }


            .loader {
                position: fixed;
                left: 0px;
                top: 0px;
                width: 100%;
                height: 100%;
                z-index: 9999;
                background: url('ajax-loader.gif') 50% 50% no-repeat rgb(249,249,249);
            }

        </style>
    </head>
    <body>

        <div id="main" >

            <div id="topbar">
                <img src="img/bank1.jpg" />
                <div id="links">
                    <ul id="menu">
                        <li class="active"><a href="user.php" >Home</a></li>
                        <li><a href="?page=status">Update Status</a></li>
                        <li><a href="?page=pay" >Transfer Money</a></li>
                        <li><a href="?page=tran">Check Transactions</a></li>
                        <li><a href="?page=loan">Apply For Loan</a></li>
                        <?php if ($profile->isAdmin()) { ?>
                            <li><a href="index.php">AdminPage</a></li>
                        <?php } ?>
                        <li><a href="?page=help">Help</a></li>

                        <li><a href="logout.php">Logout</a></li>
                    </ul>


                </div>
                <img src="img/line.png" />
            </div>
            <div id="content">
                <div class="loader"></div>

                <div id="messages">


                    <?php print @$flash; ?>

                </div>
                <?php
                $page = @$_GET['page'];
                switch ($page) {
                    case "status":
                        include_once('pages/update.php');
                        break;
                    case "tran":
                        include_once ('pages/trans.php');
                        break;
                    case "pay":
                        $data = getlist($profile->getAccNumber());
                        if (count($data) == 0) {
                            echo "<a href=\"?page=add\">Add Beneficiary</a><br>";
                            echo "<a href=\"?page=air\">Buy Airtime</a><br>";
                        } else
                            include_once('pages/pay.php');
                        break;

                    case "add":

                        include_once("pages/add.php");
                        break;
                    case "atm":
                        include_once("pages/atm.php");
                        break;

                    case "loan":
                        include_once("pages/loan.php");
                        break;

                    case "viewloans":
                        include_once("pages/viewloans.php");
                        break;

                    case "air":
                        include_once("pages/air.php");
                        break;

                    case "amend":
                        include_once("pages/amend.php");
                        break;
                        exit;

                    default:
                        include_once('pages/welcome.php');
                }
                ?>
            </div>
            <div id="footer">
                <p align="center"><b>
            </div>
        </div>
    </body>
</html>
