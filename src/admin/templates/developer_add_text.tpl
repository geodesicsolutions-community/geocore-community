{* 6.0.7-3-gce41f93 *}

<fieldset>
	<legend>upgrade/versions/VERSION/arrays.php</legend>
	<div>
		<div class="row_color1">
			<div class="leftColumn">
				$upgrade_array = array (
			</div>
			<div class="rightColumn">
				<input type="text" value="array ({$page_id}, {$message_id}, 1, '{$text|escape:'html'}')," readonly="readonly" />
			</div>
			<div class="clearColumn"></div>
		</div>
		<div class="row_color2">
			<div class="leftColumn">
				$insert_text_array = array (
			</div>
			<div class="rightColumn">
				<input type="text" value="array ({$message_id}, '{$name|escape:'html'}', '{$description|escape:'html'}', '', {$page_id}, {$display_order}, {$classauctions_only})," readonly="readonly" />
			</div>
			<div class="clearColumn"></div>
		</div>
	</div>
</fieldset>
<fieldset>
	<legend>sql/messages.sql</legend>
	<div>
		<div class="row_color1">
			<div class="leftColumn">
				INSERT INTO `geodesic_pages_messages`
			</div>
			<div class="rightColumn">
<textarea readonly="readonly" rows="3" cols="40">,
({$message_id}, '{$name|escape:'html'}', '{$description|escape:'html'}', '', {$page_id}, {$display_order}, {$classauctions_only}, 0, 0)
;</textarea>
				<br />Note: Insert with comma after the last <strong>)</strong> and do not leave 2 <strong>;</strong>
			</div>
			<div class="clearColumn"></div>
		</div>
		<div class="row_color2">
			<div class="leftColumn">
				INSERT INTO `geodesic_pages_messages_languages`
			</div>
			<div class="rightColumn">
<textarea readonly="readonly" rows="3" cols="40">,
({$page_id}, {$message_id}, 1, '{$text|escape:'html'}')
;</textarea>
				<br />Note: Insert with comma after the last <strong>)</strong> and do not leave 2 <strong>;</strong>
			</div>
			<div class="clearColumn"></div>
		</div>
	</div>
</fieldset>
<fieldset>
	<legend>Text id used</legend>
	<div class="medium_font">
		Message ID Used:  {$message_id}
	</div>
</fieldset>
