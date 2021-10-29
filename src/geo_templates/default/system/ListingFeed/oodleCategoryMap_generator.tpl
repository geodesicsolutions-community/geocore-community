{* 6.0.7-3-gce41f93 *}
{* This file used to generate the oodleCategoryMap.php config file.  *}
<html>
<head>
<title>oodleCategoryMap.php content generator</title>
</head>
<body>
<h1>oodleCategoryMap.php content generator</h1>
<p>Currently the oodle_feed.php file is configured to display this instead of an oodle feed.  Use the contents generated in the textarea below as the contents of the config file, replacing the oodle category "sales" with the appropriate category that cooresponds to each Geodesic category.</p>
<p>Note: Contents generated below should end with "End of config file", if it does not, there may be categories missing.</p>
<h2>Steps:</h2>
<ol>
	<li>Create a new file, named <strong>oodleCatMap.php</strong> using a simple text editor.</li>
	<li>Copy+Paste the contents generated below into the new file.</li>
	<li>Go line by line, and change "sales" to be the <a href="http://www.oodle.com/info/feed/category.html" onclick="window.open(this.href); return false;">Oodle Category</a> that relates.  Note that the Geodesic category will be displayed at the end of each line.</li>
	<li>Upload the file to your site, to the same location as the oodle_feed.php file.</li>
	<li>Turn this config file generator back off.  (change <strong>$generateOodleCatMapFile = 0</strong> in the file)</li>
	<li>Test the oodle feed, make sure the categories are correct.</li>
</ol>

<textarea  rows="30" cols="165">
&lt;?php
//oodleCatMap.php
//Geodesic Category to Oodle Category Map Configuration

//Guide:
//$oodleCatMap[GEO CATEGORY ID] = 'OODLE CATEGORY'; // GEO CATEGORY NAME

$oodleCatMap = array ();
{foreach from=$cats item=cat}$oodleCatMap[{$cat.category_id}] = '{$cat.oodleCategory}'; // {$cat.category_name} 
{/foreach}

//End of config file
</textarea>
</body>
</html>

