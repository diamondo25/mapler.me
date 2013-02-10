using System;
using System.Collections.Generic;
using System.ComponentModel;
using System.Data;
using System.Drawing;
using System.Linq;
using System.Text;
using System.Windows.Forms;

using Microsoft.Win32;

namespace Mapler_Client
{
    public partial class frmMain : Form
    {
        private frmStats _statScreen = null;
        private string _mapleEXE = null;

        public frmMain()
        {
            InitializeComponent();
        }

        private void Form1_Load(object sender, EventArgs e)
        {
            try
            {
                // Get executable path
                if (Registry.LocalMachine.OpenSubKey("SOFTWARE").OpenSubKey("Wizet") != null &&
                    Registry.LocalMachine.OpenSubKey("SOFTWARE").OpenSubKey("Wizet").OpenSubKey("MapleStory") != null &&
                    Registry.LocalMachine.OpenSubKey("SOFTWARE").OpenSubKey("Wizet").OpenSubKey("MapleStory").GetValue("Executable") != null)
                {
                    _mapleEXE = (string)Registry.LocalMachine.OpenSubKey("SOFTWARE").OpenSubKey("Wizet").OpenSubKey("MapleStory").GetValue("Executable");
                }

                CraftNetTools.AppUpdates.Check();

                ServerConnection.Initialize();
                GMSKeys.Initialize();
                Sniffer.Init();
            }
            catch (Exception ex)
            {
                MessageBox.Show(string.Format("Error while initializing stuff!\r\n\r\n{0}", ex.ToString()), "ERROR");
                Environment.Exit(1);
            }
        }

        private void frmMain_FormClosing(object sender, FormClosingEventArgs e)
        {
            Program.Closing = true;
            if (Sniffer.Instance != null)
            {
                Sniffer.Instance.Stop();
            }
            if (ServerConnection.Instance != null)
            {
                ServerConnection.Instance.Dispose();
            }
        }

        private void panel1_Click(object sender, EventArgs e)
        {
            if (_statScreen == null)
            {
                _statScreen = new frmStats();
                _statScreen.FormClosing += (a, b) =>
                {
                    _statScreen = null;
                };
                _statScreen.Show();
            }
            else
            {
                _statScreen.Focus();
            }
        }

        private void btnLaunch_Click(object sender, EventArgs e)
        {
            if (_mapleEXE == null || !System.IO.File.Exists(_mapleEXE))
            {
                if (MessageBox.Show("Couldn't find the MapleStory installation. Do you want to select MapleStory.exe yourself?", "Mapler.me", MessageBoxButtons.YesNo) == System.Windows.Forms.DialogResult.Yes)
                {
                    System.Windows.Forms.OpenFileDialog ofd = new System.Windows.Forms.OpenFileDialog();
                    ofd.FileName = "MapleStory.exe";
                    ofd.Filter = "MapleStory Executable|MapleStory.exe";
                    if (ofd.ShowDialog() == System.Windows.Forms.DialogResult.OK)
                    {
                        _mapleEXE = ofd.FileName;
                        var root = System.IO.Path.GetPathRoot(_mapleEXE);
                        Registry.SetValue("HKEY_LOCAL_MACHINE\\SOFTWARE\\Wizet\\MapleStory", "Executable", _mapleEXE);
                        Registry.SetValue("HKEY_LOCAL_MACHINE\\SOFTWARE\\Wizet\\MapleStory", "ExecPath", root);
                        Registry.SetValue("HKEY_LOCAL_MACHINE\\SOFTWARE\\Wizet\\MapleStory", "RootPath", root);
                    }
                    else
                    {
                        return;
                    }
                }
                else
                {
                    return;
                }
            }
            var procs = System.Diagnostics.Process.GetProcessesByName("MapleStory");
            if (procs.Length != 0)
            {
                if (MessageBox.Show("There seems to be one or multiple MapleStory clients running. Do you want to close these before opening another one?", "Mapler.me", MessageBoxButtons.YesNo) == System.Windows.Forms.DialogResult.Yes)
                {
                    foreach (var proc in procs)
                    {
                        try
                        {
                            proc.Kill();
                        }
                        catch (Exception ex)
                        {
                            MessageBox.Show("Unable to close! Error: " + ex.ToString());
                        }
                    }
                }
            }
            System.Diagnostics.Process.Start(_mapleEXE, "GameLaunching");
        }
    }
}
