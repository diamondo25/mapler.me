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
   public class AppUpdates
    {
        private static string _tempfile;

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
                        _tempfile = Path.GetTempPath() + "maplerdownload.exe";

                        pLabel.Invoke((MethodInvoker)delegate
                        {
                            pLabel.Text = "Downloading update...";
                        });

                        wc.DownloadProgressChanged += wc_DownloadProgressChanged;
                        wc.DownloadFileCompleted += wc_DownloadFileCompleted;
                        wc.DownloadFileAsync(new Uri(url), _tempfile);
                        return "Downloading...";
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

        static void wc_DownloadFileCompleted(object sender, System.ComponentModel.AsyncCompletedEventArgs e)
        {
            MaplerUpdater.frmMain.Instance.label1.Invoke((MethodInvoker)delegate
            {
                MaplerUpdater.frmMain.Instance.label1.Text = "Starting update...";
            });

            Process.Start(_tempfile, "/silent");
            Environment.Exit(100);
        }

        static void wc_DownloadProgressChanged(object sender, DownloadProgressChangedEventArgs e)
        {
            MaplerUpdater.frmMain.Instance.label1.Invoke((MethodInvoker)delegate
            {
                MaplerUpdater.frmMain.Instance.label1.Text = "Download at " + e.ProgressPercentage + "%";
            });
        }
    }
}
