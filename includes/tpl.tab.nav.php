<?php
/*
 menu of settings page
*/


$menu_item = array(
    array('caption' => __('商品仓库','waraPayi18N'), 'name' => 'products'),
    //array('caption'=>__('添加商品','waraPayi18N'),'name'=>'additem'),
    array('caption' => __('订单管理','waraPayi18N'), 'name' => 'orders'),
    //array('caption'=>__('添加订单','waraPayi18N'),'name'=>'addorder'),
    //array('caption'=>__('会员中心','waraPayi18N'),'name'=>'members'),
    array('caption' => __('模版管理','waraPayi18N'), 'name' => 'templates'),
    array('caption' => __('常规设置','waraPayi18N'), 'name' => 'general'),
);


$user_item = array(
    array('caption' => __('我的订单','waraPayi18N'), 'name' => 'orders'),
);


$user = wp_get_current_user();
if ($user->has_cap('activate_plugins')) {
    $items = $menu_item;
} elseif (waraPay_get_setting('allow_user_see_order')) {
    $items = $user_item;
}

if (!$user->has_cap('activate_plugins') && !waraPay_get_setting('allow_user_see_order')) {
    wp_die('Permission Deny!');
}

?>



<ul id="waraPay_menu">
    <?php
    foreach ($items as $value) {
        extract($value);
        echo "<li><a class=\"waraPay_menu_a\" href=\"?page=waraPay&tab=$name\">$caption</a></li>";
    }

    if ($user->has_cap('activate_plugins')) {
        echo '<li><a class="waraPay_menu_a_nojs" href="' . WARAPAY_URL . '/readme.php' . '" target="_blank">'.__('帮助文档','waraPayi18N').'</a></li>';
    }

    ?>

    <div class="clear"></div>
    <div id="waraPay_logo"><?php echo __('因为信任,所以简单','waraPayi18N');?></div>

</ul>
<div class="clear"></div>

