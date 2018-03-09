# usage: python changeAutomove.py 192.168.0.41 3

import sys
import getopt
reload(sys)
sys.setdefaultencoding('utf-8')

import contextlib
import random
import telnetlib

def main():
	try:
		if(len(sys.argv) != 3):
			print("Need a mem server")
			return;

		with contextlib.closing(telnetlib.Telnet(sys.argv[1], 11211)) as client:
			client.write('slabs automove %s\n' %(sys.argv[2]))
	except:
        	print("Failed to connect")
main()
