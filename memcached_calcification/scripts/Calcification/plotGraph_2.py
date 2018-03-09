import sys

import matplotlib
matplotlib.use('Agg')
import matplotlib.pyplot as plt

def main():

    if(len(sys.argv) != 6):
        print("Error length of arguments")
        return

    fInput = open(sys.argv[1], 'r')

    vals1=[]

    vals2=[]

    for line in fInput:

        lineSet = line.split(',')

        hitRate = float(lineSet[2])

        responseTime = float(lineSet[3])

        if(hitRate > 20 and hitRate < 100):

            vals1.append(hitRate)

        if(responseTime > 0 and responseTime < 60):

            vals2.append(responseTime)

        
    fInput2 = open(sys.argv[3], 'r')

    vals21=[]

    vals22=[]

    for line in fInput2:

        lineSet = line.split(',')

        hitRate = float(lineSet[2])

        responseTime = float(lineSet[3])

        if(hitRate > 20 and hitRate < 100):

            vals21.append(hitRate)

        if(responseTime > 0 and responseTime < 60):

            vals22.append(responseTime)


    fig = plt.figure(1)

    plt.subplot(211)

    x_series = range(0, len(vals1))

    x_series2 = range(0, len(vals21))

    plt.xlabel('Time')

    plt.ylabel('hit rate')

    title1 = "Vanilla-AutoMove_sm_"
    
    title1 = title1 + sys.argv[2]
    
    title2 = "Custom-AutoMove_sm_"
    
    title2 = title2 + sys.argv[4]

    plt.plot(x_series, vals1, label = title1)
    
    plt.plot(x_series2, vals21, label = title2)

    plt.legend(loc='best')

    plt.subplot(212)

    x_series = range(0, len(vals2))

    x_series2 = range(0, len(vals22))

    plt.xlabel('Time')

    plt.ylabel('Response Time')

    plt.plot(x_series, vals2, label = title1)
    
    plt.plot(x_series2, vals22, label = title2)

    plt.legend(loc='best')

    # plt.show()
    # argv[5] is the name of the graph .svg or .pdf or .fig .....any format is supported
    print(sys.argv[5])

    fig.savefig(sys.argv[5], dpi=fig.dpi)

main()
