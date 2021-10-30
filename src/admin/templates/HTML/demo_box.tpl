{* 7.4.2-8-gcb6e63a *}
{* Used for demo box at top of page on demo installation *}
{add_footer_html}
<script>
	//<![CDATA[
	jQuery(document).ready(function () {
		jQuery('#demo_box_label,#demo_box').fadeTo('fast',0.9);
		
		var demoHeight=jQuery('#demo_box').outerHeight(true);
		var isDemoOpen = false;
		jQuery('#demo_box').css({ top: '-'+demoHeight+'px' });
		
		jQuery('#demo_box_label').css({ top: '0' });
		
		
		
		jQuery('#demo_box_open_button').click(function () {
			var label = jQuery('#demo_box_label').stop();
			var box = jQuery('#demo_box').stop();
			
			isDemoOpen = !isDemoOpen;
			//re-calculate demobox height in case they changed orientation
			demoHeight=jQuery('#demo_box').outerHeight(true);
			if (isDemoOpen) {
				//open the demo box
				
				//start the things off where they should be
				label.css({ top: (demoHeight-5)+'px' });
				box.css({ top: '0px' });
				
				//show/hide the show/hide buttons
				jQuery('#demo_button_closed').hide();
				jQuery('#demo_button_open').show();
			} else {
				//close the demo box
				
				//start the things off where they should be
				label.css({ top: '0' });
				box.css({ top: '-'+demoHeight+'px' });
				
				//show/hide the show/hide buttons
				jQuery('#demo_button_closed').show();
				jQuery('#demo_button_open').hide();
			}
		});
	});
	//]]>
</script>
{/add_footer_html}

<div>
	<style scoped>
	
	/* Beta box controls */
	#demo_box {
		/* Really it's fixed, we just do this for stupid IE6 to at least "function" */
		position: absolute;
		
		left: 0px;
		top: -500px;
		width: 100%;
		overflow: hidden;
		padding: .5em;
		border-bottom: thick solid #7cb147;
		background: #eaeaea;
		z-index: 10000;
		
		font-size:.75rem;
		text-align: center;
		color: #333;
		font-family: Arial, Helvetica, sans-serif;
		transition-duration: .3s;
	}
	div>#demo_box {
		/* For "normal" browsers */
		position: fixed;
	}
	
	div#demo_box_label {
		/* Really it's fixed, we just do this for stupid IE6 to at least "function" */
		position: absolute;
		
		right: 45px;
		/* Start off off-screen, JS will move it in place... So it doesn't show when JS isn't there */
		top: -100px;
		z-index: 10001;
		
		font-size:.75rem;
		font-family: Arial, Helvetica, sans-serif;
		border-bottom: 5px solid #7cb147;
		border-left: 5px solid #7cb147;
		border-right: 5px solid #7cb147;
		border-top: none;
		background: #eaeaea;
		/*background: #e2e4e9 url('../admin_images/design/blu_button_bg.gif') repeat-x center left; */
		color: #666666; 
		padding: 3px 10px 2px 10px; 
		font-weight: bold; 
		white-space: nowrap;
		border-radius: 0 0 15px 15px;
		transition-duration: 0.5s;
	}
	
	div>div#demo_box_label {
		/* For "normal" browsers that know how to fix things */
		position: fixed;
	}
	
	#demo_box_label .mini_button {
		cursor: pointer;
		font-size: 12px;
		font-family: Arial, Helvetica, sans-serif;
		font-weight: bold;
	}
	
	#demo_box_label a.mini_button {
		height: 17px;
	}
	
	div#demo_box_label:hover {
		background: white;
	}
	
	/*edition control box*/
	
	.edition_dropdown,
	.master_dropdown,
	.theme_dropdown
	{
		border: 1px solid white; 
		padding: 5px;
		font-weight: bold;
		display: inline-block;
		text-align: left;
		vertical-align: top;
		margin: 5px;
	}
	
	#demo_box p {
		font-weight: normal;
	}
	
	.theme_dropdown select {
		width: 115px;
	}
	
	.edition_dropdown_text,
	.master_dropdown_text,
	.theme_dropdown_text
	{
		background-color: #efefef;
		
		font-size:12px;
	}
	
	/* Color boxes in tab thingy */
	.theme_color_box {
		display: inline-block;
		width: 12px;
		height: 10px;
		margin-top: 5px;
		margin-right: 3px;
		overflow: hidden;
	} 
	
	.text_green {
		color: #7cb147;
	}
	
	#demo_box h1 {
		color: #006699;
		margin: 3px;
		font-size: 14px;
	}
	
	</style>
	
	<div id="demo_box_label">
		<a href="#" onclick="return false;" class="mini_button" id="demo_box_open_button">
			<span id="demo_button_closed">
				{if $in_admin}
					Demo Options
				{else}
					Colors
					<span class="theme_color_box" style="background-color: {$colors.$primary};">&nbsp;</span>
					<span class="theme_color_box" style="background-color: {$colors.$secondary};">&nbsp;</span>
				{/if}
			</span>
			<span id="demo_button_open" style="display: none;">Hide Options</span>
		</a>
		|
		<a href="{if $in_admin}..{else}admin{/if}/index.php" class="mini_button"><span class="text_green">{if $in_admin}Client{else}Admin{/if}</span> Demo</a>
	</div>
	<div id="demo_box">
		<div>
			{if $err}
				<div style="text-align: center; color: red;">{$err}</div>
			{/if}
			
			<form method="post" id="switch_product" action="#" style="display: inline;">
				<div class="edition_dropdown">
					<h1>Product Selection</h1>
					<br />
					<select name="developer_force_type" onchange="jQuery('#switch_product').submit();">
						{foreach $valid_products as $name}
							<option value="{$name}"{if $name==$current_type} selected="selected"{/if}>
								GeoCore {$name|capitalize}
							</option>
						{/foreach}
					</select>
				</div>
			</form>
			
			<form method="post" id="switch_master" action="#" style="display: inline;">
				<div class="master_dropdown">
					<h1>Master Switches</h1>
					{foreach $masters as $name => $value}
						
						<input type="hidden" value="off" name="master_{$name}" />
						<label{if $name==$only} onclick="alert('Note: can not change that master switch when using GeoCore {$name|capitalize} product.'); return false;"{/if}>
							<input type="checkbox" value="on" {if $value === 'on'}checked="checked"{/if} {if $name==$only}readonly="readonly"{/if} name="master_{$name}" />
							{$name|regex_replace:"/_/":" "|capitalize}
						</label>
						<br />
					{/foreach}
					<div style="text-align: center;">
						<input type="submit" value="Update" class="mini_button" />
					</div>
				</div>
			</form>
			
			{if !$in_admin}
				<form method="post" id="switch_theme" action="#" style="display: inline;">
					<div class="theme_dropdown">
						<h1>Color Themes</h1>
						<label>Primary Color Theme: 
							<select name="css_primary_tset" class="theme_dropdown_text" onchange="jQuery('#switch_theme').submit();">
								{foreach from=$primary_tsets item='tset_title' key='tset'}
									<option value="{$tset}"{if $primary==$tset} selected="selected"{/if}>{$tset_title}</option>
								{/foreach}
							</select>
						</label>
						<div class="theme_color_box" style="background-color: {$colors.$primary};">&nbsp;</div>
						<br />
						<label>Secondary Color Theme: 
							<select name="css_secondary_tset" class="theme_dropdown_text" onchange="jQuery('#switch_theme').submit();">
								{foreach from=$secondary_tsets item='tset_title' key='tset'}
									<option value="{$tset}"{if $secondary==$tset} selected="selected"{/if}>{$tset_title}</option>
								{/foreach}
							</select>
						</label>
						<div class="theme_color_box" style="background-color: {$colors.$secondary};">&nbsp;</div>
						<p style="white-space: normal; max-width: 300px;">
							<a href="http://geodesicsolutions.com/support/geocore-wiki/doku.php/id,tutorials;design_adv;using_color_template_set/" onclick="window.open(this.href); return false;" style="color:blue;">How to apply color themes to your own site</a>
						</p>
					</div>
				</form>
			{/if}
			
			<p><strong>Note:</strong> These controls will not show on a normal installation.</p>
		</div>
	</div>
</div>