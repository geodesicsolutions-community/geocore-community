{* 6.0.7-3-gce41f93 *}
#SEO add-on

{if !$install_settings.omit_symlink}
Options +FollowSymlinks
{/if}
RewriteEngine On
RewriteBase /{$sitepath}

RewriteRule ^/?geo_testunit$ index.php [L] ##URL Rewrite Test
{if $install_settings.use_old_redirects}

#Re-directs for old SEO 1.0 URLs
RewriteRule ^/?{$index_regex}/listings/page([0-9]*)\.htm$ {$indexfile}?SEO_old_url=1&a=2&b=$2 [L]
RewriteRule ^/?{$index_regex}/listings/category([0-9]*)\.htm$ {$indexfile}?SEO_old_url=1&a=5&b=$2 [L]
RewriteRule ^/?{$index_regex}/listings/category([0-9]*)/page([0-9]*)\.htm$ {$indexfile}?SEO_old_url=1&a=5&b=$2&page=$3 [L]
RewriteRule ^/?{$index_regex}/other/seller([0-9]*)\.htm$ {$indexfile}?SEO_old_url=1&a=6&b=$2 [L]
RewriteRule ^/?{$index_regex}/other/seller([0-9]*)/page([0-9]*)\.htm$ {$indexfile}?SEO_old_url=1&a=6&b=$2&page=$3 [L]
RewriteRule ^/?{$index_regex}/featured/page([0-9]*)\.htm$ {$indexfile}?SEO_old_url=1&a=8&page=$2 [L]
RewriteRule ^/?{$index_regex}/featured/category([0-9]*)\.htm$ {$indexfile}?SEO_old_url=1&a=8&b=$2 [L]
RewriteRule ^/?{$index_regex}/featured/category([0-9]*)/page([0-9]*)\.htm$ {$indexfile}?SEO_old_url=1&a=8&b=$2&page=$3 [L]
RewriteRule ^/?{$index_regex}/listings/1day([0-9]*)\.htm$ {$indexfile}?SEO_old_url=1&a=11&b=$2&c=4 [L]
RewriteRule ^/?{$index_regex}/listings/1day([0-9]*)/page([0-9]*)\.htm$ {$indexfile}?SEO_old_url=1&a=11&b=$2&c=4&page=$3 [L]
RewriteRule ^/?{$index_regex}/listings/1week([0-9]*)\.htm$ {$indexfile}?SEO_old_url=1&a=11&b=$2&c=1 [L]
RewriteRule ^/?{$index_regex}/listings/1week([0-9]*)/page([0-9]*)\.htm$ {$indexfile}?SEO_old_url=1&a=11&b=$2&c=1&page=$3 [L]
RewriteRule ^/?{$index_regex}/listings/2weeks([0-9]*)\.htm$ {$indexfile}?SEO_old_url=1&a=11&b=$2&c=2 [L]
RewriteRule ^/?{$index_regex}/listings/2weeks([0-9]*)/page([0-9]*)\.htm$ {$indexfile}?SEO_old_url=1&a=11&b=$2&c=2&page=$3 [L]
RewriteRule ^/?{$index_regex}/listings/3weeks([0-9]*)\.htm$ {$indexfile}?SEO_old_url=1&a=11&b=$2&c=3 [L]
RewriteRule ^/?{$index_regex}/listings/3weeks([0-9]*)/page([0-9]*)\.htm$ {$indexfile}?SEO_old_url=1&a=11&b=$2&c=3&page=$3 [L]
RewriteRule ^/?{$index_regex}/print/item([0-9]*)\.htm$ {$indexfile}?SEO_old_url=1&a=14&b=$2 [L]
RewriteRule ^/?{$index_regex}/images/item([0-9]*)\.htm$ {$indexfile}?SEO_old_url=1&a=15&b=$2 [L]
#END re-directs for old SEO 1.0 URLs
{/if}

{foreach from=$items key='part_count' item='item_set'}
{foreach from=$item_set key='url' item='rule'}
RewriteRule ^/?{$rule.template}$  {$indexfile}?{$rule.regexhandler} [QSA,L] ##{$rule.registry}
{/foreach}
{/foreach}

#-end SEO addon-on {* leave a blank line after ending comment so people adding to the generated file don't paste onto commented line *}
