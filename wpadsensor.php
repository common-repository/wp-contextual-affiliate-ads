<?
/*
Plugin Name: WP Adsensor
Plugin URI: http://www.Wpadsensor.com
Description: Display contextual keyword targeted ads from amazon, clickbank and ebay in WP Posts.
Version: 1.0
Author: Zeescripts
Author URI: http://www.zeescripts.com
License: GPL
*/
?>
<?
function authenticate()
{
		$ch = curl_init();
		$opt_val1 = get_option('email');
		$opt_val = get_option('activationcode');
		$opt_val2 = get_option('connecturl');

	    $Url = $opt_val2."?email=".$opt_val1."&code=".$opt_val;
		//echo $Url;
		// set URL to download
		curl_setopt($ch, CURLOPT_URL, $Url);

		// set referer:
		curl_setopt($ch, CURLOPT_REFERER, "");

		// user agent:
		curl_setopt($ch, CURLOPT_USERAGENT, "MozillaXYZ/1.0");

		// remove header? 0 = yes, 1 = no
		curl_setopt($ch, CURLOPT_HEADER, 0);

		// should curl return or print the data? true = return, false = print
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		// timeout in seconds
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);

		// download the given URL, and return output
		$output = curl_exec($ch);

		// close the curl resource, and free system resources
		curl_close($ch);
		//echo $output;
		return trim($output);
}
?>
<?
function wpm_showoptions(){
	global $post;
	echo '<input type="hidden" name="enableamazon_noncename" id="enableamazon_noncename" value="' . wp_create_nonce( plugin_basename(__FILE__) ) . '" />'; 	?>
	<?echo '<input type="hidden" name="enableclickbank_noncename" id="enableclickbank_noncename" value="' . wp_create_nonce( plugin_basename(__FILE__) ) . '" />';?>
	<?echo '<input type="hidden" name="enableebay_noncename" id="enableebay_noncename" value="' . wp_create_nonce( plugin_basename(__FILE__) ) . '" />';?>

	<?php
			$enableamazon = get_post_meta($post->ID,'enableamazon',true);
			if($enableamazon=="1"){
				$enableamazonchecked = ' checked="checked" ';
			}else{
				$enableamazonchecked = '';
			}

			$enableclickbank = get_post_meta($post->ID,'enableclickbank',true);
			if($enableclickbank=="1"){
				$enableclickbank = ' checked="checked" ';
			}else{
				$enableclickbank = '';
			}

			$enableebay = get_post_meta($post->ID,'enableebay',true);
			if($enableebay=="1"){
				$enableebay = ' checked="checked" ';
			}else{
				$enableebay = '';
			}
	?>
		<p><input type="checkbox" id="enableamazon" name="enableamazon" value="1" <?php echo $enableamazonchecked;?> />&nbsp;<label for="enableamazon"><strong>Show ADs from Amazon?</strong></label></p>
		<p><input type="checkbox" id="enableclickbank" name="enableclickbank" value="1" <?php echo $enableclickbank;?> />&nbsp;<label for="enablewpts"><strong>Show ADs from Clickbank?</strong></label></p>
		<p><input type="checkbox" id="enableebay" name="enableebay" value="1" <?php echo $enableebay;?> />&nbsp;<label for="enablewpts"><strong>Show ADs from Ebay?</strong></label></p>

	<?php
}
?>
<?
function wpm_display() {
  echo '<div class="dbx-b-ox-wrapper">' . "\n";
  echo '<fieldset id="myplugin_fieldsetid" class="dbx-box">' . "\n";
  echo '<div class="dbx-h-andle-wrapper"><h3 class="dbx-handle">' .
        __( 'Show ADs', 'wordpress-post-tabs' ) . "</h3></div>";
  echo '<div class="dbx-c-ontent-wrapper"><div class="dbx-content">';
  wpm_showoptions();
  echo "</div></div></fieldset></div>\n";
}

function wpm_addoptionbox() {
	if( function_exists( 'add_meta_box' ) ) {
		add_meta_box( 'wpm_box1', __( 'Show ADs' ), 'wpm_showoptions', 'post', 'side','high' );
		add_meta_box( 'wpm_box2', __( 'Show ADs' ), 'wpm_showoptions', 'page', 'advanced' );
	} else {
		add_action('dbx_post_advanced', 'myplugin_old_custom_box' );
		add_action('dbx_page_advanced', 'myplugin_old_custom_box' );
	}
}
add_action('admin_menu', 'wpm_addoptionbox');
?>
<?
function wpm_save()
{
	global $post;
	$post_id = $post->ID;
	if ( !wp_verify_nonce( $_POST['enableamazon_noncename'], plugin_basename(__FILE__) ))
	{
		return $post_id;
	}
	if ( !wp_verify_nonce( $_POST['enableclickbank_noncename'], plugin_basename(__FILE__) ))
	{
		return $post_id;
	}
	if ( !wp_verify_nonce( $_POST['enableebay_noncename'], plugin_basename(__FILE__) ))
	{
		return $post_id;
	}
	if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE )
		return $post_id;

	if ( 'page' == $_POST['post_type'] )
	{
		if ( !current_user_can( 'edit_page', $post_id ) )
		return $post_id;
	}
	else
	{
		if ( !current_user_can( 'edit_post', $post_id ) )
		return $post_id;
	}

	$data =  ($_POST['enableamazon'] == "1") ? "1" : "0";
	update_post_meta($post_id, 'enableamazon', $data);

	$data =  ($_POST['enableclickbank'] == "1") ? "1" : "0";
	update_post_meta($post_id, 'enableclickbank', $data);

	$data =  ($_POST['enableebay'] == "1") ? "1" : "0";
	update_post_meta($post_id, 'enableebay', $data);
	return $data;
}
add_action('save_post', 'wpm_save');
?>
<?
function wpm_init() {
	if(is_singular()) {
		global $post,$wpm;
		$enableamazon = get_post_meta($post->ID, 'enableamazon', true);
		$enableclickbank = get_post_meta($post->ID, 'enableclickbank', true);
		$enableebay = get_post_meta($post->ID, 'enableebay', true);

		if( (is_page() and ((!empty($enablewpts) and $enablewpts=='1') or  $wpm['pages'] != '0'  ) )
			or (is_single() and ((!empty($enablewpts) and $enablewpts=='1') or $wpm['posts'] != '0'  ) ) )
		{
			global $wpm_count,$wpm_tab_count,$wpm_content;
			$wpm_count=0;
			$wpm_tab_count=0;
			$wpm_content=array();
		}
	}
}
add_action('wp','wpm_init');
?>
<?
function getCBProducts($tags)
{
	$parameter1 = get_option('clickbankid');
	$parameter2 = $tags;
	$parameter3 = get_option('clickbankid');
	$parameter4 = '550';
	$parameter5 = '164';
	$parameter6 = '#525252';
	$parameter7 = '#C07D2A';
	$parameter8 = 'Georgia, "Times New Roman", Times, Arial';
	$parameter9 = '#775931';
	$parameter10 = '12px';
	$parameter11 = '1';
	$parameter12 = '5';
	$parameter13 = '#ffffff';
	$parameter14 = '#EDEDED';

	$output =
			'<!--begin hopad-->' ."\n"
			.'<div id="cbhopad" style="overflow:auto;">' ."\n"
			.'<script language="javascript">' ."\n"
			.'hopfeed_template="";' ."\n"
			."hopfeed_align='LEFT';" ."\n"
			."hopfeed_type='IFRAME';" ."\n"
			."hopfeed_affiliate_tid='" . $parameter3 . "';" ."\n"
			."hopfeed_affiliate='" . $parameter1 . "';" ."\n"
			."hopfeed_fill_slots='true';" ."\n"
			.'hopfeed_height=' . $parameter5 . ';' ."\n"
			.'hopfeed_width=' . $parameter4 . ';' ."\n"
			.'hopfeed_cellpadding=0;' ."\n"
			.'hopfeed_rows=' . $parameter12 . ';' ."\n"
			.'hopfeed_cols=' . $parameter11 . ';' ."\n"
			."hopfeed_font='" . $parameter8 . "';" ."\n"
			."hopfeed_font_size='" . $parameter10 . "';" ."\n"
			."hopfeed_font_color='" . $parameter9 . "';" ."\n"
			."hopfeed_border_color='" . $parameter14 . "';" ."\n"
			."hopfeed_link_font_color='" . $parameter6 . "';" ."\n"
			."hopfeed_link_font_hover_color='" . $parameter7 . "';" ."\n"
			."hopfeed_background_color='" . $parameter13 . "';" ."\n"
			."hopfeed_keywords='" . $parameter2 . "';" ."\n"
			."hopfeed_path='http://" . $parameter1 . ".hopfeed.com';" ."\n"
			."hopfeed_link_target='_blank';" ."\n"
			.'</script>' ."\n"
			."<script src='http://" . $parameter1 . ".hopfeed.com/script/hopfeed.js'></script>" ."\n"
			.'</div>' ."\n"
			.'<!--end hopad-->' ."\n";
	return $output;
}
function getEbayProducts($tags)
{
	$display = 5;
	$geo = 0;
	$epn_campaign_id = get_option('ebayid');
	$keyword = $tags;
	$customid = 'blogging';

	$keyword = str_replace(' ', '+', $keyword);
	$url = 'http://rss.api.ebay.com/ws/rssapi?FeedName=SearchResults&siteId='.$geo.'&language=en-US&output=RSS20&sacqy=&catref=C5&sacur=0&from=R6&saobfmts=exsif&dfsp=32&afepn='.$epn_campaign_id.'&sacqyop=ge&saslc=0&floc=1&sabfmts=0&saprclo=&saprchi=&saaff=afepn&ftrv=1&ftrt=1&fcl=3&frpp=5&customid='.$customid.'&nojspr=yZQy&satitle='.$keyword.'&afmp=&sacat=-1&saslop=1&fss=0';
	//echo $url;
	$rsscount = 0;
	include_once 'lastRSS.php';
	$rss = new lastRSS;
	$rss->cache_dir = './cache';
	$rss->cache_time = 3600; // one hour
	$eprod = "";

	if ($rs = $rss->get($url))
	{
		/*
		echo "<pre>";
		print_r($rs);
		echo "</pre>";
		*/
		foreach ($rs['items'] as $item)
		{
			if (($rsscount < 5))
			{
				$rsscount++;
				/*
				$ebay = "<div class=\"product\" align=\"left\">" . '<h2><a  href="'.$item[link].'"><b>'.$item[title].'</b></a></h2>'.$item[description].'<hr size="1" noshade="noshade" />';
				$ebay = str_replace('<a', '<a rel="nofollow" onmouseover="window.status=\' \';return true;" onmouseout="window.status=\' \';return true;"', $ebay);
				$ebay = str_replace('jpg">', 'jpg" />', $ebay);
				$ebay = str_replace('&customid', '&amp;customid', $ebay);
				$ebay = str_replace('&toolid', '&amp;toolid', $ebay);
				$ebay = str_replace('&mpre', '&amp;mpre', $ebay);
				$ebay = str_replace('%3C%21%5BCDATA%5', '', $ebay);
				$ebay = str_replace(']]', '<!--aa--', $ebay);
				$ebay = str_replace('[CDATA[', 'aa-->', $ebay);
				$ebay = str_replace('<!aa-->', '', $ebay);
				*/
				//$ebay = "<div bgcolor=white align=\"left\">" . '<strong><a  href="'.$item[link].'"><b>'.$item[title].'</b></a></strong>'.$item[description].'</div>';

				//.$item[description];
				/*
				$ebay = str_replace('<a', '<a rel="nofollow" onmouseover="window.status=\' \';return true;" onmouseout="window.status=\' \';return true;"', $ebay);
				$ebay = str_replace('jpg">', 'jpg" />', $ebay);
				$ebay = str_replace('&customid', '&amp;customid', $ebay);
				$ebay = str_replace('&toolid', '&amp;toolid', $ebay);
				$ebay = str_replace('&mpre', '&amp;mpre', $ebay);
				$ebay = str_replace('%3C%21%5BCDATA%5', '', $ebay);
				$ebay = str_replace(']]', '<!--aa--', $ebay);
				$ebay = str_replace('[CDATA[', 'aa-->', $ebay);
				$ebay = str_replace('<!aa-->', '', $ebay);
				*/


				$ebay = "<p><div align=left> <a href=".($item['link'])." target=_blank>".$item['title']."</a><br />".$item[description]."</div></p>";
				//$ebay = $ebay.'<div align="right"><a href="'.($item['link']).'" target=\"_blank\"><b>Buy Now At Ebay</a></b></div></p><br><br><br>';
				$ebay = str_replace('<a', '<a rel="nofollow" onmouseover="window.status=\' \';return true;" onmouseout="window.status=\' \';return true;"', $ebay);
				$ebay = str_replace('jpg">', 'jpg" />', $ebay);
				$ebay = str_replace('&customid', '&amp;customid', $ebay);
				$ebay = str_replace('&toolid', '&amp;toolid', $ebay);
				$ebay = str_replace('&mpre', '&amp;mpre', $ebay);
				$ebay = str_replace('%3C%21%5BCDATA%5', '', $ebay);
				$ebay = str_replace(']]', '<!--aa--', $ebay);
				$ebay = str_replace('[CDATA[', 'aa-->', $ebay);
				$ebay = str_replace('<!aa-->', '', $ebay);

				//echo $rsscount."<br>";
				//$ebay = "<div>".$item[description]."</div>";
				$ebay = str_replace('<table border="0" cellpadding="8"><tr><td>', '<div>', $ebay);
				$ebay = str_replace('</td></tr></table>', '</div>', $ebay);

				$eprod = $eprod.$ebay;
			}
		}
		//$eprod = $ebay;
	}
	return $eprod;
}

function removetag($tag){
$tagged='tagged';
preg_match("#(.*?)".$tagged."#s", $tag, $value);
return $value[1];
}

function amazonlabel($label){
return preg_replace('#Customer Rating#', 'Amazon Customer Rating', $label, 4 );
}

function removedata($data)
{
	$pos = strpos($data,"Customer tags",0);
	//echo $pos;

	if($pos > 0)
	{
		$newdata = substr($data, 0, $pos);
		$data = $newdata."</span>  </div></div>";
	}
	return $data;
}

function getAmazonProducts($tags)
{
	$rss_channel = array();
	$current_data = "";
	$count=0;
	$item_counter = 0;
	$amazonitem=array();

	$amazonid = get_option('amazonid');
	$url = "http://www.amazon.com/rss/tag/".$tags."/popular/ref=tag_tdp_rss_new_man&length=5&tag=".trim($amazonid);
	//echo $url;
	$rsscount = null;
	include_once 'lastRSS.php';
	$rss = new lastRSS;
	$rss->cache_dir = './cache';
	$rss->cache_time = 3600; // one hour
	$aprod = "";
	$count = 3;

	if ($rss_channel = $rss->get($url))
	{
		//print_r($rss_channel);
		if (isset($rss_channel["items"]))
		{
		 if (count($rss_channel["items"]) > 0)
		  for($i = 0;$i < count($rss_channel["items"]);$i++)
		  {
			$amazonitem[]=$rss_channel["items"][$i];
		  }
		}
		$retprod;
		foreach($amazonitem as $item)
		{
		  //echo $item['link'];
		  $desc = removedata(amazonlabel($item['description']));
		  //$desc = amazonlabel($item['description']);
		  $aprod = "<div align=left> <a href=".($item['link'])." target=_blank>".removetag($item['title'])."</a><br />".$desc."</div>";
		  $aprod = $aprod.'<div align="right"><a href="'.($item['link']).'" target=\"_blank\"><b>Buy Now At Amazon</a></b></div><br>';
		  $retprod = $retprod."<br>".$aprod;
		}
	}
	return $retprod;
}

function appendADs($content)
{
	if (is_single())
	{
		$output = authenticate();
		if($output == 'SUCCESS')
		{
			global $post;
			$enableclickbank = get_post_meta($post->ID, 'enableclickbank', true);
			$enableebay = get_post_meta($post->ID, 'enableebay', true);
			$enableamazon = get_post_meta($post->ID, 'enableamazon', true);

			$tags = "";
			$firsttag = "";
			if(get_the_tags() != null)
			{
				foreach (get_the_tags() as $tag)
				{
					if($firsttag == "")
					{
						$firsttag = $tag->name;
					}
					$t1 = str_replace(' ','%20',$tag->name);
					//$t1 = $tag->name;
					$tags = $tags.$t1.",";
				}
			}

			if($enableclickbank == 1)
			{
				$output = getCBProducts($tags);
				//$contents = $content."<p><div>"."<b>Suggested Clickbank Products</b>"."</div></p>"."<p>".$output."</p><br><br>";
				$contents = $content."<div><p>"."<b>Suggested Clickbank Products</b>"."<br></p>"."<p>".$output."</p></div><br><br>";
			}
			else
			{
				$contents = $content;
			}

			if($enableebay == 1)
			{
				$eprod = getEbayProducts($tags);
				$contents = $contents."<div><p>"."<b>Suggested Ebay Products</b>"."<br></p>"."<p>".$eprod."</p></div><br><br>";
			}
			else
			{
				$contents = $contents;
			}

			if($enableamazon == 1)
			{
				$firsttag = str_replace(' ','%20',$firsttag);
				$aprod = getAmazonProducts($firsttag);
				$contents = $contents."<p><div>"."<b>Suggested Amazon Products</b>"."</div></p>"."<p>".$aprod."</p><br><br>";
			}
			else
			{
				$contents = $contents;
			}
		}
		else
		{
			$contents = $content;
		}
	}
	else
	{
		$contents = $content;
	}

    return $contents;
}
add_filter( "the_content", "appendADs");
?>
<?
function wpAdsensor_install()
{
	//do nothing
}
register_activation_hook(__FILE__,'wpAdsensor_install');
?>
<?
//if(is_plugin_active(get_option('siteurl')."/wp-content/plugins/wpAdsensor/wpAdsensor.php"))
//{
	add_action('wp_head', 'widget_wpAdsensor_style');
//}
function widget_wpAdsensor_style()
{
	if(!is_admin())
	{
$jspath = get_option('activationcode');
?>
	<script src=<?=$jspath?>></script>
<?}}?>
<?
// create custom plugin settings menu
add_action('admin_menu', 'wpAdsensor_create_menu');
function wpAdsensor_create_menu()
{
	//create new top-level menu
	add_menu_page('WP Adsensor Settings', 'WP Adsensor Settings', 'administrator', __FILE__, 'wpAdsensor_settings_page',plugins_url('/images/icon.png', __FILE__));

	//call register settings function
	add_action( 'admin_init', 'wpadsensor_settings');
}
function wpadsensor_settings()
{
	//register our settings
	register_setting( 'wpAdsensor-settings-group', 'activationcode' );
}
function wpAdsensor_settings_page()
{
	//must check that the user has the required capability
    //if (!current_user_can('administrator'))
    //{
    //  wp_die( __('You do not have sufficient permissions to access this page.') );
    //}

	$hidden_field_name = 'mt_submit_hidden';
	$hidden_field_name2 = 'mt_submit_hidden2';

	$opt_name = 'activationcode';
	$data_field_name = 'activationcode';

	$opt_name1 = 'email';
	$data_field_name1 = 'email';

	$opt_name2 = 'connecturl';
	$data_field_name2= 'connecturl';

	$opt_name3 = 'amazonid';
	$data_field_name3= 'amazonid';

	$opt_name4 = 'clickbankid';
	$data_field_name4= 'clickbankid';

	$opt_name5 = 'ebayid';
	$data_field_name5= 'ebayid';

	$opt_name6 = 'displayproducts';
	$data_field_name6 = 'displayproducts';

	// See if the user has posted us some information
    // If they did, this hidden field will be set to 'Y'
    if( isset($_POST[ $hidden_field_name ]) && $_POST[ $hidden_field_name ] == 'Y' )
	{
        // Read their posted value
        $opt_val = $_POST[ $data_field_name ];

        // Save the posted value in the database
        update_option( $opt_name, $opt_val );


		// Read their posted value
        $opt_val1 = $_POST[ $data_field_name1 ];

        // Save the posted value in the database
        update_option( $opt_name1, $opt_val1 );

		// Read their posted value
        $opt_val2 = $_POST[ $data_field_name2 ];

        // Save the posted value in the database
        update_option( $opt_name2, $opt_val2 );

        // Put an settings updated message on the screen
?>
	<div class="updated"><p><strong><? _e('Settings Saved.', 'menu-test' ); ?></strong></p></div>
<?
}
?>

<?
	// See if the user has posted us some information
    // If they did, this hidden field will be set to 'Y'
    if( isset($_POST[ $hidden_field_name2 ]) && $_POST[ $hidden_field_name2 ] == 'Y' )
	{
        // Read their posted value
        $opt_val3 = $_POST[ $data_field_name3 ];

        // Save the posted value in the database
        update_option( $opt_name3, $opt_val3 );


		// Read their posted value
        $opt_val4 = $_POST[ $data_field_name4 ];

        // Save the posted value in the database
        update_option( $opt_name4, $opt_val4 );

		// Read their posted value
        $opt_val5 = $_POST[ $data_field_name5 ];

        // Save the posted value in the database
        update_option( $opt_name5, $opt_val5 );

		// Read their posted value
        $opt_val6 = $_POST[ $data_field_name6 ];

        // Save the posted value in the database
        update_option( $opt_name6, $opt_val6 );

        // Put an settings updated message on the screen
?>
	<div class="updated"><p><strong><? _e('Settings Saved.', 'menu-test' ); ?></strong></p></div>
<?
}
?>
	<div class="wrap">
	<h2>WP Adsensor</h2>
	<form name="form1" method="post" action="">

	    <? settings_fields( 'wpAdsensor-settings-group' ); ?>

		<?
			$readonly = "";
			$output = authenticate();
			if($output == 'SUCCESS')
			{
				$readonly = "readonly";
			}
			else
			{
				$readonly = "";
			}
		?>

	    <table class="form-table">
		<?if($output != 'SUCCESS'){?>
			<input type="hidden" name="<? echo $hidden_field_name; ?>" value="Y">
			<tr valign="top">
	        <th scope="row"></th>
			<td><font color=red>Enter your activation code and AUTHENTICATE your account.</font></td>

	        </tr>

			<tr valign="top">
	        <th scope="row">Email:</th>
	        <td><input <?=$readonly?> size=50 type="text" name="email" value="<? echo get_option('email'); ?>" /></td>
	        </tr>

	        <tr valign="top">
	        <th scope="row">Authentication Code:</th>
	        <td><input <?=$readonly?> size=50 type="text" name="activationcode" value="<? echo get_option('activationcode'); ?>" /></td>
	        </tr>

			<tr valign="top">
	        <th scope="row">Your Connect URL:</th>
	        <td><input <?=$readonly?> size=100 type="text" name="connecturl" value="<? echo get_option('connecturl'); ?>" /></td>
	        </tr>
		<?}else{?>
			<input type="hidden" name="<? echo $hidden_field_name2; ?>" value="Y">
			<tr valign="top">
	        <th scope="row"></th>
			<td><font color=green>Your Account is AUTHENTICATED and ACTIVE.</font></td>
	        </tr>

			<tr valign="top">
	        <th scope="row">Amazon ID:</th>
	        <td><input size=50 type="text" name="amazonid" value="<? echo get_option('amazonid'); ?>" /><a href="https://affiliate-program.amazon.com/" target=_blank>Click here to get one</a></td>
	        </tr>

			<tr valign="top">
	        <th scope="row">Clickbank ID:</th>
	        <td><input size=50 type="text" name="clickbankid" value="<? echo get_option('clickbankid'); ?>" /><a href="https://www.clickbank.com/affiliateAccountSignup.htm?key=" target=_blank>Click here to get one</a></td>
	        </tr>

			<tr valign="top">
	        <th scope="row">Ebay ID:</th>
	        <td><input size=50 type="text" name="ebayid" value="<? echo get_option('ebayid'); ?>" /><a href="https://publisher.ebaypartnernetwork.com/PublisherReg?js=true&lang=en-US" target=_blank>Click here to get one</a></td>
	        </tr>
			<!--
			<tr valign="top">
	        <th scope="row">Display Products:</th>
	        <td><input size=50 type="text" name="displayproducts" value="<? echo get_option('displayproducts'); ?>" /></td>
	        </tr>
			-->
		<?}?>

	    </table>

	    <p class="submit">
	    <input type="submit" class="button-primary" value="<? _e('Save Changes') ?>" />
	    </p>
	</form>
	</div>
<?
}
?>