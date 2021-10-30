<?php

//addons/price_drop_auctions/cron/price_drop.php




if (!defined('GEO_CRON_RUN')) {
    die('NO ACCESS');
}

$db = DataAccess::getInstance();
//get the auctions that are ready to have their prices dropped
$sql = "SELECT * FROM `geodesic_addon_price_drop_auctions` WHERE `current_price` > `minimum_price` AND `next_drop` <= ?";
$result = $db->Execute($sql, array(geoUtil::time()));
if ($result->RecordCount() < 1) {
    $this->log('No Price Drop Auctions to drop right now. Ending task.', __LINE__);
    return true;
}

$reg = geoAddon::getRegistry('price_drop_auctions');
//so we only have to get these once...
$settings = array(
    'delay_low' => $reg->delay_low,
    'delay_high' => $reg->delay_high,
    'drop_amount_low' => $reg->drop_amount_low,
    'drop_amount_high' => $reg->drop_amount_high,
    'drop_amount_static' => $reg->drop_amount_static
);
$updateAfterDrop = $db->Prepare("UPDATE `geodesic_addon_price_drop_auctions` SET `current_price` = ?, `last_drop` = ?, `next_drop` = ? WHERE `listing_id` = ?");
foreach ($result as $drop) {
    $this->log('Beginning price drop for listing ' . $drop['listing_id'], __LINE__);
    $listing = geoListing::getListing($drop['listing_id']);
    if (!$listing) {
    //listing cancelled and no one told us or something?
        //remove from table and proceed
        $db->Execute("DELETE FROM `geodesic_addon_price_drop_auctions` WHERE `listing_id` = ?", array($drop['listing_id']));
        $this->log('Could not acquire listing. Removing from drop table.', __LINE__);
        continue;
    }

    //get current price from $listing to make sure we're totally up-to-date
    $currentPrice = $listing->buy_now;
    $this->log('Current price: ' . $currentPrice, __LINE__);

    if ($settings['drop_amount_static'] == 1) {
//we want to drop the price evenly every time, by at least enough to ensure that the minimum price is ALWAYS reached
        $maxListingDuration = $listing->ends - $listing->date;
//the maximal runtime of the auction, in seconds
        if ($settings['delay_high'] == 0) {
//special case to prevent division by zero: 5 minutes, to match Cron timer
            $settings['delay_high'] = 1 / 12;
        }
        $fewestNumDrops = $maxListingDuration / ($settings['delay_high'] * 3600);
//price will drop AT LEAST this many times over the full duration
        $dropAmt = ($drop['starting_price'] - $drop['minimum_price']) / $fewestNumDrops;
        $this->log('Drop by ' . $dropAmt . ', which is the difference between the starting and minimum price, divided by ' . $fewestNumDrops, __LINE__);
    } else {
    //generate a random percentage between drop_amount_low and drop_amount_high
        $dropPerc = mt_rand($settings['drop_amount_low'], $settings['drop_amount_high']);
        $dropAmt =  $drop['starting_price'] * ($dropPerc / 100);
    //we drop by a percentage of the STARTING (not current) price
        $this->log('Drop by ' . $dropPerc . '% which is ' . $dropAmt, __LINE__);
    }

    //new price should not be lower than the minimum price
    $newPrice = max($drop['minimum_price'], ($currentPrice - $dropAmt));
    $this->log('Minimum price is: ' . $drop['minimum_price'], __LINE__);
    $this->log('New price will be ' . $newPrice . ($drop['minimum_price'] == $newPrice ? ' [because price cannot drop below minimum]' : ''), __LINE__);
//figure out when the next drop will be, in number of seconds from now
    $delay = mt_rand($settings['delay_low'] * 3600, $settings['delay_high'] * 3600);
    $nextDrop = geoUtil::time() + $delay;
    $this->log('Next drop time for this listing: ' . $nextDrop . ' (' . $delay . ' seconds from now)', __LINE__);

    //update listing table with new price
    $listing->buy_now = $newPrice;

    //update price drop table with new dataset
    if (!$db->Execute($updateAfterDrop, array($newPrice, geoUtil::time(), $nextDrop, $drop['listing_id']))) {
        $this->log('NOTICE: failed to update price drop data', __LINE__);
    }
    $this->log('===Completed Listing===', __LINE__);
}

$this->log('Finished all tasks.', __LINE__);
return true;
