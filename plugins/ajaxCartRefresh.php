<?php

function ajaxCartRefresh()
{          	
    if ($_POST['data_type']=='price') echo user::getCurrentUser()->getCartSum();
    if ($_POST['data_type']=='count') echo user::getCurrentUser()->getCartCount() ? user::getCurrentUser()->getCartCount() : '';
}
