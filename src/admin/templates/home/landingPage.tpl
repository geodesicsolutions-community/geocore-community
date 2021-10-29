{* 16.09.0-79-gb63e5d8 *}

<fieldset>
	<legend><i class="fa fa-home"></i> Admin Login Landing Page</legend>
	<div class="x-content landingPageForm">
		<form action='' id="landingPageForm" class="form-horizontal form-label-left" method='post'>
			<div class="form-group">

				<div class='input-group'>
					<select name="landingPage" id="landingPageSelect" onchange="$('landingPageForm').submit()" class="form-control col-md-7 col-xs-12">
						<option value='0'>Show Last Page Viewed</option>
						<option value='home'{if $landingPage == 'home'} selected="selected"{/if}>Display Home Page (this page)</option>
						{if !$hide_getting_started}<option value='checklist'{if $landingPage == 'checklist'} selected="selected"{/if}>Display Getting Started Checklist</option>{/if}
					</select>
					<input type="hidden" name="auto_save" value="1" />
					<span class="input-group-btn" style="margin:0 !important;"><input type="submit" name="auto_save" value="Apply" class="btn btn-primary" /></span>
				</div>

			</div>
			{if $hide_getting_started}
				<a class="btn btn-primary" href="index.php?page=home&dismiss_gs=no">Un-hide Getting Started Checklist</a>
			{/if}
		</form>
	</div>
</fieldset>