AppDetails 2 for WordPress
========================

##What is it?
AppDetails 2 lets users to display information of mobile apps from the __AppStore__, __Google Play Store__ and __Windows Store__ easily in a WordPress blog.
##How to install:
Download [AppDetails 2](https://github.com/JMasip/AppDetails-for-WordPress/archive/master.zip) as a ZIP file. Then expand "master.zip" and copy "appdetails" folder to WordPress plugin's folder (/wp-content/plugins/). You must rename "config-sample.php" to "config.php" and fill the empty fields.
##How to use:
###AppStore:
url: _https://itunes.apple.com/us/app/drugtime/id657234388_

WP Post syntax: __[app]657234388[/app]__
###Google Play Store:
url: _https://play.google.com/store/apps/details?id=com.android.chrome_

WP Post syntax: __[app]com.android.chrome[/app]__

###Windows Store:
url: _http://apps.microsoft.com/windows/es-es/app/line/b039ba22-c3af-45b3-aea2-83d612c9bce6_

WP Post syntax: __[app]line/b039ba22-c3af-45b3-aea2-83d612c9bce6[/app]__

##Customize
You can modify `template.html`, `template-loading.html`, `style.css` and `translation.json`.
