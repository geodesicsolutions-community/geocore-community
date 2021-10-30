<?php

# This is a list of "user-agents" (or a unique "portion" of the user-agent) for
# known robots or search engine crawlers. If one of the following user-agents
# (or user-agent portion) is detected, it will not attempt to set a
# cookie or redirect.

# We recommend you DO NOT MODIFY this file, there is a way to add to the list
# in the admin, under Admin Tools & Settings > BETA Tools > BETA Settings look
# for the setting "additional_robots_list".

# Note that these are CASE SeNsiTivE!

# If you know of a better or more up to date list, let us know.  We are not search
# engine or user-agent experts, so we relied on a list at http://www.pgts.com.au/download/data/robots_list.txt
# which is referenced by many sites as the defacto place for a list of robots and user agents.

/**
 * Tips for "shortening" (or not shortening) for partials:
 *  - If not anything "distinguishable" don't shorten (if it "looks" like normal browser user agent)
 *  - If it matches exactly with "real" browser, don't include at all
 *  - Case sensitive
 *  - Don't shorten to something generic, for instance "bumblebee" as it is concievable
 *    that a "future" browser may have that in the user-agent (use full user-agent in such cases)
 *  - When in doubt, if there is not a "bot version number" in the agent string, or a lot
 *    of "variations" from the same robot, no need to shorten the string.
 *  - Full user agents, use var name of $robots.  Partials use var name $robotP
 *    (P for Partial) and put each one in it's section
 *  - For either one, in comments, add date added and link to info about bot if available.
 *  - If it is obvious a spider or bot is no longer in existance and can be verified,
 *    remove it!
 */

####  FULL User Agents  ####

$robots[] = 'Mozilla/4.0 (Search Engine Marketing Tactics Amsterdam 2002 Information Spider)'; //Amsterdam 2002
$robots[] = 'Mozilla/3.0 (compatible; AvantGo 3.2)'; //AvantGo 3.2
$robots[] = 'BDFetch'; //BDFetch
$robots[] = 'battlebot'; //Battlebot
$robots[] = 'Big Brother (http://pauillac.inria.fr/~fpottier/)'; //Big Brother
$robots[] = 'Mozilla/4.0 (compatible; BorderManager 3.0)'; //BorderManager 3.0
$robots[] = 'bumblebee/1.0 (bumblebee@relevare.com; http://www.relevare.com/)'; //Bumblebee 1.0
$robots[] = 'cd34/0.1'; //Cd34 0.1
$robots[] = 'CipinetBot (http://www.cipinet.com/bot.html)'; //CipinetBot
$robots[] = 'cosmos/0.9_(robot@xyleme.com)'; //Cosmos 0.9
$robots[] = 'Crawl_Application'; //Crawl_Application
$robots[] = 'Custo 2.0 (www.netwu.com)'; //Custo 2.0
$robots[] = 'DeepIndex (http://www.deepindex.com)'; //DeepIndex
$robots[] = 'Dual Proxy'; //Dual Proxy
$robots[] = 'Dumbot'; //Dumbot
$robots[] = 'e-SocietyRobot(http://www.yama.info.waseda.ac.jp/~yamana/es/)'; //E-SocietyRobot
$robots[] = 'EARTHCOM.info/1.2'; //EARTHCOM.info 1.2
$robots[] = 'EmailSiphon'; //EmailSiphon
$robots[] = 'Explorer 6'; //Explorer 6
$robots[] = 'Mozilla/4.0 (compatible: FDSE robot)'; //FDSE robot
$robots[] = 'FastBug http://www.ay-up.com'; //FastBug
$robots[] = 'favicon finder at http://iconsurf.com/'; //Favicon
$robots[] = 'favicon monitor at http://iconsurf.com/'; //Favicon
$robots[] = 'Firefly/1.0 (compatible; Mozilla 4.0; MSIE 5.5)'; //Firefly 1.0
$robots[] = 'Mozilla/3.0 (compatible; Fluffy the spider; http://www.searchhippo.com/; info@searchhippo.com)'; //Fluffy the spider
$robots[] = 'Mozilla/4.0 (compatible; MSIE 5.0; www.galaxy.com; http://www.pgts.com.au/; +http://www.galaxy.com/info/crawler.html)'; //FusionBot
$robots[] = 'FyberSpider (+http://www.fybersearch.com/fyberspider.php)'; //FyberSpider
$robots[] = 'gatherer/0.9'; //Gatherer 0.9
$robots[] = 'gazz/5.0 (gazz@nttr.co.jp)'; //Gazz 5.0
$robots[] = 'Generic'; //Generic
$robots[] = 'GetRight/4.5e'; //GetRight 4.5
$robots[] = 'Gigabot/1.0'; //Gigabot 1.0
$robots[] = 'Mozilla/4.0 (compatible; MSIE 5.0; Windows NT; Girafabot; girafabot at girafa dot com; http://www.girafa.com)'; //Girafabot
$robots[] = 'Goldfire Server'; //Goldfire Server
$robots[] = 'Green Research, Inc.'; //Green Research, Inc.
$robots[] = 'GregBot (compatible; MSIE; Windows; Q312461)'; //GregBot
$robots[] = 'HTTPConnect'; //HTTPConnect
$robots[] = 'Mozilla/4.5 (compatible; HTTrack 3.0x; Windows 98)'; //HTTrack 3.0x
$robots[] = 'hget/0.3'; //Hget 0.3
$robots[] = 'htdig'; //Htdig
$robots[] = 'Mozilla/4.0 (compatible; ICS 1.2.105)'; //ICS 1.2.105
$robots[] = 'IPiumBot laurion(dot)com'; //IPiumBot
$robots[] = 'ia_archiver'; //Ia_archiver
$robots[] = 'lcabotAccept: */*'; //IcabotAccept
$robots[] = 'ichiro/1.0 (ichiro@nttr.co.jp)'; //Ichiro 1.0
$robots[] = 'Mozilla/3.0 (compatible; Indy Library)'; //Indy Library
$robots[] = 'Mozilla/3.0 (INGRID/3.0 MT; webcrawler@NOSPAMexperimental.net; http://aanmelden.ilse.nl/?aanmeld_mode=webhints)'; //Ingrid 3.0
$robots[] = 'http://www.istarthere.com (spider@istarthere.com)'; //Istarthere
$robots[] = 'Java1.4.0'; //Java 1.4.0
$robots[] = 'JoBo/1.3 (http://www.matuschek.net/jobo.html)'; //JoBo 1.3
$robots[] = 'k2spider'; //K2spider
$robots[] = 'KMcrawler'; //KMcrawler
$robots[] = 'Knowledge Engine'; //Knowledge Engine
$robots[] = 'LNSpiderguy'; //LNSpiderguy
//NOTE:  A lot of variations on Larbin...  Not doing "partials" for variations
//below since the number of variations it removes would not significant, and no
//"version numbers" are involved.
$robots[] = 'LARBIN-EXPERIMENTAL (efp@gmx.net)'; //Larbin
$robots[] = 'LARBIN-EXPERIMENTAL efp@gmx.net'; //Larbin
$robots[] = 'Mozilla (la2@unspecified.mail)'; //Larbin
$robots[] = 'Mozilla la2@unspecified.mail'; //Larbin
$robots[] = 'Mozilla/4.0 (efp@gmx.net)'; //Larbin
$robots[] = 'Mozilla/4.0 efp@gmx.net'; //Larbin
$robots[] = 'SearchGuild_DMOZ_Experiment (chris@searchguild.com)'; //Larbin
$robots[] = 'SearchGuild_DMOZ_Experiment chris@searchguild.com'; //Larbin
$robots[] = 'larbin (samualt9@bigfoot.com)'; //Larbin
$robots[] = 'larbin samualt9@bigfoot.com'; //Larbin
$robots[] = 'libwww-MGET/1.0 libwww/5.2.8'; //Libwww-MGET 1.0
$robots[] = 'Perl-Win32::Internet/0.082'; //Libwww-perl
$robots[] = 'Linknzbot 2004/(+http://www.linknz.co.nz/robot.php)'; //Linknzbot 2004
$robots[] = 'Linknzbot/ (+http://www.linknz.co.nz/robot.php)'; //Linknzbot
$robots[] = 'Links SQL (http://gossamer-threads.com/scripts/links-sql/)'; //Links SQL
$robots[] = 'Look.com'; //Look
$robots[] = 'LWP::Simple/'; //Lwp-request
$robots[] = 'Lycos_Spider_(modspider)'; //Lycos_Spider
$robots[] = 'MSProxy/2.0'; //MSProxy 2.0
$robots[] = 'MSRBOT/0.1 (http://research.microsoft.com/research/sv/msrbot/)'; //MSRBot 0.1
$robots[] = 'Mercator-2.0'; //Mercator 2.0
$robots[] = 'MetaGer-LinkChecker'; //MetaGer-LinkChecker
$robots[] = 'metacarta (crawler@metacarta.com)'; //Metacarta
$robots[] = 'metacarta crawler@metacarta.com'; //Metacarta
$robots[] = 'montastic-monitor http://www.montastic.com'; //montastic monitor, http://www.montastic.com added 4-3-2012
$robots[] = 'Mozilla/4.0 (compatible; Netcraft Web Server Survey)'; //Mozilla
$robots[] = 'Mozilla/3.01 (compatible;)'; //MysteryBot
$robots[] = 'NG/1.0'; //NG 1.0
$robots[] = 'none'; //None
$robots[] = 'NuSearch Spider www.nusearch.com'; //NuSearch Spider
$robots[] = 'CreativeCommons/0.06-dev (Nutch; http://www.nutch.org/docs/en/bot.html; nutch-agent@lists.sourceforge.net)'; //NutchBot 0.06
$robots[] = 'Robot: NutchCrawler, Owner: wdavies@acm.org'; //NutchCrawler
$robots[] = 'Mozilla/4.0 (compatible; MSIE 5.5; Windows NT 4.0; obot)'; //Obot
$robots[] = 'oBot'; //Obot
$robots[] = 'OrangeBot'; //OrangeBot
$robots[] = 'PEERbot www.peerbot.com'; //PEERbot
$robots[] = 'PWS.Kiosk - Content Filtering'; //PWS.Kiosk
$robots[] = 'parabot (paracite@ecs.soton.ac.uk)'; //Parabot
$robots[] = 'Patwebbot (http://www.herz-power.de/technik.html)'; //Patwebbot
$robots[] = 'http://www.planethosting.com'; //Planethosting
$robots[] = 'Portal Manager 0.7'; //Portal Manager 0.7
$robots[] = 'potbot 1.0'; //Potbot 1.0
$robots[] = 'ProWebGuide Link Checker (http://www.prowebguide.com)'; //ProWebGuide Link Checker
$robots[] = 'Program Shareware 1.0.3'; //Program Shareware 1.0.3
$robots[] = 'QPCreep Test Rig ( We are not indexing, just testing )'; //QPCreep Test Rig
$robots[] = 'RPT-HTTPClient/0.3-3'; //RPT-HTTPClient 0.3
$robots[] = 'reifier.org (admin@reifier.org)'; //Reifier
$robots[] = 'reifier.org admin@reifier.org'; //Reifier
$robots[] = 'rico/0.1'; //Rico 0.1
$robots[] = 'RixBot (http://www.oops-as.no/rix/)'; //RixBot
$robots[] = 'RoboPal (http://www.findpal.com/)'; //RoboPal
$robots[] = 'Search Engine World Robots.txt Validator at http://www.searchengineworld.com/cgi-bin/robotcheck.cgi'; //Robots.txt Validator
$robots[] = 'Robozilla/1.0'; //Robozilla 1.0
$robots[] = 'Mozilla/5.0 (compatible; SYCLIKControl/LinkChecker;)'; //SYCLIKControl LinkChecker
$robots[] = 'Search Agent 1.0'; //Search Agent 1.0
$robots[] = 'Sensis.com.au Web Crawler (search_comments\at\sensis\dot\com\dot\au)'; //Sensis.au Web Crawler
$robots[] = 'sherlock/1.3 httpget/1.3'; //Sherlock 1.3
$robots[] = 'SiteXpert'; //SiteXpert
$robots[] = 'InternetSeer.com'; //Sitecheck
$robots[] = 'sitecheck.internetseer.com (For more info see: http://sitecheck.internetseer.com)'; //Sitecheck
$robots[] = 'sohu-search'; //Sohu-search
$robots[] = 'Speedy Spider (http://www.entireweb.com)'; //Speedy Spider
$robots[] = 'Mozilla/4.0 (compatible; SpeedySpider; www.entireweb.com)'; //SpeedySpider
$robots[] = 'Speedy_Spider_(http://www.entireweb.com)'; //Speedy_Spider
$robots[] = 'Star Downloader'; //Star Downloader
$robots[] = 'Tarantula Experimental Crawler'; //Tarantula
$robots[] = 'updated/0.1beta (updated.com; http://www.updated.com; crawler@updated.om)'; //Updated 0.1
$robots[] = 'VSE/1.0 (vsecrawler@hotmail.com)'; //VSE 1.0
$robots[] = 'vspider'; //Vspider
$robots[] = 'WWWeasel Robot v1.00 (http://wwweasel.de)'; //WWWeasel 1.00
$robots[] = 'WebFilter Robot 1.0'; //WebFilter Robot 1.0
$robots[] = 'WebRACE/1.1 (University of Cyprus, Distributed Crawler)'; //WebRACE 1.1
$robots[] = 'WebSauger 1.20b'; //WebSauger 1.20
$robots[] = 'http://www.websearch.com.au (larbin2.6.2@unspecified.mail)'; //WebSearch
$robots[] = 'http://www.websearch.com.au larbin2.6.2@unspecified.mail'; //WebSearch
$robots[] = 'webbot'; //Webbot
$robots[] = 'Webclipping.com'; //Webclipping
$robots[] = 'www.webwombat.com.au'; //Webwombat
$robots[] = 'webyield robot (http://www.webyield.net/search/search.pl)'; //Webyield robot
$robots[] = 'Willow Internet Crawler by Twotrees V2.1'; //Willow 2.1
$robots[] = 'http://www.ciml.co.uk'; //Www.ciml.co.uk
$robots[] = 'Zao-Crawler'; //Zao-Crawler
$robots[] = 'Zeus 3140 Webster Pro V2.9 Win32'; //Zeus 3140
$robots[] = 'Zeus 57657 Webster Pro V2.9 Win32'; //Zeus 57657
$robots[] = 'AnsearchBot'; //unknown
$robots[] = 'AnyBrowser.com Search Engine'; //unknown
$robots[] = 'LeechGet 2002 (www.leechget.de)'; //unknown
$robots[] = 'LeechGet 2004 (www.leechget.net)'; //unknown
$robots[] = 'NationalDirectory-WebSpider/1.3'; //unknown
$robots[] = 'arianna.libero.it Linux/2.4.9-34smp (linux)'; //unknown
$robots[] = 'facebookexternalhit/1.1 (+http://www.facebook.com/externalhit_uatext.php)'; //Facebook page scraper
$robots[] = 'Mozilla/5.0 (compatible; Alexabot/1.0; +http://www.alexa.com/help/certifyscan; certifyscan@alexa.com)'; //jan 7 2014 - Alexabox Crawler


#### Partial User Agents ####

$robotP[] = 'AdsBot-Google';//Google adwords http://www.google.com/adsbot.html
$robotP[] = 'ADSAComponent';
$robotP[] = 'ASPseek';
$robotP[] = 'http://www.almaden.ibm.com/cs/crawler'; //Almaden c01
$robotP[] = 'Amfibibot'; //Amfibibot http://www.amfibi.com
$robotP[] = 'AnswerBus'; //AnswerBus http://www.answerbus.com/
$robotP[] = 'antibot'; //Antibot
$robotP[] = 'appie'; //Appie www.walhello.com
$robotP[] = 'Argus/'; //Argus http://www.simpy.com/bot.html (end slash is intentional)
$robotP[] = 'Art-Online.com'; //Art-Online
$robotP[] = 'Ask Jeeves'; //Ask Jeeves
$robotP[] = 'BDNcentral Crawler'; //BDNcentral Crawler
$robotP[] = 'BaiDuSpider'; //BaiDuSpider
$robotP[] = 'Baiduspider'; //BaiDuSpider (variation in case)
$robotP[] = 'bingbot'; //Bingbot http://www.bing.com/bingbot.htm
$robotP[] = 'BlogBot'; //BlogBot
$robotP[] = 'boitho.com-dc'; //Boitho-dc
$robotP[] = 'boitho.com-robot'; //Boitho-robot
$robotP[] = 'BrailleBot'; //BrailleBot
$robotP[] = 'BruinBot'; //BruinBot http://webarchive.cs.ucla.edu/bruinbot.html
$robotP[] = 'Computer_and_Automation_Research_Institute_Crawler'; //CARI Crawler
$robotP[] = 'Cerberian Drtrs'; //Cerberian Drtrs
$robotP[] = 'CerberianDrtrs'; //Cerberian Drtrs variation
$robotP[] = 'Clushbot'; //Clushbot
$robotP[] = 'ComMOOnity LambdaMOO'; //ComMOOnity LambdaMOO 1.8.1
$robotP[] = 'CrawlConvera'; //ConveraCrawler
$robotP[] = 'ConveraCrawler'; //ConveraCrawler variation
$robotP[] = 'Cowbot-'; //Cowbot
$robotP[] = 'CrocCrawler '; //CrocCrawler
$robotP[] = 'CydralSpider'; //CydralSpider
$robotP[] = 'DeMozulator '; //DeMozulator
$robotP[] = 'DoCoMo/'; //DoCoMo
$robotP[] = 'EBSCO News Feed Crawler'; //EBSCO News Feed Crawler - ebsco.com - added 4-3-2012
$robotP[] = 'Enterprise_Search'; //Enterprise_Search
$robotP[] = 'exactseek-crawler-'; //Exactseek-crawler
$robotP[] = 'FAST Enterprise Crawler/'; //FAST Enterprise Crawler
$robotP[] = 'FAST FirstPage retriever'; //FAST FirstPage retriever
$robotP[] = 'FAST-WebCrawler/'; //FAST-WebCrawler
$robotP[] = 'Feedfetcher-Google';//google feed fetcher http://www.google.com/feedfetcher.html
$robotP[] = 'Filangy/'; //Filangy
$robotP[] = 'FindLinks/'; //FindLinks
$robotP[] = 'findlinks/'; //Findlinks (case variation)
$robotP[] = 'Flickbot '; //FlickBot
$robotP[] = 'FlickBot '; //FlickBot (variation on case)
$robotP[] = 'GAIS Robot/'; //GAIS Robot 1.1A2
$robotP[] = 'Gaisbot/'; //Gaisbot 3.0
$robotP[] = 'GalaxyBot/'; //GalaxyBot 1.0
$robotP[] = 'GeonaBot'; //GeonaBot
$robotP[] = 'Googlebot'; //Googlebot
$robotP[] = 'Google Web Preview'; //Google web preview bot - added 4-3-2012
$robotP[] = 'Google Wireless Transcoder'; //Google Wireless Transcoder http://google.com/gwt/n - not really a bot, it converts web pages to something easy to read on old pre-android small-screen phones - added 4-3-2012
$robotP[] = 'grub-client'; //Grub-client
$robotP[] = 'Gulper Web Bot '; //GulperBot
$robotP[] = 'Harvest-NG/'; //Harvest-NG
$robotP[] = 'Hatena Antenna/'; //Hatena Antenna
$robotP[] = 'Hitwise Spider'; //Hitwise Spider
$robotP[] = 'htdig/'; //Htdig
$robotP[] = 'Html Link Validator'; //Html Link Validator
$robotP[] = 'Httpcheck/'; //Httpcheck
$robotP[] = 'httpget-'; //Httpget
$robotP[] = 'IRLbot/'; //IRLbot
$robotP[] = 'IconSurf/'; //IconSurf
$robotP[] = 'IlTrovatore-Setaccio'; //IlTrovatore-Setaccio
$robotP[] = 'Iltrovatore-Setaccio'; //Iltrovatore-Setaccio (case variation)
$robotP[] = 'imagefetch/'; //Imagefetch 0.1
$robotP[] = 'InelaBot/'; //InelaBot 0.2
$robotP[] = 'InfoSeek Sidewinder/'; //InfoSeek Sidewinder
$robotP[] = 'Infoseek SideWinder/'; //Infoseek SideWinder (case variation)
$robotP[] = 'Slurp/'; //Slurp (Yahoo and other avariants)
$robotP[] = 'InternetLinkAgent/'; //InternetLinkAgent 3.1
$robotP[] = 'Knowledge.com/'; //Knowledge
$robotP[] = 'kuloko-bot/'; //Kuloko-bot 0.2
//NOTE:  A lot of variations on Larbin...  Not "shortening" when version # is not
//  present (so would not result in "future versions" already accounted for),
//  only going to shorten when the shortenend is "versioned" or can get rid of more
//  than just 2-3 variations with shortened version...
$robotP[] = 'Larbin '; //Larbin
$robotP[] = 'larbin_'; //Larbin (this one is "main" one)
$robotP[] = 'larbin-'; //Larbin
$robotP[] = 'larbin@unspecified.mail'; //Larbin (this one cuts out bunch of different variations)
$robotP[] = '/ libwww/'; //Libwww-perl (earlier versions)
$robotP[] = 'libwww-perl/'; //Libwww-perl (note that this trips on w3c validators, and few other perl-based crawlers as well)
$robotP[] = 'LimeBot/'; //LimeBot
$robotP[] = 'LinkLint-checkonly/'; //LinkLint-checkonly
$robotP[] = 'Linkbot '; //Linkbot
$robotP[] = 'Lite Bot '; //Lite Bot
$robotP[] = 'LoadImpactRload';//loadimpact.com load tester
$robotP[] = 'lwp-trivial/'; //Lwp-request
$robotP[] = 'lwp-request/'; //Lwp-request
$robotP[] = 'Microsoft Data Access Internet Publishing Provider'; //MS Data Access
$robotP[] = 'MSFrontPage/'; //MS FrontPage
$robotP[] = 'MS FrontPage '; //MS FrontPage
$robotP[] = 'MSIECrawler'; //MSIECrawler
$robotP[] = 'Mediapartners-Google'; //Mediapartners-Google
$robotP[] = 'Microsoft URL Control - '; //Microsoft URL Control
$robotP[] = 'Microsoft-ATL-Native/'; //Microsoft-ATL-Native
$robotP[] = 'MicrosoftPrototypeCrawler '; //MicrosoftPrototypeCrawler
$robotP[] = 'moget/'; //Moget
$robotP[] = 'mozDex/'; //MozDex
$robotP[] = 'MSNBOT'; //Msnbot
$robotP[] = 'msnbot'; //Msnbot
$robotP[] = 'NPBot'; //NPBot
$robotP[] = 'NaverBot'; //NaverBot
$robotP[] = 'NaverRobot'; //NaverRobot
$robotP[] = 'NetAnts/'; //NetAnts
$robotP[] = 'NetNoseCrawler'; //NetNoseCrawler
$robotP[] = 'NetNose-Crawler'; //NetNoseCrawler 2.0
$robotP[] = 'NetResearchServer'; //NetResearchServer (look.com)
$robotP[] = 'NextGenSearchBot'; //NextGenSearchBot
$robotP[] = 'NutchCVS/'; //NutchBot 0.05
$robotP[] = 'NutchOrg/'; //NutchOrg 0.03
$robotP[] = 'OWR_Crawler '; //OWR_Crawler 0.1
$robotP[] = 'Ocelli'; //Ocelli
$robotP[] = 'OmniExplorer_Bot'; //OmniExplorer_Bot 1.07
$robotP[] = 'Openbot/'; //Openbot
$robotP[] = 'OpenISearch';  //openisearch http://www.openisearch.com/faq.html
$robotP[] = 'Advanced Email Extractor '; //Organica
$robotP[] = 'Overture-WebCrawler'; //Overture-WebCrawler 3.8
$robotP[] = 'pavuk/'; //Pavuk
$robotP[] = 'PipeLine Spider'; //PipeLiner 0.3
$robotP[] = 'pingdom.com_bot'; //Pingdom Bot (http://www.pingdom.com/)
$robotP[] = 'polybot '; //Polybot
$robotP[] = 'Pompos/'; //Pompos
$robotP[] = 'proximic'; //proximic http://www.proximic.com
$robotP[] = 'psbot/'; //Psbot 0.1
$robotP[] = 'pverify/'; //Pverify 1.2
$robotP[] = 'QuepasaCreep '; //QuepasaCreep
$robotP[] = 'SafariBookmarkChecker/'; //SafariBookmarkChecker
$robotP[] = 'Scooter/'; //Scooter
$robotP[] = 'Scooter-'; //Scooter
$robotP[] = 'Scooter_'; //Scooter
$robotP[] = 'Scrubby/'; //Scrubby
$robotP[] = 'SearchSpider.com/'; //SearchSpider 1.1
$robotP[] = 'Seekbot/'; //Seekbot 1.0
$robotP[] = 'semanticdiscovery/'; //Semanticdiscovery 0.1
$robotP[] = 'sherlock_spider'; //Sherlock_spider
$robotP[] = 'SISTRIX Crawler'; //SISTRIX Crawler, http://crawler.sistrix.net/ added 4-3-2012
$robotP[] = 'SlySearch'; //SlySearch
$robotP[] = 'SpiderKU/'; //SpiderKU
$robotP[] = 'SpiderMonkey/'; //SpiderMonkey
$robotP[] = 'SpurlBot/)'; //SpurlBot
$robotP[] = 'Sqworm/'; //Sqworm 2.9.85
$robotP[] = 'Steeler/'; //Steeler
$robotP[] = 'SuperCleaner '; //SuperCleaner
$robotP[] = 'SWEBot/'; //SWE Bot : http://swebot.net - added 4-3-2012
$robotP[] = 'Tcl http client package'; //TclSOAP 1.0
$robotP[] = 'thesubot'; //TheSuBot
$robotP[] = 'thumbshots-de-Bot'; //Thumbshots-de-Bot
$robotP[] = 'TulipChain/'; //TulipChain
$robotP[] = 'TurnitinBot'; //TurnitinBot https://www.turnitin.com/robot/crawlerinfo.html
$robotP[] = 'TutorGigBot/'; //TutorGigBot
$robotP[] = 'Tutorial Crawler'; //Tutorial Crawler http://www.tutorgig.com/crawler
$robotP[] = 'TwitterFeed';//twitter feed?
$robotP[] = 'UIowaCrawler'; //UIowaCrawler
$robotP[] = 'UdmSearch/'; //UdmSearch
$robotP[] = 'unchaos_crawler_'; //Unchaos_crawler
$robotP[] = 'Vagabondo/'; //Vagabondo
$robotP[] = 'void-bot/'; //Void-bot
$robotP[] = 'VoilaBot'; //VoilaBot
$robotP[] = 'WebSearch'; //WebSearch
$robotP[] = 'webcollage/'; //Webcollage
$robotP[] = 'WebcraftBoot'; //WebcraftBoot
$robotP[] = 'Webinator-indexer'; //Webinator-indexer
$robotP[] = 'Wget/'; //Wget 1.5.2
$robotP[] = 'Windows-RSS-Platform'; //Windows RSS Platform - http://en.wikipedia.org/wiki/Windows_RSS_Platform - added 4-3-2012
$robotP[] = 'wotbox.com'; //Wotbox
$robotP[] = 'Xenu\'s Link Sleuth'; //Xenu Link Sleuth
$robotP[] = 'Xenu Link Sleuth'; //Xenu Link Sleuth
$robotP[] = 'Yahoo! Slurp'; //Yahoo! Slurp
$robotP[] = 'Yahoo-MMCrawler'; //Yahoo-MMCrawler
$robotP[] = 'YandexBot'; //Yandex Bot
$robotP[] = 'YandexImages'; //Yandex Images
$robotP[] = 'YodaoBot/'; //YodaoBot http://www.yodao.com/help/webmaster/spider/ - added 4-3-2012
$robotP[] = 'ZipppBot'; //ZipppBot
$robotP[] = 'ZoomSpider'; //ZoomSpider
