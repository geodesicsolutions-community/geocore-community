{* 16.09.0-79-gb63e5d8 *}

{* so we don't have to have super long thing for each link *}
{capture assign='extrnLink'}class="mini_button" style="white-space: normal;" onclick="window.open(this.href); return false;"{/capture}


<fieldset>
	<legend><i class="fa fa-support"></i> Software Support</legend>
	<div class="medium_font">
		<div style="width: 48%; display: inline-block; vertical-align: top; ">
			<div style="padding-right: 3px;">
				<div class="col_ftr">Paid Support:</div>
				
				<div class="page_note">
					{if $stats.supportLeft||$stats.supportExpire=='Never'}
						<img src="admin_images/bullet_success.png" alt="Support Active" style="margin:0 5px; vertical-align: middle; float: left;" />
						Paid Support is Active!<br />
						{if $stats.supportLeft}
							<strong class="text_blue">{$stats.supportLeft}</strong> remaining paid support.
						{elseif $product.leased}
							Paid support is included with your leased license, and will
							only expire if the lease is canceled.
						{elseif $stats.supportExpire=='Never'}
							Your paid support Never expires.
						{/if}
					{else}
						<img src="admin_images/bullet_error.png" alt="Notice" style="margin:0 5px; vertical-align: middle;float: left;" />
						Paid Support is <strong style="color: red;">Expired</strong>.
						See the renewal options below to extend paid support.
					{/if}
					<div class="clr"></div>
				</div>
				{if !$stats.supportLeft&&$stats.supportExpire!='Never'}
					<div class="center">
						<a href="index.php?clear_local_key_cache=1" class="mini_button">Refresh Support Status</a>
						<br /><br />
					</div>
				{/if}
				<a href="#" id="paidSupportToggle">See Options</a>
				<div id="paidSupport_Links" style="display: none;">
					<div style="margin-top: 10px;">
						{if $stats.supportLeft||$stats.supportExpire=='Never'}
							<strong class="text_blue">Priority Support Helpdesk:</strong>
							<ul class="home_links">
								<li><a href="https://geodesicsolutions.com/client-area/task,helpdesk/tab,start_ticket" class="btn btn-default source">Start New Ticket</a></li>
								<li><a href="https://geodesicsolutions.com/client-area/task,helpdesk/tab,open_tickets" class="btn btn-default source">Open Tickets</a></li>
								<li><a href="https://geodesicsolutions.com/client-area/task,helpdesk/tab,closed_tickets" class="btn btn-default source">Closed / Need Reply Tickets</a></li>
							</ul>
							<strong class="text_blue">Live Support Chat</strong> is also available
							during our normal business hours by logging into the
							<a href="https://geodesicsolutions.com/client-area.html" onclick="window.open(this.href); return false;">Client Area</a>
							on our website.
						{else}
							<ul class="home_links">
								<li>
									<a href="https://geodesicsolutions.com/client-area/{if $packageId}task,product/product_id,69/package_id,{$packageId}/{else}task,choose_parent/product_id,69/{/if}" {$extrnLink}>Extend Paid Support</a>
								</li>
								<li><strong>Paid Support Includes:</strong></li>
								<li>- <strong>Priority</strong> Support Tickets</li>
								<li>- Live Support Chat during our normal business hours</li>
							</ul>
						{/if}
						<br /><br />For full details, see our <a href="http://geodesicsolutions.com/company-policies.html" onclick="window.open(this.href); return false;">Company Policies</a>
					</div>
					<div class="clr"></div>
				</div>
			</div>
		</div>
		<div style="width: 49%; display: inline-block;">
			<div class="col_ftr">Free Support:</div>
			<div class="page_note">
				<img src="admin_images/bullet_success.png" alt="Support Active" style="margin:0 5px; vertical-align: middle; float: left;" />
				You have free access to the support options below that do <strong class="text_blue">not expire</strong>. 
				<div class="clr"></div>
			</div>
			<a href="#" id="freeSupportToggle">See Options</a>
			
			<div id="freeSupport_Links" style="display: none;">
				<div style="margin-top: 10px;">
					<strong class="text_blue">Support Helpdesk*:</strong>
					<ul class="home_links">
						<li><a href="https://geodesicsolutions.com/client-area/task,helpdesk/tab,start_ticket" class="btn btn-default source">Start New Ticket</a></li>
						<li><a href="https://geodesicsolutions.com/client-area/task,helpdesk/tab,open_tickets" class="btn btn-default source">Open Tickets</a></li>
						<li><a href="https://geodesicsolutions.com/client-area/task,helpdesk/tab,closed_tickets" class="btn btn-default source">Closed / Need Reply Tickets</a></li>
					</ul>
					<br />
					<strong class="text_blue">Other Support Resources:</strong>
					<ul class="home_links">
						<li><a href="http://geodesicsolutions.com/support/geocore-wiki/" class="btn btn-default source">User Manual</a></li>
						<li><a href="https://geodesicsolutions.com/geo_user_forum/index.php" class="btn btn-default source">Community Forums</a></li>
						<li><a href="https://geodesicsolutions.com/client-area.html" class="btn btn-default source">Client Area</a>
					</ul>
					<br /><br />
					<div class="page_note">
						<strong class="text_blue">*Note:</strong>
						Certain restrictions may apply to <em>free</em> support tickets, see our <a href="http://geodesicsolutions.com/company-policies.html" onclick="window.open(this.href); return false;">Company Policies</a>
						for full details.
					</div>
				</div>
			</div>
		</div>
	</div>
</fieldset>