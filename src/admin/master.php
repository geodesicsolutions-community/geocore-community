<?php

##########GIT Build Data##########
##
## File Changed In GIT Commit:
##
##    16.09.0-79-gb63e5d8
##
##################################

class manageMaster
{
    public $switches = array (
        'classifieds' => array (
            'label' => 'Classifieds',
            'description' => 'Classifieds allow users to advertise items for sale in a classified listing format and communicate with potential buyers.',
            ),
        'auctions' => array (
            'label' => 'Auctions',
            'description' => 'Auctions allow users to sell items in a variety of auction formats, with winning bidders committing to their purchases.',
            ),
        'site_fees' => array (
            'label' => 'Site Fees',
            'description' => 'Allows you to charge users of your site for listings and other items placed on your site.',
            ),
    );


    public function display_master_switches()
    {
        $admin = geoAdmin::getInstance();
        $db = DataAccess::getInstance();

        $tpl_vars = array();

        //TODO: add hook to let addons add switches as well to $this->switches

        $only = geoPC::license_only();
        $switches = array();
        foreach ($this->switches as $switch => $info) {
            if ($only && $only !== $switch && in_array($switch, array('classifieds','auctions'))) {
                continue;
            }

            $switches[$switch] = $info;

            //set value for each one
            $switches[$switch]['value'] = geoMaster::is($switch);
        }

        $tpl_vars['switches'] = $switches;
        if (isset($_GET['only']) && $_GET['only']) {
            $admin->userNotice('Cannot turn that off, since your license enables that master switch.');
        }
        $tpl_vars['admin_msgs'] = geoAdmin::m();

        $admin->setBodyTpl('master_switches.tpl')
            ->v()->setBodyVar($tpl_vars);
    }

    public function update_master_switches()
    {
        $switch = trim($_POST['toggle']);
        $master = geoMaster::getInstance();

        //toggle it!  or at least try to...
        $master->$switch = !$master->$switch;

        if (geoPC::license_only() === $switch) {
            //they tried to turn it off
            $end = '&only=1';
        }

        //reload page to prevent stale data
        header('Location: index.php?page=master_switches&mc=site_setup' . $end);
        require GEO_BASE_DIR . 'app_bottom.php';
        exit;
    }
}
