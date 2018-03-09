# This code is to compare the performance of default and custom automoves

#!/bin/bash
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

# Calcification Experiment with default automove(1) and custom automove(3) 
sh automateCalcification_trace.sh -a 1 -d ${DIRECTORY} -wlog1 ${WLOG1} -wlog2 ${WLOG2}
cmd=$(python getSlabsMoved.py 192.168.0.41) 
cmd="echo \"$cmd\"|awk -F \"#\" '{print \$2}'"
echo "Running command"
echo $cmd
sm_1=$(eval $cmd)
echo "Total Slabs Moved"
echo $sm_1

sh automateCalcification_trace.sh -a 3 -d ${DIRECTORY} -wlog1 ${WLOG1} -wlog2 ${WLOG2}
cmd=$(python getSlabsMoved.py 192.168.0.41) 
cmd="echo \"$cmd\"|awk -F \"#\" '{print \$2}'"
echo "Running command"
echo $cmd
sm_3=$(eval $cmd)
echo "Total Slabs Moved"
echo $sm_3

# Plot the comparison between default and custom automoves
python plotGraph_2.py ${DIRECTORY}/calci_move_1.log $sm_1 ${DIRECTORY}/calci_move_3.log $sm_3 ${DIRECTORY}/${DIRECTORY}.svg
