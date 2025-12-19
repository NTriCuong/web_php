<?php
    $mod = isset($_GET["mod"])?$_GET["mod"]:"";
	// $ac = isset($_GET["ac"])?$_GET["ac"]:"";
    if( $mod === "home" || $mod === '')
        include('./home/main.php');
    else if ($mod === "detail")
        include('./category/detailiphone.php');
    else if($mod === "login")
        include("./login/login.php");
    else if($mod === 'categories')
        include("./category/iphone.php");
    else if($mod === 'order')
        include('./order/order.php');