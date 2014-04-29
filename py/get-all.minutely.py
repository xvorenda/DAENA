#!/usr/bin/env python
import os
import time
import sys
import MySQLdb as mdb
import telnetlib
import re
import socket

Terminator = "\n"
SleepCommand = "sleep 1\n"
ReadProbeCommand = "T\n"
conn = mdb.connect(user='tempurify_user', passwd='idontcareaboutpasswordsrightnow', db='tempurify')
writecursor = conn.cursor()
timetime = time.time()

def putData(temp, hex):
    writepingtodb = "INSERT INTO `tempurify`.`data` (`freezer_id`, `time`, `temp`, `temp_cksum`) VALUES ('%s', '%s', '%s', '%s');" % (freezer_id, timetime, temp, hex)
    #print writepingtodb //
    writecursor.execute(writepingtodb);
    conn.commit ()
    return

def checkSum(temp, hex):
    # Need to add a space to the end of the temperature
    temp += " "
    crc = 0

    # Looks at each character in the string
    # Calculates and sums the ascii values for each character
    for byte in temp:
        crc += ord(byte)

    # Take the sum of ascii values and modulo by 256
    #print "crc", crc
    crcmod = crc % 256
    
    # Get the hex value of the modulo value
    #print "crcmod", crcmod
    crchex = '%X' % crcmod

    #print "crchex", crchex
    
    # Check if the value given is equal to the calculated value and return True or False
    if crchex == hex:
    	return True
    else:
    	return False

readcursor = conn.cursor()
readcursor.execute("""SELECT freezer_id,probe_hostport FROM probes WHERE probe_active=1 """)
#result = conn.store_result()
#rowcount = readcursor.rowcount

for record in readcursor:
    #record = readcursor.fetchone()
    
    if not record: break
    probe_clean = record[1]
    freezer_id = record[0]
    ntms_host, host_port = probe_clean.split()
    
    try:
        Telnet = telnetlib.Telnet(ntms_host,host_port,3)
    except socket.timeout:
        putData("nodata", "nodata")
        continue
    
    Telnet.write(SleepCommand)
    Telnet.write(ReadProbeCommand)
    rawtemp = Telnet.read_until(Terminator)
    
    if not rawtemp: 
        putData("nodata", "nodata")
        continue
    
    Telnet.close()
    try:    
        tempread = re.sub('\x1b.*?J', '', rawtemp)
        sensor = tempread.split()
    
        #ping_errorcheck = sensor[1]
        #if not ping_errorcheck: break
        #print "sensor", sensor
    
        temp, hex = sensor
    
    except ValueError:
	putData("nodata", "nodata")
        continue
    
    if checkSum(temp, hex):
        putData(temp, hex)
       #print True
    else:
        putData("nodata", "nodata")
       #print False
    

    #putData(temp, hex)
writecursor.close ()
conn.close ()

