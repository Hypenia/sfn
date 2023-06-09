# Crontab example.
#
# This file is an example of triggering Elgg cron events. It hits a URL to
# trigger the events. For testing, you can simulate the cronjob by loading the
# URL in a browser.
#
# See http://learn.elgg.org/en/stable/admin/cron.html for more information
#

# Location of your site (don't forget the trailing slash!)
ELGG='http://localhost/spiritualfamily.net/'

# Location of lwp-request
LWPR='/usr/bin/lwp-request'

# Make GET request and discard content
GET="$LWPR -m GET -d"

# The crontab
# Don't edit below this line unless you know what you are doing
* * * * * $GET ${ELGG}cron/minute/
*/5 * * * * $GET ${ELGG}cron/fiveminute/
15,30,45,59 * * * * $GET ${ELGG}cron/fifteenmin/
30,59 * * * * $GET ${ELGG}cron/halfhour/
@hourly $GET ${ELGG}cron/hourly/
@daily $GET ${ELGG}cron/daily/
@weekly $GET ${ELGG}cron/weekly/
@monthly $GET ${ELGG}cron/monthly/
@yearly $GET ${ELGG}cron/yearly/
# reboot is deprecated and probably doesn't work
@reboot $GET ${ELGG}cron/reboot/
