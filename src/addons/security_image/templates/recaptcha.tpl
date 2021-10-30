{* 7.4.4-29-g21d5229 *}
{*
This tpl requires the following vars to be set:
recaptcha_theme
recaptcha_server
recaptcha_pub_key
If error:  recaptcha_error
*}

	{* need to surround the entire thing with a div with inline-block style, or it newlines
		in chrome/IE *}
	<div class="inline">
		<script src="https://www.google.com/recaptcha/api.js" async defer></script>
		<div class="g-recaptcha" data-sitekey="{$recaptcha_pub_key}" {if $recaptcha_theme}data-theme="{$recaptcha_theme}"{/if}></div>
		
		<noscript>
		  <div style="width: 302px; height: 352px;">
		    <div style="width: 302px; height: 352px; position: relative;">
		      <div style="width: 302px; height: 352px; position: absolute;">
		        <iframe src="https://www.google.com/recaptcha/api/fallback?k={$recaptcha_pub_key}"
		                frameborder="0" scrolling="no"
		                style="width: 302px; height:352px; border-style: none;">
		        </iframe>
		      </div>
		      <div style="width: 250px; height: 80px; position: absolute; border-style: none;
		                  bottom: 21px; left: 25px; margin: 0px; padding: 0px; right: 25px;">
		        <textarea id="g-recaptcha-response" name="g-recaptcha-response"
		                  class="g-recaptcha-response"
		                  style="width: 250px; height: 80px; border: 1px solid #c1c1c1;
		                         margin: 0px; padding: 0px; resize: none;" value="">
		        </textarea>
		      </div>
		    </div>
		  </div>
		</noscript>
	</div>
