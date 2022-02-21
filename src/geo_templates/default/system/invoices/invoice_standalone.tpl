{if $in_admin && !$print}
	{* admin lightbox view -- add styles but not surrounding html *}
	<link href="../{external file='css/default.css'}" rel="stylesheet" type="text/css" />
	<link href="../{external file='css/custom.css'}" rel="stylesheet" type="text/css" />
	<link href="../{external file='css/system/invoices/invoice_styles.css'}" rel="stylesheet" type="text/css" />
{else}
	{include file="system/invoices/common/head.tpl"}
{/if}
{include file="system/invoices/common/invoice_body.tpl"}
{if $showOrderDetails}
	{include file="system/invoices/common/order_details.tpl"}
{/if}
{include file="system/invoices/common/footer.tpl"}
