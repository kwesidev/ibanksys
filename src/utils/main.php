
<?php

include_once "sessions.php";
include_once "crsf.php";
function print_flash_message($mess) {

    echo "<b>$mess</b><br>";
}

//send mail function
function send_mail($to, $message) {

    $headers = "From: no-reply@yourdomain.com \n Reply-To:no-reply@yourdomain.com";
    if (mail($to, "OnlineBankingApp", $message, $headers))
        return true;
    else
        return false;
}

//dispay if any error was found
function error_message($mess = array()) {
    if (count($mess) >= 1) {
        foreach ($mess as $err) {
            print "*<b>$err</b><br>";
        }
    }
}

//clear's session
function clear() {
    session_start();
    if (!isset($_SESSION['id'])) {
        header("Location:index.php");
        exit;
    }
}

//used for executing queries
function runquery($sql, $data = array()) {

    $query = DP::getlink()->prepare($sql);
    $query->execute($data);
    $count = $query->rowCount();
    $query->closeCursor();
    if ($count == 1)
        return true;
    else
        return false;
}

//checks if user exists ,before registering for OnlineBanking

function checkuser($id) {

    return(runquery("SELECT * from login where username=:id", array("id" => $id)));
}

//checks if the person is already  registered to the bank,based on the person's
//idnumber
function checkidnumber($id) {
    return(runquery("SELECT * from customer where idnumber=:id", array("id" => $id)));
}

//check's if the username already exists in the login table
function checkacc($acc) {
    return(runquery("SELECT * FROM login where accnumber=:acc", array("acc" => $acc)));
}

//generate account number depending on the customers idnumber
//or passportnumber

function generate_code($idnumber) {

    $alpha = range('A', 'Z');
    return($alpha[mt_rand(0, count($alpha) - 1)] . substr($idnumber, mt_rand(1, 3), 4));
}

//returns an array of data based on the query
function getData($sql, $data = array()) {


    $con = DP::getLink();

    if ($data == null)
        $query = $con->query($sql);
    else {
        $query = $con->prepare($sql);

        $query->execute($data);
    }
    $rows = $query->fetchAll(PDO::FETCH_ASSOC);
    $query->closeCursor();

    return($rows);
}

//gets List of Applied Loans

function getAppliedLoans($idnumber) {

    return(getData("SELECT * From loan where idnumber=:id", array("id" => $idnumber)));
}

//transfer money from one account to the other
function transfer($amt, $accnumber, $baccnumber) {
    //transfering money in the same bank
    $con = DP::getLink();

    $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    try {
        $con->beginTransaction();

        $con->prepare("UPDATE account set balance=balance-:amt WHERE accnumber=:acc")->execute(array("amt" => (float) $amt, "acc" => $accnumber));
        $con->prepare("UPDATE account set balance=balance+:amt  WHERE accnumber=:acc")->execute(array("amt" => (float) $amt, "acc" => $baccnumber));

        $con->commit();
        print_flash_message("Money Has been Transfered");
    } catch (PDOException $e) {

        print_flash_message("Failed to Transfer");
        $con->rollback();
    }
}

//getList of beneficiaries

function getlist($unique = NULL) {

    return(getData("SELECT * FROM beneficiary WHERE accnumber=:id", array("id" => $unique)));
}

//gets transactions according to ID

function getTransactions($unique) {

    return(getData("SELECT * FROM transactions where accnumber=:acc", array(":acc" => $unique)));
}

//updates the customers profile
function updateStatus($unique, $data = array()) {

    $da = array("firstname" => trim($data[0]),
        "lastname" => trim($data[1]),
        "email" => trim($data[2]),
        "mobile" => trim($data[3]),
        "address" => trim(strip_tags($data[4])),
        "idnumber" => $unique);

    if (runquery("UPDATE customer SET firstname=:firstname,lastname=:lastname,email=:email,mobilenumber=:mobile,address=:address
		WHERE idnumber=:idnumber", $da))
        header("Location:?page=status");
}

//adds beneficiary list
function addBen($unique, $data = array()) {

    $sql = "INSERT INTO beneficiary(accname,baccnumber,branchcode,bankname,accnumber) VALUES(:accname,:baccnumber,:branchcode,:bankname,:accnumber)";

    if (runquery($sql, array("accname" => $data[0],
                "baccnumber" => $data[1],
                "branchcode" => $data[2],
                "bankname" => $data[3],
                "accnumber" => $unique)))
        print_flash_message("Added Beneficiary Sucessfully");
}

//onlinebanking registration process
function addLoginData($user, $pass, $id) {

    $sql = "INSERT INTO login(username,password,idnumber) values(:user,:pass,:id)";

    if (runquery($sql, array("user" => $user, "pass" => $pass, "id" => $id)))
        print_flash_message("You are now registered for Online Banking<br><a href=index.html>SignIn</a>");
}

//apply for a loan function
function addLoan($id, $type, $amt, $date) {

    $sql = "INSERT INTO loan(idnumber,typeofloan,amount,status,dateapplied) VALUES(:id,:type,:amt,:st,:date)";
    if (insertData($sql, array("id" => $id, "type" => $type, "amt" => (double) $amt, "st" => "P", "date" => $date)))
        print_flash_message("Your Loan Request Has been Sent Wait for An Approval.<br>");
}

//check to see some loans have not been approved
function checkloan($id) {

    $sql = "SELECT * FROM loan WHERE idnumber=:id and status=:p";

    if (runquery($sql, array("id" => $id, "p" => "P")))
        return true;
    else
        return false;
}

//records transactions
function transactions($acc, $date, $des, $pay, $bal, $depo) {
    /*
      accnumber   | varchar(30)  | NO   | MUL | NULL    |       |
      | date        | date         | NO   |     | NULL    |       |
      | description | varchar(200) | NO   |     | NULL    |       |
      | payments    | float(30,0)  | NO   |     | 0       |       |
      | balance     | float(30,0)  | NO   |     | 0       |       |
      | deposits

     */

    $sql = "INSERT INTO transactions(accnumber,date,description,payments,balance,deposits) values(:acc,:date,:des,:pay,:bal,:depo)";

    $int = runquery($sql, array("acc" => $acc, "date" => $date, "des" => $des, "pay" => $pay, "bal" => (double) $bal, "depo" => (double) $depo));
}

function loanrespond($c) {
    switch ($c) {
        case "P":
            print "Pending";
            break;

        case "A":
            print "Approved";
            break;
        case "R":
            print "Rejected" . "/<a href=#>ReApply</a>";
            break;
    }
}

function sep($data) {
    return(explode('#', $data));
}

//purchase airtime
function purchase_air($acc, $amt) {
    $sql = "UPDATE account set balance=balance-:bal WHERE accnumber=:acc";
    return(runquery($sql, array("acc" => $acc, "bal" => (double) $amt)));
}

function delete_bene($id) {
    return(runquery("DELETE FROM beneficiary WHERE  benfid=:ben", array("ben" => $id)));
}

function isAdmin($acc) {
    $sql = "SELECT * FROM login where username=:usr AND level=1 OR idnumber=:usr AND level=1";
    return(runquery($sql, array("usr" => $acc)));
}

//automatically inlcude class files without have to use inlcudes
//for example include_once for every class file being called
function __autoload($classname) {
    include_once($classname . ".php");
}
