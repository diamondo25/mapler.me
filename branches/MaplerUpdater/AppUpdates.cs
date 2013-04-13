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
        public static string Check(Label pLabel)
        {
            // Get Mapler.me assembly
            FileVersionInfo fvi = FileVersionInfo.GetVersionInfo(Environment.CurrentDirectory + "\\Mapler Client.exe");

            string ApplicationName = "Mapler_Client";
            string ApplicationVersion = fvi.ProductVersion;
            try
            {
                WebClient wc = new WebClient();
                string responseText = wc.DownloadString(string.Format("http://direct.craftnet.nl/app_updates/updates.php?appname={0}&appver={1}&v=2", ApplicationName, ApplicationVersion));
                string[] lines = responseText.Split(new string[] { "\r\n" }, StringSplitOptions.None);

                if (lines[0].StartsWith("ERROR:"))
                {
                    MessageBox.Show(string.Format("Error occurred while checking for new version: {0}", responseText), ApplicationName);
                }
                else
                {
                    pLabel.Invoke((MethodInvoker)delegate
                    {
                        pLabel.Text = "Received info...";
                    });
                    string latestVersion = lines[0];
                    string url = lines[1];
                    if (latestVersion != ApplicationVersion)
                    {
                        // Download version
                        string tempfile = Path.GetTempPath() + "maplerdownload.exe";

                        pLabel.Invoke((MethodInvoker)delegate
                        {
                            pLabel.Text = "Downloading update...";
                        });

                        wc.DownloadFile(url, tempfile);

                        pLabel.Invoke((MethodInvoker)delegate
                        {
                            pLabel.Text = "Starting update...";
                        });

                        Process.Start(tempfile, "/silent");
                        Environment.Exit(100);
                    }
                    else
                    {
                        return "Boot";
                    }
                }
            }
            catch
            {
            }

            return "Boot";
        }
    }
}
