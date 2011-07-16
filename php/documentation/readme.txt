General information:
--------------------
PesaPI is an open source API for commercial accounts.

In other words you need to have an (paybill) commercial account in order to use this API.
Once you have a commercial account, you can access the account though this API, using the digital certificate that Mpesa provides you.

Current status:
---------------
The current system should be considered as a proff-of-concept, or alpha version.
The system works all the way to the local database - but there are still rough edges and things pending.
In any case, before using it in any production setup - you should carefully test and validate that it actually performs as you expect.



************************
Developer information 
************************

Design goal of the application.

1. As robust as humanly possible for a scrubber based API.
   Consering a scrubber based api is generally not very unreliabe, due to changes in the original output.

2. Simple to use api on which to build applications ontop.
