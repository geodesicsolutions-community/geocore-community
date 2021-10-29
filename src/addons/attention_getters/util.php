<?php

//addons/attention_getters/util.php
/**************************************************************************
Addon Created by Geodesic Solutions, LLC
Copyright (c) 2001-2018 Geodesic Solutions, LLC
All rights reserved
http://geodesicsolutions.com
see license attached to distribution
**************************************************************************/
##########GIT Build Data##########
##
## File Changed In GIT Commit:
## ##    7.5.3-36-gea36ae7
##
##################################

# Attention Getters Addon

class addon_attention_getters_util
{

    var $db;
    function addon_attention_getters_util()
    {
        $db = true;
        require(GEO_BASE_DIR . "get_common_vars.php");
        $this->db = $db;
    }

    function get_attention_getter_url($choice)
    {
        $sql = "select * from " . $this->db->geoTables->choices_table . " where choice_id = " . $choice;
        $attention_getter_result = $this->db->Execute($sql);
        if (!$attention_getter_result) {
            return false;
        } elseif ($attention_getter_result->RecordCount() == 1) {
            $show_attention_getter = $attention_getter_result->FetchNextObject();
            $attention_getter_url = $show_attention_getter->VALUE;
            return $attention_getter_url;
        } else {
            $this->classified_variables["attention_getter"] = 0;
            $attention_getter_url = "";
            return $attention_getter_url;
        }
    }


    function attention_getter_javascript()
    {
        $js =  <<< ag_js
		<script type='text/javascript'>
			
			function Disab() {
			var ch =  document.getElementById('agCheckbox');
			var obj = document.getElementsByName('c[attention_getter_choice]');
			
			for (i =0; i < obj.length;i++)
			{
			if (ch.checked)
			{obj[i].disabled=false;
			}
			else
			{obj[i].disabled=true;
			obj[i].checked = false;
			}
			}
		
		}


		function Enab(myid)
		{
			if (!myid) 
			{
			myid = 0;
			return false;
			}
		
			var obj = document.getElementsByName('c[attention_getter_choice]');		 
			var rd =  document.getElementById('geo_radio'+myid);
			var ch =  document.getElementById('agCheckbox');
			
			rd.checked = true;
			ch.checked = true;
			
			for (i =0; i < obj.length;i++)
			{
			obj[i].disabled=false;
			}
		}

</script>
ag_js;

        return $js;
    }

    function display_attention_getter_choices($params)
    {
        $text =& geoAddon::getText('geo_addons', 'attention_getters');


        $allFree = !geoMaster::is('site_fees');
        $price_plan = $params["price_plan"];
        $display_amount = $params["cost"];
        $body = "";
        if ((isset($price_plan['use_attention_getters']) && $price_plan['use_attention_getters']) || !geoPC::is_ent()) {
            $sql_query = "select * from " . geoTables::choices_table . " where type_of_choice = 10";
            $attention_getters_result = $this->db->Execute($sql_query);
            if (!$attention_getters_result) {
                return false;
            }

            $tpl = new geoTemplate('addon', 'attention_getters');
            $tpl->assign(geoCart::getInstance()->getCommonTemplateVars());
            $tpl->assign('mainToggle', ($params["toggle"]) ? true : false);
            $tpl->assign('toggleLabel', $text['AG_label']);

            $tpl->assign('input_extra', $params['checked']);
            $tpl->assign('checkbox_hidden', $params['checkbox_hidden']);

            if (isset($params["error"]) && (strlen($params["error"]) > 0)) {
                $tpl->assign('error', $params['error']);
            }

            $tpl->assign('allFree', $allFree);
            $tpl->assign('price', $display_amount); //already passed through geoString::displayPrice in the order item

            $list = array();
            for ($i = 0; $show_attention_getter = $attention_getters_result->FetchRow(); $i++) {
                $list[$i]['id'] = $show_attention_getter['choice_id'];
                $list[$i]['checked'] = ($params["choice"] == $show_attention_getter['choice_id']) ? true : false;
                $list[$i]['img'] = $show_attention_getter['value'];
            }
            $tpl->assign('list', $list);
            $body = $tpl->fetch('attention_getter_choices.tpl');
            //include the CSS file from external CSS
            $pre = (defined('IN_ADMIN')) ? '../' : '';
            geoView::getInstance()->addCssFile($pre . geoTemplate::getUrl('css', 'addon/attention_getters/listing_placement.css'));
        }

        return $body;
    }



    public function autoAdd($fromDir, $clearExisting = false)
    {
        $fromDir = trim($fromDir);
        if (!is_dir(GEO_BASE_DIR . $fromDir)) {
            return false;
        }
        $db = 1;
        include GEO_BASE_DIR . 'get_common_vars.php';

        if ($clearExisting) {
            $db->Execute("DELETE FROM " . geoTables::choices_table . " WHERE `type_of_choice`=10");
        }

        $existing = $db->GetAssoc("SELECT `choice_id`, `value` FROM " . geoTables::choices_table . " WHERE `type_of_choice`=10");

        $sql = $db->Prepare("INSERT INTO " . geoTables::choices_table . " 
		(`type_of_choice`,`display_value`, `value`)
		VALUES (10, ?, ?)");
        if (!$sql) {
            //error preparing sql statement
            return false;
        }
        if (!($dh = opendir(GEO_BASE_DIR . $fromDir))) {
            return false;
        }
        while (($file = readdir($dh)) !== false) {
            if (!is_dir(GEO_BASE_DIR . $fromDir . $file) && !in_array($file, array('.','..'))) {
                //insert into DB
                $location = $fromDir . $file;
                if (!in_array($location, $existing)) {
                    //remove extension, replace _ with spaces
                    $name = str_replace('_', ' ', substr($file, 0, strpos($file, '.')));
                    $db->Execute($sql, array($name, $location));
                }
            }
        }

        return true;
    }
}
