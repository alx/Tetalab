<?php
// array of bookmarks
$sexy_bookmarks_data=array(
	'sexy-scriptstyle'=>array(
		'check'=>__('Check this box to include ', 'sexybookmarks').__('Script &amp; Style', 'sexybookmarks').__(' in your bookmarking menu', 'sexybookmarks'),
		'share'=>__('Submit this to ', 'sexybookmarks').__('Script &amp; Style', 'sexybookmarks'),
		'baseUrl'=>'http://scriptandstyle.com/submit?url=PERMALINK&amp;title=TITLE',
	),
	'sexy-blinklist'=>array(
		'check'=>__('Check this box to include ', 'sexybookmarks').__('Blinklist', 'sexybookmarks').__(' in your bookmarking menu', 'sexybookmarks'),
		'share'=>__('Share this on ', 'sexybookmarks').__('Blinklist', 'sexybookmarks'),
		'baseUrl'=>'http://www.blinklist.com/index.php?Action=Blink/addblink.php&amp;Url=PERMALINK&amp;Title=TITLE',
	),
	'sexy-delicious'=>array(
		'check'=>__('Check this box to include ', 'sexybookmarks').__('Delicious', 'sexybookmarks').__(' in your bookmarking menu', 'sexybookmarks'),
		'share'=>__('Share this on ', 'sexybookmarks').__('del.icio.us', 'sexybookmarks'),
		'baseUrl'=>'http://del.icio.us/post?url=PERMALINK&amp;title=TITLE',
	),
	'sexy-digg'=>array(
		'check'=>__('Check this box to include ', 'sexybookmarks').__('Digg', 'sexybookmarks').__(' in your bookmarking menu', 'sexybookmarks'),
		'share'=>__('Digg this!', 'sexybookmarks'),
		'baseUrl'=>'http://digg.com/submit?phase=2&amp;url=PERMALINK&amp;title=TITLE',
	),
	'sexy-diigo'=>array(
		'check'=>__('Check this box to include ', 'sexybookmarks').__('Diigo', 'sexybookmarks').__(' in your bookmarking menu', 'sexybookmarks'),
		'share'=>__('Post this on ', 'sexybookmarks').__('Diigo', 'sexybookmarks'),
		'baseUrl'=>'http://www.diigo.com/post?url=PERMALINK&amp;title=TITLE&amp;desc=SEXY_TEASER',
	),
	'sexy-reddit'=>array(
		'check'=>__('Check this box to include ', 'sexybookmarks').__('Reddit', 'sexybookmarks').__(' in your bookmarking menu', 'sexybookmarks'),
		'share'=>__('Share this on ', 'sexybookmarks').__('Reddit', 'sexybookmarks'),
		'baseUrl'=>'http://reddit.com/submit?url=PERMALINK&amp;title=TITLE',
	),
	'sexy-yahoobuzz'=>array(
		'check'=>__('Check this box to include ', 'sexybookmarks').__('Yahoo! Buzz', 'sexybookmarks').__(' in your bookmarking menu', 'sexybookmarks'),
		'share'=>__('Buzz up!', 'sexybookmarks'),
		'baseUrl'=>'http://buzz.yahoo.com/submit/?submitUrl=PERMALINK&amp;submitHeadline=TITLE&amp;submitSummary=YAHOOTEASER&amp;submitCategory=YAHOOCATEGORY&amp;submitAssetType=YAHOOMEDIATYPE',
	),
	'sexy-stumbleupon'=>array(
		'check'=>__('Check this box to include ', 'sexybookmarks').__('Stumbleupon', 'sexybookmarks').__(' in your bookmarking menu', 'sexybookmarks'),
		'share'=>__('Stumble upon something good? Share it on StumbleUpon', 'sexybookmarks'),
		'baseUrl'=>'http://www.stumbleupon.com/submit?url=PERMALINK&amp;title=TITLE',
	),
	'sexy-technorati'=>array(
		'check'=>__('Check this box to include ', 'sexybookmarks').__('Technorati', 'sexybookmarks').__(' in your bookmarking menu', 'sexybookmarks'),
		'share'=>__('Share this on ', 'sexybookmarks').__('Technorati', 'sexybookmarks'),
		'baseUrl'=>'http://technorati.com/faves?add=PERMALINK',
	),
	'sexy-mixx'=>array(
		'check'=>__('Check this box to include ', 'sexybookmarks').__('Mixx', 'sexybookmarks').__(' in your bookmarking menu', 'sexybookmarks'),
		'share'=>__('Share this on ', 'sexybookmarks').__('Mixx', 'sexybookmarks'),
		'baseUrl'=>'http://www.mixx.com/submit?page_url=PERMALINK&amp;title=TITLE',
	),
	'sexy-myspace'=>array(
		'check'=>__('Check this box to include ', 'sexybookmarks').__('MySpace', 'sexybookmarks').__(' in your bookmarking menu', 'sexybookmarks'),
		'share'=>__('Post this to ', 'sexybookmarks').__('MySpace', 'sexybookmarks'),
		'baseUrl'=>'http://www.myspace.com/Modules/PostTo/Pages/?u=PERMALINK&amp;t=TITLE',
	),
	'sexy-designfloat'=>array(
		'check'=>__('Check this box to include ', 'sexybookmarks').__('DesignFloat', 'sexybookmarks').__(' in your bookmarking menu', 'sexybookmarks'),
		'share'=>__('Submit this to ', 'sexybookmarks').__('DesignFloat', 'sexybookmarks'),
		'baseUrl'=>'http://www.designfloat.com/submit.php?url=PERMALINK&amp;title=TITLE',
	),
	'sexy-facebook'=>array(
		'check'=>__('Check this box to include ', 'sexybookmarks').__('Facebook', 'sexybookmarks').__(' in your bookmarking menu', 'sexybookmarks'),
		'share'=>__('Share this on ', 'sexybookmarks').__('Facebook', 'sexybookmarks'),
		'baseUrl'=>'http://www.facebook.com/share.php?v=4&amp;src=bm&amp;u=PERMALINK&amp;t=TITLE',
	),
	'sexy-twitter'=>array(
		'check'=>__('Check this box to include ', 'sexybookmarks').__('Twitter', 'sexybookmarks').__(' in your bookmarking menu', 'sexybookmarks'),
		'share'=>__('Tweet This!', 'sexybookmarks'),
		'baseUrl'=>'http://twitter.com/home?status=SHORT_TITLE+-+FETCH_URL+POST_BY',
	),
	'sexy-mail'=>array(
		'check'=>__('Check this box to include the ', 'sexybookmarks').__('"Email to a Friend" link', 'sexybookmarks').__(' in your bookmarking menu', 'sexybookmarks'),
		'share'=>__('Email this to a friend?', 'sexybookmarks'),
		'baseUrl'=>'mailto:?subject=%22TITLE%22&amp;body=I%20thought%20this%20article%20might%20interest%20you.%0A%0A%22POST_SUMMARY%22%0A%0AYou%20can%20read%20the%20full%20article%20here%3A%20PERMALINK',
	),
	'sexy-tomuse'=>array(
		'check'=>__('Check this box to include the ', 'sexybookmarks').__('ToMuse', 'sexybookmarks').__(' in your bookmarking menu', 'sexybookmarks'),
		'share'=>__('Suggest this article to ', 'sexybookmarks').__('ToMuse', 'sexybookmarks'),
		'baseUrl'=>'mailto:tips@tomuse.com?subject=New%20tip%20submitted%20via%20the%20SexyBookmarks%20Plugin!&amp;body=I%20would%20like%20to%20submit%20this%20article%3A%20%22TITLE%22%20for%20possible%20inclusion%20on%20ToMuse.%0A%0A%22POST_SUMMARY%22%0A%0AYou%20can%20read%20the%20full%20article%20here%3A%20PERMALINK',
	),
	'sexy-comfeed'=>array(
		'check'=>__('Check this box to include ', 'sexybookmarks').__('a \'Subscribe to Comments\' link', 'sexybookmarks').__(' in your bookmarking menu', 'sexybookmarks'),
		'share'=>__('Subscribe to the comments for this post?', 'sexybookmarks'),
		'baseUrl'=>'PERMALINK',
	),
	'sexy-linkedin'=>array(
		'check'=>__('Check this box to include ', 'sexybookmarks').__('Linkedin', 'sexybookmarks').__(' in your bookmarking menu', 'sexybookmarks'),
		'share'=>__('Share this on ', 'sexybookmarks').__('Linkedin', 'sexybookmarks'),
		'baseUrl'=>'http://www.linkedin.com/shareArticle?mini=true&amp;url=PERMALINK&amp;title=TITLE&amp;summary=POST_SUMMARY&amp;source=SITE_NAME',
	),
	'sexy-newsvine'=>array(
		'check'=>__('Check this box to include ', 'sexybookmarks').__('Newsvine', 'sexybookmarks').__(' in your bookmarking menu', 'sexybookmarks'),
		'share'=>__('Seed this on ', 'sexybookmarks').__('Newsvine', 'sexybookmarks'),
		'baseUrl'=>'http://www.newsvine.com/_tools/seed&amp;save?u=PERMALINK&amp;h=TITLE',
	),
	'sexy-devmarks'=>array(
		'check'=>__('Check this box to include ', 'sexybookmarks').__('Devmarks', 'sexybookmarks').__(' in your bookmarking menu', 'sexybookmarks'),
		'share'=>__('Share this on ', 'sexybookmarks').__('Devmarks', 'sexybookmarks'),
		'baseUrl'=>'http://devmarks.com/index.php?posttext=POST_SUMMARY&amp;posturl=PERMALINK&amp;posttitle=TITLE',
	),
	'sexy-google'=>array(
		'check'=>__('Check this box to include ', 'sexybookmarks').__('Google Bookmarks', 'sexybookmarks').__(' in your bookmarking menu', 'sexybookmarks'),
		'share'=>__('Add this to ', 'sexybookmarks').__('Google Bookmarks', 'sexybookmarks'),
		'baseUrl'=>'http://www.google.com/bookmarks/mark?op=add&amp;bkmk=PERMALINK&amp;title=TITLE',
	),
	'sexy-misterwong'=>array(
		'check'=>__('Check this box to include ', 'sexybookmarks').__('Mister Wong', 'sexybookmarks').__(' in your bookmarking menu', 'sexybookmarks'),
		'share'=>__('Add this to ', 'sexybookmarks').__('Mister Wong', 'sexybookmarks'),
		'baseUrl'=>'http://www.mister-wong.com/addurl/?bm_url=PERMALINK&amp;bm_description=TITLE&amp;plugin=sexybookmarks',
	),
	'sexy-izeby'=>array(
		'check'=>__('Check this box to include ', 'sexybookmarks').__('Izeby', 'sexybookmarks').__(' in your bookmarking menu', 'sexybookmarks'),
		'share'=>__('Add this to ', 'sexybookmarks').__('Izeby', 'sexybookmarks'),
		'baseUrl'=>'http://izeby.com/submit.php?url=PERMALINK',
	),
	'sexy-tipd'=>array(
		'check'=>__('Check this box to include ', 'sexybookmarks').__('Tipd', 'sexybookmarks').__(' in your bookmarking menu', 'sexybookmarks'),
		'share'=>__('Share this on ', 'sexybookmarks').__('Tipd', 'sexybookmarks'),
		'baseUrl'=>'http://tipd.com/submit.php?url=PERMALINK',
	),
	'sexy-pfbuzz'=>array(
		'check'=>__('Check this box to include ', 'sexybookmarks').__('PFBuzz', 'sexybookmarks').__(' in your bookmarking menu', 'sexybookmarks'),
		'share'=>__('Share this on ', 'sexybookmarks').__('PFBuzz', 'sexybookmarks'),
		'baseUrl'=>'http://pfbuzz.com/submit?url=PERMALINK&amp;title=TITLE',
	),
	'sexy-friendfeed'=>array(
		'check'=>__('Check this box to include ', 'sexybookmarks').__('FriendFeed', 'sexybookmarks').__(' in your bookmarking menu', 'sexybookmarks'),
		'share'=>__('Share this on ', 'sexybookmarks').__('FriendFeed', 'sexybookmarks'),
		'baseUrl'=>'http://www.friendfeed.com/share?title=TITLE&amp;link=PERMALINK',
	),
	'sexy-blogmarks'=>array(
		'check'=>__('Check this box to include ', 'sexybookmarks').__('BlogMarks', 'sexybookmarks').__(' in your bookmarking menu', 'sexybookmarks'),
		'share'=>__('Mark this on ', 'sexybookmarks').__('BlogMarks', 'sexybookmarks'),
		'baseUrl'=>'http://blogmarks.net/my/new.php?mini=1&amp;simple=1&amp;url=PERMALINK&amp;title=TITLE',
	),
	'sexy-twittley'=>array(
		'check'=>__('Check this box to include ', 'sexybookmarks').__('Twittley', 'sexybookmarks').__(' in your bookmarking menu', 'sexybookmarks'),
		'share'=>__('Submit this to ', 'sexybookmarks').__('Twittley', 'sexybookmarks'),
		'baseUrl'=>'http://twittley.com/submit/?title=TITLE&amp;url=PERMALINK&amp;desc=POST_SUMMARY&amp;pcat=TWITT_CAT&amp;tags=DEFAULT_TAGS',
	),
	'sexy-fwisp'=>array(
		'check'=>__('Check this box to include ', 'sexybookmarks').__('Fwisp', 'sexybookmarks').__(' in your bookmarking menu', 'sexybookmarks'),
		'share'=>__('Share this on ', 'sexybookmarks').__('Fwisp', 'sexybookmarks'),
		'baseUrl'=>'http://fwisp.com/submit?url=PERMALINK',
	),
	'sexy-designmoo'=>array(
		'check'=>__('Check this box to include ', 'sexybookmarks').__('DesignMoo', 'sexybookmarks').__(' in your bookmarking menu', 'sexybookmarks'),
		'share'=>__('Moo this on ', 'sexybookmarks').__('DesignMoo', 'sexybookmarks').'!',
		'baseUrl'=>'http://designmoo.com/submit?url=PERMALINK&amp;title=TITLE&amp;body=POST_SUMMARY',
	),
	'sexy-bobrdobr'=>array(
		'check'=>__('Check this box to include ', 'sexybookmarks').__('BobrDobr', 'sexybookmarks').__(' in your bookmarking menu', 'sexybookmarks').__(' (Russian)', 'sexybookmarks'),
		'share'=>__('Share this on ', 'sexybookmarks').__('BobrDobr', 'sexybookmarks'),
		'baseUrl'=>'http://bobrdobr.ru/addext.html?url=PERMALINK&amp;title=TITLE',
	),
	'sexy-yandex'=>array(
		'check'=>__('Check this box to include ', 'sexybookmarks').__('Yandex.Bookmarks', 'sexybookmarks').__(' in your bookmarking menu', 'sexybookmarks').__(' (Russian)', 'sexybookmarks'),
		'share'=>__('Add this to ', 'sexybookmarks').__('Yandex.Bookmarks', 'sexybookmarks'),
		'baseUrl'=>'http://zakladki.yandex.ru/userarea/links/addfromfav.asp?bAddLink_x=1&amp;lurl=PERMALINK&amp;lname=TITLE',
	),
	'sexy-memoryru'=>array(
		'check'=>__('Check this box to include ', 'sexybookmarks').__('Memory.ru', 'sexybookmarks').__(' in your bookmarking menu', 'sexybookmarks').__(' (Russian)', 'sexybookmarks'),
		'share'=>__('Add this to ', 'sexybookmarks').__('Memory.ru', 'sexybookmarks'),
		'baseUrl'=>'http://memori.ru/link/?sm=1&amp;u_data[url]=PERMALINK&amp;u_data[name]=TITLE',
	),
	'sexy-100zakladok'=>array(
		'check'=>__('Check this box to include ', 'sexybookmarks').__('100 bookmarks', 'sexybookmarks').__(' in your bookmarking menu', 'sexybookmarks').__(' (Russian)', 'sexybookmarks'),
		'share'=>__('Add this to ', 'sexybookmarks').__('100 bookmarks', 'sexybookmarks'),
		'baseUrl'=>'http://www.100zakladok.ru/save/?bmurl=PERMALINK&amp;bmtitle=TITLE',
	),
	'sexy-moemesto'=>array(
		'check'=>__('Check this box to include ', 'sexybookmarks').__('MyPlace', 'sexybookmarks').__(' in your bookmarking menu', 'sexybookmarks').__(' (Russian)', 'sexybookmarks'),
		'share'=>__('Add this to ', 'sexybookmarks').__('MyPlace', 'sexybookmarks'),
		'baseUrl'=>'http://moemesto.ru/post.php?url=PERMALINK&amp;title=TITLE',
	),
	'sexy-hackernews'=>array(
		'check'=>__('Check this box to include ', 'sexybookmarks').__('Hacker News', 'sexybookmarks').__(' in your bookmarking menu', 'sexybookmarks'),
		'share'=>__('Submit this to ', 'sexybookmarks').__('Hacker News', 'sexybookmarks'),
		'baseUrl'=>'http://news.ycombinator.com/submitlink?u=PERMALINK&amp;t=TITLE',
	),
	'sexy-printfriendly'=>array(
		'check'=>__('Check this box to include ', 'sexybookmarks').__('Print Friendly', 'sexybookmarks').__(' in your bookmarking menu', 'sexybookmarks'),
		'share'=>__('Send this page to ', 'sexybookmarks').__('Print Friendly', 'sexybookmarks'),
		'baseUrl'=>'http://www.printfriendly.com/print?url=PERMALINK',
	),
	'sexy-designbump'=>array(
		'check'=>__('Check this box to include ', 'sexybookmarks').__('Design Bump', 'sexybookmarks').__(' in your bookmarking menu', 'sexybookmarks'),
		'share'=>__('Bump this on ', 'sexybookmarks').__('DesignBump', 'sexybookmarks'),
		'baseUrl'=>'http://designbump.com/submit?url=PERMALINK&amp;title=TITLE&amp;body=POST_SUMMARY',
	),
	'sexy-ning'=>array(
		'check'=>__('Check this box to include ', 'sexybookmarks').__('Ning', 'sexybookmarks').__(' in your bookmarking menu', 'sexybookmarks'),
		'share'=>__('Add this to ', 'sexybookmarks').__('Ning', 'sexybookmarks'),
		'baseUrl'=>'http://bookmarks.ning.com/addItem.php?url=PERMALINK&amp;T=TITLE',
	),
	'sexy-identica'=>array(
		'check'=>__('Check this box to include ', 'sexybookmarks').__('Identica', 'sexybookmarks').__(' in your bookmarking menu', 'sexybookmarks'),
		'share'=>__('Post this to ', 'sexybookmarks').__('Identica', 'sexybookmarks'),
		'baseUrl'=>'http://identi.ca//index.php?action=newnotice&amp;status_textarea=Reading:+&quot;SHORT_TITLE&quot;+-+from+FETCH_URL',
	),
	'sexy-xerpi'=>array(
		'check'=>__('Check this box to include ', 'sexybookmarks').__('Xerpi', 'sexybookmarks').__(' in your bookmarking menu', 'sexybookmarks'),
		'share'=>__('Save this to ', 'sexybookmarks').__('Xerpi', 'sexybookmarks'),
		'baseUrl'=>'http://www.xerpi.com/block/add_link_from_extension?url=PERMALINK&amp;title=TITLE',
	),
	'sexy-wikio'=>array(
		'check'=>__('Check this box to include ', 'sexybookmarks').__('Wikio', 'sexybookmarks').__(' in your bookmarking menu', 'sexybookmarks'),
		'share'=>__('Share this on ', 'sexybookmarks').__('Wikio', 'sexybookmarks'),
		'baseUrl'=>'http://www.wikio.com/sharethis?url=PERMALINK&amp;title=TITLE',
	),
	'sexy-techmeme'=>array(
		'check'=>__('Check this box to include ', 'sexybookmarks').__('TechMeme', 'sexybookmarks').__(' in your bookmarking menu', 'sexybookmarks'),
		'share'=>__('Tip this to ', 'sexybookmarks').__('TechMeme', 'sexybookmarks'),
		'baseUrl'=>'http://twitter.com/home/?status=Tip+@Techmeme+PERMALINK+&quot;TITLE&quot;',
	),
	'sexy-sphinn'=>array(
		'check'=>__('Check this box to include ', 'sexybookmarks').__('Sphinn', 'sexybookmarks').__(' in your bookmarking menu', 'sexybookmarks'),
		'share'=>__('Sphinn this on ', 'sexybookmarks').__('Sphinn', 'sexybookmarks'),
		'baseUrl'=>'http://sphinn.com/index.php?c=post&amp;m=submit&amp;link=PERMALINK',
	),
	'sexy-posterous'=>array(
		'check'=>__('Check this box to include ', 'sexybookmarks').__('Posterous', 'sexybookmarks').__(' in your bookmarking menu', 'sexybookmarks'),
		'share'=>__('Post this to ', 'sexybookmarks').__('Posterous', 'sexybookmarks'),
		'baseUrl'=>'http://posterous.com/share?linkto=PERMALINK&amp;title=TITLE&amp;selection=POST_SUMMARY',
	),
	'sexy-globalgrind'=>array(
		'check'=>__('Check this box to include ', 'sexybookmarks').__('Global Grind', 'sexybookmarks').__(' in your bookmarking menu', 'sexybookmarks'),
		'share'=>__('Grind this! on ', 'sexybookmarks').__('Global Grind', 'sexybookmarks'),
		'baseUrl'=>'http://globalgrind.com/submission/submit.aspx?url=PERMALINK&amp;type=Article&amp;title=TITLE',
	),
	'sexy-pingfm'=>array(
		'check'=>__('Check this box to include ', 'sexybookmarks').__('Ping.fm', 'sexybookmarks').__(' in your bookmarking menu', 'sexybookmarks'),
		'share'=>__('Ping this on ', 'sexybookmarks').__('Ping.fm', 'sexybookmarks'),
		'baseUrl'=>'http://ping.fm/ref/?link=PERMALINK&amp;title=TITLE&amp;body=POST_SUMMARY',
	),
	'sexy-nujij'=>array(
		'check'=>__('Check this box to include ', 'sexybookmarks').__('NUjij', 'sexybookmarks').__(' in your bookmarking menu', 'sexybookmarks').__(' (Dutch)', 'sexybookmarks'),
		'share'=>__('Submit this to ', 'sexybookmarks').__('NUjij', 'sexybookmarks'),
		'baseUrl'=>'http://nujij.nl/jij.lynkx?t=TITLE&amp;u=PERMALINK&amp;b=POST_SUMMARY',
	),
	'sexy-ekudos'=>array(
		'check'=>__('Check this box to include ', 'sexybookmarks').__('eKudos', 'sexybookmarks').__(' in your bookmarking menu', 'sexybookmarks').__(' (Dutch)', 'sexybookmarks'),
		'share'=>__('Submit this to ', 'sexybookmarks').__('eKudos', 'sexybookmarks'),
		'baseUrl'=>'http://www.ekudos.nl/artikel/nieuw?url=PERMALINK&amp;title=TITLE&amp;desc=POST_SUMMARY',
	),
	'sexy-netvouz'=>array(
		'check'=>__('Check this box to include ', 'sexybookmarks').__('Netvouz', 'sexybookmarks').__(' in your bookmarking menu', 'sexybookmarks'),
		'share'=>__('Submit this to ', 'sexybookmarks').__('Netvouz', 'sexybookmarks'),
		'baseUrl'=>'http://www.netvouz.com/action/submitBookmark?url=PERMALINK&amp;title=TITLE&amp;popup=no',
	),
	'sexy-netvibes'=>array(
		'check'=>__('Check this box to include ', 'sexybookmarks').__('Netvibes', 'sexybookmarks').__(' in your bookmarking menu', 'sexybookmarks'),
		'share'=>__('Submit this to ', 'sexybookmarks').__('Netvibes', 'sexybookmarks'),
		'baseUrl'=>'http://www.netvibes.com/share?title=TITLE&amp;url=PERMALINK',
	),
	'sexy-fleck'=>array(
		'check'=>__('Check this box to include ', 'sexybookmarks').__('Fleck', 'sexybookmarks').__(' in your bookmarking menu', 'sexybookmarks'),
		'share'=>__('Share this on ', 'sexybookmarks').__('Fleck', 'sexybookmarks'),
		'baseUrl'=>'http://beta3.fleck.com/bookmarklet.php?url=PERMALINK&amp;title=TITLE',
	),
	'sexy-blogospherenews'=>array(
		'check'=>__('Check this box to include ', 'sexybookmarks').__('Blogosphere News', 'sexybookmarks').__(' in your bookmarking menu', 'sexybookmarks'),
		'share'=>__('Share this on ', 'sexybookmarks').__('Blogosphere News', 'sexybookmarks'),
		'baseUrl'=>'http://www.blogospherenews.com/submit.php?url=PERMALINK&amp;title=TITLE',
	),
	'sexy-webblend'=>array(
		'check'=>__('Check this box to include ', 'sexybookmarks').__('Web Blend', 'sexybookmarks').__(' in your bookmarking menu', 'sexybookmarks'),
		'share'=>__('Blend this!', 'sexybookmarks'),
		'baseUrl'=>'http://thewebblend.com/submit?url=PERMALINK&amp;title=TITLE&amp;body=POST_SUMMARY',
	),
	'sexy-wykop'=>array(
		'check'=>__('Check this box to include ', 'sexybookmarks').__('Wykop', 'sexybookmarks').__(' in your bookmarking menu', 'sexybookmarks').__(' (Polish)', 'sexybookmarks'),
		'share'=>__('Add this to Wykop!', 'sexybookmarks'),
		'baseUrl'=>'http://www.wykop.pl/dodaj?url=PERMALINK&amp;title=TITLE',
	),
	'sexy-blogengage'=>array(
		'check'=>__('Check this box to include ', 'sexybookmarks').__('BlogEngage', 'sexybookmarks').__(' in your bookmarking menu', 'sexybookmarks'),
		'share'=>__('Engage with this article!', 'sexybookmarks'),
		'baseUrl'=>'http://www.blogengage.com/submit.php?url=PERMALINK',
	),
	'sexy-hyves'=>array(
		'check'=>__('Check this box to include ', 'sexybookmarks').__('Hyves', 'sexybookmarks').__(' in your bookmarking menu', 'sexybookmarks'),
		'share'=>__('Share this on ', 'sexybookmarks').__('Hyves', 'sexybookmarks'),
		'baseUrl'=>'http://www.hyves.nl/profilemanage/add/tips/?name=TITLE&amp;text=POST_SUMMARY+-+PERMALINK&amp;rating=5',
	),
	'sexy-pusha'=>array(
		'check'=>__('Check this box to include ', 'sexybookmarks').__('Pusha', 'sexybookmarks').__(' in your bookmarking menu', 'sexybookmarks').__(' (Swedish)', 'sexybookmarks'),
		'share'=>__('Push this on ', 'sexybookmarks').__('Pusha', 'sexybookmarks'),
		'baseUrl'=>'http://www.pusha.se/posta?url=PERMALINK&amp;title=TITLE',
	),
	'sexy-hatena'=>array(
		'check'=>__('Check this box to include ', 'sexybookmarks').__('Hatena Bookmarks', 'sexybookmarks').__(' in your bookmarking menu', 'sexybookmarks').__(' (Japanese)', 'sexybookmarks'),
		'share'=>__('Bookmarks this on ', 'sexybookmarks').__('Hatena Bookmarks', 'sexybookmarks'),
		'baseUrl'=>'http://b.hatena.ne.jp/add?mode=confirm&amp;url=PERMALINK&amp;title=TITLE',
	),
	'sexy-mylinkvault'=>array(
		'check'=>__('Check this box to include ', 'sexybookmarks').__('MyLinkVault', 'sexybookmarks').__(' in your bookmarking menu', 'sexybookmarks'),
		'share'=>__('Store this link on ', 'sexybookmarks').__('MyLinkVault', 'sexybookmarks'),
		'baseUrl'=>'http://www.mylinkvault.com/link-page.php?u=PERMALINK&amp;n=TITLE',
	),
	'sexy-slashdot'=>array(
		'check'=>__('Check this box to include ', 'sexybookmarks').__('SlashDot', 'sexybookmarks').__(' in your bookmarking menu', 'sexybookmarks'),
		'share'=>__('Submit this to ', 'sexybookmarks').__('SlashDot', 'sexybookmarks'),
		'baseUrl'=>'http://slashdot.org/bookmark.pl?url=PERMALINK&amp;title=TITLE',
	),
	'sexy-squidoo'=>array(
		'check'=>__('Check this box to include ', 'sexybookmarks').__('Squidoo', 'sexybookmarks').__(' in your bookmarking menu', 'sexybookmarks'),
		'share'=>__('Add to a lense on ', 'sexybookmarks').__('Squidoo', 'sexybookmarks'),
		'baseUrl'=>'http://www.squidoo.com/lensmaster/bookmark?PERMALINK',
	),
	'sexy-propeller'=>array(
		'check'=>__('Check this box to include ', 'sexybookmarks').__('Propeller', 'sexybookmarks').__(' in your bookmarking menu', 'sexybookmarks'),
		'share'=>__('Submit this story to ', 'sexybookmarks').__('Propeller', 'sexybookmarks'),
		'baseUrl'=>'http://www.propeller.com/submit/?url=PERMALINK',
	),
	'sexy-faqpal'=>array(
		'check'=>__('Check this box to include ', 'sexybookmarks').__('FAQpal', 'sexybookmarks').__(' in your bookmarking menu', 'sexybookmarks'),
		'share'=>__('Submit this to ', 'sexybookmarks').__('FAQpal', 'sexybookmarks'),
		'baseUrl'=>'http://www.faqpal.com/submit?url=PERMALINK',
	),
	'sexy-evernote'=>array(
		'check'=>__('Check this box to include ', 'sexybookmarks').__('Evernote', 'sexybookmarks').__(' in your bookmarking menu', 'sexybookmarks'),
		'share'=>__('Clip this to ', 'sexybookmarks').__('Evernote', 'sexybookmarks'),
		'baseUrl'=>'http://www.evernote.com/clip.action?url=PERMALINK&amp;title=TITLE',
	),
	'sexy-meneame'=>array(
		'check'=>__('Check this box to include ', 'sexybookmarks').__('Meneame', 'sexybookmarks').__(' in your bookmarking menu', 'sexybookmarks').__(' (Spanish)', 'sexybookmarks'),
		'share'=>__('Submit this to ', 'sexybookmarks').__('Meneame', 'sexybookmarks'),
		'baseUrl'=>'http://meneame.net/submit.php?url=PERMALINK',
	),
	'sexy-bitacoras'=>array(
		'check'=>__('Check this box to include ', 'sexybookmarks').__('Bitacoras', 'sexybookmarks').__(' in your bookmarking menu', 'sexybookmarks').__(' (Spanish)', 'sexybookmarks'),
		'share'=>__('Submit this to ', 'sexybookmarks').__('Bitacoras', 'sexybookmarks'),
		'baseUrl'=>'http://bitacoras.com/anotaciones/PERMALINK',
	),
	'sexy-jumptags'=>array(
		'check'=>__('Check this box to include ', 'sexybookmarks').__('JumpTags', 'sexybookmarks').__(' in your bookmarking menu', 'sexybookmarks'),
		'share'=>__('Submit this link to ', 'sexybookmarks').__('JumpTags', 'sexybookmarks'),
		'baseUrl'=>'http://www.jumptags.com/add/?url=PERMALINK&amp;title=TITLE',
	),
	'sexy-bebo'=>array(
		'check'=>__('Check this box to include ', 'sexybookmarks').__('Bebo', 'sexybookmarks').__(' in your bookmarking menu', 'sexybookmarks'),
		'share'=>__('Share this on ', 'sexybookmarks').__('Bebo', 'sexybookmarks'),
		'baseUrl'=>'http://www.bebo.com/c/share?Url=PERMALINK&amp;Title=TITLE',
	),
	'sexy-n4g'=>array(
		'check'=>__('Check this box to include ', 'sexybookmarks').__('N4G', 'sexybookmarks').__(' in your bookmarking menu', 'sexybookmarks'),
		'share'=>__('Submit tip to ', 'sexybookmarks').__('N4G', 'sexybookmarks'),
		'baseUrl'=>'http://www.n4g.com/tips.aspx?url=PERMALINK&amp;title=TITLE',
	),
	'sexy-strands'=>array(
		'check'=>__('Check this box to include ', 'sexybookmarks').__('Strands', 'sexybookmarks').__(' in your bookmarking menu', 'sexybookmarks'),
		'share'=>__('Submit this to ', 'sexybookmarks').__('Strands', 'sexybookmarks'),
		'baseUrl'=>'http://www.strands.com/tools/share/webpage?title=TITLE&amp;url=PERMALINK',
	),
	'sexy-orkut'=>array(
		'check'=>__('Check this box to include ', 'sexybookmarks').__('Orkut', 'sexybookmarks').__(' in your bookmarking menu', 'sexybookmarks'),
		'share'=>__('Promote this on ', 'sexybookmarks').__('Orkut', 'sexybookmarks'),
		'baseUrl'=>'http://promote.orkut.com/preview?nt=orkut.com&amp;tt=TITLE&amp;du=PERMALINK&amp;cn=POST_SUMMARY',
	),
	'sexy-tumblr'=>array(
		'check'=>__('Check this box to include ', 'sexybookmarks').__('Tumblr', 'sexybookmarks').__(' in your bookmarking menu', 'sexybookmarks'),
		'share'=>__('Share this on ', 'sexybookmarks').__('Tumblr', 'sexybookmarks'),
		'baseUrl'=>'http://www.tumblr.com/share?v=3&amp;u=PERMALINK&amp;t=TITLE',
	),
	'sexy-stumpedia'=>array(
		'check'=>__('Check this box to include ', 'sexybookmarks').__('Stumpedia', 'sexybookmarks').__(' in your bookmarking menu', 'sexybookmarks'),
		'share'=>__('Add this to ', 'sexybookmarks').__('Stumpedia', 'sexybookmarks'),
		'baseUrl'=>'http://www.stumpedia.com/submit?url=PERMALINK&amp;title=TITLE',
	),
	'sexy-current'=>array(
		'check'=>__('Check this box to include ', 'sexybookmarks').__('Current', 'sexybookmarks').__(' in your bookmarking menu', 'sexybookmarks'),
		'share'=>__('Post this to ', 'sexybookmarks').__('Current', 'sexybookmarks'),
		'baseUrl'=>'http://current.com/clipper.htm?url=PERMALINK&amp;title=TITLE',
	),
	'sexy-blogger'=>array(
		'check'=>__('Check this box to include ', 'sexybookmarks').__('Blogger', 'sexybookmarks').__(' in your bookmarking menu', 'sexybookmarks'),
		'share'=>__('Blog this on ', 'sexybookmarks').__('Blogger', 'sexybookmarks'),
		'baseUrl'=>'http://www.blogger.com/blog_this.pyra?t&amp;u=PERMALINK&amp;n=TITLE&amp;pli=1',
	),
	'sexy-plurk'=>array(
		'check'=>__('Check this box to include ', 'sexybookmarks').__('Plurk', 'sexybookmarks').__(' in your bookmarking menu', 'sexybookmarks'),
		'share'=>__('Share this on ', 'sexybookmarks').__('Plurk', 'sexybookmarks'),
		'baseUrl'=>'http://www.plurk.com/m?content=TITLE+-+PERMALINK&amp;qualifier=shares',
	),
);
?>