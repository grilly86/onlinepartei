# onlinepartei.eu - a social politicing platform
Communicate to make the world a better place, part by part! 
The page that is motorized by this code can be found here: http://onlinepartei.eu

## Current features
* user management
* user-to-user chat, with (mutable) sound feedback
* posts with automatic link, image or video (currently only youtube) recognition and the posibility to edit messages after posting
* poll creation and voting
* (nested) commenting possible
* tagging messages and list sorted by tags
* some user settings (profile picture, language selection, style color)
* 2-click social media plugin

## Technical overview
* **PHP 4.4.8** (until newer server is found) with **Smarty Template Engine** is used
* MySQL database is used. You can find the database structure in the `DATABASE_STRUCTURE.SQL`
* JQuery is used as JavaScript framework, couple of plugins: `jquery.fancybox`, `jquery.farbtastic`, `jquery.cookie`, `jquery.socialshareprivacy`, `jquery.jplayer`, `jquery.validate`
* RaphaelJS is used to draw the poll results

## Technical guidelines
* `style.css` is in `templates/` as it is processed by smarty
* in `static/script/functions.js` ***most*** of the JS events are stored - sometimes though the javascript is placed directly in the HTML context
