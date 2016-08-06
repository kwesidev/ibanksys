<?php

include("utils/main.php");
session_start();
session_destroy();
header("Location:index.php");
