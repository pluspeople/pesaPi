/*
 * PesaPi
 * @author Oyugik
 * @author Michael Pedersen
 * @version 0.1
 * @since 2.September 2011
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 * 1. Redistributions of source code must retain the above copyright
 *    notice, this list of conditions and the following disclaimer.
 * 2. Redistributions in binary form must reproduce the above copyright
 *    notice, this list of conditions and the following disclaimer in the
 *    documentation and/or other materials provided with the distribution.
 * 3. Neither the name of PLUSPEOPLE nor the names of its contributors 
 *    may be used to endorse or promote products derived from this software 
 *    without specific prior written permission.
 *	
 * THIS SOFTWARE IS PROVIDED BY THE REGENTS AND CONTRIBUTORS ``AS IS'' AND
 * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 * ARE DISCLAIMED.  IN NO EVENT SHALL THE REGENTS OR CONTRIBUTORS BE LIABLE
 * FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
 * DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS
 * OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION)
 * HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY
 * OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF
 * SUCH DAMAGE.
 */
package PLUSPEOPLE.PesaPi;
import java.util.Date;

public class PesaPi{
		private Date initSyncDate;
		private Date lastSyncSetting;
		private Configuration config;
	 
		public void PesaPi() {
				config = Configuration.instantiate();
		}


		/* This method returns the balance of the mpesa account at the specified point in time.
		 * If there are not transactions later than the specified time, then we can not gurantee 100%
		 * that is is the exact balance - since there might be a transaction prior to the specified time
		 * which we have not yet been informed about.
		 * The specified time is represented in a unix timestamp.
		 */
		public int availableBalance() {
				return availableBalance(new Date());
		}

		public int availableBalance(Date time) {
				// not done
				return 0;
		}

		/* Locate a particular transaction using the unique reciept number
		 * that is send to the mobile user.
		 * It is expected that this method will be the primary metod used 
		 * for e-commerce shops
		 * For extra security you might consider confirming that the phonenumber
		 * of the returned transaction match the users phonenumber.
		 */
		public Payment locateByReciept(String reciept) {
				// not done
				return new Payment();
		}

		/* This method locates all payments performed by a given phonenumber
		 * within a given time-period (all payments from a particular phone).
		 * If at all possible try not to use an until value all the way up 
		 * until now since that will greatly enhance performance. 
		 */
		public Payment[] locateByPhone(String phone) {
				return locateByPhone(phone, new Date(), new Date());
		}

		public Payment[] locateByPhone(String phone, Date from) {
				return locateByPhone(phone, from, new Date());
		}

		public Payment[] locateByPhone(String phone, Date from, Date until) {
				// not done
				return new Payment[0];
		}

		/* this method locates all the payments by a specific client name
		 * within a given time-period. The name is the name that Mpesa
		 * has in its database.
		 * Be alert that mobile users might have there records changed i.e. 
		 * if Safaricom mistyped there name.
		 */
		public Payment[] locateByName(String name) {
				return locateByName(name, new Date(), new Date());
		}

		public Payment[] locateByName(String name, Date from) {
				return locateByName(name, from, new Date());
		}

		public Payment[] locateByName(String name, Date from, Date until) {
				// not done
				return new Payment[0];
		}


		/* When using the paybill metod of a commercial account, the mobile user
		 * enters an account-number. This method locates all payments in which 
		 * a particular account name have been entered within a given timeframe.
		 * Be alert that it is higly likely that users mistype the account 
		 * number: ie. "bb 123" vs. "bb123"
		 */
		public Payment[] locateByAccount(String account) {
				return locateByAccount(account, new Date(), new Date());
		}

		public Payment[] locateByAccount(String account, Date from) {
				return locateByAccount(account, from, new Date());
		}

		public Payment[] locateByAccount(String account, Date from, Date until) {
				// not done
				return new Payment[0];
		}

		/* The method locates all payments within a particular time interval
		 *			 plain and simple.
		 */
		public Payment[] locateByTimeInterval(Date from, Date until, int type) {
				// not done
				return new Payment[0];
		}
		
		/* This method determines the different names that have been registered
		 * using a given phone number
		 */
		public String[] locateName(String phone) {
				// not done
				return new String[0];
		}

		/* This method determines the different phone numbers that have been 
		 * used by a person with a given name.
		 * This might be extended to include someone with a similar name.
		 */
		public String[] locatePhone(String name) {
				// not done
				return new String[0];
		}

		/* This method performs a syncronisation between the safaricom database
		 * and the local database. 
		 * Warning: Although possible, you should never ever have to call this method directly
		 */
		public void forceSyncronisation() {
				// not done
				System.out.println("Syncromizing\n");
		}

		public int getErrorCode() {
				return 0;
		}

		public String getErrorMessage() {
				return "";
		}
}