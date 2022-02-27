CubicleSoft PHP Libraries
=========================

A single repository containing all CubicleSoft PHP libraries.  Fully automated nightly updates.  MIT or LGPL, your choice.

[![Donate](https://cubiclesoft.com/res/donate-shield.png)](https://cubiclesoft.com/donate/) [![Discord](https://img.shields.io/discord/777282089980526602?label=chat&logo=discord)](https://cubiclesoft.com/product-support/github/)

Classes
-------

Included and supported:

* AppleICNS - Create and parse Apple icon (.icns) files.  [Documentation](https://github.com/cubiclesoft/php-misc/blob/master/docs/apple_icns.md)  (support/apple_icns.php)
* ArrayUtils - Implements missing functions for associative arrays.  [Documentation](https://github.com/cubiclesoft/php-misc/blob/master/docs/array_utils.md)  (support/array_utils.php)
* CalendarEvent - Powerful scheduling class.  Feed in a cron line, get back the next timestamp of when something should trigger.  [Documentation](https://github.com/cubiclesoft/php-misc/blob/master/docs/calendar_event.md)  (support/calendar_event.php)
* CLI - Static functions in a class to extract command-line options, parse user input on the command-line, and log messages to the console.  Do you really need a separate logging library?  I don't.  [Documentation](https://github.com/cubiclesoft/php-misc/blob/master/docs/cli.md)  (support/cli.php)
* ColorTools - Static functions in a class to convert RGB to other color spaces and calculate readable foreground text colors for any background color.  [Documentation](https://github.com/cubiclesoft/php-misc/blob/master/docs/color_tools.md)  (support/color_tools.php)
* CRC32Stream - Calculates CRC32 checksums in a streaming format.  [Documentation](https://github.com/cubiclesoft/ultimate-web-scraper/blob/master/docs/crc32_stream.md)  (support/crc32_stream.php)
* CSDB - Cross-database, cross-platform, lightweight base class for interfacing with databases.  Designed specifically for developing open source applications where the target database is unknown.  Write each SQL query one time and have all queries automagically work for all supported databases.  Complete with all the standard features you expect in a SQL injection free database class.  Uses PDO by default.  [Documentation](https://github.com/cubiclesoft/csdb)  (support/db.php)
* CSDB_mysql - Full MySQL/Maria DB interface.  (support/db_mysql.php)
* CSDB_mysql_lite - Lightweight MySQL/Maria DB interface.  (support/db_mysql_lite.php)
* CSDB_oci - Full Oracle DB interface (beta).  (support/db_oci.php)
* CSDB_oci_lite - Lightweight Oracle DB interface (beta).  (support/db_oci_lite.php)
* CSDB_pgsql - Full PostgreSQL interface.  (support/db_pgsql.php)
* CSDB_pgsql_lite - Lightweight PostgreSQL interface.  (support/db_pgsql_lite.php)
* CSDB_sqlite - Full SQLite interface.  (support/db_sqlite.php)
* CSDB_sqlite_lite - Lightweight SQLite interface.  (support/db_sqlite_lite.php)
* CSPRNG - Cross-platform Cryptographically Secure Random Number Generator (CSPRNG).  Unlike nearly all of the classes out there that claim to implement a CSPRNG, this one actually does things correctly because I scoured the actual PHP C source code and spent the necessary time figuring out which calls called the system-level CSPRNG for each major platform.  This class also doesn't wimp out and fallback to some hocus-pocus, non-random, weak sauce solution - it throws an Exception which you intentionally and correctly do not ever catch.  [Documentation](https://github.com/cubiclesoft/csprng/blob/master/docs/random.md)  (support/random.php)
* DeflateStream - Compresses/Uncompresses deflate data (including gzip) in a streaming format without intermediate files.  [Documentation](https://github.com/cubiclesoft/ultimate-web-scraper/blob/master/docs/deflate_stream.md)  (support/deflate_stream.php)
* DigitalOcean - A complete SDK for interacting with all DigitalOcean APIs.  (support/sdk_digitalocean.php)
* DirHelper - Static functions in a class for simplifying common file system tasks regarding directories, including recursive copy, delete, and permissions changes when building installers, live demos, and testing tools.  [Documentation](https://github.com/cubiclesoft/php-misc/blob/master/docs/dir_helper.md)  (support/dir_helper.php)
* DiscordSDK - An ultra-lightweight SDK for interacting with Discord APIs and Discord webhooks.  [Documentation](https://github.com/cubiclesoft/php-discord-sdk/blob/master/docs/sdk_discord.md)  (support/sdk_discord.php)
* DOHWebBrowser - A drop-in class for performing DNS over HTTPS when using the WebBrowser class.  Not a Homer Simpson reference.  [Documentation](https://github.com/cubiclesoft/ultimate-web-scraper/blob/master/docs/doh_web_browser.md)  (support/doh_web_browser.php)
* EFSS - Creates hierarchical, encrypted, compressed data stores.  Encrypted File Storage System is a real, virtual, mountable block-based file system for PHP.  [Documentation](https://github.com/cubiclesoft/efss)  (support/efss.php)
* EmailBuilder - Powerful class for constructing fancy HTML emails using arrays without having to worry about HTML tables.  [Documentation](https://github.com/cubiclesoft/ultimate-email/blob/master/docs/email_builder.md)  (support/email_builder.php)
* EventManager - Register to listen for events whenever the application fires them.  Can be the basis of a plugin/module system.  [Documentation](https://github.com/cubiclesoft/php-misc/blob/master/docs/event_manager.md)  (support/event_manager.php)
* FastCGI - Implements a FactCGI client.  [Documentation](https://github.com/cubiclesoft/php-misc/blob/master/docs/fastcgi.md)  (support/fastcgi.php)
* FlexForms - Powerful class for building/generating HTML forms.  Highly extensible with XSRF and anti-bot defenses.  [Documentation](https://github.com/cubiclesoft/php-flexforms/blob/master/docs/flex_forms.md)  (support/flex_forms.php)
* GenericServer - Implements a generic TCP/IP server class.  Can be used for creating custom protocols.  [Documentation](https://github.com/cubiclesoft/php-misc/blob/master/docs/generic_server.md)  (support/generic_server.php)
* LibEvGenericServer - The PECL ev integrated version of GenericServer for writing scalable servers.  [Documentation](https://github.com/cubiclesoft/php-misc/blob/master/docs/generic_server_libev.md)  (support/generic_server_libev.php)
* HTTP - Baseline static functions in a class for performing all things HTTP.  Has powerful URL parsing tools (e.g. relative to absolute URL conversion).  Used primarily by WebBrowser.  Asynchronous capable.  [Documentation](https://github.com/cubiclesoft/ultimate-web-scraper/blob/master/docs/http.md)  (support/http.php)
* IPAddr - Static functions in a class for processing IPv4 and IPv6 addresses into a uniform format.  [Documentation](https://github.com/cubiclesoft/php-misc/blob/master/docs/ipaddr.md)  (support/ipaddr.php)
* LineDiff - Static functions in a class for generating line-by-line diffs.  [Documentation](https://github.com/cubiclesoft/php-misc/blob/master/docs/line_diff.md)  (support/line_diff.php)
* MIMEParser - Parses MIME content of all forms.  Intended primarily for use with POP3.  [Documentation](https://github.com/cubiclesoft/ultimate-email/blob/master/docs/mime_parser.md)  (support/mime_parser.php)
* MultiAsyncHelper - Not for the feint of heart.  This class simplifies management of mixing multiple non-blocking objects.  See the Ultimate Web Scraper toolkit test suite for example usage.  [Documentation](https://github.com/cubiclesoft/ultimate-web-scraper/blob/master/docs/multi_async_helper.md)  (support/multi_async_helper.php)
* NaturalLanguage - Static functions in a class for dynamically generating content based on data inputs and rulesets via PHP arrays.  [Documentation](https://github.com/cubiclesoft/php-misc/blob/master/docs/natural_language.md)  (support/natural_language.php)
* PHPMinifier - Static functions in a class for minifying PHP code while still generally maintaining readability.  [Documentation](https://github.com/cubiclesoft/php-misc/blob/master/docs/php_minifier.md)  (support/php_minifier.php)
* POP3 - Powerful class for accessing a POP3 server.  Pair it with MIMEParser and TagFilter for advanced e-mail handling.  [Documentation](https://github.com/cubiclesoft/ultimate-email/blob/master/docs/pop3.md)  (support/pop3.php)
* ProcessHelper - Static functions in a class for starting and terminating non-blocking processes across all platforms.  [Documentation](https://github.com/cubiclesoft/php-misc/blob/master/docs/process_helper.md)  (support/process_helper.php)
* ReadWriteLock - A very old class that implements a cross-platform, named read-write lock for very old versions of PHP.  Use the [PECL sync](http://php.net/manual/en/book.sync.php) extension instead.  [Documentation](https://github.com/cubiclesoft/efss/blob/master/docs/read_write_lock.md)  (support/read_write_lock.php)
* Request - Static functions in a class for doing basic, common, but missing request initialization handling.  Common initialization routines for CubicleSoft applications.  [Documentation](https://github.com/cubiclesoft/php-misc/blob/master/docs/request.md)  (support/request.php)
* SerialNumber - Generates and validates encrypted 16 character serial numbers.  The basis of [CubicleSoft License Server](https://github.com/cubiclesoft/php-license-server).  [Documentation](https://github.com/cubiclesoft/php-misc/blob/master/docs/serial_number.md)  (support/serial_number.php)
* SMTP - Powerful set of static functions in a class for sending e-mail.  Full RFC support.  [Documentation](https://github.com/cubiclesoft/ultimate-email/blob/master/docs/smtp.md)  (support/smtp.php)
* Str - Static functions in a class for doing basic, common, but missing string manipulation.  Common initialization routines for CubicleSoft applications.  Some minor carryover from extremely old C++ libraries.  [Documentation](https://github.com/cubiclesoft/php-misc/blob/master/docs/str_basics.md)  (support/str_basics.php)
* StringBitStream - Parse data stored in a bit stream such as Flash (SWF) files.  [Documentation](https://github.com/cubiclesoft/php-misc/blob/master/docs/bits.md)  (support/bits.php)
* TagFilter - The world's most powerful tag filtering PHP class.  It can clean up the worst HTML (e.g. Word HTML) in a single pass or extract data (or both).  As a direct result, it is blistering fast.  Many, many times faster and smaller than everything else I've used.  [Documentation](https://github.com/cubiclesoft/ultimate-web-scraper/blob/master/docs/tag_filter.md)  (support/tag_filter.php)
* TagFilterNodes - Output from TagFilter::Explode(), which explodes a HTML document into a flattened DOM-like structure.  From there, use CSS3 media queries to locate nodes of interest.  [Documentation](https://github.com/cubiclesoft/ultimate-web-scraper/blob/master/docs/tag_filter.md#tagfilternodes-class)  (support/tag_filter.php)
* TagFilterNode - TagFilterNodes::Get() returns a TagFilterNode object which provides easy but slower object-oriented access to TagFilterNodes.  [Documentation](https://github.com/cubiclesoft/ultimate-web-scraper/blob/master/docs/tag_filter.md#tagfilternode-class)  (support/tag_filter.php)
* TagFilterNodeIterator - Allows iteration over CSS3 query results from TagFilterNode::Find() using foreach.  Also has a Filter() function to further reduce results using additional CSS3 selectors.  [Documentation](https://github.com/cubiclesoft/ultimate-web-scraper/blob/master/docs/tag_filter.md#tagfilternodeiterator-class)  (support/tag_filter.php)
* TagFilterStream - Used by TagFilter but can be used separately for processing large HTML files in smaller chunks (aka a stream).  [Documentation](https://github.com/cubiclesoft/ultimate-web-scraper/blob/master/docs/tag_filter.md#tagfilterstream-class)  (support/tag_filter.php)
* TwilioSDK - An ultra-lightweight SDK for interacting with Twilio APIs.  [Documentation](https://github.com/cubiclesoft/php-twilio-sdk/blob/master/docs/sdk_twilio.md)  (support/sdk_twilio.php)
* UTF8 - Flexible UTF-8 string manipulation static functions in a class.  CubicleSoft was doing Unicode and UTF-8 long before Unicode and UTF-8 were cool.  [Documentation](https://github.com/cubiclesoft/php-misc/blob/master/docs/utf8.md)  (support/utf8.php)
* UTFUtils - Convert between various Unicode Transformation Formats (UTF-8, UTF-16, UTF-32) and a Punycode implementation.  [Documentation](https://github.com/cubiclesoft/php-misc/blob/master/docs/utf_utils.md)  (support/utf_utils.php)
* WebBrowser - Probably the most powerful state tracking system in pure PHP for web scraping.  Virtually indistiguishable from a real web browser and therefore extremely difficult to detect.  Has HTML form extraction and command-line shell interface capabilities.  I occasionally dream of adding a Javascript parsing engine to it.  Superior in every way to Guzzle when it comes to web scraping (Guzzle is an API consumer, not a web scraper - different domains).  [Documentation](https://github.com/cubiclesoft/ultimate-web-scraper/blob/master/docs/web_browser.md)  (support/web_browser.php)
* WebBrowserForm - Represents and manipulates a single extracted HTML form from a page.  Generates WebBrowser-compliant output for feeding back into WebBrowser for another request.  [Documentation](https://github.com/cubiclesoft/ultimate-web-scraper/blob/master/docs/web_browser.md#webbrowserform-class)  (support/web_browser.php)
* WebMutex - A very old class that implements a cross-platform, named mutex for very old versions of PHP.  Use the [PECL sync](http://php.net/manual/en/book.sync.php) extension instead.  [Documentation](https://github.com/cubiclesoft/efss/blob/master/docs/web_mutex.md)  (support/web_mutex.php)
* WebServer - It is what it says on the tin.  It's a web server.  In pure PHP.  Abuses the HTTP class to implement a rather scary and complete feature set that probably rivals Apache.  Isn't going to win any performance awards.  Is probably susceptible to DoS attacks due to multiple bugs in PHP.  Used by Cloud Storage Server and PHP App Server.  [Documentation](https://github.com/cubiclesoft/ultimate-web-scraper/blob/master/docs/web_server.md)  (support/web_server.php)
* WebSocket - Implements a WebSocket client.  Probably not as robust as it could be on the async front but, unlike most WebSocket classes, this one allows the application to ignore those pesky control packets.  [Docuemtation](https://github.com/cubiclesoft/ultimate-web-scraper/blob/master/docs/websocket.md)  (support/websocket.php)
* WebSocketServer - Move over Node.js and whatever else is out there.  A new WebSocket server is in town.  With limits on packet size because, well, WebSocket vulnerabilities.  Actually, you probably shouldn't use this in production environments either.  WebSocket is kind of a broken protocol.  The server exists to test the client because there aren't that many open servers out there.  [Documentation](https://github.com/cubiclesoft/ultimate-web-scraper/blob/master/docs/websocket_server.md)  (support/websocket_server.php)
* LibEvWebSocketServer - The PECL ev integrated version of WebSocketServer for writing scalable WebSocket servers.  [Documentation](https://github.com/cubiclesoft/ultimate-web-scraper/blob/master/docs/websocket_server_libev.md)  (support/websocket_server_libev.php)
* WinICO - Create and parse Windows icon (.ico) and cursor (.cur) files.  [Documentation](https://github.com/cubiclesoft/php-winpefile/blob/master/docs/win_ico.md)  (support/win_ico.php)
* WinPEFile - Extract information, modify, and create Windows Portable Executable files.  [Documentation](https://github.com/cubiclesoft/php-winpefile/blob/master/docs/win_pe_file.md)  (support/win_pe_file.php)
* WinPEUtils - Advanced data extraction and manipulation of Windows Portable Executable files.  [Documentation](https://github.com/cubiclesoft/php-winpefile/blob/master/docs/win_pe_utils.md)  (support/win_pe_utils.php)
* XTerm - Static functions in a class for emitting XTerm-compatible escape codes to alter terminal behavior.  Mostly for changing font styles and colors but also supports most escape codes with easier to comprehend functions.  Many features also work with the Command Prompt in Windows 10 and later.  [Documentation](https://github.com/cubiclesoft/php-misc/blob/master/docs/xterm.md)  (support/xterm.php)
* ZipStreamWriter - Generates ZIP files on the fly.  Stream content of any size to users.  [Documentation](https://github.com/cubiclesoft/php-zipstreamwriter/blob/master/docs/zipstreamwriter.md)  (support/zip_stream_writer.php)
* simple_html_dom - Not actually a CubicleSoft class.  Accidental inclusion from Ultimate Web Scraper Toolkit.  \[Shrugs\]  Obsoleted by TagFilter.  (support/simple_html_dom.php)

Other:

* CSDB_PDO_Statement - Internal or undocumented class.  (support/db.php)
* EFSS_FirstBlock - Internal or undocumented class.  (support/efss.php)
* EFSS_DirEntry_DirFile - Internal or undocumented class.  (support/efss.php)
* EFSS_DirEntries - Internal or undocumented class.  (support/efss.php)
* EFSS_File - Internal or undocumented class.  (support/efss.php)
* EFSS_Unused - Internal or undocumented class.  (support/efss.php)
* EFSS_DirCopyHelper - Internal or undocumented class.  (support/efss.php)
* EFSS_SymlinkCopyHelper - Internal or undocumented class.  (support/efss.php)
* EFSS_FileCopyHelper - Internal or undocumented class.  (support/efss.php)
* EFSSIncremental - Internal or undocumented class.  (support/efss.php)
* Crypt_AES - Internal or undocumented class.  (support/AES.php)
* Crypt_Base - Internal or undocumented class.  (support/Base.php)
* Crypt_Rijndael - Internal or undocumented class.  (support/Rijndael.php)
* CalendarEvent_TZSwitch - Internal or undocumented class.  (support/calendar_event.php)
* simple_html_dom_node - Internal or undocumented class.  (support/simple_html_dom.php)
* WebServer_TempFile - Internal or undocumented class.  (support/web_server.php)
* WebServer_Client - Internal or undocumented class.  (support/web_server.php)

Sources
-------

* https://github.com/cubiclesoft/csdb
* https://github.com/cubiclesoft/digitalocean
* https://github.com/cubiclesoft/efss
* https://github.com/cubiclesoft/php-csprng
* https://github.com/cubiclesoft/php-discord-sdk
* https://github.com/cubiclesoft/php-flexforms
* https://github.com/cubiclesoft/php-misc
* https://github.com/cubiclesoft/php-twilio-sdk
* https://github.com/cubiclesoft/php-winpefile
* https://github.com/cubiclesoft/php-zipstreamwriter
* https://github.com/cubiclesoft/ultimate-email
* https://github.com/cubiclesoft/ultimate-web-scraper
