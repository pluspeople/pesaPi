using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;

namespace PLUSPEOPLE.Pesapi
{
	public class Configuration
	{
		private static Configuration singleton ;
		private Dictionary<string, string> configs = new Dictionary<string, string>();

		private Configuration()
		{
			configs.Add("ProductionMode", "off");
			configs.Add("SimulationMode", "on");
			configs.Add("AllowAutoUpdate", "on");
			configs.Add("AllowFeedback", "on");
			configs.Add("MaxCompatibility", "on");
			configs.Add("MpesaCertificatePath", "");
			configs.Add("MpesaLoginName", "");
			configs.Add("MpesaCorporation", "");
			configs.Add("MpesaInitialSyncDate", "");
			configs.Add("CookieFolderPath", "");
			configs.Add("DatabaseHostRead", "localhost");
			configs.Add("DatabaseUserRead", "");
			configs.Add("DatabasePasswordRead", "");
			configs.Add("DatabaseDatabaseRead", "");
			configs.Add("DatabaseHostWrite", "localhost");
			configs.Add("DatabaseUserWrite", "");
			configs.Add("DatabasePasswordWrite", "");
			configs.Add("DatabaseDatabaseWrite", "");
			configs.Add("Version", "0.0.3");
		}


		public string GetConfig(string configName)
		{
			return this.configs[configName];
		}

		public void SetConfig(string configName, string value)
		{
			if (this.configs.ContainsKey(configName))
				{
					this.configs[configName] = value;
				} 
			else
				{
					this.configs.Add(configName, value);
				}
		}

		public static Configuration Instantiate()
		{
			if (null == Configuration.singleton)
				{
					Configuration.singleton = new Configuration();
				}
			return Configuration.singleton;
		}
	}
}
