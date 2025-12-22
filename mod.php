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
    else if($mod === "register")
        include("./login/register.php");
    elseif($mod === "admin")
        include("./admin/admin_product.php");
    elseif($mod === "logout")
    {
            // Xóa thông tin user
        unset($_SESSION['user']);
        $_SESSION = [];
        session_destroy();

        // Redirect
        header("Location: /DA-cuoiky/index.php?mod=home");
    exit;
    }