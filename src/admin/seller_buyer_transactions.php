<?php

//seller_buyer_transactions.php
##########GIT Build Data##########
##
## File Changed In GIT Commit:
##
##    16.09.0-79-gb63e5d8
##
##################################

class AdminSellerBuyerTransactions
{
    function display_seller_buyer_config()
    {
        $db = true;
        include(GEO_BASE_DIR . 'get_common_vars.php');
        if (PHP5_DIR) {
            $menu_loader = geoAdmin::getInstance();
        } else {
            $menu_loader =& geoAdmin::getInstance();
        }

        $sb_html = geoSellerBuyer::callDisplay('adminDisplaySettings');

        $html = $menu_loader->getUserMessages() . "
<fieldset>
	<legend>Seller Buyer Transactions</legend>
	<form method='post' action='' class='form-horizontal'>
	<div class='x_content'>
		$sb_html
	</div>
	<div style=\"text-align: center;\"><input type=\"submit\" name=\"auto_save\" value=\"Save\" /></div>
	</form>
</fieldset>";
        geoAdmin::display_page($html);
    }



    function update_seller_buyer_config()
    {
        geoSellerBuyer::callUpdate('adminUpdateSettings');

        return true;
    }
}
