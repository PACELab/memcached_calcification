# Memcached with Custom Auto-Move
A modified Memcached 1.5.1 that allows user to use their own algorithms for auto-move to handle Calcification.

## Dependencies
It only requires all the dependencies for memcahed to run which can be found at: https://github.com/memcached/memcached/wiki/Install 

## How to Use it ?
- Go to **memcached_calcification/slab_automove.c** and implement the algorithm you want to use. 
- Do a makeclean and make.
- To Launch memcached, just to "./memcached" and provide your desired command line arguments (do not change the port, default is 11211)
- Now, to tell memcahced to use your own custom auto-move algorithm, use **scripts/changeAutomove.py** placed under the scripts folder:  
```
$ python changeAutomove <ip_of_machine_running_memc> 3
```
- Congrats! you now have memcached running with your own algorithm for automove.

## Some Additional Features
To suppport some advanced auto-move algorithms, we have added some code for more detailed stats about different slabs in memcached. If you are interested in that, feel free to raise a github issue.


