<a href="https://travis-ci.org/catalyst/moodle-format_iprestricted">
<img src="https://travis-ci.org/catalyst/moodle-format_iprestricted.svg?branch=master">
</a>

# moodle-format_iprestricted
Provides a "course format" that allows restricted access by IP address.

Choosing this course format for a course will restrict access to that course.

If you are browsing from an IP address that is not listed in the white list, you will be denied access to view that course.

# Usage
To restrict access to a course:
* Go to the course settings for the course you'd like to restrict
* Under the Course Format section, note the existing (old) course format
* choose the "IP Restricted" format for the course.
* Set the "Extended course format" to the old course format
* Choose which IP addresses (and/or netblocks) should be "whitelisted"
* Save. Done!

Users attempting to view the course from an IP address that has not been whitelisted will be denied access to that course.


# Crafted by Catalyst IT
This plugin was developed by Catalyst IT Australia:

https://www.catalyst-au.net/

![Catalyst IT](/pix/catalyst-logo.png?raw=true)
