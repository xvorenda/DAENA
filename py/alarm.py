#!/usr/bin/env python
from __future__ import division
import time
import sys
import MySQLdb as mdb
import smtplib
import re


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


class alarm(object):

    def __init__(self):
    
        #create a connection with mySQL
        self.conn = mdb.connect(user='daena_user', passwd='idontcareaboutpasswordsrightnow', db='daena_db')
        
        # Initialize write and read cursors to be used with mysql 
        self.writecursor = self.conn.cursor()
        self.readcursor = self.conn.cursor()   
        #print "initilized"
                
        
    # Temperature Alarms
    
    
    def checkTemp(self, freezer, currentTemp, setpoint1 = None, setpoint2=None):
        #print "checking temp of", freezer, currentTemp, setpoint1, setpoint2
        
        readQuery = ("select freezer_alarm_ID, freezer_setpoint1, freezer_setpoint2, freezer_location, freezer_name from freezers where freezer_id = %s")
        self.readcursor.execute(readQuery, (freezer))
        alarmIDData = self.readcursor.fetchall()
        freezerAlarmID = alarmIDData[0][0]
        setpoint1 = float(alarmIDData[0][1])
        setpoint2 = float(alarmIDData[0][2])
        location = alarmIDData[0][3]
        name = alarmIDData[0][4]
        location = re.sub("<br>", ' ', location)
        #print "freezerAlarmID, setpoint1, setpoint2", freezerAlarmID, setpoint1, setpoint2
        # Takes in freezerID, currentTemp, the two setpoints, and 
        # freezerAlarmID and checks to see if freezer is in an alarm state.  
        
        # get data for most recent alarm level
        readQuery = ("select alarm_time, alarm_level from alarm where alarm_id = %s")
        numResults = self.readcursor.execute(readQuery, (freezerAlarmID))
        
        # check to ensure there was data pulled from the database
        #if numResults == 0:
        #    break
        
        # retrieve the data from the query
        data = self.readcursor.fetchall()
        alarmTime = float(data[0][0])
        alarmLevel = (data[0][1])
        #print "alarmTime, alarmLevel:", alarmTime, alarmLevel
        
        # currentTemp is in a critical range 
        if currentTemp > setpoint2:
            #print "currentTemp in critical range", currentTemp, setpoint2
            # check if the temperature has been in a critical range for 10 min
            noAlarm = self.checkForNoAlarm(freezer, setpoint2, 15)
            
            #print "noAlarm critical 0= sound alarm 1=dont", noAlarm
            # if the temperature has been above setpoint2 send an alarm
            if noAlarm == 0:
                
                # silenced alarm does not send out message anymore 
                # need more code for this on the website
                if alarmLevel == 4:
                    #print "alarmLevel 4", alarmLevel
                    pass
                    
                # constant reminder alarm every 60min
                elif alarmLevel == 3:
                    #check to see if it has been > 60 min since the last alarm
                    if alarmTime > (time.time()-(60*60)):
                        #print "time not more than 60 min"
                        pass
                    else:
                        #print "reminder alarmLevel 3, time", alarmLevel, alarmTime
                        self.newAlarm(freezer, 3)
                        
                        readQuery = "SELECT  email FROM contacts, freezer_alarm_contacts WHERE contacts.contact_id = freezer_alarm_contacts.contact_id AND alarm3=1 AND freezer_id=%s"
                        self.readcursor.execute(readQuery, (freezer))
                        emailList = []
                        for record in self.readcursor:
                            if not record: break
                            emailList += record
                        
                        message = "This is a remider that Freezer %s %s located in %s is currently %s degrees Celsius and is above the critical temperature setting of %s degrees Celsius.  \n\nThe freezer has been in a critical range for at least 1 hour.  A reminder is sent each hour the freezer is out of range.\n\nPlease go to daena.csbc.vcu.edu to monitor the status of this freezer." % (freezer, name, location, currentTemp, setpoint2)

                        subject = 'Reminder Critical Alarm for %s' % name
                        
                        self.sendMessage(emailList, subject, message)
                        
                        
                    
                # Set alarm level to 3
                else:
                    #print "setting alarmLevel 3, time", alarmLevel, alarmTime
                    self.newAlarm(freezer, 3)
                    
                    readQuery = "SELECT  email FROM contacts, freezer_alarm_contacts WHERE contacts.contact_id = freezer_alarm_contacts.contact_id AND alarm3=1 AND freezer_id=%s"
                    self.readcursor.execute(readQuery, (freezer))
                    emailList = []
                    for record in self.readcursor:
                        if not record: break
                        emailList += record
                    
                    message = "Freezer %s %s located in %s is currently in a critical temperature range at %s degrees Celsius and is above the critical temperature setting of %s degrees Celsius.  The temperature has been out of range for at least 15 minutes.  A reminder will be sent every hour till the temperature is no longer in a critical range.\n\n \n\nPlease go to daena.csbc.vcu.edu to monitor the status of this freezer." % (freezer, name, location, currentTemp, setpoint2)

                    subject = 'Critical Alarm for %s' % name
                    
                    self.sendMessage(emailList, subject, message)
                    
                    
        # currentTemp is high    
        elif currentTemp > setpoint1:
            #print "current temp high", currentTemp, setpoint1
            # check if the current temp has been in this range for 30 min
            noAlarm = self.checkForNoAlarm(freezer, setpoint1, 30)
            
            #print "noAlarm high", noAlarm
            # the temp has been above setpoint 1 send an alarm
            if noAlarm == 0:
                #print "noAlarm == 0"
                # if freezer stays between setpoint1 and setpoint2 
                # then it will not alarm after the first two alarms
                if alarmLevel == 2:
                    #print "alarmLevel 2", alarmLevel
                    pass
                    
                # freezer has been out of range for 1 hour
                elif alarmLevel == 1 and alarmTime > (time.time()-(60*30)):
                    #print "alarmLevel 1 setting alarm level 2, time", alarmLevel, alarmTime
                    self.newAlarm(freezer, 2)
                    
                    readQuery = "SELECT  email FROM contacts, freezer_alarm_contacts WHERE contacts.contact_id = freezer_alarm_contacts.contact_id AND alarm2=1 AND freezer_id=%s"
                    self.readcursor.execute(readQuery, (freezer))
                    emailList = []
                    for record in self.readcursor:
                        if not record: break
                        emailList += record
                    
                    message = "This is the final remider that Freezer %s %s located in %s is in a high temperature range at %s degrees Celsius and is above the high temperature setting of %s degrees Celsius.  \n\nThe freezer has been out of range for at least an hour. \n\nPlease go to daena.csbc.vcu.edu to monitor the status of this freezer." % (freezer, name, location, currentTemp, setpoint1)

                    subject = 'Final Reminder High Alarm for %s' % name
                    
                    self.sendMessage(emailList, subject, message)
                    
                    
                # freezer has been out of range for 30 min, 
                # this is the first alarm
                elif alarmLevel == 0 and alarmTime > (time.time()-(60*30)):
                    #print "alarmLevel 0 setting alarm level 1, time", alarmLevel, alarmTime
                    self.newAlarm(freezer, 1)
                    
                    readQuery = "SELECT  email FROM contacts, freezer_alarm_contacts WHERE contacts.contact_id = freezer_alarm_contacts.contact_id AND alarm1=1 AND freezer_id=%s"
                    self.readcursor.execute(readQuery, (freezer))
                    emailList = []
                    for record in self.readcursor:
                        if not record: break
                        emailList += record
                    
                    message = "Freezer %s %s located in %s is in a high temperature range at %s degrees Celsius and is above the high temperature setting of %s degrees Celsius.  \n\nThe temperature has been out of range for at least 30 min.  A final alarm will be send when the freezer has been out of range for 1 hour.\n\nPlease go to daena.csbc.vcu.edu to monitor the status of this freezer." % (freezer, name, location, currentTemp, setpoint1)

                    subject = 'High Alarm for %s' % name
                    
                    self.sendMessage(emailList, subject, message)
                    
                    #send new alarm  "Alarm level 1 freezer is out of normal 
                    #range: freezer is currently currentTemp, freezerLocation, 
                    #freezerName, freezerGroup, setpoiont1, link to 
                    #daena.csbc.vcu.edu"
                    
                # if temperature is coming back down below setpoint2
                elif alarmLevel == 3 or alarmLevel == 4:
                    #print "alarmLevel 5 freezing, time", alarmLevel, alarmTime
                    freezing = self.checkForFreezing(freezer, setpoint2, 10)
                    
                    #print "freezing high", freezing
                    if freezing == 0:
                        #print "alarmLevel 3 or 4 freezing, time", alarmLevel, alarmTime
                        self.newAlarm(freezer, 5)
                        
                        readQuery = "SELECT  email FROM contacts, freezer_alarm_contacts WHERE contacts.contact_id = freezer_alarm_contacts.contact_id AND alarm5=1 AND freezer_id=%s"
                    self.readcursor.execute(readQuery, (freezer))
                    emailList = []
                    for record in self.readcursor:
                        if not record: break
                        emailList += record
                    
                    message = "Freezer %s %s located in %s has gone out of a critical temperature range and is now in a high temperature range at %s degrees Celsius which is below the critical temperature setting of %s and is above the high temperature setting of %s degrees Celsius.  \n\nThe temperature has been in the high temperature range for 30 minutes.  \n\nPlease go to daena.csbc.vcu.edu to monitor the status of this freezer." % (freezer, name, location, currentTemp, setpoint2, setpoint1)

                    subject = 'Freezer %s Out of Critical Range Notice' % name
                    
                    self.sendMessage(emailList, subject, message)

        elif currentTemp < setpoint1:
            #print "current temp normal", currentTemp, setpoint1
            if alarmLevel == 0:
                #print "alarmLevel 0", alarmLevel
                pass
            else:
                #print "alarmLevel not 0", alarmLevel
                freezing = self.checkForFreezing(freezer, setpoint2, 15)
                
                #print "freezing normal", freezing
                # freezer has gone back into normal range
                if freezing == 0:
                    #print "freezing back to normal, time", alarmLevel, alarmTime
                    self.newAlarm(freezer, 0)
                    
                    readQuery = "SELECT  email FROM contacts, freezer_alarm_contacts WHERE contacts.contact_id = freezer_alarm_contacts.contact_id AND alarm0=1 AND freezer_id=%s"
                    self.readcursor.execute(readQuery, (freezer))
                    emailList = []
                    for record in self.readcursor:
                        if not record: break
                        emailList += record
                    
                    message = "Freezer %s %s located in %s has back into a normal range at %s degrees Celsius which is below the high temperature setting of %s degrees Celsius.  \n\nThe temperature has been in normal range for 15 minutes.  \n\nPlease go to daena.csbc.vcu.edu to monitor the status of this freezer." % (freezer, name, location, currentTemp, setpoint2, setpoint1)

                    subject = 'Freezer %s Back in Normal Range Notice' % name
                    
                    self.sendMessage(emailList, subject, message)
                    
                else:
                    pass
                    #print "freezing not yet back to normal, time", alarmLevel, alarmTime
            
                    #send alarm "Alarm level 0 freezer is back in normal range: 
                    #freezer is currently currentTemp, freezerLocation, 
                    #freezerName, freezerGroup, setpoiont1, link to 
                    #daena.csbc.vcu.edu"
                
         
    def newAlarm(self, freezer, alarmLevel):
        #print "newAlarm", freezer, alarmLevel
        # get the time for the new alarm
        alarmTime = time.time()
        
        # prepare to create new entry into the alarm table
        writeQuery = ("""insert into alarm set freezer_id = %s, alarm_level = %s, alarm_time = %s""")  
        something = self.writecursor.execute(writeQuery, (freezer, alarmLevel, alarmTime))
        #print "something from write", something
        # get the alarm_id for the new alarm
        readQuery = ("""select alarm_id from alarm where alarm_time = %s""")
        self.readcursor.execute(readQuery, (alarmTime))
        data = self.readcursor.fetchall()
        #print "newAlarm data new created alarm_id", data
        alarmID = data[0][0]
        
        # update the freezer with the new alarm state
        writeQuery = ("""update freezers set freezer_alarm_id = %s where freezer_id = %s""")
        self.writecursor.execute(writeQuery, (alarmID, freezer))
        
        # Check to verify the data will be correct
        readQuery = ("""select freezer_alarm_id from freezers where freezer_id = %s""")
        self.readcursor.execute(readQuery, (freezer))
        data = self.readcursor.fetchall()
        #print "newAlarm data confirm alarm_id", data
        if alarmID == data[0][0]:
            # commit the data to the database
            self.conn.commit()
        else:
            #need to figure out what to do
            pass
            
         
    def checkForNoAlarm(self, freezer, setpoint, minutes):
        # checks if there is need for alarm, if the temp has been above the 
        #setpoint then noAlarm = 0, if temp has been below setpoint noAlarm = 1
        # this takes freezerID, setpoint temperature, and number of minutes
        
        #print "checkForNoAlarm freezer, setpoint, minutes", freezer, setpoint, minutes
        
        # select last (minutes) of temperatures from database to check if they 
        #are all above (setpoint)
        query = ("SELECT temp FROM data WHERE freezer_id = %s and data.time > %s")
        # executing the query with two variables.
        self.readcursor.execute(query, (freezer, time.time()-(60*minutes)))

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
        if noData/recordCount > .75:
            return noData
        else:
            return noAlarm
        
        
    def checkForFreezing(self, freezer, setpoint, minutes):
        # checks if the freezer has come below the threshold to update the alarm
        # freezing = 0, not freezing = 1
        # this takes freezerID, setpoint temperature, and number of minutes
        
        #print "checkForFreezing freezer, setpoint, minutes", freezer, setpoint, minutes
        # select last (minutes) of temperatures from database to check if they 
        #are all above (setpoint)
        query = ("SELECT temp FROM data WHERE freezer_id = %s and data.time > %s")
        # executing the query with two variables.
        self.readcursor.execute(query, (freezer, time.time()-(60*minutes)))

        # variable to indicate if the temperature has been below the appropriate 
        #threshold 0 = all temp above threshold, 1 = at least 1 reading below 
        #threshold
        freezing = 0
        noData = 0
        recordCount = 0
        for record in self.readcursor:
            recordCount += 1
            #print "freezing record loop, record < setpoint, freezing", record[0], setpoint, freezing
            #if not record: break
            if record[0] == "nodata":
                noData += 1
            elif float(record[0]) > setpoint:
                freezing = 1
                
        # if more that 75% of the records are "nodata" then it does not pass for 
        # freezing
        if noData/recordCount > .75:
            return noData
        else:
            return freezing
    
    # Communication Alarms
    
    def checkComAlarm(self, freezer):
        #print "checkComAlarm", freezer
        # checks and sets communication alarm
        
        readQuery = ("select freezer_alarm_ID from freezers where freezer_id = %s")
        self.readcursor.execute(readQuery, (freezer))
        alarmIDData = self.readcursor.fetchall()
        freezerAlarmID = alarmIDData[0][0]
        
        # get data for most recent alarm level
        readQuery = ("select alarm_time, alarm_level from alarm where alarm_id = %s")
        numResults = self.readcursor.execute(readQuery, (freezerAlarmID))
        
        # check to ensure there was data pulled from the database
        #if numResults == 0:
        #    break
        
        # retrieve the data from the query
        data = self.readcursor.fetchall()
        alarmTime = float(data[0][0])
        alarmLevel = (data[0][1])
        #print "alarmTime, alarmLevel:", alarmTime, alarmLevel
        
        allNoData = self.checkForAllNoData(freezer, 30)
        # the past 30 min it was all "nodata"
        if allNoData == 0:
            
            # the com alarm has been silenced
            if alarmLevel == 7:
                pass
            
            # there is a com alarm and it has been active for over an hour
            # send a new alarm
            elif alarmLevel == 6:
                if alarmTime < (time.time()-(60*60)):
                    self.newAlarm(freezer, 6)
                    
                    readQuery = "SELECT  email FROM contacts, freezer_alarm_contacts WHERE contacts.contact_id = freezer_alarm_contacts.contact_id AND alarm6=1 AND freezer_id=%s"
                    self.readcursor.execute(readQuery, (freezer))
                    emailList = []
                    for record in self.readcursor:
                        if not record: break
                        emailList += record
                    
                    message = "The system currently cannot get data for Freezer %s %s located in %s.  \n\nThere are a number of reasons this could happen.\n1. Please check and make sure the probe is connected to the NTMS\n2. Check that the NTMS is connected to the network\n3. There may be a network outage\n\nNote: this will alarm every hour till the problem is fixed.  \n\nPlease go to daena.csbc.vcu.edu to monitor the status of this freezer." % (freezer, name, location)

                    subject = 'Communication Alarm for freezer' % name
                    
                    self.sendMessage(emailList, subject, message)
                    
            # If the freezer is not in a com alarm put it in a com alarm state
            else:
                self.newAlarm(freezer, 6)
        
    
    def checkForAllNoData(self, freezer, minutes):
        # checks if the freezer has had no data for the last x minutes
        # to update the alarm
        # AllNoData = 0, not AllNoData = 1
        # this takes freezerID, and number of minutes
        
        #print "checkForAllNoData freezer, minutes", freezer, minutes
        # select last (minutes) of temperatures from database to check if they are all "nodata"
        query = ("SELECT temp FROM data WHERE freezer_id = %s and data.time > %s")
        # executing the query with two variables.
        self.readcursor.execute(query, (freezer, time.time()-(60*minutes)))

        # variable to indicate if there was all "nodata" 0 = all "nodata", 1 = 
        #at least 1 reading not "nodata"
        allNoData = 0
        for record in self.readcursor:
            #print "allNoData record loop, record = 'nodata' ", record[0], setpoint, allNoData
            #if not record: break
            if record[0] != "nodata":
                allNoData = 1
        return allNoData
    
    
    def sendMessage(self, toList, subject, message):
        # Initilize mail Server to be used
        mailserver = smtplib.SMTP("smtp.gmail.com:587")
        mailserver.ehlo()
        mailserver.starttls()
        mailserver.ehlo()
        mailserver.login("your@email.com", "password")
        sender = 'your@email.com'
        
        header = 'From: %s\n' % sender
        header += 'To: %s\n' % ','.join(emailList)
        header += 'Subject: %s\n\n' % subject
        headerMessage = header+message
        
        #send the message
        mailserver.sendmail(sender, toList, headerMessage)
         
    
    def closeAlarm(self):
        # closes the cursors used by this program
        self.writecursor.close()
        self.readcursor.close()
        self.conn.close ()
