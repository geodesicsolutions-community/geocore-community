{* 2.5.0 *}
{*
This template is used to display the example tag help page
*}
<fieldset>
	<legend>Information about Tags</legend>
	<div>
		<p class="page_note">
			You will find information about each addon tag provided by this addon
			below.
		</p>
		<div class="{cycle values='row_color1,row_color2'}">
			<div class="leftColumn">{ldelim}addon author='geo_addons' addon='example' tag='tag_name1'}</div>
			<div class="rightColumn">
				This tag uses the <strong>recommended</strong> method for
				displaying tag contents, by using a sub-template loaded internally.<br />
				Use this in your template, and it will be replaced by the text "Example Tag text 1.".
			</div>
			<div class="clearColumn"></div>
		</div>
		<div class="{cycle values='row_color1,row_color2'}">
			<div class="leftColumn">{ldelim}addon author='geo_addons' addon='example' tag='tag_name2'}</div>
			<div class="rightColumn">
				This tag uses the <strong>not</strong> recommended method for
				displaying tag contents, by echo'ing or returning the text, without
				the use of a template.  Using this method will work but is discouraged
				except for special cases.<br />
				Use this tag in your template, and it will be replaced by the text "Example echo in tag 2!Example return Tag text 2." 
			</div>
			<div class="clearColumn"></div>
		</div>
		<div class="{cycle values='row_color1,row_color2'}">
			<div class="leftColumn">{ldelim}listing addon='example' tag='listing_tag_example'}</div>
			<div class="rightColumn">
				This is a special tag, used on listing details or in the browsing
				sub template, used to display something for a listing.  This particular
				tag will display the title of the listing, in the color green.<br />
				<br />
				The color can actually be changed, by adding the parameter title_color to
				the tag, for instance to use <strong>blue</strong> instead, use:<br />
				<strong>{ldelim}listing addon='example' tag='listing_tag_example' title_color='blue'}</strong>
			</div>
			<div class="clearColumn"></div>
		</div>
	</div>
</fieldset>