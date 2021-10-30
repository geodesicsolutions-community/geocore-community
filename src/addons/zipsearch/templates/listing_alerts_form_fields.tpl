{*  7.4.4-22-g21b6325 *}
<label class="field_label" for="d[zip_center]">{$msgs.listing_alert_basic_distance_header}</label>
{$msgs.listing_alert_within}
{if $units == 'M'}{$u = $msgs.listing_alert_mi}{else}{$u = $msgs.listing_alert_km}{/if}
<select class="field" name="d[zip_distance]" />
	<option value="0">0 {$u}</option>
	<option value="5">5 {$u}</option>
	<option value="10">10 {$u}</option>
	<option value="15">15 {$u}</option>
	<option value="20">20 {$u}</option>
	<option value="25">25 {$u}</option>
	<option value="30">30 {$u}</option>
	<option value="40">40 {$u}</option>
	<option value="50">50 {$u}</option>
	<option value="75">75 {$u}</option>
	<option value="100">100 {$u}</option>
	<option value="200">200 {$u}</option>
	<option value="300">300 {$u}</option>
	<option value="400">400 {$u}</option>
	<option value="500">500 {$u}</option>
</select>
{$msgs.listing_alert_of}
<input type="text" class="field" name="d[zip_center]" size="5" />
