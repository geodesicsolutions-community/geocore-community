<?php

// GeoCoreCE needs this for Version numbers (current and latest available)
// The other stuff may be able to be stripped.
// Intellisense reports: Undefined constant 'DEMO_MODE_TEXT'.

// DON'T FORGET THIS
if (class_exists('admin_AJAX') or die()) {
}

class ADMIN_AJAXController_homeLicense extends admin_AJAX
{

    public function getLicenseData()
    {
        $tpl = new geoTemplate(geoTemplate::ADMIN);
        $db = DataAccess::getInstance();
        if (defined('DEMO_MODE')) {
            $key = 'DEMO';
        } elseif (defined('DEMO_MODE_TEXT')) {
            $key = DEMO_MODE_TEXT;
        } else {
            $key = $db->get_site_setting('license');
        }
        $settings['version'] = $version = geoPC::getVersion();
        $settings['latestVersion'] = $latest = geoPC::getLatestVersion();

        if ($latest) {
            $settings['is_latest'] = version_compare($version, $latest, '>=');
        }

        //always show GeoTurbo as being the "latest" version
        if (geoPC::geoturbo_status()) {
            $settings['is_latest'] = true;
        } else {
            //Lease info
            $leased = geoPC::is_leased();
            $lease_extra = ' (Or at Lease Cancellation)';

            $settings['localLicenseExp'] = (defined('DEMO_MODE')) ? 'DEMO' : $this->_formatTimeLeft(geoPC::getLocalLicenseExpire());

            $settings['licenseExp'] = (defined('DEMO_MODE')) ? 'DEMO' : $this->_formatTimeLeft(geoPC::getLicenseExpire());

            $exp = geoPC::getDownloadExpire();

            $settings['updatesExpired'] = false;
            if (!defined('DEMO_MODE_TEXT') && $exp && $exp !== 'never' && $exp <= geoUtil::time()) {
                $settings['updatesExpired'] = true;
            }
            $exp = $this->_formatTimeLeft($exp);
            $settings['downloadExp'] = $exp;

            $settings['supportExp'] = $this->_formatTimeLeft(geoPC::getSupportExpire());

            if (!defined('DEMO_MODE_TEXT')) {
                $settings['packageId'] = $packageId = geoPC::getPackageId();
            }
            $settings['maxSeats'] = geoPC::maxSeats();
            if ($settings['maxSeats'] == -1) {
                $settings['maxSeats'] = 'Unlimited';
            }
            $settings['currentSeats'] = 0;

            $settings['show_upgrade_pricing'] = false;
        }

        $tpl->assign($settings);

        echo $tpl->fetch('home/versionAjax.tpl');
    }

    private function _formatTimeLeft($exp)
    {
        $leased = geoPC::is_leased();
        $lease_extra = ' (Or at Lease Cancellation)';

        if ($exp === false) {
            $exp = 'Unknown (Error checking geodesicsolutions site)';
        } elseif ((!$exp || $exp == 'never') && $leased) {
            $exp = 'Never' . $lease_extra;
        } elseif (!$exp) {
            $exp = '<span style="color: red; font-weight: bold;">None Found!</span>';
        } elseif (!defined('DEMO_MODE_TEXT') && $exp != 'never' && $exp != 'pending...') {
            $currentTime = geoUtil::time();

            $expTime = $exp;
            $exp = date('F j, Y', $exp);

            if ($expTime > $currentTime) {
                $left = $expTime - $currentTime;
                $left = floor($left / (60 * 60 * 24));
                $exp .= " (<strong style='color: " . (($left < 14) ? 'red' : 'green') . ";'>$left days left</strong>)";
            } else {
                $exp .= " (<strong style='color: red;'>Expired!</strong>)";
            }
        }
        return $exp;
    }
}
