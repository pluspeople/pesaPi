The Java port of PesaPi is currently at a very very early state - mainly it is just the inital structure that have been created.

In other words A LOT of help is needed if this port is to get to a production level.

Currently the java-port is using Makefile (ask google if you are in doubt) for build-management so all you need to do is to enter the main java directory and type "make" and the code will be build.

Example:
$ cd java
$ make
javac -d build/ source/PLUSPEOPLE/PesaPi/Configuration.java source/PLUSPEOPLE/PesaPi/PesaPi.java source/PLUSPEOPLE/PesaPi/Payment.java
Note: source/PLUSPEOPLE/PesaPi/Configuration.java uses unchecked or unsafe operations.
Note: Recompile with -Xlint:unchecked for details.


If you want to see an example running - you just enter into an example directory and type "make run" example:

$ cd examples/simple_syncronisation/
$ make run
javac -classpath ../../build/ example.java
java -classpath ../../build:. example
Syncromizing

