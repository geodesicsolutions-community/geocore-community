<?php
//addons/tokens/admin.php
/**************************************************************************
Addon Created by Geodesic Solutions, LLC
Copyright (c) 2001-2018 Geodesic Solutions, LLC
All rights reserved
http://geodesicsolutions.com
see license attached to distribution
**************************************************************************/
##########GIT Build Data##########
## 
## File Changed In GIT Commit:
## ##    6.0.7-2-gc953682
## 
##################################

# tokens Addon

class addon_tokens_admin extends addon_tokens_info
{
	public function init_pages ($menuName)
	{
		//menu_page::addonAddPage('addon_tokens_add','','Add Tokens for User',$this->name, $this->icon_image);
	}
	
	public function init_text($language_id)
	{
		return array (
			//Choose tokens page
			'chooseTokens_title' => array (
				'name' => 'Choose Tokens page - title',
				'desc' => '',
				'type' => 'input',
				'default' => 'Account Tokens'
			),
			'chooseTokens_subtitle' => array (
				'name' => 'Choose Tokens page - sub-title',
				'desc' => '',
				'type' => 'input',
				'default' => 'Purchase Additional Account Tokens'
			),
			'chooseTokens_instructions' => array (
				'name' => 'Choose Tokens page - description',
				'desc' => '',
				'type' => 'textarea',
				'default' => 'Choose how many tokens you wish to purchase from the selections below.'
			),
			'chooseTokens_choose_label' => array (
				'name' => 'Choose Tokens page - choose number label',
				'desc' => '',
				'type' => 'input',
				'default' => 'Purchase Token Choices'
			),
			'chooseTokens_tokens_after' => array (
				'name' => 'Choose Tokens page - # tokens',
				'desc' => '',
				'type' => 'input',
				'default' => 'Tokens'
			),
			'chooseTokens_continue_button' => array (
				'name' => 'Choose Tokens page - continue button text',
				'desc' => '',
				'type' => 'input',
				'default' => 'Continue'
			),
			'chooseTokens_cancel' => array (
				'name' => 'Choose Tokens page - cancel link text',
				'desc' => '',
				'type' => 'input',
				'default' => 'Cancel &amp; Remove'
			),
			'chooseTokens_step_label' => array (
				'name' => 'Choose Tokens page - cart step label',
				'desc' => '',
				'type' => 'input',
				'default' => 'Choose Tokens'
			),
			
			//cart
			'cart_token_purchase_item_label' => array (
				'name' => 'View Cart Page - Order Item Label : purchase token',
				'desc' => 'This is prepended with the number of tokens being purchased.',
				'type' => 'input',
				'default' => 'Tokens'
			),
			'cart_token_purchase_action_label' => array (
				'name' => 'View Cart Page - Action Label',
				'desc' => '',
				'type' => 'input',
				'default' => 'Purchase Tokens'
			),
			'my_account_links_current_tokens_label' => array (
				'name' => 'My Account Links - Tokens label',
				'desc' => '',
				'type' => 'input',
				'default' => 'Tokens Available: '
			),
			
			//purchase buttons
			'purchase_token_button_cart' => array (
				'name' => 'Purchase Tokens - Purchase Button Text in Cart',
				'desc' => '',
				'type' => 'input',
				'default' => 'Purchase Tokens'
			),
			'purchase_token_button_module' => array (
				'name' => 'Purchase Tokens - Purchase Button Text in My Account Links Module',
				'desc' => '',
				'type' => 'input',
				'default' => 'Purchase Tokens'
			),
			
			//attach tokens
			'attach_use_token' => array (
				'name' => 'View Cart Page - Use Token text',
				'desc' => 'text used for "use token" link',
				'type' => 'input',
				'default' => 'Use Token'
			),
			'attach_use_token_label_inactive' => array (
				'name' => 'View Cart Page - Tokens available label',
				'desc' => 'text used for item label when no token selected',
				'type' => 'input',
				'default' => 'Tokens Available:'
			),
			'attach_use_token_label_active' => array (
				'name' => 'View Cart Page - Tokens available label',
				'desc' => 'text used for item label when token is used',
				'type' => 'input',
				'default' => 'Token applied. Tokens Available:'
			),
			'attach_use_token_label_outside_cart' => array (
				'name' => 'Token Attached - Item Label ouside cart',
				'desc' => 'text used for item label when order viewed outside cart',
				'type' => 'input',
				'default' => 'Token Used'
			),
			
			//user info page
			'user_info_label' => array (
				'name' => 'User Info - Token Label',
				'desc' => '',
				'type' => 'input',
				'default' => 'Tokens'
			),
			'user_info_count_column_header' => array (
				'name' => 'User Info - Token count column header',
				'desc' => '',
				'type' => 'input',
				'default' => '# Tokens'
			),
			'user_info_expire_column_header' => array (
				'name' => 'User Info - Token expiration column header',
				'desc' => '',
				'type' => 'input',
				'default' => 'Tokens Expire'
			),
			'user_info_total_column_header' => array (
				'name' => 'User Info - Token total label',
				'desc' => '',
				'type' => 'input',
				'default' => 'Total Tokens:'
			),
			'user_info_expires_text' => array (
				'name' => 'User Info - Expires text',
				'desc' => 'Used if there is only a single group of tokens',
				'type' => 'input',
				'default' => 'Expires'
			),
			
			//errors
			'error_choose_tokens' => array (
				'name' => 'Error - must choose number of tokens to purchase',
				'desc' => '',
				'type' => 'input',
				'default' => 'Please select the number of tokens to purchase.'
			),
			'error_invalid_selection' => array (
				'name' => 'Error - invalid selection',
				'desc' => '',
				'type' => 'input',
				'default' => 'Invalid selection.'
			),
		);
	}
}