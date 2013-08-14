<?php
	require_once("../config.php");

	if($AD_DEBUG_MODE) {
		ini_set('display_errors', 1);
		error_reporting(~0);
	}

	function bytesConverter($int_bytes) {
		if(strlen((string) $int_bytes) >= 10) {
			return round(intval($int_bytes) / 1073741824, 2) . " GB";
		}
		else {
			return round(intval($int_bytes) / 1048576, 2) . " MB";
		}
	}	

	function getInfoBox($id) {

		//$plugin_path = plugins_url("", __FILE__);
		// S'ha d'acabar de fer això
		$plugin_path = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

		$app_info = array();

		// Windows Phone
		if(strpos($id, "/") != false) {
			$app_info = getWindowsPhoneAppInfo($id);
		}
		// Apple AppStore:
		else if(is_numeric($id)) {
			$json = getAppleAppInfo($id);

			if($json->resultCount == 0)
			{
				$app_info["count"] = 0;
			}
			else {
				$json = $json->results[0];

				$app_info["store"] = "iTunesStore";
				$app_info["title"] = $json->trackName;
				$app_info["company"] = $json->artistName;
				$app_info["company_url"] = $json->sellerUrl;
				$app_info["store_url"] = $json->trackViewUrl;
				$app_info["summary"] = substr($json->description, 0, 500) . "… " . "<a href='" . $app_info["store_url"] . "' target='_blank'>" . $AD_READ_MORE_TEXT . "</a>";
				$app_info["icon"] = $json->artworkUrl512;
				$app_info["price"] = $json->price;
				$app_info["formatted_price"] = $json->formattedPrice;
				if(isset($json->averageUserRating)) {
					$app_info["rating"] = $json->averageUserRating;	
				}
				else {
					$app_info["rating"] = "--";
				}
				$app_info["version"] = $json->version;
				//$app_info["size"] = "<div class='app-box-key'>MIDA:</div><div class='app-box-value'>" . bytesConverter($json->fileSizeBytes) . "</div>";
				$app_info["size"] = bytesConverter($json->fileSizeBytes);

				$itunes_app_class = " app-box-icon-itunes";
				$powered_by = "<span class='app-box-powered-by'><br><br></span>";
				$border_radius = "-webkit-border-radius: 20px; -moz-border-radius: 20px; border-radius: 20px;";
			}
		}
		else // Google Play
		{
			$json = getGooglePlayAppInfo($id);

			if(isset($json->status_code)) {
				if($json->status_code == 404) {
					$app_info["count"] = 0;
				}
			}
			else
			{
				$app_info["store"] = "GooglePlay";
				$app_info["title"] = $json->name;
				$app_info["company"] = $json->developer;
				$app_info["company_url"] = $json->developer_url;
				$app_info["store_url"] = $json->market_url;
				$app_info["description"] = substr($json->description, 0, 500) . "… " . "<a href='" . $app_info["store_url"] . "' target='_blank'>" .$AD_READ_MORE_TEXT . "</a>";
				$app_info["size"] = "<div class='app-box-key'>MIDA:</div><div class='app-box-value'>" . bytesConverter(intval($json->size)) . "</div>";
				$app_info["formatted_price"] = $json->price;
				$app_info["rating"] = $json->rating;
				$app_info["version"] = $json->version;
				$app_info["icon"] = $json->icon_full;

				if($json->price == "Free")
				{
					$app_info["price"] = 0;
				}
				else
				{
					$app_info["price"] = substr($json->price, 1);
				}

				$itunes_app_class = "";
				$powered_by = "<span class='app-box-powered-by'>" . $AD_DISCOVER_MORE_APPS . "<a href='http://playboard.me/app/$id'>Playboard</a></span>";
				$border_radius = "";
			}
		}
		
		return json_encode($app_info);
		//return replace_line_breaks($html);
	}

	function replace_line_breaks($str, $replace = "") {
		$chars = array("", "\n", "\r", "chr(13)", "\t", "\0", "\x0B");
		return str_replace($chars, $replace, $str);
	}

	function getWindowsPhoneAppInfo($id) {
		require_once("engine/simple_html_dom.php");

		$url = "http://www.windowsphone.com/$AD_WS_COUNTRY/store/app/$id";

		$html = file_get_html($url);
		$info = $html->find("#main");
		$summary = $info[0]->children(0)->children(3);
		$details = $info[0]->children(0)->children(4);

		$json = array(
			"store" => "WindowsPhoneStore",
			"store_url" => $url,
			"title" => $info[0]->children(0)->children(1)->innertext,
			"description" => str_replace("&#10;", "<br />", $details->getElementById("appDescription")->children(0)->children(0)->innertext),
			"icon" => $summary->children(0)->children(0)->getAttribute("src"),
			"price" => (float) str_replace(",", ".", explode(" ", $summary->children(1)->children(0)->children(0)->innertext)[0]),
			"formatted_price" => $summary->children(1)->children(0)->children(0)->innertext,
			"rating" => explode("Pt", explode(" ", $summary->children(1)->getElementById("rating")->children(2)->getAttribute("class"))[1]),
			"size" => $summary->children(4)->getElementById("packageSize")->children(1)->innertext,
			"version" => $summary->children(4)->getElementById("version")->children(1)->innertext,
			"company" => $summary->children(4)->getElementById("publisher")->children(1)->innertext,
			"company_url" => $summary->children(4)->getElementById("publisher")->children(1)->getAttribute("href")
		);

		$json["summary"] = substr(str_replace("<br />", " ", $json["description"]), 0, 888) . "[...]";

		if($json["formatted_price"] == "Gratis") {
			$json["formatted_price"] = "Gratuïta";
		}

		if($json["company_url"] == false) {
			$json["company_url"] = "#";
		}

		$ratings_to_num = array("zero" => 0 ,"one" => 1, "two" => 2, "three" => 3, "four" => 4, "five" => 5);
		$json["rating"] = $ratings_to_num[strtolower($json["rating"][0])] . "." . $ratings_to_num[strtolower($json["rating"][1])];

		return $json;
	}

	function getGooglePlayAppInfo($bundleId) {
		$url = "http://dev.appaware.com/1/app/show.json?p=" . $bundleId . "&client_token=" . $AD_APPAWARE_CLIENT_TOKEN;		
		return json_decode(file_get_contents($url));
	}

	function getAppleAppInfo($id) {
		$url = "https://itunes.apple.com/lookup?id=" . $id . "&country=" . $AD_COUNTRY;
		return json_decode(file_get_contents($url));
	}

	header('Content-Type: application/json');
	echo getInfoBox($_GET["app"]);
?>