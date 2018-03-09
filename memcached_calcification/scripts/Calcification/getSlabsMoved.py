import sys
import getopt
reload(sys)
sys.setdefaultencoding('utf-8')

import contextlib
import random
import telnetlib

def main():
	try:
		if(len(sys.argv) != 2):
			print("Need a mem server")
			print("#")
			return -1

		with contextlib.closing(telnetlib.Telnet(sys.argv[1], 11211)) as client:
			print("Connecting to %s" %sys.argv[1])
			client.write('stats\n')
			result = client.read_until('END');
			parsedLines = result.split("\r\n");
			for line in parsedLines:
				values = line.split(" ");
				if values[1] == "slabs_moved":
					evics = values[2]
					break;
			print("Closing Connection, evictions:%s" %evics);
			print("#")
			return int(evics);
	except:
        	print("Failed to connect")
		print("#")
		return -1


print(main());
