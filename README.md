# Piwik ClientCertificates Plugin

## Description

This plugin will add the following features to a piwik installation:

 * User identification based on client certificate
 * User authorization against specified service
 * Populate visits with metadata from authorization service
 * View reports related to agency and user name


ClientCertficates plugin will also require deployment of the ozone-enhancements branch of piwik to be able to tap into custom events needed for proper operation.

## Installation

* Clone plugin repo into `piwik/plugins/` directory 
```
cd piwik/plugins
git clone <git-url> ClientCertificates
```

* Activate plugin by going to web interface, Settings --> Plugins, and then Activate the ClientCertificates plugin.
* Go to Plugin Settings on left side, and set URL for authentication service, and the paths to server certificates. Remember to make sure the apache user has permission to read those files..

Now clients of a Piwik enabled application should be enabled with client certificates.

## Configuration

* To enable new user support must add the following configuration to `config/config.ini.php`
```
[Tracker]
window_look_back_for_visitor = 315360000
```

* To change the length of time that defines a visit you must add the following configuration to `config/config.ini.php`
```
[Tracker]
visit_standard_length = <time-in-seconds>
```