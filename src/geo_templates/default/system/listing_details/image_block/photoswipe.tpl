{* 17.07.0 *}
{* implements PhotoSwipe image viewer. Documentation at http://photoswipe.com *}
<div class="galleryContainer">
	<div class="galleryThumbs">
		<ul>
			{foreach $images as $image}
				<li>
					{if $image.icon}
						<img class="ps-thumb" data-index="{$image@index}" src="{external file=$image.icon}" alt="" />
					{else}
						<img class="ps-thumb" data-index="{$image@index}" src="{if $image.thumb_url}{$image.thumb_url}{else}{$image.url}{/if}"{if $image.scaled.small_gallery} style="width: {$image.scaled.small_gallery.width}px;"{/if} alt="{$image.image_text}" />
					{/if}
				</li>
			{/foreach}
		</ul>
	</div>	
	<div class="clr"></div>
</div>

{* This next bit is boilerplate code that MUST be included for PhotoSwipe to function *}
	<div class="pswp" tabindex="-1" role="dialog" aria-hidden="true">
	    <div class="pswp__bg"></div>
	    <div class="pswp__scroll-wrap">
	        <div class="pswp__container">
	            <div class="pswp__item"></div>
	            <div class="pswp__item"></div>
	            <div class="pswp__item"></div>
	        </div>
	        <div class="pswp__ui pswp__ui--hidden">
	            <div class="pswp__top-bar">
	
	                {* order of controls may be changed here *}
	                <div class="pswp__counter"></div>
	                <button class="pswp__button pswp__button--close" title="Close (Esc)"></button>
	                <button class="pswp__button pswp__button--share" title="Share"></button>
	                <button class="pswp__button pswp__button--fs" title="Toggle fullscreen"></button>
	                <button class="pswp__button pswp__button--zoom" title="Zoom in/out"></button>
	
	                <div class="pswp__preloader">
	                    <div class="pswp__preloader__icn">
	                      <div class="pswp__preloader__cut">
	                        <div class="pswp__preloader__donut"></div>
	                      </div>
	                    </div>
	                </div>
	            </div>
	            <div class="pswp__share-modal pswp__share-modal--hidden pswp__single-tap">
	                <div class="pswp__share-tooltip"></div> 
	            </div>
	            <button class="pswp__button pswp__button--arrow--left" title="Previous (arrow left)">
	            </button>
	            <button class="pswp__button pswp__button--arrow--right" title="Next (arrow right)">
	            </button>
	            <div class="pswp__caption">
	                <div class="pswp__caption__center"></div>
	            </div>
	        </div>
	    </div>
	</div>
{* End PhotoSwipe boilerplate *}

{add_footer_html}
<script>
pswpElement = document.querySelectorAll('.pswp')[0];

psSlides = [
	{foreach $images as $image}
	
		{
			src: '{$image.url}',
			{if $image.thumb_url}msrc: '{$image.thumb_url}',{/if} 
			{if $image.image_text}title: '{$image.image_text}',{/if} 
			w: {$image.original_image_width},
			h: {$image.original_image_height}		
		}{if !$image@last},{/if}
		
	{/foreach}
];

jQuery('.ps-thumb').click(function() {
	var psOptions = {
		index: parseInt(jQuery(this).data('index')),
		showHideOpacity: true,
		bgOpacity: 1.0
	};
	var psGallery = new PhotoSwipe( pswpElement, PhotoSwipeUI_Default, psSlides, psOptions);
	psGallery.init();
});
</script>
{/add_footer_html}

