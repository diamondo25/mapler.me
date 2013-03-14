using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;

using System.Net;
using System.IO;
using System.Windows.Forms;

using System.Diagnostics;
using System.Threading;

namespace CraftNetTools
{
    class AppUpdates
    {
        public static void Check()
        {
            string ApplicationName = Application.ProductName;
            string ApplicationVersion = Application.ProductVersion;
            try
            {
                if (File.Exists("noupdate.txt")) return;

                HttpWebRequest req = (HttpWebRequest)WebRequest.Create(string.Format("http://direct.craftnet.nl/app_updates/updates.php?appname={0}&appver={1}&v=2", ApplicationName, ApplicationVersion));
                req.Proxy = null;
                req.Timeout = 10000; // 10 seconds

                using (HttpWebResponse response = req.GetResponse() as HttpWebResponse)
                using (StreamReader sr = new StreamReader(response.GetResponseStream()))
                {
                    string responseText = sr.ReadLine();
                    if (responseText.StartsWith("ERROR:"))
                    {
                        MessageBox.Show(string.Format("Error occurred while checking for new version: {0}", responseText), ApplicationName);
                    }
                    else
                    {
                        string url = sr.ReadLine();
                        string latestVersion = responseText;
                        if (latestVersion != ApplicationVersion)
                        {
                            if (MessageBox.Show(string.Format("A new version is released!\r\nVersion: {0}\r\n\r\nWould you like to visit the downloads page?", latestVersion), ApplicationName, MessageBoxButtons.OKCancel) == DialogResult.OK)
                            {
                                Process.Start(url);
                            }
                        }
                    }
                }

            }
            catch
            {
            }
        }
    }
}
