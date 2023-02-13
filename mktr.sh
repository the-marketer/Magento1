#!/bin/sh
# mod="Mktr_Tracker"
# m=$(echo $mod | tr '[:upper:]' '[:lower:]')
# rep=$(echo $mod | sed -e "s/_/\//g")
# master=${mod%_*}

start ()
{
  echo "Please use sh mktr/mktr.sh install"
  echo "or sh mktr/mktr.sh uninstall"
}

install ()
{
    echo "Creating Directory for Mktr"
    mkdir -p app/code/community/Mktr
    echo "Copy Files Mktr"

    cp -rf mktr/app/etc/modules/Mktr_Google.xml app/etc/modules/Mktr_Google.xml
    cp -rf mktr/app/etc/modules/Mktr_Tracker.xml app/etc/modules/Mktr_Tracker.xml

    cp -rf mktr/app/code/community/Mktr/Google app/code/community/Mktr
    cp -rf mktr/app/code/community/Mktr/Tracker app/code/community/Mktr

    cp -rf mktr/app/design/frontend/base/default/layout/mktr_google.xml app/design/frontend/base/default/layout/mktr_google.xml
    cp -rf mktr/app/design/frontend/base/default/layout/mktr_tracker.xml app/design/frontend/base/default/layout/mktr_tracker.xml
    read -p "Almost Done, Press enter to continue " responce
}

uninstall ()
{
    read -r -p "Are you sure? [Y/n]" response
    response=$(echo $response | tr '[:upper:]' '[:lower:]')
    if [ "${response}" = "y" ]; then
        rm -rf app/code/community/Mktr

        rm -rf app/etc/modules/Mktr_Google.xml
        rm -rf app/design/frontend/base/default/layout/mktr_google.xml

        rm -rf app/etc/modules/Mktr_Tracker.xml
        rm -rf app/design/frontend/base/default/layout/mktr_tracker.xml
    fi    
}

if [ -z "$1" ]; then
    start
else
    $1
fi