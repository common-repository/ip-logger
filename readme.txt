=== IP Logger ===
Contributors: Steve
Donate link: http://www.mretzlaff.com/wordpress-plugins/donate/
Tags: plugin, log, protection, IP, hostnames, analyze, traffic, charts, OpenStreetMap, Google Analytics, export, CSV, XML, download, SQLie, mySQL, database, db
Requires at least: 2.0.2
Tested up to: 3.0
Stable tag: 3.0

Logs the IP of all your blog visitors & enables you to protect your WordPress website against 
undesirables (IP Logger Block option). Export of saved records into CSV and XML.
You can easily download all logged informations to your local computer with our freeware 
"IP Logger Analyzer" (IPLA). The logs will be saved in a SQLite database, so you're enabled to 
run your own analytics on your logs. Within the next weeks we'll also implement a datastorage in 
mySQL and other database systems.

== Description ==

You can easily get an overview of all your website visitors. Every click on your sites will be 
stored with some details into your own database. None of your data (traffic, number of visitors,
IPs, etc.) will be stored outside your database. This can be important for your security and to 
keep 'spying' services like Google Analytics outside.

Some simple charts (please see screenshots) will help you to get a quick overview about the most
important informations about your website visitors. A charts with traffic informations (allowed 
and blocked accesses), a chart with an overview about the home-countries of your visitors and
(last but not least!) a Map (based on OpenStreetMap) where you can find all your visitors by a
green (= allowed) and red (= denied/blocked access to your website) dot.

The blocking Filter is <u>optional and has to be enabled</u> within the IP Logger Admin Settings Area.

To ignore (but log) visitors in the charts, use the new "Ignore filter". With this filter you can
define IPs, WordPress Usernames, Countries and Hostnames to be ignored within the dashboard charts.
Of course all those visitors will be logged - but marked as "ignored".

Supported languages: English, Swedish (Svensk), German (Deutsch).
For new languages please feel free to contact us. You are welcome.

Please feel free to write us an email with your wishes and ideas for the next version(s) to:
info@mretzlaff.com

And please note:
Wishes of our sponsors will be handled with priority :o)

== Installation ==

1. Unzip the downloaded ZIP archive file to a folder
2. Upload the folder "yhc-ip-logger" to your WordPress Plugin folder "/wp-content/plugins/"
3. Activate the plugin through the "Plugins" menu in WordPress (Backend)

To disburden your online mySQL Server, you can use our free software "IP Logger Analyzer" (IPLA). 
This tool will enable you to download the logged hits from your webserver to your local computer
and to run more statistics. On your local computer (or network) you can save the hits without
any limit and create visitor statistics over a long period. An Export to a local mySQL Server and
other RDBMS (Databasesystems) is planned to the next IPLA version.

== Update ==

To avoid error messages on your website(s) during the file update to your server, follow these
instructions. Of course, a normal update will also work.

1. Extract the "IP Logger" folder completely from the downloaded archive
2. Rename the new "IP Logger" folder to eg. "ip-logger-new"
3. Upload this renamed folder to your webserver (into the folder /wp-content/plugins/)
4. Wait until the update is finished
5. Delete the current "ip-logger" folder from your webserver
6. Rename the uploaded folder (eg. "ip-logger-new") to "ip-logger"
7. Call your website to run all internal updates automatically
8. Done

== Screenshots ==

1. Chart with all hits (allowed and blocked) of the past 30 days
2. Chart with an overview of the countries of your visitors
3. Map (OpenStreetMap) to get an overview of the cities of your visitors
4. Of course you can zoom in & out. You can get more details and the visited URLs
5. Administration Backend (Plugin Settings)
6. Easy to use export and feedback functions in the IP Logger backend

== Frequently Asked Questions ==

= Why are some of the bars in the chart red ? =

They show you the weekend (Sat and Sun). Mon - Fri are shown in blue.
You'll also find the weekday in the chart hint. Move you mouse over a bar and the hint will popup with some more details about that day.

= Why are where default filters within the block filter ? =

Those entries are well known website crawler and spiders. Those programs scans your whole website 
and store details, texts and other informations on other servers and sometimes websites.

= How can i check the IP Logger blocking option ? =

1. Get your current IP ("http://www.yourhelpcenter.de/2009/12/meine-aktuelle-ip-adresse/")
2. Copy and paste that IP Address (eg. 123.123.123.123) to the "new filter textfield" and press "Save filter"
3. Try to enter your website. You'll get a message like "Sorry, but the access to this website has been blocked"
4. Please don't forget to remove that IP Filter! 

= My charts are empty =

Is Flash installed ? You can find the software here: http://www.adobe.com/go/DE-H-M-A2 

== Changelog ==

= 3.1 =

* Fix: Sometimes the old hits table is not renamed
* Info: Spanish translation will be included in the next version
* Info: I (Steve) overtook this project from the author

= 3.0 =

* New: The "IP Logger Analyzer (IPLA)" is available for download on our websites
* New: Dynamic updates are possible now: Your IP Logger Version can updates itself
* New: If the plugin logging is disabled, the other option will not be shown (clear & easy GUI)
* New: You need a PIN to download your log informations. That PIN can be changed within the IP Logger settings in your WP Backend
* New: You can define IPs, Hosts and Username to be ignored within the normal visitors statistics
* New: There are several selectable modules to get the geographic informations about your visitors
* New: A link in the panel header of the IP Logger dashboard (charts panel) will open the IP Logger settings directly
* New: An "explanation" link in the panel header of the IP Logger dashboard (charts panel) opens the IP Logger online manual for further information
* New: You can define in the settings panel, which role (and the roles above) should be able to see the IP Logger dashboard
* New: The geographic details module can now also be switched off (then no detail informations will be read)
* New: Faster charts on the WP Backend Dashboard
* New: Within the IP Logger map (dashboard) you can click on the dots to get more details about those visitors (URL, hits, visited pages, ...)
* New: A new form on the settings panel to send us your ideas directly online and without extra work or extra e-mail client
* New: In the "About IP Logger" tab we inform you about our included 3rd party tools
* New: You can find the number of totally saved and blocked hits in the "About IP Logger" tab
* New: The plugin creates automatically an unique GUID for the communication with the IP Logger Analyzer (IPLA)
* New: The last IPLA communication will be displayed (timestamp and IP of client)
* New: At complex settings you'll find the new "Read more ..." link to a manual on our websites
* New: All IPLA communication parameters are protected against SQL injection attacks
* Fix: Bug for filtering visitors by country code fixed
* Fix: Sometimes the error "JSON Parse Error [Syntax Error]" accorred in the charts. Fixed
* Better: Easier to handle settings panel
* Better: Optimized panel layout (fonts, paddings, lines, etc.)
* Better: If the IP Logger plugin has been disabled, the charts also will not be displayed (just an information message)
* Better: E-Mail notifications after updates (if enabled in the settings)
* Better: Supplemented the map hint (IP Logger Dashboard > Map) with some useful informations (Longitude, Latitude, Country/Code3, ...)
* Better: Optimized charts, some small layout and analysis changes (optimized y axis, dates in the x axis, etc.)
* Better: Small spelling corrections
* Better: Optimized e-mail layout and sender address
* Better: Hits will be displayed with grouped thousands now
* Better: In the charts you'll find now also "blocked" and "igoned" hits in the info hint (move the mouse over a bar in the chart)
* Better: Optimized plugin itself. Due to this optimization the plugin is faster now
* Better: Lines within the filter panels optimize the clearness of the panels
* Info: During the running upload (update) of IP Logger (copying the files via FTP to the server) there could be errors on the website (due to folders are not already existing). This is a normal problem of all plugins. 
* Info: With this version, the internal IP Logger option names have been renamed (wp_options)
* Info: GoogleMaps will not be supported within the next future, we'll continue using OpenStreetMaps
* Info: An option to get IP details from your own local server will be implemented within the next version
* Info: Some URLs have changed. Updated within the plugin
* Info: The internal documentation has been supplemented

= 2.9 =
* Better: Some small fixes like spelling corrections
* Info: Preparation for V3.0 and the new "IP Logger Analyser (IPLA)"

= 2.8 =
* New: Swedish translation (thanks to Emil Isberg - http://emil.isberg.eu) (Svensk)
* New: Export saved data in XML format
* Better: Map-details table (Backend > Dashboard > Map > Details to location) is easier to read
* Fixed: Map-details table displays now to correct filter (blocked/not blocked)

= 2.7 = 
* New: Export saved data to CSV format, other formats are following
* New: More map details (now details to each location will be displayed)
* Better: Define new filters from data of your last visitors. The last 20 records for IP, Country and Agent are displayed. You can easily choose an entry and add a filter.
* Better: The "country-chart" is now limited to the Top-15 countries (easier to read in most cases)

= 2.6 =
* New: Completely new built version. ChangeLog with infos about all previous version cleared.
