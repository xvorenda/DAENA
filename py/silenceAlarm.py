#!/usr/bin/python

from __future__ import division
from alarm import alarm
import argparse
import time
import sys
import MySQLdb as mdb
import smtplib
import re
import getpass
print getpass.getuser()

parser = argparse.ArgumentParser(description=""""Send an email indicating the 
    alarm will be silenced given freezer id and alarm level""")

parser.add_argument("-f", "--freezerid", type=int)
parser.add_argument("-a", "--alarmlevel", type=int)
args = parser.parse_args()
freezer = args.freezerid
alarmLevel = args.alarmlevel

changeAlarm = alarm()

# Get necessary information from database 
readQuery = ("select freezer_alarm_ID, freezer_setpoint1, freezer_setpoint2, freezer_location, freezer_name, freezer_description, freezer_send_alarm from freezers where freezer_id = %s")
changeAlarm.readcursor.execute(readQuery, (freezer))
alarmIDData = changeAlarm.readcursor.fetchall()
freezerAlarmID = alarmIDData[0][0]
setpoint1 = float(alarmIDData[0][1])
setpoint2 = float(alarmIDData[0][2])
location = alarmIDData[0][3]
name = alarmIDData[0][4]
description = alarmIDData[0][5]

# Send Alarm = 0 it will not sound an alarm
send_alarm = alarmIDData[0][6]

location = re.sub("<br>", ' ', location)

    #get last temperature reading and the time it was taken
readQuery = "SELECT data.temp, data.int_time FROM data, freezers WHERE freezers.freezer_id = %s AND freezers.freezer_id = data.freezer_id AND data.temp != 'nodata' ORDER BY data.int_time DESC LIMIT 0,1"
changeAlarm.readcursor.execute(readQuery, (freezer))
tempData = changeAlarm.readcursor.fetchall()
lastTemp = tempData[0][0]
lastTime = int(tempData[0][1])
lastDateTime = time.strftime("%A, %B %d, %Y, at %H:%M:%S", time.localtime(lastTime/1000))
    

# 6 > silenced Alarm 7 
if alarmLevel == changeAlarm.COMMUNICATION_ALARM:
    changeAlarm.newAlarm(freezer, changeAlarm.COMMUNICATION_ALARM_SILENCED)
    
    # prepare query to get email addresses
    # alarm(alarm level number) = 1, the contact should get an
    # email at this level
    readQuery = "SELECT  email, alt_email FROM contacts, freezer_alarm_contacts WHERE contacts.contact_id = freezer_alarm_contacts.contact_id AND (alarm6=1 OR alarm7=1) AND freezer_id=%s"
    changeAlarm.readcursor.execute(readQuery, (freezer))
    
    emailList = []
    # loop through the email addresses and concatenate  
    # emailList with the email addresses
    for record in changeAlarm.readcursor:
        if not record: break
        emailList += record
    
    message = "The communication alarm for Freezer %s %s located in %s with description %s has been silenced and will no longer send out notificaions while the freezer remains in a communication alarm.  There will be notifications when communications have been reestablished.  \n\nThe last recorded temperature was %s degrees Celsius on %s. \n\nThere are a number of reasons for a communication alarm.\n1. Please check and make sure the probe is connected to the NTMS\n2. Check that the NTMS is connected to the network\n3. There may be a network outage\n\nNote: this will no longer send out notifications while it remains in a communication alarm.  \n\nPlease go to daena.csbc.vcu.edu to monitor the status of this freezer." % (freezer, name, location, description, lastTemp, lastDateTime, setpoint1, setpoint2)
    subject = 'Silenced: Communication Alarm for freezer %s' % name
    
    # Filter blank email addresses
    emailList = filter(None, emailList)
    
    # send the email
    if emailList and send_alarm:
        changeAlarm.sendMessage(emailList, subject, message)
        
# 3 > silenced Alarm 4   
elif alarmLevel == changeAlarm.CRITICAL_TEMP_ALARM:
    changeAlarm.newAlarm(freezer, changeAlarm.CRITICAL_TEMP_ALARM_SILENCED)
                        
    # prepare query to get email addresses
    # alarm(alarm level number) = 1, the contact should get an
    # email at this level
    readQuery = "SELECT  email, alt_email FROM contacts, freezer_alarm_contacts WHERE contacts.contact_id = freezer_alarm_contacts.contact_id AND (alarm3=1 or alarm4=1) AND freezer_id=%s"
    # Execute the query
    changeAlarm.readcursor.execute(readQuery, (freezer))
    
    # setup an empty list for the email addresses
    emailList = []
    # loop through the email addresses and concatenate  
    # emailList with the email addresses
    for record in changeAlarm.readcursor:
        if not record: break
        emailList += record
    
    # prepare meaningful message to send out
    message = "The critical alarm for Freezer %s %s located in %s with description %s has been silenced and will no longer send out notificaions while the freezer remains in a critical alarm.  \n\nThere will be notifications when communications have been reestablished. The last recorded temperature was %s  degrees Celsius on %s and is above the critical temperature setting of %s degrees Celsius.  \n\nNote: The freezer is still in a critical range but will not send out any reminder while it remains in a critical range.\n\nPlease go to daena.csbc.vcu.edu to monitor the status of this freezer." % (freezer, name, location, description, lastTemp, lastDateTime, setpoint2)

    # prepare meaningful subject for email
    subject = 'Silenced: Critical Alarm for %s' % name
    
    # Filter blank email addresses
    emailList = filter(None, emailList)
    
    # send the email
    if emailList and send_alarm:
        changeAlarm.sendMessage(emailList, subject, message)