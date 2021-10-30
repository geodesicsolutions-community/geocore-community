{* 7.4.6-11-g316131f *}
<fieldset id="price_plan_items">
	<legend>Price Plan Items</legend>
	<div>
		{if $saveMe}
			<div class="page_note">
				Save the page to start using category specific pricing and enable editing of plan item settings.
			</div>
		{else}
			<ul class="expandable_list">
				{foreach from=$plan_items item="plan_item" key="index"}
					<li id="row_for{$index}">
						<div id="requireAdmin{$index}" class="btn_require">
							{if count($plan_item.parents) == 0}
								<a href="javascript:void(0);" id='require_{$index}' class="{if $plan_item.admin_approve}mini_cancel{else}mini_button{/if}">
									{if $plan_item.admin_approve}
										Stop requiring admin approval
									{else}
										Require admin approval
									{/if}
								</a>
								<script type="text/javascript">
									require_state_{$index} = {if $plan_item.admin_approve}1{else}0{/if};
									jQuery('#require_{$index}').click(function() {
										//alert('{$plan_item.url_requireToggle}');
										jQuery.post('{$plan_item.url_requireToggle}',
											{
												newState: (require_state_{$index} == 1) ? 0 : 1
											},
											function(changedTo) {
												if(changedTo != 1) {
													//changed from required to not-required
													gjUtil.addMessage('<div style="text-align: center; font-size: 12pt; font-weight: bold;">Item will now go live automatically without admin approval</div>', 3500);
													jQuery('#require_{$index}').html("Require admin approval").removeClass('mini_cancel').addClass('mini_button');
													require_state_{$index} = 0;
												} else {
													//changed from not-required to required
													gjUtil.addMessage('<div style="text-align: center; font-size: 12pt; font-weight: bold;">Item now requires admin approval before going live.</div>', 3500);
													jQuery('#require_{$index}').html("Stop requiring admin approval").removeClass('mini_button').addClass('mini_cancel');
													require_state_{$index} = 1;
												}
											}
										);
									});
								</script>
							{else}
								&nbsp;
							{/if}
						</div>
						<div class="itemTitle">{$plan_item.title}</div>
						<div class="btn_config">
							{$plan_item.config_button}
						</div> 
						<div class="clr"></div>
						{if $plan_item.config_button}
							<div id="container_{$index}" style="display: none;"></div>
						{/if}
					</li>
				{foreachelse}
					<li>No plan items found!</li>
				{/foreach}
			</ul>
		{/if}
	</div>
</fieldset>