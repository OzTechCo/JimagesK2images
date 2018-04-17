# JimagesK2images
Copy Intro Image from Joomla! Content to K2 Items

You must first configure the database connection in the commented section; then uncomment to enable the script.

This script will find all Joomla! Articles that have an Intro Image specific, copy them to the K2 media folder, and generate the size variants.

Simple plase addk2images.php in your Joomla root folder, next to configuration.php. The script will use your Joomla db parameters.

Adjust as necessary if the script times out due to too many articles. Perhaps only process images that aren't in K2 src:

//copy image to K2 src folder
if(!file_exists($newimg)) {
  //the copy and image crunching
}
