using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;

#region License Information
/*	Copyright (c) 2011, PLUSPEOPLE Kenya Limited. 
		All rights reserved.

		Redistribution and use in source and binary forms, with or without
		modification, are permitted provided that the following conditions
		are met:
		1. Redistributions of source code must retain the above copyright
		   notice, this list of conditions and the following disclaimer.
		2. Redistributions in binary form must reproduce the above copyright
		   notice, this list of conditions and the following disclaimer in the
		   documentation and/or other materials provided with the distribution.
		3. Neither the name of PLUSPEOPLE nor the names of its contributors 
		   may be used to endorse or promote products derived from this software 
		   without specific prior written permission.
		
		THIS SOFTWARE IS PROVIDED BY THE REGENTS AND CONTRIBUTORS ``AS IS'' AND
		ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
		IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
		ARE DISCLAIMED.  IN NO EVENT SHALL THE REGENTS OR CONTRIBUTORS BE LIABLE
		FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
		DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS
		OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION)
		HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
		LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY
		OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF
		SUCH DAMAGE.
 */
#endregion

namespace PLUSPEOPLE.Pesapi
{
	/// <summary>
	/// This is the main interface to the Mpesa API.
	/// Features are collected here for simple interfacing by the user.
	/// </summary>
	public class PesaPi
	{
		protected int initSyncDate = 0;
		protected DateTime lastSyncSetting;
//		pesapiDataContext db = null;


		public PesaPi()
		{
//			initSyncDate = Settings.Default.MpesaInitialSyncDate;
//			lastSyncSetting = SettingFactory.FactoryByName("LastSync").value_date;
//			db = new pesapiDataContext(Settings.Default.pesaPiConnectionString);
		}

		/// <summary>
		/// This method returns the balance of the mpesa account at the specified point in time.
		/// If there are not transactions later than the specified time, then we can not gurantee 100%
		/// that is is the exact balance - since there might be a transaction prior to the specified time
		/// which we have not yet been informed about.
		/// The specified time is represented in a unix timestamp.
		/// </summary>
		/// <param name="time"></param>
		/// <returns></returns>
		public long AvailableBalance(DateTime time)
		{
			if (lastSyncSetting < time)
				{
					this.ForceSyncronisation();
				}

			long balance = 0;
//			long balance = db.Mpesapi_Payments.Where(payments => payments.time <= time).FirstOrDefault().post_balance;

			return balance;
		}


		public MpesaPayment LocateByReceipt(string reciept)
		{
			// Not done
			return null;
		}


		public MpesaPayment[] LocateByPhone(string phone)
		{
			return this.LocateByPhone(phone, null, null);
		}
		public MpesaPayment[] LocateByPhone(string phone, DateTime fromtime)
		{
			return this.LocateByPhone(phone, fromtime, null);
		}
		public MpesaPayment[] LocateByPhone(string phone, DateTime? fromtime, DateTime? until)
		{
			// not done
			return new MpesaPayment[0];
		}



		public MpesaPayment[] LocateByName(string name)
		{
			return this.LocateByName(name, null, null);
		}
		public MpesaPayment[] LocateByName(string name, DateTime fromtime)
		{
			return this.LocateByName(name, fromtime, null);
		}
		public MpesaPayment[] LocateByName(string name, DateTime? fromtime, DateTime? until)
		{
			// not done
			return new MpesaPayment[0];
		}


		public MpesaPayment[] LocateByAccount(string account)
		{
			return this.LocateByAccount(account, null, null);
		}
		public MpesaPayment[] LocateByAccount(string account, DateTime fromtime)
		{
			return this.LocateByAccount(account, fromtime, null);
		}
		public MpesaPayment[] LocateByAccount(string account, DateTime? fromtime, DateTime? until)
		{
			// not done
			return new MpesaPayment[0];
		}


		public MpesaPayment[] LocateByTimeInterval(DateTime fromtime, DateTime until, int type)
		{
			// not done
			return new MpesaPayment[0];
		}


		public string[] LocateName(string phone)
		{
			// not done
			return new string[0];
		}

		public string[] LocatePhone(string name)
		{
			// not done
			return new string[0];
		}


		public void ForceSyncronisation()
		{
			// not done
		}

		public int GetErrorCode()
		{
			// not done
			return 0;
		}

		public string GetErrorMessage()
		{ 
			// not done
			return ""; 
		}
	}
}
