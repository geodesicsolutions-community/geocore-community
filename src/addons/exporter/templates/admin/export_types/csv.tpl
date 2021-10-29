{* 7.4.4-10-g8576128 *}{*
   OK we're not going to be doing much templating here...  going to let built in CSV
   methods do most of the work for us, inside template plugin. *}
{foreach $listings as $listing}{process_listing listing=$listing}{csv_line}{/foreach}