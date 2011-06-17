PesaPI
=======
PesaPI is an unofficial open source API for (paybill) commercial accounts, released under the BSD(lite) license.  
Currently the api is implemented in PHP, Mysql, Curl.


Current status
--------------
The current system should be considered as a proff-of-concept, or alpha version - it works but should not be used in a production setup as is.    
The system works all the way to the local database - but there are still rough edges and things pending.  


System design overview
----------------------
* Does synchroization between Mpesa and a local database.
* Transaction data are available even when main server is down.
* Super easy to utilize for integrators (a goal - not there yet).
* Fast response on historical data.
* Keep the load on servers as low as possible.
* Hopefully more reliable than other APIs.


API Overview
------------
The PesaPi class contains several static methods, these methods are the main interface.

* availableBalance(time) -- returns the balance at a given point in time
* locateByReceipt(receipt) -- returns a payment or null for the given receipt number
* locateByPhone(phone, from, until) -- returns an array of payments from a particular phone
* locateByName(name, from, until) -- returns an array of payments from a particular client name
* locateByAccount(account, from, until) -- returns an array of payments from a particular account-no
* locateByTimeInterval(from, until) -- returns an array of all payments within a given time interval 


Way forward
-----------
The following is a highlevel "todo" list for the project

* Getting the code to release/production quality
* Getting more developers onboard
* "simulator", enables you to develope/test without having a commercial account
* Port to other languages (Java/Python/Ruby/etc)
