{* 6.0.7-3-gce41f93 *}
{if !$class}{assign var='class' value='mini_button'}{/if}
<a {if $link_is_really_javascript}href="#" {$link}{else}href="{$link}"{/if} class="{if $lightUpBox}lightUpLink {/if}{$class}" {if $id}id="{$id}"{/if}>{$label}</a>