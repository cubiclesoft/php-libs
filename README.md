CubicleSoft PHP Libraries
=========================

A single repository containing all CubicleSoft PHP libraries.  Fully automated nightly updates.  MIT or LGPL, your choice.

Classes
-------

Included and supported:

* CalendarEvent - Powerful scheduling class.  Feed in a cron line, get back the next timestamp of when something should trigger.  (support/calendar_event.php)
* CLI - Static functions in a class to extract command-line options, parse user input on the command-line, and log messages to the console.  Do you really need a separate logging library?  I don't.  (support/cli.php)
* CRC32Stream - Calculates CRC32 checksums in a streaming format.  [Documentation](https://github.com/cubiclesoft/ultimate-web-scraper/blob/master/docs/crc32_stream.md)  (support/crc32_stream.php)
* CSDB - Cross-database, cross-platform, lightweight base class for interfacing with databases.  Designed specifically for developing open source applications where the target database is unknown.  Write each SQL query one time and have all queries automagically work for all supported databases.  Complete with all the standard features you expect in a SQL injection free database class.  Uses PDO by default.  (support/db.php)
* CSDB_mysql - Full MySQL/Maria DB interface.  (support/db_mysql.php)
* CSDB_mysql_lite - Lightweight MySQL/Maria DB interface.  (support/db_mysql_lite.php)
* CSDB_oci - Full Oracle DB interface (needs work).  (support/db_oci.php)
* CSDB_oci_lite - Lightweight Oracle DB interface (needs work).  (support/db_oci_lite.php)
* CSDB_pgsql - Full PostgreSQL interface.  (support/db_pgsql.php)
* CSDB_pgsql_lite - Lightweight PostgreSQL interface.  (support/db_pgsql_lite.php)
* CSDB_sqlite - Full SQLite interface.  (support/db_sqlite.php)
* CSDB_sqlite_lite - Lightweight SQLite interface.  (support/db_sqlite_lite.php)
* CSPRNG - Cross-platform Cryptographically Secure Random Number Generator (CSPRNG).  Unlike nearly all of the classes out there that claim to implement a CSPRNG, this one actually does things correctly because I scoured the actual PHP C source code and spent the necessary time figuring out which calls called the system-level CSPRNG for each major platform.  This class also doesn't wimp out and fallback to some hocus-pocus, non-random, weak sauce solution - it throws an Exception which you intentionally and correctly do not ever catch.  (support/random.php)
* DeflateStream - Compresses/Uncompresses deflate data (including gzip) in a streaming format without intermediate files.  [Documentation](https://github.com/cubiclesoft/ultimate-web-scraper/blob/master/docs/deflate_stream.md)  (support/deflate_stream.php)
* DigitalOcean - A complete SDK for interacting with all DigitalOcean APIs.  (support/sdk_digitalocean.php)
* EventManager - Register to listen for events whenever the application fires them.  Can be the basis of a plugin/module system.  (support/event_manager.php)
* FlexForms - Powerful class for building/generating HTML forms.  Highly extensible with XSRF and anti-bot defenses.  [Documentation](https://github.com/cubiclesoft/php-flexforms/blob/master/docs/flex_forms.md)  (support/flex_forms.php)
* HTTP - Baseline static functions in a class for performing all things HTTP.  Has powerful URL parsing tools (e.g. relative to absolute URL conversion).  Used primarily by WebBrowser.  Asynchronous capable.  [Documentation](https://github.com/cubiclesoft/ultimate-web-scraper/blob/master/docs/http.md)  (support/http.php)
* IPAddr - Static functions in a class for processing IPv4 and IPv6 addresses into a uniform format.  (support/ipaddr.php)
* MIMEParser - Parses MIME content of all forms.  Intended primarily for use with POP3.  (support/mime_parser.php)
* MultiAsyncHelper - Not for the feint of heart.  This class simplifies management of mixing multiple non-blocking objects.  See the Ultimate Web Scraper toolkit test suite for example usage.  [Documentation](https://github.com/cubiclesoft/ultimate-web-scraper/blob/master/docs/multi_async_helper.md)  (support/multi_async_helper.php)
* POP3 - Powerful class for accessing a POP3 server.  Pair it with MIMEParser and TagFilter for advanced e-mail handling.  (support/pop3.php)
* SMTP - Powerful set of static functions in a class for sending e-mail.  Full RFC support.  (support/smtp.php)
* Str - Static functions in a class for doing basic, common, but missing string manipulation.  Common initialization routines for CubicleSoft applications.  Some minor carryover from extremely old C++ libraries.  (support/str_basics.php)
* TagFilter - The world's most powerful tag filtering PHP class.  It can clean up the worst HTML (e.g. Word HTML) in a single pass or extract data (or both).  As a direct result, it is blistering fast.  Many, many times faster and smaller than everything else I've used.  [Documentation](https://github.com/cubiclesoft/ultimate-web-scraper/blob/master/docs/tag_filter.md)  (support/tag_filter.php)
* TagFilterNodes - Output from TagFilter::Explode(), which explodes a HTML document into a flattened DOM-like structure.  From there, use CSS3 media queries to locate nodes of interest.  [Documentation](https://github.com/cubiclesoft/ultimate-web-scraper/blob/master/docs/tag_filter.md#tagfilternodes-class)  (support/tag_filter.php)
* TagFilterNode - TagFilterNodes::Get() returns a TagFilterNode object which provides easy but slower object-oriented access to TagFilterNodes.  [Documentation](https://github.com/cubiclesoft/ultimate-web-scraper/blob/master/docs/tag_filter.md#tagfilternode-class)  (support/tag_filter.php)
* TagFilterNodeIterator - Allows iteration over CSS3 query results from TagFilterNode::Find() using foreach.  Also has a Filter() function to further reduce results using additional CSS3 selectors.  [Documentation](https://github.com/cubiclesoft/ultimate-web-scraper/blob/master/docs/tag_filter.md#tagfilternodeiterator-class)  (support/tag_filter.php)
* TagFilterStream - Used by TagFilter but can be used separately for processing large HTML files in smaller chunks (aka a stream).  [Documentation](https://github.com/cubiclesoft/ultimate-web-scraper/blob/master/docs/tag_filter.md#tagfilterstream-class)  (support/tag_filter.php)
* UTF8 - Flexible UTF-8 string manipulation static functions in a class.  CubicleSoft was doing Unicode and UTF-8 long before Unicode and UTF-8 were cool.  (support/utf8.php)
* WebBrowser - Probably the most powerful state tracking system in pure PHP for web scraping.  Virtually indistiguishable from a real web browser and therefore extremely difficult to detect.  Has HTML form extraction and command-line shell interface capabilities.  I occasionally dream of adding a Javascript parsing engine to it.  Superior in every way to Guzzle when it comes to web scraping (Guzzle is an API consumer, not a web scraper - different domains).  [Documentation](https://github.com/cubiclesoft/ultimate-web-scraper/blob/master/docs/web_browser.md)  (support/web_browser.php)
* WebBrowserForm - Represents and manipulates a single extracted HTML form from a page.  Generates WebBrowser-compliant output for feeding back into WebBrowser for another request.  [Documentation](https://github.com/cubiclesoft/ultimate-web-scraper/blob/master/docs/web_browser.md#webbrowserform-class)  (support/web_browser.php)
* WebServer - It is what it says on the tin.  It's a web server.  In pure PHP.  Abuses the HTTP class to implement a rather scary and complete feature set that probably rivals Apache.  Isn't going to win any performance awards.  Is probably susceptible to DoS attacks due to bugs in PHP.  Used by Cloud Storage Server to implement a really nice API.  [Documentation](https://github.com/cubiclesoft/ultimate-web-scraper/blob/master/docs/web_server.md)  (support/web_server.php)
* WebSocket - Implements a WebSocket client.  Probably not as robust as it could be on the async front but, unlike most WebSocket classes, this one allows the application to ignore those pesky control packets.  [Docuemtation](https://github.com/cubiclesoft/ultimate-web-scraper/blob/master/docs/websocket.md)  (support/websocket.php)
* WebSocketServer - Move over Node.js and whatever else is out there.  A new WebSocket server is in town.  With limits on packet size because, well, WebSocket vulnerabilities.  Actually, you probably shouldn't use this in production environments either.  WebSocket is kind of a broken protocol.  The server exists to test the client because there aren't that many open servers out there.  [Documentation](https://github.com/cubiclesoft/ultimate-web-scraper/blob/master/docs/websocket_server.md)  (support/websocket_server.php)
* simple_html_dom - Not actually a CubicleSoft class.  Accidental inclusion from Ultimate Web Scraper Toolkit.  \[Shrugs\]  Obsoleted by TagFilter.  (support/simple_html_dom.php)

Other:

* CSDB_PDO_Statement - Internal or undocumented class.  (support/db.php)
* simple_html_dom_node - Internal or undocumented class.  (support/simple_html_dom.php)
* CalendarEvent_TZSwitch - Internal or undocumented class.  (support/calendar_event.php)
* Request - Internal or undocumented class.  (support/request.php)
* WebServer_TempFile - Internal or undocumented class.  (support/web_server.php)
* WebServer_Client - Internal or undocumented class.  (support/web_server.php)

Sources
-------

* https://github.com/cubiclesoft/csdb
* https://github.com/cubiclesoft/digitalocean
* https://github.com/cubiclesoft/php-csprng
* https://github.com/cubiclesoft/php-flexforms
* https://github.com/cubiclesoft/php-misc
* https://github.com/cubiclesoft/ultimate-email
* https://github.com/cubiclesoft/ultimate-web-scraper
