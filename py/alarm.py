#!/usr/bin/env python
from __future__ import division
import time
import sys
import MySQLdb as mdb
import smtplib
import re
import bz2
import alarm

# Alarm Levels:
# Temperature Alarms
# 0 - No alarm, freezer is in a normal state
# 1 - freezer is in a high temp range, and has been for 30 min
# 2 - freezer is in a high temp range, and has been for 60 min, no more alarms
# 3 - freezer is in a critical temp range, and has been for 10 min, Sound alarm
#     every hour
# 4 - freezer is in a critical temp range, and has been for at least 10 min,
#     alarm has been silenced
# 5 - freezer is back in high temp range from being in critical range, freezer
#     is cooling

# Communication Alarms:
# 6 - communication alarm, there has been no contact with the freezer for 30
#     min, or all the data has ended up being "nodata", alarm every hour
# 7 - communication alarm, there has been no contact with the freezer for at
#     least 30 min or all the data ends up being "nodata", alarm has been
#     silenced

# Handy mysql scripts:
# insert new contact into table freezer_alarm_contacts
# INSERT INTO freezer_alarm_contacts (contact_id, freezer_id) SELECT contacts.contact_id, freezers.freezer_id FROM contacts, freezers WHERE contact_id = %s
#
# insert new freezer into freezer_alarm_contacts
# INSERT INTO freezer_alarm_contacts (contact_id, freezer_id) SELECT contacts.contact_id, freezers.freezer_id FROM contacts, freezers WHERE freezer_id = %s


class alarm(object):

    def __init__(self):

        # Open the configuration file which has the Alarm Email and database
        # information
        conf = open("/www/admin/config/db.php", "r")

        # Loop through the file
        for line in conf:
            # Remove the trailing white space (including return character)
            line = line.rstrip()
            # Dont waste the effort if the line is commented out
            if re.search('^//', line):
                pass
            elif re.search('EMAIL_ADDRESS', line):
                #pulls out the email address from the line
                self.email = re.sub('[\ \";\)]','', (line.split(',')[1]))
            elif re.search('EMAIL_PASSWORD', line):
                #pulls out the email password from the line
                self.emailPass = (re.sub('[\ \";\)]','', (line.split(',')[1])))
            elif re.search('DB_HOST', line):
                #pulls out the db host from the line
                host =(re.sub('[\ \";\)]','', (line.split(',')[1])))
            elif re.search('DB_NAME', line):
                #pulls out the database from the line
                database =(re.sub('[\ \";\)]','', (line.split(',')[1])))
            elif re.search('DB_USER', line):
                #pulls out the database user name from the line
                dbUser = (re.sub('[\ \";\)]','', (line.split(',')[1])))
            elif re.search('DB_PASS', line):
                #pulls out the database password from the line
                dbPass = (re.sub('[\ \";\)]','', (line.split(',')[1])))

        # Debug to make sure the passwords and such are correct
        #print "email, emailPass, host, database, dbUser, dbPass", self.email, self.emailPass, host, database, dbUser, dbPass

        # Close the file when it is done
        conf.close()

        # Create the connection to the database
        self.conn = mdb.connect(user=dbUser, passwd=dbPass, db=database)

        # Initialize write and read cursors to be used with mysql
        self.writecursor = self.conn.cursor()
        self.readcursor = self.conn.cursor()

        # Contants used in program
        self.IS_FREEZING = 0
        self.IS_NOT_FREEZING = 1

        # Alarm Timing Constants
        self.SIXTY_SECONDS = 60
        self.MINUTES_FOR_CRITICAL_RANGE_REMINDER = 60
        self.MINUTES_IN_CRITICAL_RANGE = 15
        self.MINUTES_IN_HIGH_RANGE = 30
        self.MINUTES_AT_ALARM_1 = 30
        self.MINUTES_AT_ALARM_0 = 30
        self.MINUTES_BELOW_CRITICAL_RANGE = 15
        self.MINUTES_BELOW_HIGH_RANGE = 15
        self.MINUTES_WITH_NO_DATA = 30
        self.MINUTES_FOR_COM_ALARM_REMINDER = 60

        # Alarm Level Constants
        self.NORMAL_STATE = 0
        self.HIGH_TEMP_ALARM_1 = 1
        self.HIGH_TEMP_ALARM_2 = 2
        self.CRITICAL_TEMP_ALARM = 3
        self.CRITICAL_TEMP_ALARM_SILENCED = 4
        self.CRITICAL_TEMP_TO_HIGH_TEMP_ALARM = 5
        self.COMMUNICATION_ALARM = 6
        self.COMMUNICATION_ALARM_SILENCED = 7

        # Used to make time into an int which is how time is stored in the database (time.time()*1000)
        self.TIME_THOUSAND = 1000


        #print "initilized"


################################################################################
    # Temperature Alarms


    def checkTemp(self, freezer, currentTemp, setpoint1 = None, setpoint2=None):
        #print "checking temp of", freezer, currentTemp, setpoint1, setpoint2

        readQuery = ("select freezer_alarm_ID, freezer_setpoint1, freezer_setpoint2, freezer_location, freezer_name, freezer_description, freezer_send_alarm from freezers where freezer_id = %s")
        self.readcursor.execute(readQuery, (freezer))
        alarmIDData = self.readcursor.fetchall()
        #print alarmIDData
        freezerAlarmID = alarmIDData[0][0]
        if not setpoint1:
            setpoint1 = float(alarmIDData[0][1])
        if not setpoint2:
            setpoint2 = float(alarmIDData[0][2])
        location = alarmIDData[0][3]
        name = alarmIDData[0][4]
        description = alarmIDData[0][5]

        # Send Alarm = 0 it will not sound an alarm
        send_alarm = alarmIDData[0][6]

        location = re.sub("<br>", ' ', location)
        #print "freezerAlarmID, setpoint1, setpoint2", freezerAlarmID, setpoint1, setpoint2
        # Takes in freezerID, currentTemp, the two setpoints, and
        # freezerAlarmID and checks to see if freezer is in an alarm state.

        #print "checking temp of", freezer, currentTemp, setpoint1, setpoint2

        # get data for most recent alarm level
        readQuery = ("select alarm_time, alarm_level from alarm where alarm_id = %s")
        numResults = self.readcursor.execute(readQuery, (freezerAlarmID))

        # check to ensure there was data pulled from the database
        #if numResults == 0:
        #    break

        # retrieve the data from the query
        data = self.readcursor.fetchall()
        alarmTime = int(data[0][0])
        alarmLevel = (data[0][1])
        #print "alarmTime, alarmLevel:", alarmTime, alarmLevel

        # currentTemp is in a critical range
        if currentTemp > setpoint2:
            #print "currentTemp in critical range", currentTemp, setpoint2
            # check if the temperature has been in a critical range for 15 min
            noAlarm = self.checkForNoAlarm(freezer, setpoint2, self.MINUTES_IN_CRITICAL_RANGE)

            #print "noAlarm critical 0= sound alarm 1=dont", noAlarm
            # if the temperature has been above setpoint2 send an alarm
            if noAlarm == 0:
                #print "critical"
                # silenced alarm does not send out message anymore
                # need more code for this on the website
# silenced (3 > Alarm 4)
                if alarmLevel == self.CRITICAL_TEMP_ALARM_SILENCED:
                    #print "alarmLevel self.CRITICAL_TEMP_ALARM_SILENCED", alarmLevel
                    pass

                # constant reminder alarm every 60min
                elif alarmLevel == self.CRITICAL_TEMP_ALARM:
                    #check to see if it has been > 60 min since the last alarm
# Reminder 3 > Alarm 3 (1 hour)
                    if alarmTime < (((time.time())-(self.SIXTY_SECONDS * self.MINUTES_FOR_CRITICAL_RANGE_REMINDER))*self.TIME_THOUSAND):
                        #print "reminder alarmLevel self.CRITICAL_TEMP_ALARM, time", alarmLevel, alarmTime
                        # Set alarm to self.CRITICAL_TEMP_ALARM, critical range reminder
                        self.newAlarm(freezer, self.CRITICAL_TEMP_ALARM)

                        # prepare query to get email addresses
                        # alarm(alarm level number) = 1, the contact should get an
                        # email at this level
                        readQuery = "SELECT  email, alt_email FROM contacts, freezer_alarm_contacts WHERE contacts.contact_id = freezer_alarm_contacts.contact_id AND alarm3=1 AND freezer_id=%s"
                        # Execute the query
                        self.readcursor.execute(readQuery, (freezer))

                        # setup an empty list for the email addresses
                        emailList = []
                        # loop through the email addresses and concatenate
                        # emailList with the email addresses
                        for record in self.readcursor:
                            if not record: break
                            emailList += record

                        # prepare meaningful message to send out
                        message = "This is a remider that Freezer %s %s located in %s with description %s is currently %s degrees Celsius and is above the critical temperature setting of %s degrees Celsius.  \n\nThe freezer has been in a critical range for at least 1 hour.  A reminder is sent each hour the freezer is out of range.\n\nPlease go to http://daena.csbc.vcu.edu to monitor the status of this freezer." % (freezer, name, location, description, currentTemp, setpoint2)

                        # prepare meaningful subject for email
                        subject = 'Reminder Critical Alarm for %s' % name

                        # Filter blank email addresses
                        emailList = filter(None, emailList)

                        # send the email
                        if emailList and send_alarm:
                            self.sendMessage(emailList, subject, message)

                # Set alarm level to 3
                else:
# 0 > Alarm 3
                    if alarmLevel == self.NORMAL_STATE:
                        #print "setting alarmLevel self.CRITICAL_TEMP_ALARM, time", alarmLevel, alarmTime
                        self.newAlarm(freezer, self.CRITICAL_TEMP_ALARM)

                        # prepare query to get email addresses
                        # alarm(alarm level number) = 1, the contact should get an
                        # email at this level
                        readQuery = "SELECT  email, alt_email FROM contacts, freezer_alarm_contacts WHERE contacts.contact_id = freezer_alarm_contacts.contact_id AND alarm3=1 AND freezer_id=%s"

                        # Execute the query
                        self.readcursor.execute(readQuery, (freezer))

                        emailList = []

                        # loop through the email addresses and concatenate
                        # emailList with the email addresses
                        for record in self.readcursor:
                            if not record: break
                            emailList += record

                        message = "Freezer %s %s located in %s with description %s is currently in a critical temperature range at %s degrees Celsius and is above the critical temperature setting of %s degrees Celsius.  The temperature has been out of range for at least 15 minutes.  \n\n  The freezer was previously in a normal state which is below the high temperature setting of %s degrees Celsius.  \n\nA reminder will be sent every hour till the temperature is no longer in a critical range.\n\n \n\nPlease go to http://daena.csbc.vcu.edu to monitor the status of this freezer." % (freezer, name, location, description, currentTemp, setpoint2, setpoint1)
                        subject = 'Critical Alarm for %s' % name

                        # Filter blank email addresses
                        emailList = filter(None, emailList)

                        # send the email
                        if emailList and send_alarm:
                            self.sendMessage(emailList, subject, message)

# 1 or 2 > Alarm 3
                    elif alarmLevel == self.HIGH_TEMP_ALARM_1 or alarmLevel == self.HIGH_TEMP_ALARM_2:
                        #print "alarmLevel self.HIGH_TEMP_ALARM_1 or self.HIGH_TEMP_ALARM_2 setting alarm level self.CRITICAL_TEMP_ALARM, time", alarmLevel, alarmTime
                        self.newAlarm(freezer, self.CRITICAL_TEMP_ALARM)

                        # prepare query to get email addresses
                        # alarm(alarm level number) = 1, the contact should get an
                        # email at this level
                        readQuery = "SELECT  email, alt_email FROM contacts, freezer_alarm_contacts WHERE contacts.contact_id = freezer_alarm_contacts.contact_id AND alarm3=1 AND freezer_id=%s"
                        self.readcursor.execute(readQuery, (freezer))

                        emailList = []
                        # loop through the email addresses and concatenate
                        # emailList with the email addresses
                        for record in self.readcursor:
                            if not record: break
                            emailList += record

                        message = "Freezer %s %s located in %s with description %s is in a critical temperature range at %s degrees Celsius and is above the critical temperature setting of %s degrees Celsius.  \n\nThe freezer was previously in a high temperature state which has a setting of %s degrees Celsius and is now in a critical state.\n\nThe temperature has been out of range for at least 30 min.  A reminder will be sent every hour till the temperature is no longer in a critical range.\n\nPlease go to http://daena.csbc.vcu.edu to monitor the status of this freezer." % (freezer, name, location, description, currentTemp, setpoint2, setpoint1)
                        subject = 'Critical Alarm for %s' % name

                        # Filter blank email addresses
                        emailList = filter(None, emailList)

                        # send the email
                        if emailList and send_alarm:
                            self.sendMessage(emailList, subject, message)
# 5 > Alarm 3
                    elif alarmLevel == self.CRITICAL_TEMP_TO_HIGH_TEMP_ALARM:
                        # Freezer was freezing but is now back into a critical
                        # state
                        #print "alarmLevel was self.CRITICAL_TEMP_TO_HIGH_TEMP_ALARM now self.CRITICAL_TEMP_ALARM freezing, time", alarmLevel, alarmTime

                        self.newAlarm(freezer, self.CRITICAL_TEMP_ALARM)

                        # prepare query to get email addresses
                        # alarm(alarm level number) = 1, the contact should get an
                        # email at this level
                        readQuery = "SELECT  email, alt_email FROM contacts, freezer_alarm_contacts WHERE contacts.contact_id = freezer_alarm_contacts.contact_id AND alarm3=1 AND freezer_id=%s"
                        self.readcursor.execute(readQuery, (freezer))

                        emailList = []
                        # loop through the email addresses and concatenate
                        # emailList with the email addresses
                        for record in self.readcursor:
                            if not record: break
                            emailList += record

                        message = "Freezer %s %s located in %s with description %s has gone back into a critical temperature range and is currently at %s degrees Celsius which is above the critical temperature setting of %s degrees Celsius. It was in a high temperature range degrees Celsius in which the high temperature setting is %s degrees Celsius.  \n\nThe temperature has been in the critical temperature range for 15 minutes.  A reminder will be sent every hour till the temperature is no longer in a critical range.\n\nPlease go to http://daena.csbc.vcu.edu to monitor the status of this freezer." % (freezer, name, location, description, currentTemp, setpoint2, setpoint1)
                        subject = 'Critical Range Alert for %s' % name

                        # Filter blank email addresses
                        emailList = filter(None, emailList)

                        # send the email
                        if emailList and send_alarm:
                            self.sendMessage(emailList, subject, message)

# 6 or 7 > Alarm 3
                    elif alarmLevel == self.COMMUNICATION_ALARM or alarmLevel == self.COMMUNICATION_ALARM_SILENCED:
                        #print "alarmLevel self.COMMUNICATION_ALARM or self.COMMUNICATION_ALARM_SILENCED setting alarm level self.CRITICAL_TEMP_ALARM, time", alarmLevel, alarmTime
                        self.newAlarm(freezer, self.CRITICAL_TEMP_ALARM)

                        # prepare query to get email addresses
                        # alarm(alarm level number) = 1, the contact should get an
                        # email at this level
                        readQuery = "SELECT  email, alt_email FROM contacts, freezer_alarm_contacts WHERE contacts.contact_id = freezer_alarm_contacts.contact_id AND alarm3=1 AND freezer_id=%s"
                        self.readcursor.execute(readQuery, (freezer))

                        emailList = []
                        # loop through the email addresses and concatenate
                        # emailList with the email addresses
                        for record in self.readcursor:
                            if not record: break
                            emailList += record

                        message = "Freezer %s %s located in %s with description %s is in a critical temperature range at %s degrees Celsius and is above the critical temperature setting of %s degrees Celsius.  \n\nThe freezer was previously in a Communication Alarm state and is no longer in a Communication alarm State.\n\nThe temperature has been out of range for at least 15 min.  A reminder will be sent every hour till the temperature is no longer in a critical range.\n\nPlease go to http://daena.csbc.vcu.edu to monitor the status of this freezer." % (freezer, name, location, description, currentTemp, setpoint2)
                        subject = 'Critical Alarm for %s' % name

                        # Filter blank email addresses
                        emailList = filter(None, emailList)

                        # send the email
                        if emailList and send_alarm:
                            self.sendMessage(emailList, subject, message)

# unknown > Alarm 3 (Generic Message)
                    else:
                        #print "setting alarmLevel self.CRITICAL_TEMP_ALARM, time", alarmLevel, alarmTime
                        self.newAlarm(freezer, self.CRITICAL_TEMP_ALARM)

                        # prepare query to get email addresses
                        # alarm(alarm level number) = 1, the contact should get an
                        # email at this level
                        readQuery = "SELECT  email, alt_email FROM contacts, freezer_alarm_contacts WHERE contacts.contact_id = freezer_alarm_contacts.contact_id AND alarm3=1 AND freezer_id=%s"

                        # Execute the query
                        self.readcursor.execute(readQuery, (freezer))

                        emailList = []

                        # loop through the email addresses and concatenate
                        # emailList with the email addresses
                        for record in self.readcursor:
                            if not record: break
                            emailList += record

                        message = "Freezer %s %s located in %s with description %s is currently in a critical temperature range at %s degrees Celsius and is above the critical temperature setting of %s degrees Celsius.  The temperature has been out of range for at least 15 minutes.  A reminder will be sent every hour till the temperature is no longer in a critical range.\n\n \n\nPlease go to http://daena.csbc.vcu.edu to monitor the status of this freezer." % (freezer, name, location, description, currentTemp, setpoint2)
                        subject = 'Critical Alarm for %s' % name

                        # Filter blank email addresses
                        emailList = filter(None, emailList)

                        # send the email
                        if emailList and send_alarm:
                            self.sendMessage(emailList, subject, message)


        # currentTemp is high
        elif currentTemp > setpoint1:
            #print "current temp high", currentTemp, setpoint1
            # check if the current temp has been in this range for 30 min
            noAlarm = self.checkForNoAlarm(freezer, setpoint1, self.MINUTES_IN_HIGH_RANGE)

            #print "noAlarm high", noAlarm
            # the temp has been above setpoint 1 send an alarm
            if noAlarm == 0:
                #print "noAlarm == 0"
                # if freezer stays between setpoint1 and setpoint2
                # then it will not alarm after the first two alarms
# Silenced ( 2 > Alarm 2)
                if alarmLevel == self.HIGH_TEMP_ALARM_2:
                    #print "alarmLevel 2", alarmLevel
                    pass

                # freezer has been out of range for 1 hour
# 1 > Alarm 2 (at least 30 min in alarm 1 state)
                elif alarmLevel == self.HIGH_TEMP_ALARM_1 and alarmTime < ((time.time()-(self.SIXTY_SECONDS * self.MINUTES_AT_ALARM_1)) *self.TIME_THOUSAND):
                    #print "alarmLevel self.HIGH_TEMP_ALARM_1 setting alarm level self.HIGH_TEMP_ALARM_2, time", alarmLevel, alarmTime
                    self.newAlarm(freezer, self.HIGH_TEMP_ALARM_2)

                    # prepare query to get email addresses
                    # alarm(alarm level number) = 1, the contact should get an
                    # email at this level
                    readQuery = "SELECT  email, alt_email FROM contacts, freezer_alarm_contacts WHERE contacts.contact_id = freezer_alarm_contacts.contact_id AND alarm2=1 AND freezer_id=%s"
                    self.readcursor.execute(readQuery, (freezer))

                    emailList = []
                    # loop through the email addresses and concatenate
                    # emailList with the email addresses
                    for record in self.readcursor:
                        if not record: break
                        emailList += record

                    message = "This is the final remider that Freezer %s %s located in %s with description %s is in a high temperature range at %s degrees Celsius and is above the high temperature setting of %s degrees Celsius.  \n\nThe freezer has been out of range for at least an hour. \n\nPlease go to http://daena.csbc.vcu.edu to monitor the status of this freezer." % (freezer, name, location, description, currentTemp, setpoint1)
                    subject = 'Final Reminder High Alarm for %s' % name

                    # Filter blank email addresses
                    emailList = filter(None, emailList)

                    # send the email
                    if emailList and send_alarm:
                        self.sendMessage(emailList, subject, message)


                # freezer has been out of range for 30 min,
                # this is the first alarm
# 0 > Alarm 1 (at least 30 min in alarm 0 state)
                elif alarmLevel == self.NORMAL_STATE and alarmTime < ((time.time()-(self.SIXTY_SECONDS * self.MINUTES_AT_ALARM_0)) *self.TIME_THOUSAND):
                    #print "alarmLevel 0 setting alarm level 1, time", alarmLevel, alarmTime
                    self.newAlarm(freezer, self.HIGH_TEMP_ALARM_1)

                    # prepare query to get email addresses
                    # alarm(alarm level number) = 1, the contact should get an
                    # email at this level
                    readQuery = "SELECT  email FROM contacts, freezer_alarm_contacts WHERE contacts.contact_id = freezer_alarm_contacts.contact_id AND alarm1=1 AND freezer_id=%s"
                    self.readcursor.execute(readQuery, (freezer))

                    emailList = []
                    # loop through the email addresses and concatenate
                    # emailList with the email addresses
                    for record in self.readcursor:
                        if not record: break
                        emailList += record

                    message = "Freezer %s %s located in %s with description %s is in a high temperature range at %s degrees Celsius and is above the high temperature setting of %s degrees Celsius.  \n\nThe temperature has been out of range for at least 30 min.  A final alarm will be send when the freezer has been out of range for 1 hour.\n\nPlease go to http://daena.csbc.vcu.edu to monitor the status of this freezer." % (freezer, name, location, description, currentTemp, setpoint1)
                    subject = 'High Alarm for %s' % name

                    # Filter blank email addresses
                    emailList = filter(None, emailList)

                    # send the email
                    if emailList and send_alarm:
                        self.sendMessage(emailList, subject, message)

                # if temperature is coming back down below setpoint2

# 6 or 7 > Alarm 1
                elif alarmLevel == self.COMMUNICATION_ALARM or alarmLevel == self.COMMUNICATION_ALARM_SILENCED:
                    #print "alarmLevel self.COMMUNICATION_ALARM or self.COMMUNICATION_ALARM_SILENCED setting alarm level self.HIGH_TEMP_ALARM_1, time", alarmLevel, alarmTime
                    self.newAlarm(freezer, self.HIGH_TEMP_ALARM_1)

                    # prepare query to get email addresses
                    # alarm(alarm level number) = 1, the contact should get an
                    # email at this level
                    readQuery = "SELECT  email FROM contacts, freezer_alarm_contacts WHERE contacts.contact_id = freezer_alarm_contacts.contact_id AND alarm1=1 AND freezer_id=%s"
                    self.readcursor.execute(readQuery, (freezer))

                    emailList = []
                    # loop through the email addresses and concatenate
                    # emailList with the email addresses
                    for record in self.readcursor:
                        if not record: break
                        emailList += record

                    message = "Freezer %s %s located in %s with description %s is in a high temperature range at %s degrees Celsius and is above the high temperature setting of %s degrees Celsius.  \n\nThe freezer was previously in a Communication Alarm state and is no longer in a Communication alarm State.\n\nThe temperature has been out of range for at least 30 min.  A final alarm will be send when the freezer has been out of range for 1 hour.\n\nPlease go to http://daena.csbc.vcu.edu to monitor the status of this freezer." % (freezer, name, location, description, currentTemp, setpoint1)
                    subject = 'High Alarm for %s' % name

                    # Filter blank email addresses
                    emailList = filter(None, emailList)

                    # send the email
                    if emailList and send_alarm:
                        self.sendMessage(emailList, subject, message)
# 3 or 4 > Alarm 5
                elif alarmLevel == self.CRITICAL_TEMP_ALARM or alarmLevel == self.CRITICAL_TEMP_ALARM_SILENCED:
                    #print "alarmLevel self.CRITICAL_TEMP_TO_HIGH_TEMP_ALARM freezing, time", alarmLevel, alarmTime
                    freezing = self.checkForFreezing(freezer, setpoint2, self.MINUTES_BELOW_CRITICAL_RANGE)

                    #print "freezing high", freezing
                    if freezing == self.IS_FREEZING:
                        #print "alarmLevel self.CRITICAL_TEMP_ALARM or self.CRITICAL_TEMP_ALARM_SILENCED freezing, time", alarmLevel, alarmTime
                        self.newAlarm(freezer, self.CRITICAL_TEMP_TO_HIGH_TEMP_ALARM)

                        # prepare query to get email addresses
                        # alarm(alarm level number) = 1, the contact should get an
                        # email at this level
                        readQuery = "SELECT  email, alt_email FROM contacts, freezer_alarm_contacts WHERE contacts.contact_id = freezer_alarm_contacts.contact_id AND alarm5=1 AND freezer_id=%s"
                    self.readcursor.execute(readQuery, (freezer))

                    emailList = []
                    # loop through the email addresses and concatenate
                    # emailList with the email addresses
                    for record in self.readcursor:
                        if not record: break
                        emailList += record

                    message = "Freezer %s %s located in %s with description %s has gone out of a critical temperature range and is now in a high temperature range at %s degrees Celsius which is below the critical temperature setting of %s and is above the high temperature setting of %s degrees Celsius.  \n\nThe temperature has been in the high temperature range for 30 minutes.  \n\nPlease go to http://daena.csbc.vcu.edu to monitor the status of this freezer." % (freezer, name, location, description, currentTemp, setpoint2, setpoint1)
                    subject = 'High Alarm: Freezer %s Out of Critical Range Notice' % name

                    # Filter blank email addresses
                    emailList = filter(None, emailList)

                    if emailList and send_alarm:
                        self.sendMessage(emailList, subject, message)


        elif currentTemp < setpoint1:
            #print "current temp normal", currentTemp, setpoint1
# 0 > Normal 0
            if alarmLevel == self.NORMAL_STATE:
                #print "alarmLevel self.NORMAL_STATE", alarmLevel
                pass
            else:
                #print "alarmLevel not self.NORMAL_STATE", alarmLevel
                freezing = self.checkForFreezing(freezer, setpoint1, self.MINUTES_BELOW_HIGH_RANGE)

                #print "freezing normal", freezing
                # freezer has gone back into normal range
                if freezing == self.IS_FREEZING:

# 1 or 2 > Normal 0
                    if alarmLevel == self.HIGH_TEMP_ALARM_1 or alarmLevel == self.HIGH_TEMP_ALARM_2:
                        # Freezer coming out of a High Temperature Alarm state
                        #print "alarmLevel self.HIGH_TEMP_ALARM_1 or self.HIGH_TEMP_ALARM_2 setting alarm level self.NORMAL_STATE, time", alarmLevel, alarmTime
                        self.newAlarm(freezer, self.NORMAL_STATE)

                        # prepare query to get email addresses
                        # alarm(alarm level number) = 1, the contact should get an
                        # email at this level
                        readQuery = "SELECT  email FROM contacts, freezer_alarm_contacts WHERE contacts.contact_id = freezer_alarm_contacts.contact_id AND (alarm0=1 OR alarm1=1 OR alarm2=1) AND freezer_id=%s"
                        self.readcursor.execute(readQuery, (freezer))

                        emailList = []
                        # loop through the email addresses and concatenate
                        # emailList with the email addresses
                        for record in self.readcursor:
                            if not record: break
                            emailList += record

                        message = "Freezer %s %s located in %s with description %s is now in a normal temperature range at %s degrees Celsius.  The freezer was previously in a high temperature range with the high temperature setting of %s degrees Celsius.  \n\nPlease go to http://daena.csbc.vcu.edu to monitor the status of this freezer." % (freezer, name, location, description, currentTemp, setpoint1)
                        subject = 'Freezer %s Back in Normal Range Notice' % name

                        # Filter blank email addresses
                        emailList = filter(None, emailList)

                        # send the email
                        if emailList and send_alarm:
                            self.sendMessage(emailList, subject, message)

# 3 or 4 > Normal 0
                    elif alarmLevel == self.CRITICAL_TEMP_ALARM or alarmLevel == self.CRITICAL_TEMP_ALARM_SILENCED:
                        # Freezer coming out of a Critical Temperature Alarm state
                        #print "alarmLevel self.CRITICAL_TEMP_ALARM or self.CRITICAL_TEMP_ALARM_SILENCED setting alarm level self.NORMAL_STATE, time", alarmLevel, alarmTime
                        self.newAlarm(freezer, self.NORMAL_STATE)

                        # prepare query to get email addresses
                        # alarm(alarm level number) = 1, the contact should get an
                        # email at this level
                        readQuery = "SELECT  email, alt_email FROM contacts, freezer_alarm_contacts WHERE contacts.contact_id = freezer_alarm_contacts.contact_id AND (alarm0=1 OR alarm3=1 OR alarm4=1) AND freezer_id=%s"
                        self.readcursor.execute(readQuery, (freezer))

                        emailList = []
                        # loop through the email addresses and concatenate
                        # emailList with the email addresses
                        for record in self.readcursor:
                            if not record: break
                            emailList += record

                        message = "Freezer %s %s located in %s with description %s is now in a normal temperature range at %s degrees Celsius.  The freezer was previously in a critical temperature range with the critical temperature setting of %s degrees Celsius.  \n\nPlease go to http://daena.csbc.vcu.edu to monitor the status of this freezer." % (freezer, name, location, description, currentTemp, setpoint2)
                        subject = 'Freezer %s Back in Normal Range Notice' % name

                        # Filter blank email addresses
                        emailList = filter(None, emailList)

                        # send the email
                        if emailList and send_alarm:
                            self.sendMessage(emailList, subject, message)

# 5 > Normal 0
                    elif alarmLevel == self.CRITICAL_TEMP_TO_HIGH_TEMP_ALARM:
                        # Freezer coming out of a High Temperature Alarm state
                        #print "alarmLevel self.CRITICAL_TEMP_TO_HIGH_TEMP_ALARM setting alarm level self.NORMAL_STATE, time", alarmLevel, alarmTime
                        self.newAlarm(freezer, self.NORMAL_STATE)

                        # prepare query to get email addresses
                        # alarm(alarm level number) = 1, the contact should get an
                        # email at this level
                        readQuery = "SELECT  email, alt_email FROM contacts, freezer_alarm_contacts WHERE contacts.contact_id = freezer_alarm_contacts.contact_id AND (alarm0=1 OR alarm5=1) AND freezer_id=%s"
                        self.readcursor.execute(readQuery, (freezer))

                        emailList = []
                        # loop through the email addresses and concatenate
                        # emailList with the email addresses
                        for record in self.readcursor:
                            if not record: break
                            emailList += record

                        message = "Freezer %s %s located in %s with description %s is now in a normal temperature range at %s degrees Celsius.  \n\nThe freezer was previously in a high temperature range with the high temperature setting of %s degrees Celsius.  The freezer had been in a critical temperature range above %s degrees Celsius and has cooled back down to a normal range.  \n\nPlease go to http://daena.csbc.vcu.edu to monitor the status of this freezer." % (freezer, name, location, description, currentTemp, setpoint1, setpoint2)
                        subject = 'Freezer %s Back in Normal Range Notice' % name

                        # Filter blank email addresses
                        emailList = filter(None, emailList)

                        # send the email
                        if emailList and send_alarm:
                            self.sendMessage(emailList, subject, message)

# 6 or 7 > Normal 0
                    elif alarmLevel == self.COMMUNICATION_ALARM or alarmLevel == self.COMMUNICATION_ALARM_SILENCED:
                        # Freezer coming out of a Communication Alarm state
                        #print "alarmLevel self.COMMUNICATION_ALARM or self.COMMUNICATION_ALARM_SILENCED setting alarm level 1, time", alarmLevel, alarmTime
                        self.newAlarm(freezer, self.NORMAL_STATE)

                        # prepare query to get email addresses
                        # alarm(alarm level number) = 1, the contact should get an
                        # email at this level
                        readQuery = "SELECT  email, alt_email FROM contacts, freezer_alarm_contacts WHERE contacts.contact_id = freezer_alarm_contacts.contact_id AND (alarm0=1 OR alarm6=1 OR alarm7=1) AND freezer_id=%s"
                        self.readcursor.execute(readQuery, (freezer))

                        emailList = []
                        # loop through the email addresses and concatenate
                        # emailList with the email addresses
                        for record in self.readcursor:
                            if not record: break
                            emailList += record

                        message = "Freezer %s %s located in %s with description %s was previously in a Communication Alarm state and is no longer in a Communication alarm State.\n\nThe freezer is in a normal state with a temperature of %s degrees Celsius where the high temperature setting is %s degrees Celsius.\n\nPlease go to http://daena.csbc.vcu.edu to monitor the status of this freezer." % (freezer, name, location, description, currentTemp, setpoint1)
                        subject = 'Freezer %s Back in Normal Range Notice' % name

                        # Filter blank email addresses
                        emailList = filter(None, emailList)

                        # send the email
                        if emailList and send_alarm:
                            self.sendMessage(emailList, subject, message)

# else (unknown) > Normal 0
                    else:
                        # Freezer back in a normal state
                        #print "freezing back to normal, time", alarmLevel, alarmTime
                        self.newAlarm(freezer, self.NORMAL_STATE)

                        # prepare query to get email addresses
                        # alarm(alarm level number) = 1, the contact should get an
                        # email at this level
                        readQuery = "SELECT  email FROM contacts, freezer_alarm_contacts WHERE contacts.contact_id = freezer_alarm_contacts.contact_id AND alarm0=1 AND freezer_id=%s"
                        self.readcursor.execute(readQuery, (freezer))

                        emailList = []
                        # loop through the email addresses and concatenate
                        # emailList with the email addresses
                        for record in self.readcursor:
                            if not record: break
                            emailList += record

                        message = "Freezer %s %s located in %s with description %s has back into a normal range at %s degrees Celsius which is below the high temperature setting of %s degrees Celsius.  \n\nThe temperature has been in normal range for 15 minutes.  \n\nPlease go to http://daena.csbc.vcu.edu to monitor the status of this freezer." % (freezer, name, location, description, currentTemp, setpoint2, setpoint1)
                        subject = 'Freezer %s Back in Normal Range Notice' % name

                        # Filter blank email addresses
                        emailList = filter(None, emailList)

                        # send the email
                        if emailList and send_alarm:
                            self.sendMessage(emailList, subject, message)

                else:
                    # Freezer is not back into a normal state yet
                    pass
                    #print "freezing not yet back to normal, time", alarmLevel, alarmTime

################################################################################

    def newAlarm(self, freezer, alarmLevel):
        #print "newAlarm", freezer, alarmLevel
        # get the time for the new alarm
        alarmTime = time.time()*self.TIME_THOUSAND

        # prepare to create new entry into the alarm table
        writeQuery = ("""insert into alarm set freezer_id = %s, alarm_level = %s, alarm_time = %s""")
        writeError = self.writecursor.execute(writeQuery, (freezer, alarmLevel, alarmTime))
        #print "error from write", writeError

        # get the alarm_id for the new alarm
        readQuery = ("""select alarm_id from alarm where alarm_time = %s""")
        numResults = self.readcursor.execute(readQuery, (alarmTime))
        data = self.readcursor.fetchone()
        #print "newAlarm data new created alarm_id", data, numResults, (readQuery % alarmTime)
        alarmID = data[0]

        # update the freezer with the new alarm state
        writeQuery = ("""update freezers set freezer_alarm_id = %s where freezer_id = %s""")
        writeError2 = self.writecursor.execute(writeQuery, (alarmID, freezer))
        #print "error from write2", writeError2

        # Check to verify the data will be correct
        readQuery = ("""select freezer_alarm_id from freezers where freezer_id = %s""")
        numResults2 = self.readcursor.execute(readQuery, (freezer))
        data = self.readcursor.fetchall()
        #print "newAlarm data confirm alarm_id", data, numResults2
        if alarmID == data[0][0]:
            # commit the data to the database
            self.conn.commit()
        else:
            #need to figure out what to do
            pass

################################################################################

    def checkForNoAlarm(self, freezer, setpoint, minutes):
        # checks if there is need for alarm, if the temp has been above the
        #setpoint then noAlarm = 0, if temp has been below setpoint noAlarm = 1
        # this takes freezerID, setpoint temperature, and number of minutes

        #print "checkForNoAlarm freezer, setpoint, minutes", freezer, setpoint, minutes

        # select last (minutes) of temperatures from database to check if they
        #are all above (setpoint)
        query = ("SELECT temp FROM data WHERE freezer_id = %s and data.int_time > %s")
        # executing the query with two variables.
        self.readcursor.execute(query, (freezer, ((time.time()-(self.SIXTY_SECONDS*minutes))*self.TIME_THOUSAND)))
        #print self.readcursor.fetchall()
        #print query % (freezer, time.time()-(self.SIXTY_SECONDS*minutes))
        # variable to indicate if the temperature has been below the appropriate
        #threshold 0 = all temp above threshold, 1 = at least 1 reading below
        #threshold
        noAlarm = 0
        noData = 0
        recordCount = 0
        for record in self.readcursor:
            recordCount += 1
            #print "Alarm record loop, record < setpoint, noAlarm", record[0], setpoint, noAlarm
            #if not record: break
            if record[0] == 'nodata':
                noData += 1
            elif float(record[0]) < setpoint:
                noAlarm = 1

        # more that 75% of the records are "nodata" then it does not pass for no
        # Alarm
        if recordCount == 0:
            return 1
        if noData/recordCount > .75:
            return noData
        else:
            return noAlarm

################################################################################

    def checkForFreezing(self, freezer, setpoint, minutes):
        # checks if the freezer has come below the threshold to update the alarm
        # this takes freezerID, setpoint temperature, and number of minutes

        #print "checkForFreezing freezer, setpoint, minutes", freezer, setpoint, minutes
        # select last (minutes) of temperatures from database to check if they
        #are all above (setpoint)
        query = ("SELECT temp FROM data WHERE freezer_id = %s and data.int_time > %s")
        # executing the query with two variables.
        self.readcursor.execute(query, (freezer, ((time.time()-(self.SIXTY_SECONDS*minutes))*self.TIME_THOUSAND)))

        # variable to indicate if the temperature has been below the appropriate
        #threshold 0 = all temp above threshold, 1 = at least 1 reading below
        #threshold
        freezing = self.IS_FREEZING
        noData = 0
        recordCount = 0
        for record in self.readcursor:
            recordCount += 1
            #print "freezing record loop, record < setpoint, freezing", record[0], setpoint, freezing
            #if not record: break
            if record[0] == "nodata":
                noData += 1
            elif float(record[0]) > setpoint:
                freezing = self.IS_NOT_FREEZING

        # if more that 75% of the records are "nodata" then it does not pass for
        # freezing
        if noData/recordCount > .75:
            return noData
        else:
            return freezing

################################################################################
    # Communication Alarms

    def checkComAlarm(self, freezer):
        #print "checkComAlarm", freezer
        # checks and sets communication alarm

        # Get necessary information from database
        readQuery = ("select freezer_alarm_ID, freezer_setpoint1, freezer_setpoint2, freezer_location, freezer_name, freezer_description, freezer_send_alarm from freezers where freezer_id = %s")
        self.readcursor.execute(readQuery, (freezer))
        alarmIDData = self.readcursor.fetchall()
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
        self.readcursor.execute(readQuery, (freezer))
        tempData = self.readcursor.fetchall()
        lastTemp = tempData[0][0]
        lastTime = int(tempData[0][1])
        lastDateTime = time.strftime("%A, %B %d, %Y, at %H:%M:%S", time.localtime(lastTime/1000))

        # get data for most recent alarm level
        readQuery = ("select alarm_time, alarm_level from alarm where alarm_id = %s")
        numResults = self.readcursor.execute(readQuery, (freezerAlarmID))

        # check to ensure there was data pulled from the database
        #if numResults == 0:
            #break

        # retrieve the data from the query
        data = self.readcursor.fetchall()

	try:
    		if data[0][1]:
    	       alarmTime = int(data[0][0])
		       alarmLevel = data[0][1]
		       alarmDateTime = time.strftime("%A, %B %d, %Y, at %H:%M:%S", time.localtime(alarmTime/1000))
	except IndexError:
    		pass

        #print "alarmTime, alarmLevel:", alarmTime, alarmLevel

        allNoData = self.checkForAllNoData(freezer, self.MINUTES_WITH_NO_DATA)
        # the past 30 min it was all "nodata"
        if not 'alarmLevel' in locals(): alarmLevel = '0'
	if allNoData == 0:

            # the com alarm has been silenced

# Silenced (6 > Alarm 7)
            if alarmLevel == self.COMMUNICATION_ALARM_SILENCED:
                pass

            # there is a com alarm and it has been active for over an hour
            # send a new alarm
            elif alarmLevel == self.COMMUNICATION_ALARM:

# Reminder 6 > Alarm 6 (1 hour)
                if alarmTime < ((time.time()-(self.SIXTY_SECONDS * self.MINUTES_FOR_COM_ALARM_REMINDER))*self.TIME_THOUSAND):
                    self.newAlarm(freezer, self.COMMUNICATION_ALARM)

                    # prepare query to get email addresses
                    # alarm(alarm level number) = 1, the contact should get an
                    # email at this level
                    readQuery = "SELECT  email, alt_email FROM contacts, freezer_alarm_contacts WHERE contacts.contact_id = freezer_alarm_contacts.contact_id AND alarm6=1 AND freezer_id=%s"
                    self.readcursor.execute(readQuery, (freezer))

                    emailList = []
                    # loop through the email addresses and concatenate
                    # emailList with the email addresses
                    for record in self.readcursor:
                        if not record: break
                        emailList += record

                    message = "This is a reminder the system currently cannot get data for Freezer %s %s located in %s with description %s.  The last recorded temperature was %s degrees Celsius on %s.  \n\nThere are a number of reasons this could happen.\n1. Please check and make sure the probe is connected to the NTMS\n2. Check that the NTMS is connected to the network\n3. There may be a network outage\n\nNote: this will alarm every hour till the problem is fixed.  \n\nPlease go to http://daena.csbc.vcu.edu to monitor the status of this freezer." % (freezer, name, location, description, lastTemp, lastDateTime)
                    subject = 'Reminder Communication Alarm for freezer %s' % name

                    # Filter blank email addresses
                    emailList = filter(None, emailList)

                    # send the email
                    if emailList and send_alarm:
                        self.sendMessage(emailList, subject, message)

            # If the freezer is not in a com alarm put it in a com alarm state

# 0 > Alarm 6
            elif alarmLevel == self.NORMAL_STATE:
                self.newAlarm(freezer, self.COMMUNICATION_ALARM)

                # prepare query to get email addresses
                # alarm(alarm level number) = 1, the contact should get an
                # email at this level
                readQuery = "SELECT  email, alt_email FROM contacts, freezer_alarm_contacts WHERE contacts.contact_id = freezer_alarm_contacts.contact_id AND alarm6=1 AND freezer_id=%s"
                self.readcursor.execute(readQuery, (freezer))

                emailList = []
                # loop through the email addresses and concatenate
                # emailList with the email addresses
                for record in self.readcursor:
                    if not record: break
                    emailList += record

                message = "The system currently cannot get data for Freezer %s %s located in %s with description %s.  The last recorded temperature was %s degrees Celsius on %s. \n\nThere are a number of reasons this could happen.\n1. Please check and make sure the probe is connected to the NTMS\n2. Check that the NTMS is connected to the network\n3. There may be a network outage\n\nNote: this will alarm every hour till the problem is fixed.  \n\nPlease go to http://daena.csbc.vcu.edu to monitor the status of this freezer." % (freezer, name, location, description, lastTemp, lastDateTime)
                subject = 'Communication Alarm for freezer %s' % name

                # Filter blank email addresses
                emailList = filter(None, emailList)

                # send the email
                if emailList and send_alarm:
                    self.sendMessage(emailList, subject, message)

# 1 or 2 > Alarm 6
            elif alarmLevel == self.HIGH_TEMP_ALARM_1 or alarmLevel == self.HIGH_TEMP_ALARM_2:
                self.newAlarm(freezer, self.COMMUNICATION_ALARM)

                # prepare query to get email addresses
                # alarm(alarm level number) = 1, the contact should get an
                # email at this level
                readQuery = "SELECT  email, alt_email FROM contacts, freezer_alarm_contacts WHERE contacts.contact_id = freezer_alarm_contacts.contact_id AND (alarm6=1 OR alarm1=1 OR alarm2=1) AND freezer_id=%s"
                self.readcursor.execute(readQuery, (freezer))

                emailList = []
                # loop through the email addresses and concatenate
                # emailList with the email addresses
                for record in self.readcursor:
                    if not record: break
                    emailList += record

                message = "The system currently cannot get data for Freezer %s %s located in %s with description %s.  The last recorded temperature was %s degrees Celsius on %s. \n\nThe freezer was previously in a high temperature range with the high temperature setting of %s degrees Celsius.  \n\nThere are a number of reasons this could happen.\n1. Please check and make sure the probe is connected to the NTMS\n2. Check that the NTMS is connected to the network\n3. There may be a network outage\n\nNote: this will alarm every hour till the problem is fixed.  \n\nPlease go to http://daena.csbc.vcu.edu to monitor the status of this freezer." % (freezer, name, location, description, lastTemp, lastDateTime, setpoint1)
                subject = 'Communication Alarm for freezer %s' % name

                # Filter blank email addresses
                emailList = filter(None, emailList)

                # send the email
                if emailList and send_alarm:
                    self.sendMessage(emailList, subject, message)

# 3 or 4 > Alarm 6
            elif alarmLevel == self.CRITICAL_TEMP_ALARM or alarmLevel == self.CRITICAL_TEMP_ALARM_SILENCED:
                self.newAlarm(freezer, self.COMMUNICATION_ALARM)

                # prepare query to get email addresses
                # alarm(alarm level number) = 1, the contact should get an
                # email at this level
                readQuery = "SELECT  email, alt_email FROM contacts, freezer_alarm_contacts WHERE contacts.contact_id = freezer_alarm_contacts.contact_id AND (alarm6=1 OR alarm3=1 OR alarm4=1) AND freezer_id=%s"
                self.readcursor.execute(readQuery, (freezer))

                emailList = []
                # loop through the email addresses and concatenate
                # emailList with the email addresses
                for record in self.readcursor:
                    if not record: break
                    emailList += record

                message = "The system currently cannot get data for Freezer %s %s located in %s with description %s.  The last recorded temperature was %s degrees Celsius on %s. \n\nThe freezer was previously in a critical temperature range with the critical temperature setting of %s degrees Celsius.  \n\nThere are a number of reasons this could happen.\n1. Please check and make sure the probe is connected to the NTMS\n2. Check that the NTMS is connected to the network\n3. There may be a network outage\n\nNote: this will alarm every hour till the problem is fixed.  \n\nPlease go to http://daena.csbc.vcu.edu to monitor the status of this freezer." % (freezer, name, location, description, lastTemp, lastDateTime, setpoint2)
                subject = 'Communication Alarm for freezer %s' % name

                # Filter blank email addresses
                emailList = filter(None, emailList)

                # send the email
                if emailList and send_alarm:
                    self.sendMessage(emailList, subject, message)

# 5 > Alarm 6
            elif alarmLevel == self.CRITICAL_TEMP_TO_HIGH_TEMP_ALARM:
                self.newAlarm(freezer, self.COMMUNICATION_ALARM)

                # prepare query to get email addresses
                # alarm(alarm level number) = 1, the contact should get an
                # email at this level
                readQuery = "SELECT  email, alt_email FROM contacts, freezer_alarm_contacts WHERE contacts.contact_id = freezer_alarm_contacts.contact_id AND (alarm6=1 OR alarm5=1) AND freezer_id=%s"
                self.readcursor.execute(readQuery, (freezer))

                emailList = []
                # loop through the email addresses and concatenate
                # emailList with the email addresses
                for record in self.readcursor:
                    if not record: break
                    emailList += record

                message = "The system currently cannot get data for Freezer %s %s located in %s with description %s.  The last recorded temperature was %s degrees Celsius on %s. \n\nThe freezer was previously in a high temperature range with the high temperature setting of %s degrees Celsius.  The freezer had been in a critical temperature range above %s degrees Celsius.  \n\nThere are a number of reasons this could happen.\n1. Please check and make sure the probe is connected to the NTMS\n2. Check that the NTMS is connected to the network\n3. There may be a network outage\n\nNote: this will alarm every hour till the problem is fixed.  \n\nPlease go to http://daena.csbc.vcu.edu to monitor the status of this freezer." % (freezer, name, location, description, lastTemp, lastDateTime, setpoint1, setpoint2)
                subject = 'Communication Alarm for freezer %s' % name

                # Filter blank email addresses
                emailList = filter(None, emailList)

                # send the email
                if emailList and send_alarm:
                    self.sendMessage(emailList, subject, message)

# else (unknown) > Alarm 6
            else:
                self.newAlarm(freezer, self.COMMUNICATION_ALARM)

                # prepare query to get email addresses
                # alarm(alarm level number) = 1, the contact should get an
                # email at this level
                readQuery = "SELECT  email, alt_email FROM contacts, freezer_alarm_contacts WHERE contacts.contact_id = freezer_alarm_contacts.contact_id AND alarm6=1 AND freezer_id=%s"
                self.readcursor.execute(readQuery, (freezer))

                emailList = []
                # loop through the email addresses and concatenate
                # emailList with the email addresses
                for record in self.readcursor:
                    if not record: break
                    emailList += record

                message = "The system currently cannot get data for Freezer %s %s located in %s with description %s.  The last recorded temperature was %s degrees Celsius on %s.  \n\nThere are a number of reasons this could happen.\n1. Please check and make sure the probe is connected to the NTMS\n2. Check that the NTMS is connected to the network\n3. There may be a network outage\n\nNote: this will alarm every hour till the problem is fixed.  \n\nPlease go to http://daena.csbc.vcu.edu to monitor the status of this freezer." % (freezer, name, location, description, lastTemp, lastDateTime)
                subject = 'Communication Alarm for freezer %s' % name

                # Filter blank email addresses
                emailList = filter(None, emailList)

                # send the email
                if emailList and send_alarm:
                    self.sendMessage(emailList, subject, message)

################################################################################

    def checkForAllNoData(self, freezer, minutes):
        # checks if the freezer has had no data for the last x minutes
        # to update the alarm
        # AllNoData = 0, not AllNoData = 1
        # this takes freezerID, and number of minutes

        #print "checkForAllNoData freezer, minutes", freezer, minutes
        # select last (minutes) of temperatures from database to check if they are all "nodata"
        query = ("SELECT temp FROM data WHERE freezer_id = %s and data.int_time > %s")
        # executing the query with two variables.
        self.readcursor.execute(query, (freezer, ((time.time()-(self.SIXTY_SECONDS*minutes))*self.TIME_THOUSAND)))

        # variable to indicate if there was all "nodata" 0 = all "nodata", 1 =
        #at least 1 reading not "nodata"
        allNoData = 0
        for record in self.readcursor:
            #print "allNoData record loop, record = 'nodata' ", record[0], setpoint, allNoData
            #if not record: break
            if record[0] != "nodata":
                allNoData = 1
        return allNoData

################################################################################

    def sendMessage(self, toList, subject, message):
        # Get email password from email_password.txt
        # password has been stored in bz2 compressed format

        # Initilize mail Server to be used
        mailserver = smtplib.SMTP("smtp.gmail.com",587)
        mailserver.ehlo()
        mailserver.starttls()
        mailserver.ehlo()
        mailserver.login(self.email, self.emailPass)
        sender = self.email

        header = 'From: %s\n' % sender
        header += 'To: %s\n' % ','.join(toList)
        header += 'Subject: %s\n\n' % subject
        headerMessage = header+message

        #print "sending message", headerMessage

        #send the message
        mailserver.sendmail(sender, toList, headerMessage)
        mailserver.close()

 ################################################################################

    def closeAlarm(self):
        # closes the cursors used by this program
        self.writecursor.close()
        self.readcursor.close()
        self.conn.close ()
