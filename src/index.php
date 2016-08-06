<?php
require_once "utils/main.php";
session_start();
$errors = array();


if (isset($_SESSION['admin'])) {
    include("manager/admin.php");
    exit;
} else if (isset($_SESSION['id'])) {

    header("Location:user.php");
    exit;
}
if (isset($_POST['send'])) {
    check_token();
    $login = new Login($_POST['username'], $_POST['pass']);
    if ($login->checkLogin()) {
        if ($login->isAdmin()) {

            $_SESSION['id'] = $login->getid();
            $_SESSION['admin'] = true;
            include_once("manager/admin.php");
            exit;
        } else {

            if ($login->blocked()) {
                array_push($errors, "Sorry Your Account Has Been Blocked");
            } else {
                $_SESSION['id'] = $login->getid();
                header("Location:user.php");
            }
        }
    } else
        array_push($errors, "Invalid UserName or Password");
}
?>


<!doctype html>
<html>
    <head>
        <meta charset="utf-8">
        <title>AfricoBank</title>
        <link rel="stylesheet" href="/bootstrap.css" />
        <link rel="shortcut icon" href="/img/africo.png" />
        <script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
        <script type="text/javascript">
            $(window).load(function () {
                $(".loader").fadeOut("slow");
            })
        </script>

        <style style="text/css">
            body{margin:0px auto;

                 font-family:verdana;
                 font-size:12px;
                 background-color:$f5f5f5;
                 #background-color:#333;
                 #color:white;
            }
            #main{
                width:800px;
                margin:0 auto;
                height:600px;

            }

            #topbar{
                width:auto;
                background-image:url('/img/bank1.jpg'); background-repeat:no-repeat;
                height:80px;


            }


            #content{
                width:800px;
                height:auto;
                padding-top:30px;

                padding-left:20px;
            }

            #footer{

                padding-top:30px;


            }
            .loader {
                position: fixed;
                left: 0px;
                top: 0px;
                width: 100%;
                height: 100%;
                z-index: 9999;
                background: url('img/ajax-loader.gif') 50% 50% no-repeat rgb(249,249,249);
            }
        </style>
    </head>
    <body>
        <div id="main">
            <div id="topbar"></div>
            <div id="content">
                <div class="loader"></div>
<?php
if (isset($_GET['page'])) {
    $page = $_GET['page'];
    if ($page == "register") {

        //error_message($errors);
        ?>
        <?php
        $bt = new BotCheck();
        if (isset($_POST['reg'])) {
            check_token();
            $id = $_POST['idnumber'];
            $user = $_POST['username'];
            $pass = $_POST['pass'];
            $con = $_POST['c'];
            if (!checkidnumber($id))
                array_push($errors, "You need to be a customer of this Bank before  You can register for Online Banking");

            if (checkuser($user))
                array_push($errors, "Choose a different Username");



            if (runquery("SELECT * from login WHERE idnumber=:id", array("id" => $id)))
                array_push($errors, "This account has already  been registered for Online Banking");

            if ($con != $pass)
                array_push($errors, "Passwords do not Match");
            /*
              if(!$bt->checkanswer((int)$_POST['ans']))

              array_push($errors,"Security Question is Wrong");

             */
            if (count($errors) == 0) {
                addLoginData($user, $pass, $id);
                unset($_POST);
            } else
                error_message($errors);
        }
        ?>
                        <br>

                        <form action="/?page=register" method="post">
                            <fieldset>
                                <legend>Register for Internet Banking</legend>
                                <label>Your IDNUMBER</label><br>
                                <input type="text" name="idnumber" value="<?php echo @$_POST['idnumber']; ?>" required autocomplete="off" /><br>
                                <label>Choose a Username</label><br>
                                <input type="text" name="username" value="<?php echo @$_POST['username']; ?>"  autocomplete="off" required/>
                                <?php draw_tokenbox();?>
                                <br>
                                <label>Choose a password</label>
                                <input type="password" name="pass" required autocomplete="off"/><br>
                                <label>Confirm Password</label>
                                <input type="password" name="c" required autocomplete="off" /><br>

                        <?php
                        /*
                          <label>Security Question</label><br>
                          <?php $bt->showquestion();?><br>
                          <input type="text" name="ans" size="5" required/><br>
                         */
                        ?>
                                <input type="submit" name="reg" value="Register" class="btn"/>
                                <input type="reset" name="cancel" value="Cancel" class="btn" />

                            </fieldset>
                        </form>
                        <p><a href="/index.php"><<<<</a></p>

    <?php
    }

    else if ($page == 'forgot') {
        ?>

                        <div id="forgot" align="center">

                                <?php
                                if (isset($_POST['rfg'])) {
                                    check_token();
                                    $idnum = $_POST['id'];
                                    $p = new Profile($idnum);
                                    if ($p->getUsername() != "") {
                                        $mess = "Username:" . $p->getUsername() . "\n" . "Password:" . $p->getPassword();
                                        if (send_mail($p->getEmail(), $mess))
                                            print_flash_message("Your Username and Password has been sent to your mail");
                                        else
                                            print_flash_message("Failed to send try again Later");
                                    } else
                                        array_push($errors, "You have not registered for Online Banking");
                                }
                                error_message($errors);
                                ?>
                            <p>
                            <form action="/?page=forgot" method="post">
                                <label><b>ENTER YOUR ID NUMBER</b></label><input type="text" name="id" required pattern="[0-9]{13}" title="Enter a Valid IDnumber"/><br>
                                <?php draw_tokenbox(); ?>
                                <input type="submit" name="rfg" value="Send Password" class="btn" />
                            </form>
                            <br>

                            <a href="index.php"><<<<</a>
                            </p>
                        </div>


                        <?php } else header("Location:/index.php");
                    }else { ?>

                    <div id="login" align="center" >
                        <p>
                        <?php error_message($errors); ?>
                        </p>
                        <form action="" method="post" accept-charset="utf-8">
                            <p>
                                <label><b>UserName</b></label>
                                <br>
                                <input type="text" name="username" value="" size="" required autocomplete="off"/><br>
                                <label><b>Password</b></label>
                                <br>

                                <input type="password" name="pass" value="" required autocomplete="off"/>
                                <?php draw_tokenbox(); ?>
                                <br>
                                <input type="submit" name="send" value="Login" class="btn"/>
                                <input type="reset" name="reset" value="Cancel" class="btn"/>
                                <br>
                            <p><b><a href="/?page=register">Register</a>&nbsp;&nbsp;<a href="/?page=forgot">ForgotPassword?</a></b> </p>

                            <p><img src="img/CREDITlaptop.jpg" />
                                <br>
                            </p>

                        </form>
                    </div>
<?php }
?>

            </div>

            <div id="footer">

                <p align="center">

                </p>

            </div>
        </div>


    </body>
</html>
