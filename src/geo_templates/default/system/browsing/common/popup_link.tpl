{* 7.0.1-74-gf000f4d *}{strip}
{* Kind of old-school to use popups, but at least one client still uses this option... *}
{if $cfg.popup} onclick="window.open(this.href,'_blank','width={$cfg.popup_width},height={$cfg.popup_height},scrollbars=1,location=0,menubar=0,resizable=1,status=0'); return false;"{/if}
{/strip}