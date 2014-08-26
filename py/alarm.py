#!/usr/bin/env python
import os
import time
import sys
import MySQLdb as mdb
import re

class alarm(object)

    def __init__(self)
        #variables (stuff from get all script)
        conn = mdb.connect(user='daena_user', passwd='idontcareaboutpasswordsrightnow', db='daena_db')
        writecursor = conn.cursor()
        timetime = time.time()

        #readcursor = conn.cursor()
        # select currently active probes from database
        #readcursor.execute("""SELECT freezer_id,probe_hostport FROM probes WHERE probe_active=1 """)
        
        
    def checkTemp(self, freezer, currentTemp)    
        select setpoint2, setpoint1, freezerAlarmID, from freezers where freezer = freezerID
        select alarmLevel, alarmTime from alarm where freezerAlarmID = alarmID
        if currentTemp > setpoint2
            # checking to see if the last 10 min of temps were above setpoint2
            select temp from data where freezerID = freezer and time < 10min
            noAlarm = 0
            for minOfTemp in tenMinOfTemps:
                if minOfTemp < setpoint2:
                    # noAlarm = 1 if there are temperatures below setpoint2
                    noAlarm = 1
            if noAlarm = 0
                ### need to think about :if alarmTime > 90 min from current time
                # silenced alarm
                if alarmLevel = 4
                    pass
                #constant alarm every 60min
                elif alarmLevel = 3 and alarmTime is > 60min from now
                    send new Alarm "Alarm level 3 freezer is in very critical range: freezer is currently currentTemp, freezerLocation, freezerName, freezerGroup, setpoiont2, link to daena.csbc.vcu.edu"
        elif currentTemp is above setpoint1
            if last 30 min of temps is above setpoint1
                # if freezer stays between setpoint1 and setpoint2 then it will not alarm after the first two alarms
                if alarmLevel = 2 
                    pass
                # freezer has been out of range for 1 hour
                elif alarmLevel = 1 and alarmTime is > 30 min from now
                    send new alarm  "Alarm level 2 freezer is out of normal range for an hour: freezer is currently currentTemp, freezerLocation, freezerName, freezerGroup, setpoiont1, link to daena.csbc.vcu.edu"
                    set alarmLevel = 2
                # freezer has been out of range for 30 min, this is the first alarm
                elif alarmLevel = 0 and alarmTime is > 30 min from now
                    send new alarm  "Alarm level 1 freezer is out of normal range: freezer is currently currentTemp, freezerLocation, freezerName, freezerGroup, setpoiont1, link to daena.csbc.vcu.edu"
                    set alarmLevel = 1
                # if temperature is coming back down below setpoint2
                elif alarmLevel = 3 or alarmLevel = 4 and last 10 temps is below setpoint2
                    send new alarm  "Alarm level 5 freezer is cooling and is out of critical range but still out of normal range: freezer is currently currentTemp, freezerLocation, freezerName, freezerGroup, setpoiont2, link to daena.csbc.vcu.edu"
                    set alarmLevel = 5
        elif currentTemp < setpoint1
            # freezer has gone back into normal range
            if last 10 min of temps is below setpoint1 and alarmLevel > 0
                send alarm "Alarm level 0 freezer is back in normal range: freezer is currently currentTemp, freezerLocation, freezerName, freezerGroup, setpoiont1, link to daena.csbc.vcu.edu"
                set alarmLevel = 0
            