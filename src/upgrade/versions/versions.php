<?php

//This file defines the different versions.

//initialize $versions
$versions = array();

//use the following sample as starting point for new versions.
$versions['sample_from_version'] = array (
    //index name is the "from" version, as listed in the
    //database for that version.

    'to' => '2.0.8b',       //to is the version the software will be at
                            //after running this upgrade. If set to
                            //'latest', that means there is no upgrade for
                            //it, it is the latest version.

    'folder' => 'sample',   //folder name for this upgrade, where all
                            //the files for this upgrade will be located.
                            //if "to" is 'latest', folder is not needed.
                            //** SPECIAL CASE **
                            //If folder = none (the string "none"), then no upgrade queries will
                            //be run for that upgrade.
);

//example if this is a beta release:
$versions['beta'] = array(
    'start' => '3.0.2', //this is where to start the updates if already at one
                        //of the beta versions below
    'beta_versions' => array ('3.0.2','3.0.3','3.1.0beta')
                        //if in one of the versions above, the upgrade will instead
                        //start from the 'start' version.  Even if the current version
                        //is the "latest", if it is in the array above, it will be treated
                        //as if the version is the version set in start.
);

//unset the sample index, since it's not really a valid version.
unset($versions['sample_from_version']);
unset($versions['beta']);

#####New Versions Add HERE#######
#####New Versions Add HERE#######
#####New Versions Add HERE#######

#Add new versions to top.  Make sure to change the latest version.
# Don't forget to update updateFactory.php as well!

$versions['20.0.0'] = [
    'to' => 'latest',
];

$versions['18.02.0'] = array(
    'to' => '20.0.0',
    'folder' => 'none',
);

$versions['17.12.0'] = array(
        'to' => '18.02.0',
        'folder' => 'none',
        'changelog' => '17.12.0/changelog.html'
);

$versions['17.10.0'] = array(
        'to' => '17.12.0',
        'folder' => '17.12.0'
);

$versions['17.05.0'] = $versions['17.07.0'] = $versions['17.10.0'];


$versions['17.04.0'] = array(
        'to' => '17.12.0',
        'folder' => '17.12.0',
        'changelog' => '17.04.0/changelog.html',
);

$versions['17.03.0'] = array(
        'to' => '17.07.0',
        'folder' => '17.04.0',

);

$versions['17.01.0'] = array(
    'to' => '17.03.0',
    'folder' => 'none',
    'changelog' => '17.01.0/changelog.html',
);

$versions['16.09.0'] = array(
        'to' => '17.01.0',
        'folder' => '17.01.0',
);

$versions['16.07.0'] = array(
    'to' => '16.09.0',
    'folder' => '16.07.0',
    'changelog' => '16.07.0/changelog.html',
);

$versions['16.05.0'] = array(
    'to' => '16.07.0',
    'folder' => '16.07.0'
);

$versions['16.03.0'] = array(
    'to' => '16.05.0',
    'folder' => 'none',
    'changelog' => '16.03.0/changelog.html',
);


$versions['16.02.1'] = array(
    'to' => '16.03.0',
    'folder' => '16.03.0'
);

$versions['16.02.0'] = array(
    'to' => '16.02.1',
    'folder' => 'none',
    'changelog' => '16.02.0/changelog.html',
);

$versions['7.6.3'] = array (
        'to' => '16.02.0',
        'folder' => '16.02.0'
);

//development versions
$versions['16.01beta1'] = $versions['16.01.0'] = $versions['15.12.0'] = $versions['7.6.3'];

$versions['7.6.2'] = array (
        'to' => '7.6.3',
        'folder' => 'none'
);

$versions['7.6.1'] = $versions['7.6.2'];

$versions['7.6.0'] = array (
        'to' => '7.6.2',
        'folder' => '7.6', // db fixes from 7.6.0 -> 7.6.1
        'changelog' => '7.6/changelog.html',
);

$versions['7.5.3'] = array (
        'to' => '7.6.0',
        'folder' => '7.6',
);

$versions['7.5.2'] = array (
        'to' => '7.5.3',
        'folder' => 'none'
);

$versions['7.5.1'] = $versions['7.5.2'];

$versions['7.5.0'] = array (
    'to' => '7.5.3',
    'folder' => 'none',
    'changelog' => '7.5/changelog.html',
);


$versions['7.4.6'] = array (
    'to' => '7.5.0',
    'folder' => '7.5'
);

$versions['7.4.5'] = $versions['7.4.6'];

$versions['7.4.4'] = array (
    'to' => '7.4.5',
    'folder' => '7.4' //need to run the db update here for a 7.4.5 fix
);

$versions['7.4.1'] = $versions['7.4.2'] = $versions['7.4.3'] = $versions['7.4.4'];

$versions['7.4.0'] = array (
    'to' => '7.4.5',
    'folder' => '7.4',
    'changelog' => '7.4/changelog.html',
);


$versions['7.3.6'] = array(
    'to' => '7.4.5',
    'folder' => '7.4'
);

$versions['7.4beta1'] = $versions['7.4beta2'] = $versions['7.4beta3'] = $versions['7.3.6'];

$versions['7.3.5'] = array(
    'to' => '7.3.6',
    'folder' => 'none'
);

$versions['7.3.4'] = array (
    'to' => '7.3.5',
    'folder' => '7.3'
);

$versions['7.3.4'] = array (
    'to' => '7.3.5',
    'folder' => 'none',
    );
$versions['7.3.1'] = $versions['7.3.2'] = $versions['7.3.3'] = $versions['7.3.4'];

$versions['7.3.0'] = array (
    'to' => '7.3.5',
    'folder' => 'none',
    'changelog' => '7.3/changelog.html',
    );

$versions['7.3rc2'] = array (
    'to' => '7.3.4',
    'folder' => '7.3',
    );

//all the 7.3 beta / rc versions
$versions['7.3rc1'] = $versions['7.3beta5'] = $versions['7.3beta4'] = $versions['7.3rc2'];
$versions['7.3beta3'] = $versions['7.3beta2'] = $versions['7.3beta1'] = $versions['7.3rc2'];

$versions['7.2.6'] = array (
    'to' => '7.3.4',
    'folder' => '7.3',
    //NOTE: norelease is fancy thingy for the changelog, this prevents changelog
    //from saying "versions 7.2.0 - 7.2.6"
    'norelease' => true,
    );
$versions['7.2.5'] = array (
    'to' => '7.2.6',
    'folder' => '7.2',
    );
$versions['7.2.4'] = $versions['7.2.3'] = $versions['7.2.2'] = $versions['7.2.1'] = $versions['7.2.5'];

$versions['7.2.0'] = array (
    'to' => '7.2.6',
    'folder' => '7.2',
    'changelog' => '7.2/changelog.html',
);

//all the 7.2 beta versions
$versions['7.2beta1'] = $versions['7.2beta2'] = $versions['7.2beta3']
    = $versions['7.2beta4']
    = array (
        'to' => '7.2.6',
        'folder' => '7.2',
        );

$versions['7.1.4'] = array (
    'to' => '7.2.6',
    'folder' => '7.2',
    'norelease' => true,
    );

$versions['7.1.3'] = array (
    'to' => '7.1.4',
    'folder' => 'none',
    );
$versions['7.1.2'] = $versions['7.1.3'];
$versions['7.1.1'] = $versions['7.1.3'];

$versions['7.1.0'] = array (
    'to' => '7.1.4',
    'folder' => 'none',
    'changelog' => '7.1/changelog.html',
    );

//All the 7.1 beta versions
$versions['7.1.0beta1'] = $versions['7.1beta1'] = $versions['7.1beta2']
    = $versions['7.1beta3'] = $versions['7.1beta4'] = $versions['7.1beta5']
    = array (
        'to' => '7.1.4',
        'folder' => '7.1',
        );

$versions['7.0.4'] = array (
    'to' => '7.1.4',
    'folder' => '7.1',
);

$versions['7.0.3'] = array (
    'to' => '7.1.4',
    'folder' => '7.1',
);

$versions['7.0.2'] = array (
    'to' => '7.1.4',
    'folder' => '7.1',
);

$versions['7.0.1'] = array (
    'to' => '7.0.4',
    'folder' => '7.0',
);

$versions['7.0.0'] = array (
    'to' => '7.0.4',
    'folder' => '7.0',
    'changelog' => '7.0/changelog.html',
);
$versions['7.0.0beta'] = array (
    'to' => '7.0.4',
    'folder' => '7.0',
);
$versions['6.1.0beta'] = array (
    'to' => '7.0.4',
    'folder' => '7.0',
);

$versions['6.0.8'] = array (
    'to' => '7.0.4',
    //'changelog' => '6.0/changelog.html#milestone_6.0.8',
    'folder' => '7.0',
);
$versions['6.0.7'] = array (
    'to' => '6.0.8',
    //'changelog' => '6.0/changelog.html#milestone_6.0.7',
    'folder' => 'none',
);
$versions['6.0.6'] = array (
    'to' => '6.0.8',
    //'changelog' => '6.0/changelog.html#milestone_6.0.6',
    'folder' => 'none'
);

$versions['6.0.5'] = array (
    'to' => '6.0.8',
    //'changelog' => '6.0/changelog.html#milestone_6.0.5',
    'folder' => 'none',
);

$versions['6.0.4'] = array(
    'to' => '6.0.8',
    //'changelog' => '6.0/changelog.html#milestone_6.0.4',
    'folder' => 'none',
);

$versions['6.0.3'] = array(
    'to' => '6.0.8',
    'folder' => '6.0',
    //'changelog' => '6.0/changelog.html#milestone_6.0.3'
);

$versions['6.0.2'] = array (
    'to' => '6.0.8',
    'folder' => '6.0',
    //'changelog' => '6.0/changelog.html#milestone_6.0.2',
);

$versions['6.0.1'] = array (
    'to' => '6.0.8',
    'folder' => '6.0',
    //'changelog' => '6.0/changelog.html#milestone_6.0.1',
);

$versions['6.0.0'] = array(
    'to' => '6.0.8',
    'folder' => '6.0',
    'changelog' => '6.0/changelog.html',
);

$versions['6.0.0beta'] = array (
    'to' => '6.0.8',
    'folder' => '6.0'
);
$versions['5.2.4'] = $versions['6.0.0beta'];
$versions['5.2.3'] = $versions['6.0.0beta'];
$versions['5.2.2'] = $versions['6.0.0beta'];
$versions['5.2.1'] = $versions['6.0.0beta'];
$versions['5.2.0'] = $versions['6.0.0beta'];

$versions['5.1.4'] = array (
    'to' => '5.2.0',
    'folder' => '5.2'
);
$versions['5.1.3'] = $versions['5.1.4'];

$versions['5.1.2'] = array (
    'to' => '5.1.4',
    'folder' => '5.1'
);
$versions['5.1.1'] = $versions['5.1.2'];
$versions['5.1.0'] = $versions['5.1.2'];
$versions['5.1.0beta'] = $versions['5.1.2'];

$versions['5.0.3'] = $versions['5.1.2'];
$versions['5.0.2'] = array (
    'to' => '5.0.3',
    'folder' => '4.1_to_5.0'
);
$versions['5.0.1'] = $versions['5.0.2'];
$versions['5.0.0'] = $versions['5.0.2'];



$versions['5.0.0RC2'] = $versions['5.0.2'];
$versions['5.0.0RC1'] = $versions['5.0.2'];

$versions['4.2.0beta'] = $versions['5.0.2'];

$versions['4.1.3'] = $versions['5.0.2'];
$versions['4.1.2'] = array(
    'to' => '4.1.3',
    'folder' => '4.0_to_4.1'
);
$versions['4.1.1'] = $versions['4.1.2'];

$versions['4.1.0'] = $versions['4.1.2'];
$versions['4.1.0beta'] = $versions['4.1.2'];
$versions['4.0.9'] = $versions['4.1.2'];
$versions['4.0.8'] = $versions['4.0.9'];
$versions['4.0.7'] = $versions['4.0.9'];
$versions['4.0.6'] = $versions['4.0.9'];
$versions['4.0.5'] = $versions['4.0.9'];
$versions['4.0.4'] = $versions['4.0.9'];
$versions['4.0.3'] = $versions['4.0.9'];
$versions['4.0.2'] = $versions['4.0.9'];
$versions['4.0.1'] = $versions['4.0.9'];
$versions['4.0.0'] = $versions['4.0.9'];

$versions['4.0.0RC11'] = array (
    'to' => '4.0.0',
    'folder' => '3.1_to_4.0'
);
$versions['4.0.0RC10'] = $versions['4.0.0RC11'];
$versions['4.0.0RC9'] = $versions['4.0.0RC11'];
$versions['4.0.0RC8'] = $versions['4.0.0RC11'];
$versions['4.0.0RC7'] = $versions['4.0.0RC11'];
$versions['4.0.0RC6'] = $versions['4.0.0RC11'];
$versions['4.0.0RC5'] = $versions['4.0.0RC11'];
$versions['4.0.0RC4'] = $versions['4.0.0RC11'];
$versions['4.0.0RC3'] = $versions['4.0.0RC11'];
$versions['4.0.0RC2'] = $versions['4.0.0RC11'];
$versions['4.0.0RC1'] = $versions['4.0.0RC11'];
$versions['4.0.0beta3'] = $versions['4.0.0RC11'];
$versions['4.0.0beta2'] = $versions['4.0.0RC11'];
$versions['4.0.0beta1'] = $versions['4.0.0RC11'];
$versions['4.0beta'] = $versions['4.0.0RC11'];
$versions['3.2beta'] = $versions['4.0.0RC11'];
$versions['3.1.10'] = $versions['4.0.0RC11'];

$versions['3.1.9'] = array(
    'to' => '3.1.10',
    'folder' => 'none' //no update needed
);
$versions['3.1.8'] = $versions['3.1.9'];
$versions['3.1.7'] = $versions['3.1.8']; //same as 3.1.8
$versions['3.1.6'] = $versions['3.1.8']; //same as 3.1.8
$versions['3.1.5'] = $versions['3.1.8']; //same as 3.1.8
$versions['3.1.4'] = $versions['3.1.8']; //same as 3.1.8
$versions['3.1.3'] = $versions['3.1.8']; //same as 3.1.8
$versions['3.1.2'] = $versions['3.1.8']; //same as 3.1.8
$versions['3.1.1'] = $versions['3.1.8']; //same as 3.1.8

$versions['3.1.0'] = array(
    'to' => '3.1.8',
    'folder' => '3_0_to_3_1'
);
### 3.0.0 Upgrades ###

$versions['3.0.2'] = $versions['3.1.0']; //same update as 3.1.0 to 3.1.1

$versions['3.0.1'] = array (
    'to' => '3.0.2',
    'folder' => '2_0_10b_to_3_0_0'
);
$versions['3.0.0'] = $versions['3.0.1']; //uses same updates

//All RC releases use same upgrade,
//should only have 1 folder for each major release.
$versions['3.0.0RC4'] = $versions['3.0.1'];
$versions['3.0.0RC3'] = $versions['3.0.1'];
$versions['3.0.0RC2'] = $versions['3.0.1'];
$versions['3.0.0RC1'] = $versions['3.0.1'];

//Developer preview release
$versions['3.0.0pre1'] = $versions['3.0.1'];

### 2.0.10 Upgrades ###

//below versions all use same upgrade to get to 3.0.2
$versions['2.0.10b'] = $versions['3.0.1'];
$versions['2.0.9b'] = $versions['3.0.1'];
$versions['2.0.8b'] = $versions['3.0.1'];
