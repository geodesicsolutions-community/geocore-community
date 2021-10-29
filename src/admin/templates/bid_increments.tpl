{* 16.09.0-79-gb63e5d8 *}
{if !$inAjax}{$adminMsgs}{/if}

{if !$inAjax}<fieldset id="currentBidFieldset">{/if}
	<legend>Current Bid Increments</legend>
	<div>
		{if $inAjax}{$adminMsgs}{/if}
		<p class="page_note">Click on start or increment value to edit existing bid increments.  Note that if start of bracket is changed, bid increment brackets will automatically be re-ordered according to start amounts.</p>
		<form method="post" action="index.php?page=listing_bid_increments">
			<div class="table-responsive">
				<table class="table table-hover table-striped table-bordered">
					<thead>
						<tr class="col_hdr_top">
							<th style="width: 10px;">
								<input type="checkbox" id="bracketDeleteCheckAll" />
							</th>
							<th>
								Start of Bracket
							</th>
							<th>Up To</th>
							<th>
								Bid Increment
							</th>
						</tr>
					</thead>
					<tbody>
						{foreach from=$increments item=increment name=increments}
							<tr class="{cycle values='row_color1,row_color2'}">
								<td class="center">
									<input type="checkbox" name="deleteBrackets[]" value="{$increment.low}" class="deleteBracketCheckboxes" />
								</td>
								<td class="center" style="width: 200px;">
									{if $increment.low>0}<div style="float: right; cursor: pointer;" class="text_blue">[Edit]</div>
									<input type="hidden" value="{$increment.low}" />{/if}
									<div{if $increment.low>0} class="lowBrackets"{/if}>{$increment.low}</div>
								</td>
								<td class="center">{if $smarty.foreach.increments.last}And Up{else}Next Highest Bracket{/if}</td>
								<td class="center" style="width: 200px;">
									<div style="float: right; cursor: pointer;" class="text_blue">[Edit]</div>
									<input type="hidden" value="{$increment.low}" />
									<div class="bidIncrements">{$increment.increment}</div>
								</td>
							</tr>
						{/foreach}
					</tbody>
				</table>
			</div>
			<div class="center">
				<br /><br />
				<input type="submit" name="auto_save" value="Delete Selected Brackets" class="mini_cancel" />
			</div>
		</form>
	</div>
{if !$inAjax}</fieldset>{/if}

{if $inAjax}
{literal}
	<script type="text/javascript">
	//<![CDATA[
		geoBid.init();
	//]]>
	</script>
{/literal}
{else}
	<fieldset>
		<legend>New Bid Increment Bracket</legend>
		<div>
			<form action="index.php?page=listing_bid_increments" method="post" class='form-horizontal form-label-left'>
				<div class='x_content'>
					<div class='form-group'>
					<label class='control-label col-md-6 col-sm-6 col-xs-12'>Start of Bracket: </label>
					  <div class='col-md-6 col-sm-6 col-xs-12'>
					  <input type="text" name="addLow" value="0.00" size="5" class='form-control col-md-7 col-xs-12' style='max-width: 100px;' />
					  </div>
					</div>

					<div class='form-group'>
					<label class='control-label col-md-6 col-sm-6 col-xs-12'>Up To: </label>
					  <div class='col-md-6 col-sm-6 col-xs-12'>
					  <span class='vertical-form-fix'>Next Highest Bracket</span>
					  </div>
					</div>
									
					<div class='form-group'>
					<label class='control-label col-md-6 col-sm-6 col-xs-12'>Bid Increment: </label>
					  <div class='col-md-6 col-sm-6 col-xs-12'>
					  <input type="text" name="addIncrement" value="0.00" size="5" class='form-control col-md-7 col-xs-12' style='max-width: 100px;' />
					  </div>
					</div>
					
					<div class="center">
						<br />
						<input type="submit" name="auto_save" value="Add Increment" class="mini_button" />
					</div>
				</div>
			</form>
		</div>
	</fieldset>
	<div class='clearColumn'></div>
{/if}
