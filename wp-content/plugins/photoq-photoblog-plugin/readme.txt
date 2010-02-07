=== PhotoQ Photoblog Plugin ===
Contributors: whoismanu
Donate link: http://www.whoismanu.com/blog/
Tags: photoq, images, photos, photoblog, photo, upload, thumbnail, photo, picture, pictures, post, automatic, lightbox, admin, flash, photoq photoblog
Requires at least: 2.8.1
Tested up to: 2.8.4
Stable tag: 1.8.3

Turns WordPress into a photoblog. Allows queue based photo management, batch uploads and automatic 
posting of photos at regular intervals.

== Description ==

PhotoQ is a WordPress plugin that turns your blog into a photoblog. PhotoQ is a plugin for photo enthusiasts: 
If you have a lot of pictures to post, PhotoQ is your ideal companion. With PhotoQ you can mass upload several photos at the same time thanks to 
its batch upload functionality. PhotoQ places uploaded photos in a queue which gives you a convenient way 
to manage photos to be posted. The plugin then gives you the possibility to have the top of the queue 
automatically posted at a given interval. PhotoQ was designed to automate and simplify your photo posting 
process as much as possible. It takes away the hassle of browsing uploaded image files and embedding them 
into posts: You simply upload your photo to the queue and enter desired information about the photo. PhotoQ 
then automatically generates the post based on this information.

To make a long story short, WordPress + PhotoQ = Photoblog. With the latest PhotoQ version this formula became more 
valid than ever: PhotoQ is now compatible with virtually any WordPress theme out-of-the-box. It even goes further by giving you presets for 
some of the most popular photoblog themes, allowing you to setup your photoblog without having to worry about configuration options. Whatever
theme you use, you can now benefit from PhotoQ's easy photo publishing, making juggling with custom fields in order to post images a thing of the past. 

**Feature list:**

* Convenient queue-based photo management
* Batch uploading of photos to your photoblog
* Hassle-free, fully automated posting of photo posts
* Compatible with virtually any theme; Auto-configuration for popular photo themes
* Advanced EXIF support, automatic post tag creation from EXIF data
* Photo Watermarking to protect your photos
* Possibility to add custom metadata to photo posts
* Automatic generation of thumbnails and alternative image sizes
* Updating of all your posted photos with only a few clicks
* Automatic posting through cronjobs
* Integration with Lightbox, Shutter Reloaded and similar libraries/plugins


== Installation ==

** Upgrading **

Before upgrading please check [the PhotoQ Blog](http://www.whoismanu.com/blog/ "Whoismanu Blog") for specific upgrading instructions/warnings.
Also, whenever you upgrade to a new version it is advised that you backup your database and files just like you do for a WordPress upgrade. If
you use the iQ2 theme in addition to PhotoQ, please update iQ2 first, before upgrading PhotoQ.

** Fresh Installation **

1. Unzip the downloaded file, you should end up with a folder called "photoq-photoblog-plugin".
2. Upload the "photoq-photoblog-plugin" folder to your "plugin" directory (wp-content/plugins).
3. If you plan to use the automatic posting capability, move the file "photoq-photoblog-plugin/wimpq-cronpost.php" to the same directory as your wp-config.php file.
4. Make sure that the file permissions of the "wp-content" directory are such that the plugin is allowed 
to write to it (otherwise, uploaded photos cannot be stored).
5. You are almost done. Just go to the "Plugins" Wordpress admin panel and activate the Photoq 
plugin.

For longer, more detailed instructions and a documentation explaining all the features, please check 
[my homepage](http://www.whoismanu.com/photoq-wordpress-photoblog-plugin/ "Home of PhotoQ").

== Frequently Asked Questions ==

= Where can I get answers to my questions regarding PhotoQ? =

* Full documentation can be found on [the PhotoQ Homepage](http://www.whoismanu.com/photoq-wordpress-photoblog-plugin/ "PhotoQ WordPress Photoblog Plugin") 
* For support, please visit [the PhotoQ Support Forum](http://www.whoismanu.com/forum/ "PhotoQ Support Forum")
* Latest news are found on [the PhotoQ Blog](http://www.whoismanu.com/blog/ "Whoismanu Blog")

= What themes can I use with PhotoQ? =

You can use PhotoQ with any theme that relies on the standard WordPress template tags, i.e. you can use it with virtually every WordPress theme that exists.

= What is a theme preset? =

A theme preset is a small XML configuration file that you can load into PhotoQ with one click. Every theme preset is specific to a particular theme and tells
PhotoQ how to interact with this theme, without you having to configure anything at all.

= Sounds great, so which themes have a preset included with PhotoQ? =

Currently, PhotoQ comes with a preset for the following themes:

*[Click!](http://fivebyfive.com.ar/wp-themes/click/)
*[Elegant Grunge](http://michael.tyson.id.au/wordpress/themes/elegant-grunge/)
*[Fotolog](http://www.flisterz.com/2008/06/18/fotolog-wp-theme-for-photoblog/)
*[Grace](http://7879designs.co.uk/demo/gracephotoblogtheme/)
*[iQ2](http://www.whoismanu.com/iq2-wordpress-photoblog-theme/)
*[Linquist](http://redworks.sk/wp-themes/linquist/)
*[Photo Blog](http://www.blogohblog.com/wordpress-theme-photo-blog/)
*[Sharpfolio](http://webrevolutionary.com/sharpfolio/)
*[Spotless](http://fivebyfive.com.ar/wp-themes/spotless/)
*[Viewport](http://labs.paulicio.us/viewport/)
*[Zack-990](http://labs.andreamignolo.com/zack990/)

= I am a theme author and I would love to see my theme in the above list, what can I do? =

* Well, you could start by creating a preset for your theme. It is simple and you will find instructions on [the PhotoQ Homepage](http://www.whoismanu.com/photoq-wordpress-photoblog-plugin/ "PhotoQ WordPress Photoblog Plugin") 
* You can then make the preset(s) available to your users on your homepage. Any preset they drop into a special folder called "myPhotoQPresets" inside "wp-content" will be included in the preset list in the PhotoQ settings
* Finally, you can let me know by sending me a pointer to the preset, I might then include it as one of the defaults.   

= I really like PhotoQ, what can I do to support it? =

* You could start by giving it a nice rating on the very [page you are looking at right now](http://wordpress.org/extend/plugins/photoq-photoblog-plugin/ "PhotoQ on WordPress.org)
* Please spread the word and tell other people how great it is
* Link back to [the PhotoQ Homepage](http://www.whoismanu.com/photoq-wordpress-photoblog-plugin/ "PhotoQ WordPress Photoblog Plugin") 
* More ideas can be found [here](http://www.whoismanu.com/photoq-wordpress-photoblog-plugin/#help "Support PhotoQ")


== Screenshots ==

1. Batch upload process
2. Entering information for uploaded photos.
3. The queue. Reordering can be done by drag and drop.
4. Tons of options.


