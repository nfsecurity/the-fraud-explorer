#!/bin/bash

# The Fraud Explorer
# https://www.thefraudexplorer.com/
#
# Copyright (c) 2014-2020 The Fraud Explorer
# email: customer@thefraudexplorer.com
# Licensed under GNU GPL v3
# https://www.thefraudexplorer.com/License
#
# Author: jrios@nofraud.la
# Version code-name: nemesis
#
# Description: Code to enable DEMO platform

THEFRAUDEXPLORER="/var/www/html/thefraudexplorer"
EVENTMARKING="$THEFRAUDEXPLORER/mods/eventMarking.php"
FRAUDSIMULATOR="$THEFRAUDEXPLORER/mods/fraudSimulator.php"
BUILDENDPOINT="$THEFRAUDEXPLORER/mods/buildEndpoint.php"
MAINTENANCEPURGE="$THEFRAUDEXPLORER/mods/maintenancePurge.php"
SETUPENDPOINT="$THEFRAUDEXPLORER/mods/setupEndpoint.php"
MAINCONFIG="$THEFRAUDEXPLORER/mods/mainConfig.php"
ROLESCONFIG="$THEFRAUDEXPLORER/mods/rolesConfig.php"
EVENTPHRASES="$THEFRAUDEXPLORER/mods/eventPhrases.php"
FRAUDTRIANGLERULES="$THEFRAUDEXPLORER/mods/fraudTriangleRules.php"
ADVANCEDREPORTS="$THEFRAUDEXPLORER/mods/advancedReports.php"
MAILCONFIG="$THEFRAUDEXPLORER/mods/mailConfig.php"
BUSINESSUNITS="$THEFRAUDEXPLORER/mods/businessUnits.php"
SETUPRULESET="$THEFRAUDEXPLORER/mods/setupRuleset.php"
BACKUPDATA="$THEFRAUDEXPLORER/mods/backupData.php"
FRAUDTRIANGLEFLOWS="$THEFRAUDEXPLORER/mods/fraudTriangleFlows.php"
SWUPDATE="$THEFRAUDEXPLORER/mods/swUpdate.php"
WORDSUNIVERSE="$THEFRAUDEXPLORER/mods/wordsUniverse.php"
ANALYTICSHOLDER="$THEFRAUDEXPLORER/mods/analyticsHolder.php"
LOGINSESSION="$THEFRAUDEXPLORER/lbs/login/session.php"
LOGINFORM="$THEFRAUDEXPLORER/lbs/login/form.php"
DELENDPOINT="$THEFRAUDEXPLORER/helpers/endpointsProcessing.php"
DISABLEDOWNPHRASESRULES="$THEFRAUDEXPLORER/mods/fraudTriangleRules.php"
DISABLEDOWNPHRASESRULESET="$THEFRAUDEXPLORER/mods/setupRuleset.php"
LICENSE="$THEFRAUDEXPLORER/mods/libraryLicense.php"
WEEKDATA="$THEFRAUDEXPLORER/mods/dashHolder.php"

# Replace types

SUBMIT="s/input type=\"submit\"/input type=\"submit\" disabled/g"
PUTEVENT="s/name=\"putEvent\"/name=\"putEvent\" disabled/g"
ENDPOINT="s/id=\"btn-build-endpoint\"/id=\"btn-build-endpoint\" disabled/g"
BUTTON="s/button type=\"submit\"/button type=\"submit\" disabled/g"
DELPROFILE="s/id=\"button-del-profile\"/id=\"button-del-profile\" disabled/g"
ADDPROFILE="s/id=\"button-createmodify-profile\"/id=\"button-createmodify-profile\" disabled/g"
SCHRULE="s/id=\"search-rule\"/id=\"search-rule\" disabled/g"
DELRULE="s/id=\"delete-rule\"/id=\"delete-rule\" disabled/g"
MDFRULE="s/id=\"modify-rule\"/id=\"modify-rule\" disabled/g"
ADDRULE="s/id=\"add-rule\"/id=\"add-rule\" disabled/g"
REPORTS="s/id=\"btn-excel\"/id=\"btn-excel\" disabled/g"
MAIL="s/id=\"button-set-preferences\"/id=\"button-set-preferences\" disabled/g"
UNITS="s/id=\"departmentsToUpload\"/id=\"departmentsToUpload\" disabled/g"
UNITSBUTTON="s/id=\"departmentsToUpload-button\"/id=\"departmentsToUpload-button\" disabled/g"
RULESET="s/id=\"fileToUpload\"/id=\"fileToUpload\" disabled/g"
SETBACKUP="s/id=\"button-set-schedule\"/id=\"button-set-schedule\" disabled/g"
DOWNLOADBACKUP="s/id=\"button-download-backup\"/id=\"button-download-backup\" disabled/g"
DELFLOW="s/id=\"button-del-workflow\"/id=\"button-del-workflow\" disabled/g"
ADDFLOW="s/id=\"button-add-workflow\"/id=\"button-add-workflow\" disabled/g"
UPDATE="s/id=\"btn-update\"/id=\"btn-update\" disabled/g"
ADDREGIONALISM="s/id=\"button-add-words-regionalism\"/id=\"button-add-words-regionalism\" disabled/g"
DELREGIONALISM="s/id=\"button-del-words-regionalism\"/id=\"button-del-words-regionalism\" disabled/g"
ADDTONE="s/id=\"button-add-words-tone\"/id=\"button-add-words-tone\" disabled/g"
DELTONE="s/id=\"button-del-words-tone\"/id=\"button-del-words-tone\" disabled/g"
REMOTE="s/REMOTE_ADDR/HTTP_X_REAL_IP/g"
CREDENTIALS="s/values = array()/values = array(\"user\"=>\"admin\",\"pass\"=>\"N0FR4UD\")/g"
DELETEAGENT="s/#confirm-delete/#/g"
DISABLEDOWNLOAD="s/id=\"download-rules\"/id=\"#\"/g"
DISABLEDOWNLOADLICENSE="s/id=\"button-download-license\"/id=\"#\"/g"
DISABLEDOWNLOADLICENSEHREF="s/mods\/downloadLicense?le=<?php if (\$noBackup == true) echo \"nobackupfile\"; else echo encRijndael(\$latestBackup\[3\]); ?>/#/g"
DISABLEACTIVATELICENSE="s/id=\"button-activate-license\"/id=\"#\"/g"
WEEK="s/echo \$pressureWeekReduced/echo \"073\"/g ; s/echo \$opportunityWeekReduced/echo \"012\"/g ; s/echo \$rationalizationWeekReduced/echo \"091\"/g"

# Replace files

/bin/sed "$SUBMIT" --in-place $EVENTMARKING
/bin/sed "$PUTEVENT" --in-place $FRAUDSIMULATOR
/bin/sed "$ENDPOINT" --in-place $BUILDENDPOINT
/bin/sed "$BUTTON" --in-place $MAINTENANCEPURGE
/bin/sed "$SUBMIT" --in-place $SETUPENDPOINT
/bin/sed "$BUTTON" --in-place $MAINCONFIG
/bin/sed "$DELPROFILE ; $ADDPROFILE" --in-place $ROLESCONFIG
/bin/sed "$SUBMIT" --in-place $EVENTPHRASES
/bin/sed "$SUBMIT" --in-place $FRAUDTRIANGLERULES
/bin/sed "$SCHRULE ; $DELRULE ; $MDFRULE ; $ADDRULE" --in-place $FRAUDTRIANGLERULES
/bin/sed "$REPORTS" --in-place $ADVANCEDREPORTS
/bin/sed "$MAIL" --in-place $MAILCONFIG
/bin/sed "$UNITS ; $UNITSBUTTON" --in-place $BUSINESSUNITS
/bin/sed "$RULESET" --in-place $SETUPRULESET
/bin/sed "$SETBACKUP ; $DOWNLOADBACKUP" --in-place $BACKUPDATA
/bin/sed "$ADDFLOW ; $DELFLOW" --in-place $FRAUDTRIANGLEFLOWS
/bin/sed "$UPDATE" --in-place $SWUPDATE
/bin/sed "$ADDREGIONALISM ; $DELREGIONALISM ; $ADDTONE ; $DELTONE" --in-place $WORDSUNIVERSE
/bin/sed "$SUBMIT" --in-place $ANALYTICSHOLDER
/bin/sed "$REMOTE" --in-place $LOGINSESSION
/bin/sed "$CREDENTIALS" --in-place $LOGINFORM
/bin/sed "$DELETEAGENT" --in-place $DELENDPOINT
/bin/sed "$DISABLEDOWNLOAD" --in-place $DISABLEDOWNPHRASESRULES $DISABLEDOWNPHRASESRULESET
/bin/sed "$DISABLEDOWNLOADLICENSE ; $DISABLEACTIVATELICENSE ; $DISABLEDOWNLOADLICENSEHREF" --in-place $LICENSE
/bin/sed "$WEEK" --in-place $WEEKDATA