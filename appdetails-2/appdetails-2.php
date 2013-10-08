<?php
	/*
	Plugin Name: AppDetails 2
	Plugin URI: http://jordi.masip.cat/
	Description: Get easily the description of an app from the AppStore or Google Play. How to use: 1) Google Play: <strong>[app]com.android.chrome[/app]</strong> <em>(https://play.google.com/store/apps/details?id=<strong>com.android.chrome</strong>&hl=ca)</em>. 2) AppStore: <strong>[app]535886823[/app]</strong> <em>(https://itunes.apple.com/en/app/chrome/id<strong>535886823</strong>?mt=8)</em> 3) Windows Phone: <strong>[app]vimeo/ff8dadc8-8efd-42c7-a0f4-de7a48dd186b[/app]</strong> <em>(http://www.windowsphone.com/es-es/store/app/<strong>vimeo/ff8dadc8-8efd-42c7-a0f4-de7a48dd186b</strong>)</em>
	Version: 2.0.1
	Author: Jordi Masip i Riera
	Author URI: http://jordi.masip.cat/
	License: GPL2
	*/

	//ini_set('display_errors', 1);
	//error_reporting(E_ALL);

	function AP_custom_head() {
		echo '<link rel="stylesheet" type="text/css" href="' . plugins_url("", __FILE__) . '/template/style.css">';
	}
	add_action("wp_head", "AP_custom_head");

	function AP_the_reescriptor($text) {
		$plugins_url = plugins_url("", __FILE__);
		$pattern = "/\[app\](.*?)\[\/app\]/s";
		preg_match_all($pattern, $text, $id);

		$len = count($id) - 1;
		$i = 0;
		if($len >= 0) {
			$template_translation = addslashes(str_replace("\n", "", file_get_contents($plugins_url . "/template/translation.json")));
			$urls = "[";
			foreach ($id[$len] as $key => $value) {
				if($value !== null || $value !== "") {
					$urls .= '"' . $plugins_url . '/engine/get-json.php?app=' . $value . '",';
					$app_box = str_replace("{{i}}", $i, file_get_contents($plugins_url . "/template/template-loading.html"));
					$text = preg_replace($pattern, $app_box, $text, 1);
					$i++;
				}
			}
			$urls = substr($urls, 0, strlen($urls) - 1) . "]";
			$template = str_replace("\n", "", file_get_contents($plugins_url . "/template/template.html"));

$text .= <<<END
<script type="text/javascript">
(function () {
	function showAppInfo() {
		var template = '$template', template_translation = eval("(" + "$template_translation" + ")");
		for(key in template_translation) {
			template = template.replace(key, template_translation[key]);
		}
		var $ = jQuery, urls = $urls, i = 0;
		window.app_info = document.getElementsByClassName("ai-container");
		window.app_info_count = 0;
		for(i = 0; i < urls.length; i++) {
			$.getJSON(urls[i], function(data) {
				var app_info = window.app_info, new_template = template, k = 0;
				for(key in data) {
					new_template = new_template.replace("{{" + key + "}}", data[key]);
				}
				app_info[window.app_info_count].innerHTML = new_template;
				window.app_info_count += 1;
			});
		}
	}
	if (typeof jQuery == 'undefined') {
	    window.addEventListener("load", showAppInfo);
	} else {
	    showAppInfo();
	}
})();
</script>
END;
		}
		return $text;
	}

	function bytesConverter($int_bytes){
		if(strlen((string) $int_bytes) >= 10) {
			return round(intval($int_bytes) / 1073741824, 2) . " GB";
		}
		else {
			return round(intval($int_bytes) / 1048576, 2) . " MB";
		}
	}

	add_filter("the_content", "AP_the_reescriptor");
?>