<?php

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
##    7.5.0-16-gf5139d7
##
##################################

class adminGettingStarted
{
    private $_checks = array();
    public function display_checklist()
    {
        if ($_GET['sync'] === 'yes') {
            $this->syncWithDetected();
            header("Location: index.php?page=checklist&mc=getting_started");
            exit();
        }

        $this->_loadChecks();
        $tpl_vars['completion'] = $this->getCompletionPercentage();

        $checks = array();
        foreach ($this->_checks as $sectionName => $section) {
            foreach ($section as $checkName => $check) {
                $checks[$sectionName][$checkName] = array(
                    'name' => $check->name,
                    'description' => $check->description,
                    'weight' => $check->weight,
                    'percentage' => round($check->weight / $this->totalWeight * 100),
                    'isChecked' => $check->isChecked,
                    'isComplete' => $check->isComplete()
                );
            }
        }
        $tpl_vars['checks'] = $checks;
        $tpl_vars['admin_msgs'] = geoAdmin::m();
        $tpl_vars['white_label'] = geoPC::is_whitelabel();


        geoAdmin::getInstance()->setBodyTpl('getting_started.tpl')->v()->setBodyVar($tpl_vars);
    }

    public function update_checklist()
    {
        $this->_loadChecks();
        $checkboxes = $_POST['checkboxes'];
        foreach ($checkboxes as $sectionName => $section) {
            foreach ($section as $checkName => $check) {
                if ($check == 1) {
                    $this->_checks[$sectionName][$checkName]->check();
                } elseif ($check == 0) {
                    $this->_checks[$sectionName][$checkName]->uncheck();
                }
            }
        }
        $this->_updateLandingPage();
        return true;
    }

    public function syncWithDetected()
    {
        $this->_loadChecks();
        foreach ($this->_checks as $sectionName => $section) {
            foreach ($section as $checkName => $check) {
                if ($check->isComplete()) {
                    $check->check();
                } else {
                    $check->uncheck();
                }
            }
        }
        $this->_updateLandingPage();
    }

    public $totalWeight;
    public $percentageComplete = false;

    public function getCompletionPercentage()
    {
        if ($this->percentageComplete) {
            return $this->percentageComplete;
        }
        $this->_loadChecks();

        $totalWeight = 0;
        $completeWeight = 0;
        foreach ($this->_checks as $sectionName => $section) {
            foreach ($section as $checkName => $check) {
                $totalWeight += $check->weight;
                if ($check->isChecked) {
                    $completeWeight += $check->weight;
                }
            }
        }
        if (!$totalWeight) {
            //didn't find any weights, and dividing by zero is bad
            return 0;
        }
        $percentage = round($completeWeight * 100 / $totalWeight);

        if ($percentage < 100) {
            //since we're already on notice page, no use for this as they can see
            //it's not 100% yet...
            //Notifications::addNoticeAlert("You have not yet completed the <a href='index.php?page=checklist&mc=getting_started'>Getting Started Checklist</a> (Currently {$percentage}% complete)");
        }

        $this->totalWeight = $totalWeight;
        $this->percentageComplete = $percentage;

        return $percentage;
    }

    private function _loadChecks()
    {
        if (count($this->_checks) > 0) {
            //checks already loaded
            return;
        }

        $files = geoFile::getInstance();
        $dir = ADMIN_DIR . 'getting_started_checks/';
        $files->jailTo($dir);
        $checks = $files->scandir($dir);
        foreach ($checks as $filename) {
            $className = substr($filename, 0, -4);
            require_once($dir . $filename);
            $theCheck = new $className();
            if (!$theCheck->name) {
                //something's wrong with this one...skip it
                continue;
            }
            $this->_checks[$theCheck->section][$className] = $theCheck;
        }
    }

    private function _updateLandingPage()
    {
        $db = DataAccess::getInstance();
        if ($db->get_site_setting('adminLandingPage') == 'checklist' && $this->getCompletionPercentage() == 100) {
            //all done!  switch it to use home page as landing page...
            $db->set_site_setting('adminLandingPage', 'home');
            geoAdmin::m('Congratulations, checklist is 100% complete!  Switching the
					landing page to the admin home page.', geoAdmin::NOTICE);
        }
    }
}

abstract class geoGettingStartedCheck
{
    /**
     * User-readable name/title for this check
     * @var String
     */
    public $name;
    /**
     * Name of the section this check belongs in
     * @var String
     */
    public $section;
    /**
     * Descriptive text that explains the check and how to resolve it
     * @var String
     */
    public $description;

    /**
     * Value that represents how important this check is towards final completion.
     * Most will use a value of 1. A check with a weight of 2 should be roughly twice as important as normal.
     * @var float
     */
    public $weight;

    /**
     * Accessor for user-selected state of checkbox for this item
     * @var bool
     */
    public $isChecked;

    /**
     * Just a constructor.
     */
    public function __construct()
    {
        $this->isChecked = (bool)DataAccess::getInstance()->get_site_setting('gettingstarted_' . $this->name . '_isChecked');
    }

    /**
     * This function should return a bool based on whether the checked item "appears" to be complete.
     * @return bool
     */
    public function isComplete()
    {
        //do stuff here specific to each check to determine if it "looks" complete or not
        if ($complete) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Sets object's isChecked flag
     */
    public function check()
    {
        $this->isChecked = true;
        DataAccess::getInstance()->set_site_setting('gettingstarted_' . $this->name . '_isChecked', true);
    }

    /**
     * Unsets object's isChecked flag
     */
    public function uncheck()
    {
        $this->isChecked = false;
        DataAccess::getInstance()->set_site_setting('gettingstarted_' . $this->name . '_isChecked', false);
    }
}
