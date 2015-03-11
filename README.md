# ortho-ctsi-profile
wordpress plugin - CTSI database integration for faculty templates

CTSI database hosted by another department that contains up-to-date info on staff/faculty details.
The plugin integrates the most current CTSI data with data maintained by the internal departmental database and allows
the admin to administer what content gets displayed, how it's sorted and how the content is merged.
The resulting data is rendered on the A2 Faculty Detail template pages.

	What this plugin does:
	* allow for configuration of data acquisition from Clinical & Transitional Science Institute (CTSI) UCSF database
	* CTSI API is ReSTful
	* dataformat is XML
	* Implements CTSI Meta Box for admin-side configuration
	* Implements live acquisition of CTSI data on client side.
	* Display on A2 Faculty Detail template: Staff description, grant data, publication data, Award data as acquired from CTSI.
	* other data: full name, position, title
	* CTSI entries can be prioritized such that sort order can be altered.
	* CTSI entries can be hidden, if desired.

Sample client-side link with plugin rendering
http://orthosurg.ucsf.edu/home/faculty/biography/richard-a-schneider-phd
Data acquired from external CTSI database: name, title, description, publications, grants, awards, etc.

Settings (admin side)
https://github.com/enuggetry/ortho-ctsi-profile/blob/master/images/ctsi-profile-admin-settings.jpg

Metabox (admin side)
https://github.com/enuggetry/ortho-ctsi-profile/blob/master/images/ctsi-profile-admin-side-biography.jpg

