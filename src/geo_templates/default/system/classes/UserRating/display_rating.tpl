{* 7.4.0-9-gb844ce3 *}
<div class="user_rating">
	{if !$is_anon}<div style="display:inline-block;" id="user_rating_wrapper">{/if}
	
	<span class="user_rating_star" id="user_rating_{$id}_star_1">
		{if $individual_rating >= 1}
			<img src="{if $in_admin}../{/if}{external file='images/user_rating_selected.png'}" alt="" />
		{elseif $average_rating >= 1}
			<img src="{if $in_admin}../{/if}{external file='images/user_rating_average_full.png'}" alt="" />
		{else}
			<img src="{if $in_admin}../{/if}{external file='images/user_rating_empty.png'}" alt="" />
		{/if}
	</span>
	<span class="user_rating_star" id="user_rating_{$id}_star_2">
		{if $individual_rating >= 2}
			<img src="{if $in_admin}../{/if}{external file='images/user_rating_selected.png'}" alt="" />
		{elseif $average_rating == 1.5}
			<img src="{if $in_admin}../{/if}{external file='images/user_rating_average_half.png'}" alt="" />
		{elseif $average_rating >= 2}
			<img src="{if $in_admin}../{/if}{external file='images/user_rating_average_full.png'}" alt="" />
		{else}
			<img src="{if $in_admin}../{/if}{external file='images/user_rating_empty.png'}" alt="" />
		{/if}
	</span>
	<span class="user_rating_star" id="user_rating_{$id}_star_3">
		{if $individual_rating >= 3}
			<img src="{if $in_admin}../{/if}{external file='images/user_rating_selected.png'}" alt="" />
		{elseif $average_rating == 2.5}
			<img src="{if $in_admin}../{/if}{external file='images/user_rating_average_half.png'}" alt="" />
		{elseif $average_rating >= 3}
			<img src="{if $in_admin}../{/if}{external file='images/user_rating_average_full.png'}" alt="" />
		{else}
			<img src="{if $in_admin}../{/if}{external file='images/user_rating_empty.png'}" alt="" />
		{/if}
	</span>
	<span class="user_rating_star" id="user_rating_{$id}_star_4">
		{if $individual_rating >= 4}
			<img src="{if $in_admin}../{/if}{external file='images/user_rating_selected.png'}" alt="" />
		{elseif $average_rating == 3.5}
			<img src="{if $in_admin}../{/if}{external file='images/user_rating_average_half.png'}" alt="" />
		{elseif $average_rating >= 4}
			<img src="{if $in_admin}../{/if}{external file='images/user_rating_average_full.png'}" alt="" />
		{else}
			<img src="{if $in_admin}../{/if}{external file='images/user_rating_empty.png'}" alt="" />
		{/if}
	</span>
	<span class="user_rating_star" id="user_rating_{$id}_star_5">
		{if $individual_rating >= 5}
			<img src="{if $in_admin}../{/if}{external file='images/user_rating_selected.png'}" alt="" />
		{elseif $average_rating == 4.5}
			<img src="{if $in_admin}../{/if}{external file='images/user_rating_average_half.png'}" alt="" />
		{elseif $average_rating >= 5}
			<img src="{if $in_admin}../{/if}{external file='images/user_rating_average_full.png'}" alt="" />
		{else}
			<img src="{if $in_admin}../{/if}{external file='images/user_rating_empty.png'}" alt="" />
		{/if}
	</span>
	{if !$is_anon}</div>{/if}
	<span class="user_rating_success" id="user_rating_success_{$id}" style="color: #00C000; display: none; font-weight: bold; font-size: 0.7rem; vertical-align: bottom;">Saved</span>
</div>

{if !$is_anon}
	{add_footer_html}
		{* this user is logged in, so he is able to rate other users. Add the rating overlay/script *}
		<script>
			originalRating_{$id} = [
				jQuery('#user_rating_{$id}_star_1').html(),
				jQuery('#user_rating_{$id}_star_2').html(),
				jQuery('#user_rating_{$id}_star_3').html(),
				jQuery('#user_rating_{$id}_star_4').html(),
				jQuery('#user_rating_{$id}_star_5').html()
			];
			jQuery('.user_rating_star').css('cursor','pointer'); //show a hand cursor when over the ratings
			
			ShowRating_{$id} = function(rating, obj) {
				if(jQuery(obj).is('.block-events')) {
					return;
				}
				for(i=1; i<=5; i++) {
					if(i <= rating) {
						jQuery('#user_rating_{$id}_star_'+i).html("<img src=\"{external file='images/user_rating_selected.png'}\" alt=\"\" />");
					} else {
						jQuery('#user_rating_{$id}_star_'+i).html("<img src=\"{external file='images/user_rating_empty.png'}\" alt=\"\" />");
					}
				}
			};
			
			SendRating_{$id} = function(rating) {
				jQuery.post('AJAX.php?controller=UserRating&action=SetRating',
					{
						newRating: rating,
						about: {$about} 
					},
					function(returned) {
						if(returned == 'SAVED') {
							//rating saved to DB OK. Make sure we're showing it, then replace the contents of the 'default' array
							ShowRating_{$id}(rating);
							originalRating_{$id} = [
								jQuery('#user_rating_{$id}_star_1').html(),
								jQuery('#user_rating_{$id}_star_2').html(),
								jQuery('#user_rating_{$id}_star_3').html(),
								jQuery('#user_rating_{$id}_star_4').html(),
								jQuery('#user_rating_{$id}_star_5').html()
							];
							jQuery('#user_rating_success_{$id}').show('fast');
							setTimeout(function(){ jQuery('#user_rating_success_{$id}').hide('fast'); },3000);
						}
					},
					'html'
				);
			};
			
			CancelShowRating_{$id} = function() {
				jQuery('#user_rating_{$id}_star_1').html(originalRating_{$id}[0]);
				jQuery('#user_rating_{$id}_star_2').html(originalRating_{$id}[1]);
				jQuery('#user_rating_{$id}_star_3').html(originalRating_{$id}[2]);
				jQuery('#user_rating_{$id}_star_4').html(originalRating_{$id}[3]);
				jQuery('#user_rating_{$id}_star_5').html(originalRating_{$id}[4]);
			};
			
			//set up event observers
			jQuery(document).ready(function(){
					
				jQuery('#user_rating_wrapper').mouseleave(function(){ CancelShowRating_{$id}(); });
				
				//need to add a blocking class on mouseenter, or else Safari gets hung up and never fires the onclick 
				jQuery('#user_rating_{$id}_star_1').mouseenter(function(){ ShowRating_{$id}(1, this); jQuery(this).addClass('block-events'); })
					.click(function(){ SendRating_{$id}(1); })
					.mouseleave(function(){ jQuery(this).removeClass('block-events'); });
				jQuery('#user_rating_{$id}_star_2').mouseenter(function(){ ShowRating_{$id}(2, this); jQuery(this).addClass('block-events'); })
					.click(function(){ SendRating_{$id}(2); })
					.mouseleave(function(){ jQuery(this).removeClass('block-events'); });
				jQuery('#user_rating_{$id}_star_3').mouseenter(function(){ ShowRating_{$id}(3, this); jQuery(this).addClass('block-events'); })
					.click(function(){ SendRating_{$id}(3); })
					.mouseleave(function(){ jQuery(this).removeClass('block-events'); });
				jQuery('#user_rating_{$id}_star_4').mouseenter(function(){ ShowRating_{$id}(4, this); jQuery(this).addClass('block-events'); })
					.click(function(){ SendRating_{$id}(4); })
					.mouseleave(function(){ jQuery(this).removeClass('block-events'); });
				jQuery('#user_rating_{$id}_star_5').mouseenter(function(){ ShowRating_{$id}(5, this); jQuery(this).addClass('block-events'); })
					.click(function(){ SendRating_{$id}(5); })
					.mouseleave(function(){ jQuery(this).removeClass('block-events'); });
			});
		</script>
	{/add_footer_html}
{/if}