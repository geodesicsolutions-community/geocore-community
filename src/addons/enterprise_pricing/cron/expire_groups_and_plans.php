<?php
//expire_groups_and_plans.php
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
## 
##    7.5.3-36-gea36ae7
## 
##################################

if (!defined('GEO_CRON_RUN')){
	die('NO ACCESS');
}
$this->log('Top of expire_groups_and_plans!',__line__);
$sql = "select * from ".geoTables::expirations_table." where expires < ".$this->time();
$type_result = $this->db->Execute($sql);
if (!$type_result)
{
	$this->log('DB Error:  Sql: '.$sql.' Error: '.$this->db->ErrorMsg(),__line__);
	return false;
}

while ($show = $type_result->FetchNextObject()) {
	if ($show->TYPE == 1) {
		$sql = "select group_expires_into from ".$this->db->geoTables->groups_table." where group_id = ?";
		$expire_into_result = $this->db->Execute($sql, $show->TYPE_ID);
		if (!$expire_into_result) {
			$this->log('DB Error:  Sql: '.$sql.' Error: '.$this->db->ErrorMsg(), __line__);
			return false;
		} elseif ($expire_into_result->RecordCount() == 1) {
			$show_expire_into = $expire_into_result->FetchNextObject();
			//expire group
			if ($show->TYPE_ID_EXPIRES_TO)
			{
				$sql = "update ".$this->db->geoTables->user_groups_price_plans_table." set
					group_id = ?
					where group_id = ?";
				$update_group_result = $this->db->Execute($sql, array($show_expire_into->GROUP_EXPIRES_INTO, $show->TYPE_ID));
				if (!$update_group_result)
				{
					$this->log('DB Error:  Sql: '.$sql.' Error: '.$this->db->ErrorMsg(),__line__);
					return false;
				}
			}
		}
	} elseif ($show->TYPE == 2) {
		//expire price plans
		$sql = "select * from ".$this->db->geoTables->price_plans_table." where price_plan_id = ?";
		$price_plan_result = $this->db->Execute($sql, array($show->TYPE_ID));
		if (!$price_plan_result) {
			$this->log('DB Error:  Sql: '.$sql.' Error: '.$this->db->ErrorMsg(),__line__);
			return false;
		} else if ($price_plan_result->RecordCount() == 1) {
			$expire_into_check = true;
			if ($show_price_plan->PRICE_PLAN_EXPIRES_INTO) {
				//make sure price plan still exists...  and set $expire_into_check
				//to false if the expire into price plan is not found...
				$sql = "SELECT count(*) FROM ".geoTables::price_plans_table." WHERE `price_plan_id`=?";
				$count = (int)$this->db->GetOne($sql, array((int)$show_price_plan->PRICE_PLAN_EXPIRES_INTO));
				if (!$count) {
					//could not find the price plan "expiring into" so don't bother
					//going on with this expiration...
					$this->log('Cannot expire into price plan '.$show_price_plan->PRICE_PLAN_EXPIRES_INTO.', it does not exist!  Skiping this one.', __line__);
					$expire_into_check = false;
				}
			}
			
			if ($show->USER_ID && $expire_into_check) {
				//expires this specific user's price plan
				$show_price_plan = $price_plan_result->FetchNextObject();

				//check to see if ads expire with price plan
				if ($show_price_plan->AD_AND_SUBSCRIPTION_EXPIRATION == 1) {
					$sql = "update ".$this->db->geoTables->classifieds_table." set
						live = 0
						where seller = ?";
					$update_live_result = $this->db->Execute($sql, array($show->USER_ID));
					if ($update_live_result===false)
					{
						$this->log('DB Error:  Sql: '.$sql.' Error: '.$this->db->ErrorMsg(),__line__);
						return false;
					}
				}
				if ($show_price_plan->PRICE_PLAN_EXPIRES_INTO) {
					$field = ($show_price_plan->APPLIES_TO == 1) ? 'price_plan_id' : 'auction_price_plan_id';
					$sql = "update ".$this->db->geoTables->user_groups_price_plans_table." set
						$field = ?
						where id = ?";
					$update_price_plan_result = $this->db->Execute($sql, array($show_price_plan->PRICE_PLAN_EXPIRES_INTO, $show->USER_ID));
					if ($update_price_plan_result===false) {
						$this->log('DB Error:  Sql: '.$sql.' Error: '.$this->db->ErrorMsg(), __line__);
						return false;
					}
				}
			} else if ($expire_into_check) {
				//expires this price plan for every user
				$show_price_plan = $price_plan_result->FetchNextObject();
				if ($show_price_plan->AD_AND_SUBSCRIPTION_EXPIRATION == 1)
				{

					$sql = "select * from ".$this->db->geoTables->user_groups_price_plans_table."
						where price_plan_id = ?";
					$select_users_result = $this->db->Execute($sql, array($show->TYPE_ID));
					if ($select_users_result===false)
					{
						$this->log('DB Error:  Sql: '.$sql.' Error: '.$this->db->ErrorMsg(), __line__);
						return false;
					}
					elseif ($select_users_result->RecordCount() > 0)
					{
						while ($show_users = $select_users_result->FetchNextObject())
						{
							$sql = "update ".$this->db->geoTables->classifieds_table." set
								live = 0
								where seller = ?";
							$update_live_result = $this->db->Execute($sql, array($show_users->ID));
							if (!$update_live_result)
							{
								$this->log('DB Error:  Sql: '.$sql.' Error: '.$this->db->ErrorMsg(), __line__);
								return false;
							}
						}
					}
				}
				if ($show_price_plan->PRICE_PLAN_EXPIRES_INTO)
				{
					$field = ($show_price_plan->APPLIES_TO == 1) ? 'price_plan_id' : 'auction_price_plan_id';
					$sql = "update ".$this->db->geoTables->user_groups_price_plans_table." set
						$field = ?
						where $field = ?";
					$update_price_plan_result = $this->db->Execute($sql, array($show_price_plan->PRICE_PLAN_EXPIRES_INTO, $show->TYPE_ID));
					if (!$update_price_plan_result)
					{
						$this->log('DB Error:  Sql: '.$sql.' Error: '.$this->db->ErrorMsg(), __line__);
						return false;
					}
				}
			}
		}
	}
	//delete the expiration
	$sql = "delete from ".$this->db->geoTables->expirations_table." where expiration_id = ?";
	$delete_result = $this->db->Execute($sql, array($show->EXPIRATION_ID));
	$this->log('Finished moving one plan.',__line__);
}
return true; //finished task all the way.