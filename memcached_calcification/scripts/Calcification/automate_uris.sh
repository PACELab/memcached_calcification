# usage: sh automateuris.sh BigTraceFile_to_be_split prefix_for_small_traces #traces logFileTobeCreated

# eg.,sh automate_uris.sh dist_50_50.trace trace_50_50_ 20 uris_d1.log 

bigTraceFile=$1
prefix=$2
partitions=$3
fileName="urifile.log"
logFile=$4

rm -r $fileName
# make a directory to write all the trace files
sudo mkdir $prefix
sudo chmod 777 $prefix
sh /home/ubuntu/generateSplit.sh /home/ubuntu/$bigTraceFile $prefix/$prefix $partitions

for entry in "$prefix"/*
do
  echo $entry
  echo "/memcache_calci_from_traces.php?fileName=$entry">>$fileName
done

tr "\n" "\0" < $fileName > $logFile

# Moving the .log file generated to the loadgen machine
scp -i keys/compass.key $logFile ubuntu@192.168.0.42:/home/ubuntu/ 
