# Convert New RoyalSlider Galleries to WordPress

This is a WordPress plugin that will convert image galleries in the [New RoyalSlider Gallery](http://dimsemenov.com/plugins/royal-slider/wordpress/) format into the native WordPress gallery format.

A list is generated showing all the posts and pages that are using New RoyalSlider galleries. They can be converted one page at a time, or all the pages can be converted at once.

The [new_royalslider] shortcodes are replaced with corresponding [gallery] shortcodes, and all the attachments assigned to the gallery.

## How to use it

* Backup your database and files.
* Install and activate this plugin. If you're into Git, you can do a `git clone` in your plugins folder, or alternatively you can download the raw version of the convert-new-royalslider-galleries.php file and put it in your plugins folder.
* Browse to the 'Convert New RoyalSlider Galleries' page under 'Settings'.
* Click 'List galleries to convert' to see what galleries will be converted.
* Click 'Convert' on a singe post, to convert just that post, or 'Convert all galleries', to convert all.

It's a good idea to run the conversion on one page as a test before converting all your galleries. It may take some time to convert if there are lots of images, so you may want to do the conversion a few pages at a time. 

The New RoyalSlider galleries and images remain untouched so, if you want to revert, you can manually restore the [new_royalslider] shortcodes and the New RoyalSlider galleries will work as they did before.

If you're happy with the results, and all galleries have been converted, you can uninstall the New RoyalSlider plugin and remove any New RoyalSlider gallery folders and database tables.


## How it works

The plugin works by finding all the posts and pages with [new_royalslider] shortcodes inside. It then loops over all those shortcodes and finds the corresponding New RoyalSlider galleries for them. The [new_royalslider] shortcodes are replaced with [gallery] shortcodes including the ids of those images.


## Additional options

The admin page can be found at:

```
/wp-admin/options-general.php?page=convert-new-royalslider-galleries.php
```

You can append additional arguments to that URL to perform the different operations.

### post

If you want to work the galleries on one specific post, you can use `&post=`. 

For example, to list all galleries on page 43 you can use:

```
/wp-admin/options-general.php?page=convert-new-royalslider-galleries.php&action=list&post=43
```

Then to convert those galleries you can use: 

```
/wp-admin/options-general.php?page=convert-new-royalslider-galleries.php&action=convert&post=43
```

### max_num

If you want work the galleries on the first 4 posts, you can use `&max_num=`. 

For example, to list all galleries on the first 4 pages you can use:

```
/wp-admin/options-general.php?page=convert-new-royalslider-galleries.php&action=list&max_num=4
```

Then to convert those galleries you can use: 

```
/wp-admin/options-general.php?page=convert-new-royalslider-galleries.php&action=convert&max_num=4
```


## Screenshot

Here is a screenshot of the admin screen listing the images to convert:

![Listing galleries to convert](https://raw.github.com/stefansenk/convert-new-royalslider-galleries/master/screenshot-listing-galleries.png)
