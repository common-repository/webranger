=== WebRanger ===
Copyright (c) 2015 Pandora Security Labs
Author: PandoraLabs
Contributors: PandoraLabs
Tags: web application security, siem, webranger, exploit, sql injection, xss, hack, protection, vulnerability, web application firewall, owasp top ten, waf, security-as-a-service, soc, data analytics
Requires at least: 3.4
Tested up to: 4.4
Stable tag: 1.0.3
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl.txt

WebRanger is a free security WordPress plugin that keeps your site secure by blocking the threats away while providing detailed security reports.

== Description == 
= EFFECTIVELY BLOCK AND MONITOR YOUR WEBSITE TRAFFIC THROUGH A SECURITY CONTROL PANEL = 
WebRanger starts by reviewing incoming traffic to your website and if a request is flagged as malicious, it is then sent over to your personal [WebRanger Console](https://webranger.pandoralabs.net). In the console, you can review security events individually and prevent any further request coming from that particular IP address by blocking them. Conversely through the infographs available, you can have a precise overview of what are the security events occurring in your website. 

WebRanger is free and can be integrated in your website in less than five minutes. We also offer services which allows you to upgrade your free sensor to a higher tier. This allows you to gain access to the Pandora Labs' Security Operation Center that monitors and respond to security incidents 24 x 7. **Within just a few clicks, you can have your own personal security team watching over your website**. [Click here to know more](https://www.pandoralabs.net/webranger) or simply install WebRanger for free and start monitoring your website.  For a live demonstration, go to our [demo site]( https://webranger.pandoralabs.net/demo/) and take a tour in the WebRanger console. 



This is a video presentation of WebRanger:

[youtube https://www.youtube.com/watch?v=KBvWEzxWAsw]


= WEBRANGER SECURITY FEATURES =
= WebRanger Sensor = 
* Detection of common web application attacks (e.g. SQLi, XSS, Directory Traversal)
* Blocking malicious live traffic from IP Address, preventing any further request from the source
* Ease of installation

= WebRanger Console = 
* Creation of customized rule blocking for IP addresses
* Event Aggregation for generation of alerts
* Detailed events viewing for analysis
* Generation of security reports (e.g. Top 5 Source IP Address, Common Tags)
* Managing of multiple WebRanger sensors
* Multi-tier user account, creation of a sub-user account if you want someone to manage it for you

= Pandora Labs' Security Operation Center (for paid tiers) =
* 24x7 Monitoring and Response from Security Events
* Security Events Ticket Handling
* Email and phone support from Security Incidents

= Technicalities =
The WebRanger plugin is composed of two components: the **Web Application Intrusion Detection System (WIDS)** and the **Web Application Firewall (WAF)**.

The WIDS is in charge in scanning the malicious HTTP request and sending them to your WebRanger console. The scanning and detection function of WIDS is based on the open-source project named PHPIDS. For a detailed discussion in how this powerful scanner detects attacks, refer to this [link](https://github.com/PHPIDS/PHPIDS). Moreover, WIDS is also in charge in transmitting the malicious request over to your WebRanger console for further consolidation and analysis.

The WAF is in charge in blocking or permitting IP addresses from accessing your website. WebRanger maintains a blacklist IP address list in your database. If a blacklisted IP address attempts to enter your website, a 404 Page will be displayed from their browser. Configuring your WAF can be done from your WebRanger console through an API call method. These APIs are securely programmed to only allow you or Pandora SOC personnel to configure your WebRanger plugin from the WebRanger console.

For further information about WebRanger, you can visit our [website.](https://www.pandoralabs.net/webranger)

== Installation ==
[Click here to watch a video on how to integrate WebRanger in WordPress](https://www.youtube.com/watch?v=CHsVEsl4M9I)
= To Integrate and test WebRanger in your Wordpress: =
1. Install automatically or upload the ZIP file to /wp-content/plugins/ directory
2. Activate the plugin through the 'Plugins' menu option screen in the WordPress Admin console.
3. Go to Settings then select the submenu item WebRanger
4. From here, there are 3 available options in activating your WebRanger plugin to the WebRanger console. Select one of your preference. 
5. If the configuration is successful, a message saying "WebRanger is Currently Active" is displayed in the settings page of the WebRanger plugin.
6. (For Testing Purposes) Click the “Test WebRanger” to determine if WebRanger is configured properly. 

= Possible Options in Activating WebRanger =
* (a) If you don't have an existing WebRanger account, choose the option "Register New Account".
* (b) If you have an existing WebRanger account but have not subscribed to any subscription tier, choose the option "Use Existing Account".
* (c) If you have an existing WebRanger account and subscribed to any subscription tier, choose option "Use Existing Sensor".


== Frequently Asked Questions ==
[Visit our FAQ page for a more detailed and updated inquiries.]( https://www.pandoralabs.net/webranger/faqs)

= What is the WebRanger plugin? =
* The WebRanger plugin is composed of two components: the Web Application Intrusion Detection System (WIDS) and the Web Application Firewall (WAF).
* The WIDS is in charge in scanning the malicious HTTP request and sending them to your WebRanger console. 
* Moreover, WIDS is also in charge in transmitting the malicious request over to your WebRanger console for further consolidation and analysis.
* The WAF is in charge in blocking or permitting IP addresses from accessing your website. 
* WebRanger maintains a blacklist IP address list in your database. If a blacklisted IP address attempts to enter your website, a 404 Page will be displayed from their browser. 
* Configuring your WAF can be done from your WebRanger console through an API call method. These APIs are securely programmed to only allow you or Pandora SOC personnel to configure your WebRanger plugin from the WebRanger console.
= What is the WebRanger console? =
* The WebRanger console is a simple SIEM (security information and event manager) that is responsible for displaying identified malicious HTTP requests and alerts on your website which has an active WebRanger plugin is installed. 
* The WebRanger console is located at https://webranger.pandoralabs.net.
= How does the WebRanger plugin and WebRanger console work? =
* WebRanger protects your web application in real-time by identifying attacks towards your website and blocking them. Detection of attacks and responding to it in a timely manner is the key to ensuring your web application’s safety. 
* WebRanger starts by reviewing each HTTP request from your visitors and scanning them for malicious signatures. When a HTTP request is flagged as malicious, it then sends identified HTTP request to WebRanger console (https://webranger.pandoralabs.net) for evaluation and analysis by a security analyst. 
* NOT ALL/EVERY HTTP request are sent over to the console; only those that were flagged. 
* Depending on your current subscription tier, a security analyst from Pandora’s Security Operation Center can validate if this specific request is truly malicious. If it is, there is an option in the WebRanger console to block the specific IP address to prevent any further request from that IP.

= Who can see the malicious HTTP requests? =
* All malicious HTTP requests identified by the WebRanger plugin
= A “WebRanger Error: Activation Failure” is displayed in the WebRanger Settings page, what happened? =
* (1) An existing email or WebRanger plugin name is already taken.
* (2) An existing free WebRanger subscription is already present in your WebRanger account.
* (3) Invalid characters for the website name field. Present syntax only allows (alphanumeric characters and the ‘_’ character)
= How I will be notified by our team if an event is flagged as an attack? = 
* Alerts are always displayed on the WebRanger console.
* If you want to be notified (and be protected) when a legitimate attack occurs, you need to upgrade your WebRanger subscription to a paid tier (Basic to Business tiers) and you will be notified via email (both Basic and Pro Tiers) and by phone (for Business Tier). 
* Attacks are also blocked automatically by a Pandora SOC personnel.
= How can I upgrade to a paid WebRanger tier? =
* From the WebRanger console, go to WebApps->then Click the Upgrade Now button for the specified website/domain and follow the upgrade wizard.
= How can I block an IP Address? =
* To be able to manually block IP addresses accessing your website, you can access the web application firewall rules through the WebRanger console, under Webapps. You can click on the WebRanger logo to display the list of rules currently applied.

== Screenshots ==
1. Download WebRanger
2. Configure and register to WebRanger
3. Gain access to WebRanger Console Dashboard
4. Gain a new perspective about your website's security events
5. A single alert being handled by a Pandora SOC personnel

== Changelog ==
= 1.0.3 =
* Replaced __DIR__
* Modified htaccess
= 1.0.1 = 
* Tool Tip in Security Settings
* Key Management Feature
* Test Button
* Fined Tuned Signatures
* Link to video tutorials in readme page
= 1.0.0 =
* Initial Release of WebRanger Plugin
* Comes with WebRanger 1.1.1

== Upgrade Notice ==
= 1.0.0 =
Updated readme.txt and fix screenshots for plugin page.

= 1.0.1 =
Link a youtube video in the readme page

= 1.0.3 =
Compatibility with some hosting providers