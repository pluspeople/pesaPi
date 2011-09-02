/*
 * Configuration
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
import java.util.Hashtable;

public class Configuration{
    private static Configuration config;
    private Hashtable values = new Hashtable();

    public static Configuration instantiate() {
        if (config == null) {
            config = new Configuration();
        }    
        return config;
    }

    public String getConfig(String argument) {
        return (String)values.get(argument);
    }

    private Configuration() {
				// Enable this feature when in production - in order to disable debuginformation
        values.put("ProductionMode", "yes");

				// Enable this feature when you want to run the API against the simulator
				// The simulator does not use SSL and is more easy to get up and running.
        values.put("SimulationMode", "yes");

				// Enabling this will allow the system to automatically
				// update the scrubbing methods in use.
				// Hereby ensuring the system will keep running with
				// minimum downtime, in case of Safaricom changing any code.
				values.put("AllowAutoUpdate", "yes");

				// Enabling this feature allows the system to give feedback regarding
				// errors, problems and performance.
				// feedback to the developers of Mpesapi - hereby making it
				// possible to better analyse how to improve the system further.
				values.put("AllowFeedback", "yes");

				// To ensure the system is a robust as possible you want
				// to keep this feature active - By doing so you enable
				// method triangulation to ensure it fallsback to a different
				// method in case one fails.
				// the downside is slower performance.
				values.put("MaxCompatibility", "yes");

				// Mpesa information
				values.put("MpesaCertificatePath", "change_this_path.pem");
				values.put("MpesaLoginName", "test");
				values.put("MpesaPassword", "best");
				values.put("MpesaCorporation", "PesaPi");
				values.put("MpesaInitialSyncData", "2011-01-01");
				values.put("CookieFolderPath", ".");

				// Database settings follow - please note that they are repeated twice
				values.put("DatabaseHostRead", "localhost");
				values.put("DatabaseUserRead", "root");
				values.put("DatabasePasswordRead", "***PASSWORD***");
				values.put("DatabaseDatabaseRead", "pesaPi");
				values.put("DatabaseHostWrite", "localhost");
				values.put("DatabaseUserWrite", "root");
				values.put("DatabasePasswordWrite", "***PASSWORD***");
				values.put("DatabaseDatabaseWrite", "pesaPi");

				values.put("Version", "0.0.0");
    }
}
