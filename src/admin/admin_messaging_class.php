<?php
//admin_messaging_class.php
/**************************************************************************
Geodesic Classifieds & Auctions Platform 18.02
Copyright (c) 2001-2018 Geodesic Solutions, LLC
All rights reserved
http://geodesicsolutions.com
see license attached to distribution
**************************************************************************/
##########GIT Build Data##########
##
## File Changed In GIT Commit:
## ##    16.09.0-109-g68fca00
## 
##################################

class Admin_messaging extends Admin_site {

	var $debug_messaging = 0;
	var $do_not_email = 0;  //0 sends the emails, 1 does not send the email
	function admin_messaging_form($db = 0,$list_info=0,$message_id=0)
	{
		if (!$this->admin_demo()) {
			$this->body .= "<form action='index.php?page=admin_messaging_send' method='post' class='form-horizontal form-label-left'>";
		} else {
			$this->body .= "<div class='form-horizontal'>";
		}
		$this->body .= "<fieldset id='SendMessage'>
			<legend>Send a Message</legend>
			<div class=\"table-responsive\">
			<table cellpadding='3' cellspacing='0' border='0' align='center' width=\"100%\" class=\"table table-hover table-striped table-bordered\">
				<tr>
					<td width='22%' valign='top' rowspan='2'>
						<table cellpadding='1' cellspacing='1' border='0' align='center' class='table table-hover table-striped table-bordered' width='100%'>
							<thead>
								<tr class='col_hdr_top'>
									<td colspan='2' class='col_hdr_left' valign='top'>
										List of Recipients
									</td>
								</tr>
							</thead>";
		$this->row_count = 0;

		$this->row_count++;
		$users_only = false;
		if ((is_array($list_info) && count($list_info) > 0) )
		{
			reset($list_info);
			while (list($key,$value) = each($list_info))
			{
				if ($key != "all" && strpos($key,'group')===0)
				{
					//it's a group yo
					//get the group name..
					$sql = 'SELECT name FROM '.geoTables::classified_groups_table.' WHERE group_id = '.$value;
					$result_gname = $this->db->Execute($sql);
					if (!$result_gname){
						$name = 'Unknown';
					} else {
						$row = $result_gname->FetchRow();
						$name = $row['name'];
					}

					$html .= "
					<tr class='".$this->get_row_color()."'>
						<td align='right' class='small_font'>
							$name
						</td>
						<td>
							<input type='checkbox' name='b[".$key."]' value=\"$value\" />
						</td>
					</tr>";
					$this->row_count++;
				}
				elseif ($key != 'all')
				{
					$users_only = true;
					//it's a user yo
					/*
					 * Default to be checked - LEAVE IT THIS WAY
					 * it defaults to checked because you get to this page from the
					 * search users page, and there can be tons and tons of users found.  It would
					 * neglect any benifit this feature might add if they have to go through and
					 * check Every Single User, insted it should stay so that it defaults
					 * to be checked for all the users found in the search.
					 *
					 * We might consider adding a JS check/uncheck all, but do NOT make it
					 * default to un-checked as a solution.
					 */
					$html .= "
					<tr class=".$this->get_row_color().">
						<td align=right class=small_font>
							$value
						</td>
						<td>
							<input type=\"checkbox\" checked='checked' name=\"b[$key]\" value=\"$value\" />
						</td>
					</tr>";
					$this->row_count++;
				}
			}
		}



		if (geoPC::is_ent() || geoPC::is_premier() || geoPC::is_basic())
		{
			$non_lite = true;
		}
		else
		{
			//if is lite, making sure that only the default group is set
			$html .= "<input type='hidden' name=b[group0] value='1'>";
			$html .= "<tr class=".$this->get_row_color().">
						<td align=right valign=top class=medium_font>
							<b>General group</b>
						</td>
						<td>
						</td>
					</tr>
				</table>";
		}


		if (!$users_only && $non_lite){
			$this->body .= "
					<tr class=".$this->get_row_color().">
						<td align=right valign=top class=medium_font>
							<b>All Groups</b>
						</td>
						<td>
							<input type=checkbox name=b[all] value=1".(($list_info["all"] == 1)? ' checked="checked"': '')." />
						</td>
					</tr>
					$html
					<tr>
						<td colspan=2 class=col_hdr_left>
							Groups
						</td>
					</tr>\n\t";
			$sql = "select name, group_id from ".$this->classified_groups_table." order by group_id";
			$result = $this->db->Execute($sql);
			if(!$result)
			{
				trigger_error('ERROR SQL MESSAGE: Query error, sql: '.$sql.' error: '.$this->db->ErrorMsg());
				//close tags to be nice
				$this->body .= "
				</table>
			</td>
		</tr>
	</table>
	</div>
</fieldset>
</form>";

				$this->site_error($this->db->ErrorMsg());

				return false;
			}
			else
				$group_number = 0;
			while($group = $result->FetchRow())
			{
				$this->body .= "
					<tr class=".$this->get_row_color().">
						<td align=right valign=top class=medium_font>
							<b>".$group["name"]."</b>
						</td>
						<td>
							<input type=checkbox name=b[group".$group_number."] value=".$group["group_id"].">
						</td>
					</tr>";
				$group_number++;
			}

		} else {
			$this->body .= $html;
		}


		$this->body .= "
				</table>
			</td>
			<td width=78% valign=top>
				<table cellpadding=1 cellspacing=1 border=0 width=100%>";

		if ($message_id && ($message_id["message_type"] == "Select Form Message" && $message_id["message_id_form"]) || $message_id['message_id_past']) {
			if ($message_id["message_type"] == "Select Form Message" && $message_id["message_id_form"])
				$this->message_sql_query = "select * from ".$this->form_messages_table." where message_id = ".$message_id["message_id_form"];
			elseif ($message_id["message_id_past"])
				$this->message_sql_query = "select * from ".$this->past_messages_table." where message_id = ".$message_id["message_id_past"];

			$result = $this->db->Execute($this->message_sql_query);
			if (!$result) {
				trigger_error('ERROR SQL: sql: '.$this->message_sql_query.' Error msg: '.$this->db->ErrorMsg());
				die ("message id: <pre>".print_r($message_id,1));
				return false;
			}
			if ($result->RecordCount() == 1) {
				//get message
				$show_message = $result->FetchRow();
				$message = geoString::fromDB($show_message["message"]);
				$subject = $show_message["subject"];
				$name_of_message = $show_message["message_name"];
				$content_type = $show_message['content_type'];
			}
			$select_plain = ($content_type == 'text/html')? '':' selected="selected"';
			$select_html = ($content_type == 'text/html')? ' selected="selected"':'';

			$this->body .= "
					<div class=\"header-color-primary-mute\">Message to Send</div>
					<div>
						<p class=page_note>
							Confirm the message and subject by reviewing and editing the form
							below.  Also confirm the list of users to the left.
						</p>
					</div>

					<div class='form-group'>
					<label class='control-label col-md-3 col-sm-3 col-xs-12'>Content Type: </label>
					  <div class='col-md-9 col-sm-9 col-xs-12'>
							<select name=\"d[content_type]\" class='form-control col-md-7 col-xs-12'>
								<option value=\"text/plain\"$select_plain>Plain Text</option>
								<option value=\"text/html\"$select_html>HTML</option>
							</select>
					  </div>
					</div>

					<div class='form-group'>
					<label class='control-label col-md-3 col-sm-3 col-xs-12'>Message Name: </label>
					  <div class='col-md-9 col-sm-9 col-xs-12'>
					  <input type=input size=50 maxsize=100 class='form-control col-md-7 col-xs-12' name=d[message_name] value='{$name_of_message}'>
					  </div>
					</div>

					<div class='form-group'>
					<label class='control-label col-md-3 col-sm-3 col-xs-12'>Subject: </label>
					  <div class='col-md-9 col-sm-9 col-xs-12'>
					  <input type=input size=50 maxsize=100 class='form-control col-md-7 col-xs-12' name=d[subject] value='{$subject}'>
					  </div>
					</div>

					<div class='form-group'>
					<label class='control-label col-md-3 col-sm-3 col-xs-12'>Message: </label>
					  <div class='col-md-9 col-sm-9 col-xs-12'>
					  <textarea name=d[message] cols=50 rows=20 class=\"form-control\">".geoString::specialChars($message)."</textarea>
					  </div>
					</div>
					";
		} else {
			$this->body .= "
				<div class=\"header-color-primary-mute\">Message to Send</div>
				<div>
					<p class=page_note>
						Select either a \"Recent Message\", a \"Form Message\", or create a new message using the form below. When selecting a \"Recent\" or \"Form\" message,
						you will have the opportunity to \"edit\" the message prior to sending it out.
					</p>
				</div>";
			//display the past messages list
			$this->get_last_ten_messages_list($db);

			//display the form messages list
			$this->get_form_messages_list($db);
			$select_plain = ($content_type == 'text/html')? '':' selected="selected"';
			$select_html = ($content_type == 'text/html')? ' selected="selected"':'';
			$this->body .= "

				<div class='form-group'>
				<label class='control-label col-md-3 col-sm-3 col-xs-12'>Content Type: </label>
				  <div class='col-md-9 col-sm-9 col-xs-12'>
					<select name=\"d[content_type]\" class='form-control col-md-7 col-xs-12'>
						<option value=\"text/plain\"$select_plain>Plain Text</option>
						<option value=\"text/html\"$select_html>HTML</option>
					</select>
				  </div>
				</div>

				<div class='form-group'>
				<label class='control-label col-md-3 col-sm-3 col-xs-12'>Message Name: </label>
				  <div class='col-md-9 col-sm-9 col-xs-12'>
				  <input type=input size=50 maxsize=100 name=d[message_name] class='form-control col-md-7 col-xs-12' value='{$name_of_message}'>
				  </div>
				</div>

				<div class='form-group'>
				<label class='control-label col-md-3 col-sm-3 col-xs-12'>Subject: </label>
				  <div class='col-md-9 col-sm-9 col-xs-12'>
				  <input type=input size=50 maxsize=100 name=d[subject] class='form-control col-md-7 col-xs-12' value='{$subject}'>
				  </div>
				</div>

				<div class='form-group'>
				<label class='control-label col-md-3 col-sm-3 col-xs-12'>Message: </label>
				  <div class='col-md-9 col-sm-9 col-xs-12'>
				  <textarea name=d[message] cols=50 rows=20 class=\"form-control\">".geoString::specialChars($message)."</textarea>
				  </div>
				</div>
				";
		}
		if (!$this->admin_demo()) $this->body .= "<div class=\"center\"><input type=submit name='auto_save' value=\"Send\"></div>";
		$this->body .= "</table>\n\t</td>\n</tr>\n";
		$this->body .= "</table></fieldset>";
		$this->body .= ($this->admin_demo()) ? "</div>" : "</form>";
		return true;
	} //end of function admin_message_form

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

	function get_last_ten_messages_list($db)
	{
		//get the last ten messages
		$sql = "select `message_id`,`subject`,`date_sent` from ".$this->past_messages_table." order by date_sent desc limit 0,10";
		$past_messages_result = $this->db->Execute($sql);
		if (!$past_messages_result)
		{
			$this->site_error($this->db->ErrorMsg());
			return false;
		}
		elseif ($past_messages_result->RecordCount() > 0)
		{
			$this->body .= "
			 <div class='form-group'>

				<div class='col-md-12 col-sm-12 col-xs-12 input-group'>";
				$this->body .= "<select name=c[message_id_past] class='form-control col-md-7 col-xs-12 input-group'>";
					while ($show = $past_messages_result->FetchRow())
					{
						$this->body .= "<option value=".$show["message_id"].">".$show["subject"]." - ".date($this->configuration_data['entry_date_configuration'],$show["date_sent"])."</option>";
					}
			$this->body .= "</select>";
				if (!$this->admin_demo()) $this->body .= "<span class='input-group-btn'><input type=submit name=c[message_type] class=\"btn btn-primary\" value=\"Select Recent Message\" style=\"margin: 0 !important; font-size: 14px;\"></span>
				</div>
			  </div>
			";
		}

	} //end of function get_last_ten_messages_list

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

	function get_form_messages_list($db)
	{
		//get the form messages list
		$sql = "select `message_id`,`message_name` from ".$this->form_messages_table." order by message_name";
		$form_messages_result = $this->db->Execute($sql);
		if (!$form_messages_result)
		{
			$this->site_error($this->db->ErrorMsg());
			return false;
		}

		elseif ($form_messages_result->RecordCount() > 0)
		{
			$this->body .= "
			 <div class='form-group'>
				<div class='col-md-12 col-sm-12 col-xs-12 input-group'>";
				$this->body .= "<select name=c[message_id_form] class='form-control col-md-7 col-xs-12 input-group'>";
					while ($show = $form_messages_result->FetchRow())
					{
						$this->body .= "<option value=".$show["message_id"].">".$show["message_name"]."</option>";
					}
			$this->body .= "</select>";
				if (!$this->admin_demo()) $this->body .= "<span class='input-group-btn'><input type=submit name=c[message_type] class=\"btn btn-primary\" value=\"Select Form Message\" style=\"margin: 0 !important; font-size: 14px;\"></span>
				</div>
			  </div>";
		else
		{
			$this->body .= "<div class=\"center page_note_error\">There are currently no form messages to display.</div>";
		}
		//echo "<div class=\"center\"><a href=index.php?a =26><span class=medium_font>Click to add or edit Form Messages</span></a></div>";

		}

	} //end of function get_form_messages_list

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

	function send_admin_message($db,$list_info=0,$message_info=0,$start_from=0,$message_id=0)
	{
		$mail = geoEmail::getInstance();
		$admin = geoAdmin::getInstance();

		if ($list_info && ($message_info || $message_id))
		{
			$limit = $this->db->get_site_setting('admin_messaging_send_limit');
			$limit = ($limit)? $limit : 5000; //set default value
			if (!$start_from)
				$start_from = 0;

			$todays_date = geoUtil::time();
			if (!$message_id)
			{
				if (count($list_info) > 0)
				{
					reset($list_info);
					while (list($key,$value) = each($list_info))
					{
						if ($key == "all")
						{
							$send_to_all = 1;
						}
						elseif(substr_count($key,"group") > 0)
						{
							$send_to_groups = 1;
							$groups[] = $value;
						}
					}
				}
				if(!$send_to_all)
					$send_to_all = 0;
				if(!$send_to_groups)
					$send_to_groups = 0;

				if ((strlen(trim($message_info["subject"])) > 0) &&
					(strlen(trim($message_info["message"])) > 0))
				{
					//insert the new info

					$sql = "insert into ".$this->past_messages_table."
						(date_sent,all_sent,message_name,subject,message, content_type)
						values (?, ?, ?, ?, ?, ?)";
					$query_data = array($todays_date, $send_to_all, $message_info["message_name"], $message_info["subject"], geoString::toDB($message_info["message"]), $message_info['content_type']);
					$result = $this->db->Execute($sql, $query_data);
					if ($this->debug_messaging) echo $sql."<bR>\n";
					if (!$result)
					{
						$admin->userError("Database error occured.");
						trigger_error("ERROR SQL: SQL $sql Error: ".$this->db->ErrorMsg());
						return false;
					}
					$message_id = $this->db->Insert_ID();
				}
			}
			else
			{
				if ($list_info == 1)
					$send_to_all = 1;
				else{
					$send_to_all = 0;
					$send_to_groups = 1;
					$groups = $list_info;
				}

				$sql = "select * from ".$this->past_messages_table."
					where message_id = ".$message_id;
				$result = $this->db->Execute($sql);
				if ($this->debug_messaging) echo $sql."<bR>\n";
				if (!$result) {
					$admin->userError("Database error occured.");
					trigger_error("ERROR SQL: SQL $sql Error: ".$this->db->ErrorMsg());
					return false;
				} elseif ($result->RecordCount() == 1) {
					$show_message = $result->FetchRow();
					$message_info["subject"] = $show_message["subject"];
					$message_info["message"] = geoString::fromDB($show_message["message"]);
					$message_info["message_name"] = $show_message["message_name"];
					$message_info['content_type'] = $show_message['content_type'];
				} else {
					$admin->userError("Could not find the pas message requested.");
					return false;
				}
			}

			$subject = $message_info["subject"];
			$message = $message_info["message"];
			$content_type = (isset($message_info['content_type']))? $message_info['content_type'] : 0;

			if ($send_to_all || $send_to_groups)
			{
				if ($send_to_all){
					$group_query = '';
				} else {
					//sending to groups
					$group_query = 'and groups.group_id in ( '.implode(', ',$groups).' )';
				}

				$anon = geoAddon::getRegistry('anonymous_listing');
				$anon_query = '';
				if($anon) {
					$anon_user_id = $anon->get('anon_user_id',false);
					if($anon_user_id) {
						$anon_query = " and groups.id <> '".$anon_user_id."' ";
					}
				}

				//$sql = "select count(*) as total_receivers from ".$this->userdata_table." where id != 1";
				$sql = "select count(*) as total_receivers from ".$this->user_groups_price_plans_table." as groups where groups.id != 1 ".$anon_query.$group_query;

				$total_result = $this->db->Execute($sql);
				if ($this->debug_messaging) echo $sql."<bR>\n";
				if (!$total_result)
				{
					$admin->userError("Database error occured.");
					trigger_error("ERROR SQL: SQL $sql Error: ".$this->db->ErrorMsg());
					return false;
				}
				//$sql = "select username,email from ".$this->userdata_table." where id != 1 limit ".$start_from.",".$limit;
				$sql = "select user.id as id, username, email from ".$this->userdata_table." as user, ".$this->user_groups_price_plans_table." as groups where user.id = groups.id and user.id != 1 ".$anon_query.$group_query. " limit $start_from, $limit";

				$result = $this->db->Execute($sql);
				if ($this->debug_messaging) echo $sql."<bR>\n";
				if (!$result) {
					$admin->userError("Database error occured.");
					trigger_error("ERROR SQL: SQL $sql Error: ".$this->db->ErrorMsg());
					return false;
				}
				elseif (($result->RecordCount() > 0) && ($total_result)) {
					$show_total = $total_result->FetchRow();
					if ($show_total["total_receivers"] > $limit) {
						//loop through the emails using the limit as the max number per invocation
						$this->body .= "<html><head><title>sent to ".$start_from." of ".$show_total["total_receivers"]." so far</title>";
						if (!$send_to_all && $send_to_groups){
							$count = 1;
							foreach ($groups as $group){
								$b []='b['.$count.']='.$group;
								$count++;
							}
							$b = implode('&',$b);
						} else {
							$b = 'b=1';
						}
						$all = ($send_to_all)? '&all=1':'&all=0';
						$delay = $this->db->get_site_setting('admin_messaging_refresh_delay');
						$delay = ($delay)? $delay : 10;
						$next = "index.php?page=admin_messaging_send&amp;e={$message_id}&amp;{$b}&amp;start_from=".($start_from + $limit)."&amp;auto_save_ajax=1";
						$this->body .= "<META HTTP-EQUIV=\"Refresh\" CONTENT=\"$delay;URL={$next}\">";
						$this->body .= "</head><body>
						<p class='page_note'><strong>WARNING:</strong> Do NOT refresh the page, or it will send duplicate e-mails to every user processed in the current batch.</p>
						<table width=\"100%\">";
						$this->body .= "<tr class='col_hdr_top'><td>Emails have just been added to the e-mail queue:</td></tr>";
						//disable time limit
						set_time_limit();
						while ($show = $result->FetchRow()) {
							if (!$this->do_not_email) {
								//send e-mail
								$mail->addQueue($show["email"],$subject,$message,0,0,0,$content_type);
							}
							$this->body .= "<tr class=".$this->get_row_color().">\n\t
								<td class=medium_font>\n\t".$show["username"]." - ".$show["email"]." \n\t</td>\n</tr>\n";
							if (!$send_to_all){
								$sql = "insert into ".$this->past_messages_recipients_table."
									(user_id,message_id)
									values
									(".$show['id'].",".$message_id.")";
								$user_record_result = $this->db->Execute($sql);
							}
							$this->row_count++;
						}
						//save the queue
						$mail->saveQueue();
						$this->body .= "<tr class='col_ftr'><td>queued e-mails for ";
						if (($start_from + $limit) > $show_total["total_receivers"])
							$this->body .= "all chosen recipients";
						else
							$this->body .= ($start_from + $limit)." of ".$show_total["total_receivers"]." so far</td></tr>";
						$this->body .= "</table>
							<div class='center'>Will continue in {$delay} seconds, or <a href='{$next}'>click here to continue</a></div>
						</body></html>";

					} else {
						$subject_output = geoString::specialChars($subject);
						$message_output = ($content_type == 'text/html')? $message : nl2br(geoString::specialChars($message));

						$this->body .= "<fieldset id='MessageSent'><legend>Message Queued</legend><table cellpadding=3 cellspacing=1 border=0 align=center width=\"100%\">\n";
						//$this->title .= "Admin Messaging";
						$this->description .= "The following message has been sent.";
						$this->body .= "<tr>\n\t<td valign=top align=left class=medium_font width=\"20%\">\n\t<b>Subject:</b> \n\t</td>\n\t";
						$this->body .= "<td class=medium_font>\n\t".$subject_output." \n\t</td>\n</tr>\n";
						$this->body .= "<tr><td class='medium_font'>Content Type:</td><td style='medium_font'>$content_type</td></tr>";
						$this->body .= "<tr>\n\t<td valign=top align=left class=medium_font>\n\t<b>Message:</b> \n\t</td>\n\t";
						$this->body .= "<td class=medium_font>\n\t$message_output\n\t</td>\n</tr>\n";
						$this->body .= "<tr>\n\t<td colspan=2 class=medium_font_btop><strong>Message Sent to the Following Recipients:</strong></td>\n</tr>\n";
						$this->row_count = 0;

						while ($show = $result->FetchRow())
						{
							@set_time_limit(300);
							if (!$this->do_not_email)
							{
								//send e-mail
								$mail->addQueue($show["email"],$subject,$message,0,0,0,$content_type);
							}
							$this->body .= "<tr class=".$this->get_row_color().">\n\t
								<td colspan=2 class=medium_font>\n\t".$show["username"]." - ".$show["email"]." \n\t</td>\n</tr>\n";
							if (!$send_to_all){
								$sql = "insert into ".$this->past_messages_recipients_table."
									(user_id,message_id)
									values
									(".$show['id'].",".$message_id.")";
								$user_record_result = $this->db->Execute($sql);
							}
							$this->row_count++;
						}
						$mail->saveQueue();
						$this->body .= "</table></fieldset>\n";
					}
				} else {
					$this->body .= "<table width=\"100%\">";
					$this->body .= "<tr><td align=center class=medium_font><br><br><strong>Your message was queued for complete list of recipients.</strong><br><br><a href='index.php?page=admin_messaging_send' class='mini_button'>Continue</a></td></tr>\n";
					$this->body .= "</table>";
				}
			} else {
				//send to just the ones on the list
				$subject_output = geoString::specialChars($subject);
				$message_output = ($content_type == 'text/html')? $message : nl2br(geoString::specialChars($message));

				$this->body .= "<fieldset><legend>Message Queued</legend>
								<div class='form-horizontal form-label-left x_content'>
									<div class='form-group'>
									<label class='control-label col-md-5 col-sm-5 col-xs-12'>Subject: </label>
									  <div class='col-md-6 col-sm-6 col-xs-12'>
									  	<span class='vertical-form-fix'>$subject_output</span>
									  </div>
									</div>
									<div class='form-group'>
									<label class='control-label col-md-5 col-sm-5 col-xs-12'>Content Type: </label>
									  <div class='col-md-6 col-sm-6 col-xs-12'>
									  	<span class='vertical-form-fix'>$content_type</span>
									  </div>
									</div>
									<div class='form-group'>
									<label class='control-label col-md-5 col-sm-5 col-xs-12'>Message: </label>
									  <div class='col-md-6 col-sm-6 col-xs-12'>
									  	<span class='vertical-form-fix'>$message_output</span>
									  </div>
									</div>
									";

				reset($list_info);
				while (list($key,$value) = each($list_info))
				{
					@set_time_limit(300);
					$sql = "select email from ".$this->userdata_table." where id = ".$key;
					$result = $this->db->Execute($sql);
					if ($this->debug_messaging) echo $sql."<bR>\n";
					if (!$result)
					{
						$admin->userError("Database error occured.");
						trigger_error("ERROR SQL: SQL $sql Error: ".$this->db->ErrorMsg());
						return false;
					}
					$show_email = $result->FetchRow();
					if ($this->debug_messaging)
					{
						echo $this->db->get_site_setting("email_configuration")." is email config<Br>\n";
					}
					if (!$this->do_not_email)
					{
						//send e-mail
						$mail->addQueue($show_email["email"],$subject,$message, 0, 0, 0, $content_type);
					}

					$this->body .= "
									<div class='form-group'>
									<label class='control-label col-md-5 col-sm-5 col-xs-12'>Recipient: </label>
									  <div class='col-md-6 col-sm-6 col-xs-12'>
									  	<span class='vertical-form-fix'>".$value." - ".$show_email["email"]."</span>
									  </div>
									</div>
									";

					$sql = "insert into ".$this->past_messages_recipients_table."
						(user_id,message_id)
						values
						(".$key.",".$message_id.")";
					$result = $this->db->Execute($sql);
					if (!$result)
					{
						$admin->userError("Database error occured.");
						trigger_error("ERROR SQL: SQL $sql Error: ".$this->db->ErrorMsg());
						return false;
					}
					$this->row_count++;
				} //end of while
				$mail->saveQueue();
				$this->body .= "</div></fieldset><div class='clearColumn'></div>";
			}
			return true;
		}
		else
		{
			$admin->userError("Missing required fields!");
			unset($_REQUEST['c']); // don't reload an old message on error
			return false;

		}

	} //end of function send_admin_message

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

	function display_message_history()
	{
		//get the message history list
		$this->body .= "<fieldset id='MessageHistory'><legend>Message History</legend><div class='table-responsive'><table cellpadding=2 cellspacing=0 border=0 align=center  width=\"100%\">\n\t";
		//$this->title .= "Messaging > Messages Sent";
		$this->description .= "The list below is the list of messages that
			have been sent to registrants in the past.  View details of a specific message by clicking the \"details\" link next to the appropriate
			message.  Delete a message from the history by clicking the \"delete\" link next to the appropriate message.";
		$sql = "select * from ".$this->past_messages_table." order by date_sent desc";
		$form_messages_result = $this->db->Execute($sql);
		if (!$form_messages_result)
		{
			$this->site_error($this->db->ErrorMsg());
			return false;
		}
		elseif ($form_messages_result->RecordCount() > 0)
		{
			$this->row_count = 0;
			$this->body .= '<div class="center"><a href="index.php?mc=admin_messaging&page=admin_messaging_history&c=ALL" onclick="return confirm(\'This will permanently erase all saved messages. Are you sure?\');">Clear Message History</a></div>';

			$this->body .= "<tr>\n\t<td colspan=2>\n\t<div class='table-responsive'><table cellpadding=2 cellspacing=1 border=0 class='table table-hover table-striped table-bordered'>\n\t";
			$this->body .= "<thead><tr class=col_hdr_top>\n\t\t<td class=col_hdr_left>\n\tMessage Name\n\t\t</td>\n\t\t";
			$this->body .= "<td class=col_hdr_left>\n\t<b>Date Sent</b> \n\t\t</td>\n\t\t";
			$this->body .= "<td class=col_hdr_left>\n\t<b>Subject</b> \n\t\t</td>\n\t\t";
			$this->body .= "<td class=col_hdr_left>Content Type</td>";
			$this->body .= "<td colspan=2 class=col_hdr_left>\n\t&nbsp; \n\t\t</td>\n\t</tr></thead>\n\t";
			while ($show = $form_messages_result->FetchRow())
			{
				$subject = geoString::specialChars($show['subject']);
				$this->body .= "<tr class=".$this->get_row_color().">\n\t<td valign=top class=medium_font>\n\t".$show["message_name"]." \n\t</td>\n\t";
				$this->body .= "<td valign=top class=medium_font>\n\t".date($this->configuration_data['entry_date_configuration'],$show["date_sent"])." \n\t</td>\n\t";
				$this->body .= "<td valign=top class=medium_font>\n\t".$subject." \n\t</td>\n\t";
				$this->body .= "<td valign=\"top\" class=\"medium_font\">{$show['content_type']}</td>";
				$this->body .= "<td valign=top width=100 align=center>\n\t".geoHTML::addButton('view', "index.php?mc=admin_messaging&page=admin_messaging_history&b=".$show["message_id"])."\n\t</td>\n\t";
				$this->body .= "<td valign=top width=100 align=center>\n\t".geoHTML::addButton('delete', "index.php?mc=admin_messaging&page=admin_messaging_history&c=".$show["message_id"]."&auto_save=1", false, '', 'lightUpLink mini_cancel')."\n\t</td>\n</tr>\n";
				$this->row_count++;
			}
			$this->body .= "</table></div>\n\t</td>\n</tr>\n";
		}
		else
		{
			$this->body .= "<tr>\n\t\t<td colspan=2 align=center>\n\t<div class=page_note_error>There are currently no messages to display.</div> </td></tr>\n\t\t";
		}
		$this->body .= "</table></div></fieldset>\n";
		return true;
	} //end of function display_message_history

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

	function delete_from_message_history($db,$message_id=0)
	{
		//$message_id is either the numerical ID of the message to delete, or 'ALL' to delete all messages
		if ($message_id)
		{
			//insert the new info
			$sql = "delete from ".$this->past_messages_table;
			if($message_id !== 'ALL') {
				$sql .=" where message_id = ".$message_id;
			}
			$result = $this->db->Execute($sql);
			if (!$result)
			{
				$this->site_error($this->db->ErrorMsg());
				return false;
			}

			$sql = "delete from ".$this->past_messages_recipients_table;
			if($message_id !== 'ALL') {
				$sql .=" where message_id = ".$message_id;
			}
			$result = $this->db->Execute($sql);
			if (!$result)
			{
				$this->site_error($this->db->ErrorMsg());
				return false;
			}
			return true;
		}
		else
		{
			return false;
		}

	}

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

	function display_message_history_detail($db,$message_id)
	{
		if ($message_id)
		{
			$sql = "select * from ".$this->past_messages_table." where message_id = ".$message_id;
			if ($this->debug_messaging) echo $sql."<bR>\n";
			$result = $this->db->Execute($sql);
			if (!$result)
			{
				if ($this->debug_messaging) echo $sql."<bR>\n";
				$this->site_error($this->db->ErrorMsg());
				return false;
			}
			elseif ($result->RecordCount() == 1)
			{
				$show = $result->FetchRow();
				$message_name = geoString::specialChars($show['message_name']);
				$subject=geoString::specialChars($show['subject']);
				$message = ($show['content_type']=='text/html')? geoString::fromDB($show['message']):nl2br(geoString::specialChars(geoString::fromDB($show['message'])));

				//get the message details
				$this->body .= "<fieldset id='MessageHistory'><legend>Message Details</legend><table cellpadding=2 cellspacing=0 border=0 align=center width=100%>\n\t";
				//$this->title = "Message History Details";
				$this->description = "Below is the details of the message that was sent.";
				$this->body .= '<tr class=row_color2><td width=50% align="right" class="medium_font"><strong>Content Type: </strong></td>';
				$this->body .= '<td class="medium_font">'.$show['content_type'].'</td>';
				$this->body .= "<tr>\n\t<td align=right class=medium_font>\n\t<b>Message Name: </b> \n\t</td>\n\t";
				$this->body .= "<td class=medium_font>\n\t".$message_name." \n\t</td>\n</tr>\n";
				$this->body .= "<tr class=row_color2>\n\t<td align=right class=medium_font>\n\t<b>Date Sent: </b> \n\t</td>\n\t";
				$this->body .= "<td class=medium_font>\n\t".date($this->configuration_data['entry_date_configuration'],$show["date_sent"])." \n\t</td>\n</tr>\n";
				$this->body .= "<tr>\n\t<td align=right class=medium_font>\n\t<b>Subject: </b> \n\t</td>\n\t";
				$this->body .= "<td class=medium_font>\n\t".$subject." \n\t</td>\n</tr>\n";
				$this->body .= "<tr class=row_color2>\n\t<td colspan=2 class=medium_font>\n\t<b>Message:</b> <br>";
				$this->body .= "\n\t".$message."\n\t</td>\n</tr>\n";

				//display list of registrants that received this message
				$this->body .= "<tr>\n\t<td colspan=2 class=medium_font>\n\t<b>List of Users that Received this Message:</b> \n\t</td>\n</tr>\n";
				if ($show["all_sent"])
				{
					$this->body .= "<tr>\n\t<td align=center colspan=2 class=medium_font>\n\t<div class=page_note_error>This message was sent to all current registrants at the time.</div> \n\t</td>\n</tr>\n";
				}
				else
				{
					$this->body .= "<tr>\n\t<td colspan=2>\n\t<table cellpadding=1 cellspacing=1 border=0 width=\"100%\">\n\t";
					$sql = "select * from ".$this->past_messages_recipients_table." where message_id = ".$message_id;
					$message_result = $this->db->Execute($sql);
					if ($this->debug_messaging) echo $sql."<bR>\n";
					if (!$message_result)
					{
						if ($this->debug_messaging) echo $sql."<bR>\n";
						$this->site_error($this->db->ErrorMsg());
						return false;
					}
					elseif ($message_result->RecordCount() > 0)
					{
						while ($show_message_recipient = $message_result->FetchRow())
						{
							$sql = "select username,email from ".$this->userdata_table." where id = ".$show_message_recipient["user_id"];
							$user_result = $this->db->Execute($sql);
							if ($this->debug_messaging) echo $sql."<bR>\n";
							if (!$user_result)
							{
								if ($this->debug_messaging) echo $sql."<bR>\n";
								$this->site_error($this->db->ErrorMsg());
								return false;
							}
							elseif ($user_result->RecordCount() == 1)
							{
								$show_user = $user_result->FetchRow();
								$this->body .= "<tr>\n\t\t<td align=center class=small_font>\n\t".$show_user["username"]." - ".$show_user["email"]." \n\t\t</td>\n\t</tr>\n\t";
							}
						}
					}
					else
					{
						$this->body .= "<tr>\n\t\t<td class=medium_font>\n\tno recipients \n\t\t</td>\n\t</tr>\n\t";
					}
					$this->body .= "</table>\n\t</td>\n</tr>\n";
				}
				$this->body .= "</table></fieldset>\n";
				$this->body .= "<table valign=center cellspacing=0 cellpadding=3 width=\"100%\">\n";
				$this->body .= "<tr>\n\t<td colspan=2 class=medium_font>\n\t
					<strong><br>Back to: </strong><a href=index.php?mc=admin_messaging&page=admin_messaging_history><span class=medium_font><strong>Message History</strong></span></a>\n\t</td>\n</tr>\n";
				$this->body .= "</table>\n";
				return true;
			}
			else
			{
				$this->body .= "<tr>\n\t\t<td colspan=2 class=medium_font align=center>\n\t<br><br><strong>There are no messages to display.</strong><br><br> \n\t\t";
			}
				$this->body .= "<tr>\n\t<td colspan=2 class=medium_font>\n\t
					<strong><br>Back to: </strong><a href=index.php?mc=admin_messaging&page=admin_messaging_history><span class=medium_font><strong>Message History</strong></span></a>\n\t</td>\n</tr>\n";
			$this->body .= "</table>\n";
			return true;
		}
		else
		{
			return false;
		}
	} //end of function display_message_history_detail

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	function display_admin_messaging_send ()
	{
		if ($this->body) {
			//must be in middle of sending to the list...  just display page
			$this->display_page();
			return;
		}
		//send a message to a list
		$this->body .= geoAdmin::m();
		$start_from = (isset($_REQUEST['start_from']))? $_REQUEST['start_from']:false;
		if (($_POST["d"] || $_REQUEST["e"]) && $_REQUEST["b"] && ($_REQUEST["z"] || $start_from)) {
			//send the text message to the list
			$this->admin_messaging_form($this->db,$_REQUEST["b"],$_REQUEST["c"]);
		} else if ($_REQUEST["c"]) {
			//display of prechosen message to edit
			$this->admin_messaging_form($this->db,$_REQUEST["b"],$_REQUEST["c"]);
		} else {
			//display the text management homepage
			$this->admin_messaging_form($this->db,$_REQUEST["b"]);
		}
		$this->display_page();
	}
	function update_admin_messaging_send()
	{
		$start_from = (isset($_REQUEST['start_from']))? $_REQUEST['start_from']:false;
		return $this->send_admin_message($this->db,$_REQUEST["b"],$_POST["d"],$start_from,$_REQUEST["e"]);
	}
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	function display_admin_messaging_form ()
	{
		$tpl_vars = array();

		//get the form messages list
		$sql = "select `message_name`,`message_id`,`content_type` from ".geoTables::form_messages." order by message_name";
		$form_messages_result = $this->db->Execute($sql);
		if (!$form_messages_result) {
			geoAdmin::m("DB Error, could not retrieve message list.",geoAdmin::ERROR);
		}

		$tpl_vars['messages_list'] = $form_messages_result;
		$tpl_vars['admin_msgs'] = geoAdmin::m();

		geoView::getInstance()->setBodyTpl('messaging/forms/list.tpl')
			->setBodyVar($tpl_vars);
	}
	function update_admin_messaging_form ()
	{
		//nothing is done here, just displaying forms
	}

	public function display_admin_messaging_form_new ()
	{
		//let main one show everything
		$this->display_admin_messaging_form();
	}

	public function update_admin_messaging_form_new ()
	{
		$type = $_POST['content_type'];
		$name = trim($_POST['message_name']);
		$subject = trim($_POST['subject']);

		$message = trim($_POST['message']);

		if (!$type || !in_array($type, array('text/plain','text/html'))) {
			geoAdmin::m("Invalid message content type specified.", geoAdmin::ERROR);
			return false;
		}
		if (!$name) {
			geoAdmin::m("Form Message Name required.", geoAdmin::ERROR);
			return false;
		}
		if (!$subject) {
			geoAdmin::m("Message Subject required.", geoAdmin::ERROR);
			return false;
		}

		if (!$message) {
			geoAdmin::m("Form Message Contents Required.", geoAdmin::ERROR);
			return false;
		}

		$sql = "insert into ".geoTables::form_messages."
					(message_name,subject,message, content_type)
					values
					(?, ?, ?, ?)";
		$query_data = array ($name,$subject,geoString::toDB($message), $type);
		$result = $this->db->Execute($sql, $query_data);
		if (!$result) {
			geoAdmin::m("Error adding new message.  Debug info:  ".$this->db->ErrorMsg(),geoAdmin::ERROR);
			return false;
		}
		return true;
	}

	public function display_admin_messaging_form_delete ()
	{
		//let main one do it all...
		$this->display_admin_messaging_form();
	}

	public function update_admin_messaging_form_delete ()
	{
		$message_id = (int)$_POST['message_id'];

		if (!$message_id) {
			geoAdmin::m("Invalid message, could not delete.");
			return false;
		}
		if (!$this->db->Execute ("DELETE FROM ".geoTables::form_messages." WHERE `message_id`=$message_id LIMIT 1")) {
			geoAdmin::m("Error deleting message.  Debug info: ".$this->db->ErrorMsg());
			return false;
		}
		geoAdmin::m("Message deleted successfully.");
		return true;
	}

	public function display_admin_messaging_form_edit ()
	{
		$message_id = (int)$_GET['message_id'];
		if (!$message_id) {
			geoAdmin::m("Message ID to edit not specified, could not edit.", geoAdmin::ERROR);
			return $this->display_admin_messaging_form();
		}
		$data = $this->db->GetRow("SELECT * FROM ".geoTables::form_messages." WHERE `message_id`=$message_id");

		if (!$data) {
			geoAdmin::m("Invalid ID specified, cannot edit that message.", geoAdmin::ERROR);
			return $this->display_admin_messaging_form();
		}

		$data['admin_msgs'] = geoAdmin::m();

		geoView::getInstance()->setBodyTpl("messaging/forms/edit.tpl")
			->setBodyVar($data);
	}

	public function update_admin_messaging_form_edit ()
	{
		$message_id = (int)$_GET['message_id'];

		if (!$message_id) {
			geoAdmin::m("Invalid message, could not edit.");
			return false;
		}
		$type = $_POST['content_type'];
		$name = trim($_POST['message_name']);
		$subject = trim($_POST['subject']);

		$message = trim($_POST['message']);

		if (!$type || !in_array($type, array('text/plain','text/html'))) {
			geoAdmin::m("Invalid message content type specified.", geoAdmin::ERROR);
			return false;
		}
		if (!$name) {
			geoAdmin::m("Form Message Name required.", geoAdmin::ERROR);
			return false;
		}
		if (!$subject) {
			geoAdmin::m("Message Subject required.", geoAdmin::ERROR);
			return false;
		}

		if (!$message) {
			geoAdmin::m("Form Message Contents Required.", geoAdmin::ERROR);
			return false;
		}

		$sql = "UPDATE ".geoTables::form_messages." SET `message_name`=?,
			`subject`=?,
			`message`=?,
			`content_type`=?
			WHERE `message_id`=?";
		$query_data = array ($name,$subject,geoString::toDB($message), $type, $message_id);
		$result = $this->db->Execute($sql, $query_data);
		if (!$result) {
			geoAdmin::m("DB Error editing existing message.  Debug info:  ".$this->db->ErrorMsg(),geoAdmin::ERROR);
			return false;
		}
		return true;
	}

	function display_admin_messaging_history()
	{
		$admin = geoAdmin::getInstance();
		if ($_REQUEST['b']) {
			$message_id = (int)$_REQUEST['b'];
			$sql = "select * from ".$this->past_messages_table." where message_id = ".$message_id;
			if ($this->debug_messaging) echo $sql."<bR>\n";
			$result = $this->db->Execute($sql);
			if (!$result) {
				$admin->userError("Error getting message data from database!");
			} elseif ($result->RecordCount() == 1) {
				$show = $result->FetchRow();
				$message_name = geoString::specialChars($show['message_name']);
				$subject=geoString::specialChars($show['subject']);
				$message = ($show['content_type']=='text/html')? geoString::fromDB($show['message']):nl2br(geoString::specialChars(geoString::fromDB($show['message'])));

				//get the message details
				$this->body .= "<fieldset id='MessageHistory'><legend>Message Details</legend><div class='x_content form-horizontal form-label-left'>";

				$this->body .= "<div class='form-group'>";
				$this->body .= "<label class='control-label col-md-5 col-sm-5 col-xs-12'>Content Type: </label>";
				$this->body .= "<div class='col-md-6 col-sm-6 col-xs-12'><span class='vertical-form-fix'>".$show['content_type']."</span>";
				$this->body .= "</div>";
				$this->body .= "</div>";

				$this->body .= "<div class='form-group'>";
				$this->body .= "<label class='control-label col-md-5 col-sm-5 col-xs-12'>Message Name: </label>";
				$this->body .= "<div class='col-md-6 col-sm-6 col-xs-12'><span class='vertical-form-fix'>".$message_name."</span>";
				$this->body .= "</div>";
				$this->body .= "</div>";

				$this->body .= "<div class='form-group'>";
				$this->body .= "<label class='control-label col-md-5 col-sm-5 col-xs-12'>Date Sent: </label>";
				$this->body .= "<div class='col-md-6 col-sm-6 col-xs-12'><span class='vertical-form-fix'>".date($this->configuration_data['entry_date_configuration'],$show["date_sent"])."</span>";
				$this->body .= "</div>";
				$this->body .= "</div>";

				$this->body .= "<div class='form-group'>";
				$this->body .= "<label class='control-label col-md-5 col-sm-5 col-xs-12'>Subject: </label>";
				$this->body .= "<div class='col-md-6 col-sm-6 col-xs-12'><span class='vertical-form-fix'>".$subject."</span>";
				$this->body .= "</div>";
				$this->body .= "</div>";

				$this->body .= "<div class='form-group'>";
				$this->body .= "<label class='control-label col-md-5 col-sm-5 col-xs-12'>Message: </label>";
				$this->body .= "<div class='col-md-6 col-sm-6 col-xs-12'><span class='vertical-form-fix'>".$message."</span>";
				$this->body .= "</div>";
				$this->body .= "</div>";

				//display list of registrants that received this message
				$this->body .= "<div class='form-group'>";
				$this->body .= "<label class='control-label col-md-5 col-sm-5 col-xs-12'>List of Users that Received this Message: </label>";
				if ($show["all_sent"]) {
					$this->body .= "<div class='center page_note_error'>This message was sent to all current registrants at the time.</div>";
				} else {
					$this->body .= "<div class='col-md-6 col-sm-6 col-xs-12'>";
					$sql = "select * from ".$this->past_messages_recipients_table." where message_id = ".$message_id;
					$message_result = $this->db->Execute($sql);
					if ($this->debug_messaging) echo $sql."<bR>\n";
					if (!$message_result) {
						if ($this->debug_messaging) echo $sql."<bR>\n";
						$this->site_error($this->db->ErrorMsg());
						return false;
					} elseif ($message_result->RecordCount() > 0) {
						while ($show_message_recipient = $message_result->FetchRow()) {
							$sql = "select username,email from ".$this->userdata_table." where id = ".$show_message_recipient["user_id"];
							$user_result = $this->db->Execute($sql);
							if ($this->debug_messaging) echo $sql."<bR>\n";
							if (!$user_result) {
								continue;
							} elseif ($user_result->RecordCount() == 1) {
								$show_user = $user_result->FetchRow();
								$this->body .= "<div><span class='vertical-form-fix'>".$show_user["username"]." - ".$show_user["email"]."</span></div>";
							}
						}
					} else {
						$this->body .= "<div><span class='vertical-form-fix'>No Recipients</span></div>";
					}
					$this->body .= "</div>";
					$this->body .= "</div>";
				}

				$this->body .= "</div></fieldset>";

			} else {
				$this->body .= "<div class='center'>\n\t<br><br><strong>There are no messages to display.</div><br><br>";
			}
			$this->body .= "
			<div style='padding: 5px;'><a href='index.php?mc=admin_messaging&page=admin_messaging_history' class='back_to'>
			<i class='fa fa-backward'> </i> Back to Message History</a></div>
			";
			$this->body .= "</div>";

		} else {
			//Display list of history
			$this->body .= "<fieldset id='MessageHistory'><legend>Message History</legend><div class=\"table-responsive\"><table cellpadding=2 cellspacing=0 border=0 align=center class=\"table table-hover table-striped table-bordered\">\n\t";
			//$this->title .= "Messaging > Messages Sent";
			$sql = "select * from ".$this->past_messages_table." order by date_sent desc";
			$form_messages_result = $this->db->Execute($sql);
			if (!$form_messages_result) {
				$admin->userError("Database error getting list of history messages.");
			} elseif ($form_messages_result->RecordCount() > 0) {
				$this->row_count = 0;

				$this->body .= '<tr><td colspan="2" style="text-align: center;"><a href="index.php?mc=admin_messaging&page=admin_messaging_history&c=ALL&auto_save=1" class="mini_button lightUpLink">Clear Message History</a></td></tr>';
				$this->body .= "<tr>\n\t<td colspan=2>\n\t<table cellpadding=2 cellspacing=1 border=0  class=\"table table-hover table-striped table-bordered\">\n\t";
				$this->body .= "<thead><tr class=col_hdr_top>\n\t\t<td class=col_hdr_left>\n\tMessage Name\n\t\t</td>\n\t\t";
				$this->body .= "<td class=col_hdr_left>\n\t<b>Date Sent</b> \n\t\t</td>\n\t\t";
				$this->body .= "<td class=col_hdr_left>\n\t<b>Subject</b> \n\t\t</td>\n\t\t";
				$this->body .= "<td class=col_hdr_left>Content Type</td>";
				$this->body .= "<td colspan=2 class=col_hdr_left>\n\t&nbsp; \n\t\t</td>\n\t</tr></thead>\n\t";
				while ($show = $form_messages_result->FetchRow()) {
					$subject = geoString::specialChars($show['subject']);
					$this->body .= "<tr class=".$this->get_row_color().">\n\t<td valign=top class=medium_font>\n\t".$show["message_name"]." \n\t</td>\n\t";
					$this->body .= "<td valign=top class=medium_font>\n\t".date($this->configuration_data['entry_date_configuration'],$show["date_sent"])." \n\t</td>\n\t";
					$this->body .= "<td valign=top class=medium_font>\n\t".$subject." \n\t</td>\n\t";
					$this->body .= "<td valign=\"top\" class=\"medium_font\">{$show['content_type']}</td>";
					$this->body .= "<td valign=top width=100 align=center>\n\t".geoHTML::addButton('view', "index.php?mc=admin_messaging&page=admin_messaging_history&b=".$show["message_id"])."\n\t</td>\n\t";
					$this->body .= "<td valign=top width=100 align=center>\n\t".geoHTML::addButton('delete', "index.php?mc=admin_messaging&page=admin_messaging_history&c=".$show["message_id"]."&auto_save=1", false, '', 'lightUpLink mini_cancel')."\n\t</td>\n</tr>\n";
					$this->row_count++;
				}
				$this->body .= "</table>\n\t</td>\n</tr>\n";
			} else {
				$this->body .= "<tr>\n\t\t<td colspan=2 align=center>\n\t<div class=page_note_error>There are currently no messages to display.</div> </td></tr>\n\t\t";
			}
			$this->body .= "</table></div>";

			$this->body .= "</fieldset><div class='clearColumn'></div>\n";
		}
		$this->body = geoAdmin::m().$this->body;
		$this->display_page();
	}

	function update_admin_messaging_history()
	{
		$admin = geoAdmin::getInstance();

		$message_id = trim($_POST['c']);
		if (!$message_id) {
			$admin->userError("No message history ID specified, cannot remove.");
			return false;
		}

		//insert the new info
		$sql = "delete from ".$this->past_messages_table;
		if($message_id !== 'ALL') {
			$sql .=" where message_id = ".(int)$message_id;
		}
		$result = $this->db->Execute($sql);
		if (!$result) {
			$admin->userError("Database error when attempting to delete message from history.");
			return false;
		}

		$sql = "delete from ".$this->past_messages_recipients_table;
		if ($message_id !== 'ALL') {
			$sql .=" where message_id = ".(int)$message_id;
		}
		$result = $this->db->Execute($sql);
		if (!$result) {
			$admin->userError("Database error when attempting to delete message from history.");
			return false;
		}
		return true;
	}
}

