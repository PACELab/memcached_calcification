#!/bin/bash

# Usage:
#  sh automateCalcification_trace.sh -a 1 -d ${DIRECTORY} -wlog1 ${WLOG1} -wlog2 ${WLOG2}

## Notes:
# This script does the following:
# -> Kills all memcache services on hosts in servers.txt and starts them with specified memory
# -> Using Loadgen and web server populate MC from trace files for first distribution
# -> Change automove settings to either 1(default) or 3(custom) automove 
# -> Using Loadgen and web server populate MC from trace files for second distribution 

# 1. Configure servers.txt to reflect servers to be used.
# 2. Configure memcached service Memory variable $mcMem
#    Command used to start service << sudo ./memcached -d -m $mcMem -r -c 32768 -u ubuntu -o no_chunked_items >>
# 3. We use following cmd to start loadgen:
#	<< ./src/httperf --server 192.168.0.78 --wlog n,$WLOG1 --rate 50 --num-conn 20  >>
# 4. wlog1 is the .log file for distribution 1 and wlog2 is the .log file for distribution 2

while [ $# -gt 1 ]
do
key="$1"

case $key in
    -d|--directory)
    DIRECTORY="$2"
    shift # past argument
    ;;
    -p|--pval)
    PVAL="$2"
    shift # past argument
    ;;
    -wlog1)
    WLOG1="$2"
    shift
    ;;
    -wlog2)
    WLOG2="$2"
    shift
    ;;
    -a|--automove)
    AUTOMOVE="$2"
    shift #past argument
    ;;
    -r|--rate)
    RATE="$2"
    shift #past argument
    ;;
esac
shift
done
echo DIRECTORY  = "${DIRECTORY}"
echo P VALUE     = "${PVAL}"
echo Rate = "${RATE}"
echo AutoMove = "${AUTOMOVE}"

outDir=${DIRECTORY}
pVal=${PVAL}
echo $outDir
echo $pVal
mcMem=20
logLines=200
hrIncr=0.2
sleeptime=10
rate=${RATE}
autoMove=${AUTOMOVE}
outDir=$(pwd)/$outDir
mkdir $outDir

# kill the existing httperf process if any in the loadgen

pgrep -f httperf > out
sed -i 's/^/kill /' out
chmod 777 out
./out

# ssh to all memcached servers, killall memcached, restart the memcached.

for f in `cat /home/ubuntu/Calcification/scripts/servers.txt`;
	 do echo "################### Memcached: $f ################";
	    echo "killing the existing memcached and restarting the memcached"
	    ssh -i ~/keys/compass.key ubuntu@$f "killall memcached;
						 cd /home/ubuntu/mem_calcification/memcached-1.5.1;
						 sudo ./memcached -d -m $mcMem -r -c 32768 -u ubuntu -o no_chunked_items";
	 done

# ssh to web server, truncating mymsg.log
echo "###################### Web1 : 192.168.0.78 ############### ";
echo "truncating mymsg.log"
ssh -i ~/keys/compass.key ubuntu@192.168.0.78 "sudo truncate -s 0 /var/tmp/mymsg.log" 

# Reset Automove Algo
cd /home/ubuntu/memc_calcification/mem_calcification/memcached-1.5.1/scripts/Calcification
python changeAutomove.py 192.168.0.41 0

# start the loadgen
echo "Starting the httperf loadgen from trace files of first distribution";
cd /home/ubuntu/LoadGen/httperf-master/httperf-master
./src/httperf --server 192.168.0.78 --wlog n,$WLOG1 --rate 50 --num-conn 20
cd ~

# kill the existing httperf if any in the loadgen
echo "killing the existing httperf in loadgen"
pgrep -f httperf > out
sed -i 's/^/kill /' out
chmod 777 out
./out


# start the loadgen again
echo "Changing automove as per option given";
# Change Automove Algo
cd /home/ubuntu/memc_calcification/mem_calcification/memcached-1.5.1/scripts/Calcification
python changeAutomove.py 192.168.0.41 $autoMove

echo "Starting the httperf loadgen from trace files of second distribution"
cd /home/ubuntu/LoadGen/httperf-master/httperf-master
./src/httperf --server 192.168.0.78 --wlog n,$WLOG2 --rate 50 --num-conn 20
cd ~

# kill the existing httperf if any in the loadgen
echo "kill the existing httperf in the loadgen"
pgrep -f httperf > out
sed -i 's/^/kill /' out
chmod 777 out
./out

logFile="calci_move_"
logFile="$logFile$autoMove.log"

ssh -i ~/keys/compass.key ubuntu@192.168.0.78 "cat /var/tmp/mymsg.log | awk '{date[\$2]=\$1;hits[\$2]+=\$4; rt[\$2]+=((\$4/100*\$6)+((\$8)/100*\$10)); N[\$2]+=1}END{for (i in hits){print date[i],i,hits[i]/N[i],rt[i]/N[i]*1000;} }' | sort -k1,1 -k2,2 | sed 's/ /,/g' > calci_log.txt"

# Moving the data file to loadgen from web1
scp -i ~/keys/compass.key ubuntu@192.168.0.78:/home/ubuntu/calci_log.txt $outDir/$logFile
